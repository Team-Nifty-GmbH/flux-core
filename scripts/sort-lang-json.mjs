#!/usr/bin/env node
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const check = process.argv.includes('--check');
const langDir = resolve(dirname(fileURLToPath(import.meta.url)), '..', 'lang');

function sortKeys(obj) {
    if (Array.isArray(obj)) return obj.map(sortKeys);
    if (obj && typeof obj === 'object') {
        const sortedEntries = Object.keys(obj)
            .sort((a, b) => {
                const al = a.toLowerCase();
                const bl = b.toLowerCase();
                if (al < bl) return -1;
                if (al > bl) return 1;
                return 0;
            })
            .map((k) => [k, sortKeys(obj[k])]);
        return Object.fromEntries(sortedEntries);
    }
    return obj;
}

const files = readdirSync(langDir)
    .filter((f) => f.endsWith('.json'))
    .map((f) => join(langDir, f));

let drift = false;
for (const file of files) {
    const original = readFileSync(file, 'utf8');

    let parsed;
    try {
        parsed = JSON.parse(original);
    } catch (err) {
        throw new Error(`Failed to parse JSON in "${file}": ${err.message}`);
    }

    const sorted = JSON.stringify(sortKeys(parsed), null, 4) + '\n';

    if (original === sorted) continue;

    if (check) {
        console.error(`[sort-lang-json] ${file} is not sorted`);
        drift = true;
    } else {
        writeFileSync(file, sorted);
        console.log(`[sort-lang-json] sorted ${file}`);
    }
}

if (check && drift) {
    console.error('Run `node scripts/sort-lang-json.mjs` to fix.');
    process.exit(1);
}
