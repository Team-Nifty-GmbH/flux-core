// What a user waits for is not the server response. Flux links internally with
// wire:navigate, so a page change is a fetch plus a DOM morph plus Alpine
// initialising, and the last two only happen in a browser. Everything here runs
// in one browser instance against a running application: the page is loaded
// once, the assets stay in the browser cache after that, and every route is
// reached the way a click reaches it.
import { chromium } from 'playwright';
import { readFileSync, writeFileSync } from 'node:fs';

const baseUrl = (process.env.APP_URL ?? 'http://127.0.0.1:8000').replace(
    /\/$/,
    '',
);
const email = process.env.PERFORMANCE_EMAIL;
const password = process.env.PERFORMANCE_PASSWORD;
const budget = Number(process.env.PERFORMANCE_BUDGET_MS ?? 0);
const timeout = Number(process.env.PERFORMANCE_TIMEOUT_MS ?? 15000);
const reportPath = process.env.PERFORMANCE_REPORT ?? 'route-performance.md';

if (!email || !password) {
    throw new Error('PERFORMANCE_EMAIL and PERFORMANCE_PASSWORD are required.');
}

// A route behind a parameter needs a record to point at, and which record that
// is differs per route, so those are left to the tests that know them.
const routes = [
    ...new Set(
        JSON.parse(
            readFileSync(
                process.env.PERFORMANCE_ROUTES ?? 'routes.json',
                'utf8',
            ),
        )
            .filter((route) => route.method.split('|').includes('GET'))
            .filter((route) =>
                (route.middleware ?? []).some((middleware) =>
                    middleware.startsWith(
                        'Illuminate\\Auth\\Middleware\\Authenticate',
                    ),
                ),
            )
            .map((route) => '/' + route.uri.replace(/^\//, ''))
            .filter(
                (path) =>
                    !path.includes('{') &&
                    !path.startsWith('/api/') &&
                    !path.endsWith('/logout'),
            ),
    ),
].sort();

// livewire:navigated fires once the DOM is swapped, before the browser has laid
// the new markup out. Two frames later the paint has happened, which is the
// moment the page stands in front of the user.
async function measure(page, target) {
    await page.evaluate((path) => {
        delete window.fluxRoutePerformance;

        const started = performance.now();

        document.addEventListener(
            'livewire:navigated',
            () =>
                requestAnimationFrame(() =>
                    requestAnimationFrame(() => {
                        window.fluxRoutePerformance = {
                            ms: Math.round(performance.now() - started),
                            rows: document.querySelectorAll(
                                '[tall-datatable] tbody tr',
                            ).length,
                            elements: document.querySelectorAll('*').length,
                        };
                    }),
                ),
            { once: true },
        );

        window.Livewire.navigate(path);
    }, target);

    try {
        await page.waitForFunction(
            () => window.fluxRoutePerformance ?? null,
            null,
            { timeout },
        );
    } catch {
        return {
            route: target,
            ms: timeout,
            rows: 0,
            elements: 0,
            settled: false,
        };
    }

    return {
        route: target,
        ...(await page.evaluate(() => window.fluxRoutePerformance)),
        settled: true,
    };
}

function report(measured, seedSeconds) {
    const settled = measured
        .filter((row) => row.settled)
        .map((row) => row.ms)
        .sort((a, b) => a - b);
    const mean = settled.length
        ? Math.round(settled.reduce((sum, ms) => sum + ms, 0) / settled.length)
        : 0;

    const rows = [...measured]
        .sort((a, b) => b.ms - a.ms)
        .map(
            (row) =>
                `| \`${row.route}\` | ${row.ms} | ${row.rows} | ${row.elements} | ${
                    !row.settled
                        ? 'never finished rendering'
                        : budget > 0 && row.ms > budget
                          ? 'over budget'
                          : ''
                } |`,
        );

    return [
        '# Route performance',
        '',
        `${settled.length} routes, slowest first. Median ${settled[Math.floor(settled.length / 2)] ?? 0} ms, ` +
            `mean ${mean} ms, slowest ${settled[settled.length - 1] ?? 0} ms.` +
            (budget > 0
                ? ` Budget ${budget} ms.`
                : ' No budget set, reporting only.'),
        '',
        'Measured in one browser instance through `wire:navigate`, from the call to the',
        'paint that follows the render, with the assets cached after the first load. The',
        `demo database was seeded in ${seedSeconds} seconds before measuring.`,
        '',
        `${measured.filter((row) => row.rows > 0).length} of ${measured.length} routes rendered rows; the rest show no table or are backed by`,
        `a model the seeder does not reach. ${measured.filter((row) => !row.settled).length} never finished rendering and are listed`,
        'with the time they were given.',
        '',
        '| route | ms | rows | DOM elements | |',
        '| --- | ---: | ---: | ---: | --- |',
        ...rows,
        '',
    ].join('\n');
}

const browser = await chromium.launch();
const page = await browser.newPage({ baseURL: baseUrl });

// The run logs in the way a user does, so the session, its permissions and the
// menu that hangs off them are the ones a user actually gets.
await page.goto(`${baseUrl}/login`);
await page.fill('#email', email);
await page.fill('#password', password);
await page.press('#password', 'Enter');
await page.waitForURL((url) => !url.pathname.startsWith('/login'), { timeout });
await page.waitForFunction(() => window.Livewire ?? null, null, { timeout });

const measured = [];

for (const target of routes) {
    // A route's first visit compiles its blade templates, a cost paid once per
    // deploy rather than per user. The second run is the measurement.
    await measure(page, target);

    const measurement = await measure(page, target);

    process.stdout.write(
        `  ${target.padEnd(45)} ${String(measurement.ms).padStart(6)} ms  ${
            measurement.settled ? '' : 'never finished'
        }  ${measurement.rows} rows\n`,
    );

    measured.push(measurement);
}

await browser.close();

writeFileSync(
    reportPath,
    report(measured, process.env.PERFORMANCE_SEED_SECONDS ?? 'unknown'),
);

const overBudget =
    budget > 0 ? measured.filter((row) => row.settled && row.ms > budget) : [];

if (overBudget.length > 0) {
    process.stdout.write(
        `\n${overBudget.length} routes over the ${budget} ms budget.\n`,
    );
    process.exit(1);
}
