/**
 * Keeps a table's column labels in view while the page scrolls.
 *
 * Plain sticky cannot do it: a sticky element binds to its nearest scrolling
 * ancestor, and that is the overflow-x wrapper around the table. The wrapper
 * only ever scrolls sideways, so the head would stick to something that never
 * moves vertically. Dropping that overflow would move the table's horizontal
 * scrolling onto the page, taking the toolbar out of view to the right.
 *
 * Nudging the head on every scroll event is no good either: the events arrive
 * after the frame has been painted, so the head visibly trails one frame
 * behind.
 *
 * Nothing here runs per frame therefore. The shift is an animation driven by
 * the page's scroll position, which runs on the compositor and cannot lag.
 * Only the three numbers behind it come from JavaScript: from where, to where,
 * how far. Those change when rows arrive or the window resizes, not while
 * scrolling.
 *
 * Where that timeline is missing, the same three numbers drive the shift from a
 * scroll listener instead. It is the lagging variant described above, and it
 * stays the second choice for that reason, but a head that trails by a frame is
 * still a head, whereas the animation alone leaves nothing at all: a browser
 * drops a property it cannot parse without a word, so the labels would simply
 * scroll away and no one would know why.
 */
const hasScrollTimeline = CSS.supports(
    'animation-timeline: scroll(root block)',
);

const STYLES = `
    [tall-datatable] thead tr:first-child {
        position: relative;
        z-index: 9;
    }

    /*
     * The row does carry a bottom border, but it is of no use while it floats:
     * with border-collapse the table paints the borders rather than the cell,
     * so they stay behind as the row travels. An inset shadow belongs to the
     * cell and moves along.
     */
    [tall-datatable] thead tr:first-child > * {
        border-bottom-width: 0;
        box-shadow: inset 0 -1px 0 var(--color-gray-200);
    }

    .dark [tall-datatable] thead tr:first-child > * {
        box-shadow: inset 0 -1px 0 var(--color-secondary-700);
    }
`;

/*
 * Only ever handed to a browser that has the timeline. The animation carries no
 * duration, so anywhere else it would count as finished the moment it starts
 * and park the head at the far end of its travel, in the middle of the table.
 */
const TIMELINE_STYLES = `
    [tall-datatable] thead tr:first-child {
        animation: flux-table-head linear both;
        animation-timeline: scroll(root block);
        animation-range: var(--flux-head-start, 0px) var(--flux-head-end, 0px);
    }

    @keyframes flux-table-head {
        to {
            transform: translateY(var(--flux-head-travel, 0px));
        }
    }
`;

/**
 * The line the head comes to rest on: below everything that stays up there
 * itself, otherwise the head slides behind the search bar.
 *
 * What counts is bars, not overlays: a box covering half the screen is a
 * notification area and must not push the head down. Measured once and kept,
 * the height of a bar does not change while scrolling.
 */
let edge = 0;

const measureTopEdge = () => {
    const limit = window.innerHeight / 3;

    edge = 0;

    document.querySelectorAll('body *').forEach((element) => {
        const style = getComputedStyle(element);

        if (
            (style.position !== 'fixed' && style.position !== 'sticky') ||
            style.pointerEvents === 'none' ||
            style.visibility === 'hidden'
        ) {
            return;
        }

        const rect = element.getBoundingClientRect();

        if (
            rect.top <= 1 &&
            rect.bottom > 1 &&
            rect.height <= limit &&
            rect.width > 200
        ) {
            edge = Math.max(edge, rect.bottom);
        }
    });
};

/**
 * The shift is tied to the page's scroll position. For a table inside a dialog
 * or a scroll area of its own that would be the wrong reference, its head would
 * set off while nothing beneath it moves.
 */
const followsPage = (table) => {
    for (
        let node = table.parentElement;
        node && node !== document.body;
        node = node.parentElement
    ) {
        const style = getComputedStyle(node);

        if (style.position === 'fixed') {
            return false;
        }

        if (
            style.overflowY !== 'visible' &&
            node.scrollHeight > node.clientHeight + 1
        ) {
            return false;
        }
    }

    return true;
};

const measure = (table) => {
    const head = table.querySelector('thead');
    const body = table.querySelector('tbody');

    if (!head || !body) {
        return;
    }

    // Only the labels travel. The filter row below them is semi transparent,
    // floating it would let the rows shine through, so it stays put and scrolls
    // away like everything else.
    const labels = head.rows[0];

    if (!labels) {
        return;
    }

    if (!followsPage(table)) {
        labels.style.setProperty('--flux-head-travel', '0px');

        return;
    }

    const headRect = head.getBoundingClientRect();
    const bodyRect = body.getBoundingClientRect();

    // A table that fits between the bar and the lower edge of the screen has
    // nothing that could scroll underneath its labels. Letting them travel
    // anyway would only lay the row over the few rows there are, and with a
    // single one it covers the entire table.
    if (bodyRect.bottom - headRect.top <= window.innerHeight - edge) {
        labels.style.setProperty('--flux-head-travel', '0px');

        return;
    }

    // The row is shifted, the head around it is not: its top edge is where the
    // row would sit without the shift.
    const height = labels.offsetHeight;

    // Past this point the row would have left the top of the screen.
    const start = Math.max(0, headRect.top + window.scrollY - edge);

    // And this is how far it may travel: until its bottom edge meets the end of
    // the table. After that it leaves with the table instead of hovering over
    // whatever comes next.
    const travel = bodyRect.bottom - height - headRect.top;

    labels.style.setProperty('--flux-head-start', `${start}px`);
    labels.style.setProperty('--flux-head-end', `${start + travel}px`);
    labels.style.setProperty('--flux-head-travel', `${travel}px`);
};

/**
 * Measuring means reading layout, and rows arrive in bursts. Hence at most one
 * measurement per frame, no matter how many changes come in between.
 *
 * A hidden tab paints nothing and therefore never calls back for a frame. A
 * table built there would stay unmeasured and its head would sit still on
 * return. So a timer over there; what gets bundled is only what would occur per
 * frame anyway.
 */
let queued = false;

const measureAll = () => {
    if (queued) {
        return;
    }

    queued = true;

    const run = () => {
        queued = false;

        document.querySelectorAll('[tall-datatable]').forEach((table) => {
            measure(table);

            // Rows can arrive long after the page was scrolled down. With the
            // timeline the animation places the head by itself, without it the
            // fresh numbers have to be applied once.
            if (!hasScrollTimeline) {
                shift(table);
            }
        });
    };

    document.hidden ? setTimeout(run) : requestAnimationFrame(run);
};

/**
 * The fallback. Reads the numbers measure() left behind and turns the page's
 * scroll position into the same translation the animation would perform.
 */
const shift = (table) => {
    const labels = table.querySelector('thead')?.rows[0];

    if (!labels) {
        return;
    }

    const travel = parseFloat(
        labels.style.getPropertyValue('--flux-head-travel'),
    );

    // A table that fits on screen is measured to a travel of zero. Its head
    // belongs where the table put it, so anything left over has to go.
    if (!travel) {
        labels.style.removeProperty('transform');

        return;
    }

    const from =
        parseFloat(labels.style.getPropertyValue('--flux-head-start')) || 0;
    const to =
        parseFloat(labels.style.getPropertyValue('--flux-head-end')) || 0;
    const progress =
        to > from
            ? Math.min(1, Math.max(0, (window.scrollY - from) / (to - from)))
            : 0;

    labels.style.transform = `translateY(${progress * travel}px)`;
};

/**
 * Scroll events arrive in bursts, and each one would read layout again. At most
 * one pass per frame therefore, the same bundling the measurements use.
 */
let shifting = false;

const shiftAll = () => {
    if (shifting) {
        return;
    }

    shifting = true;

    requestAnimationFrame(() => {
        shifting = false;

        document
            .querySelectorAll('[tall-datatable]')
            .forEach((table) => shift(table));
    });
};

// Rows arrive, groups unfold, filters open: all of that moves the table without
// anyone scrolling.
const observer = new MutationObserver(measureAll);

const start = () => {
    measureTopEdge();
    measureAll();
    observer.disconnect();
    observer.observe(document.body, { childList: true, subtree: true });
};

document.head.insertAdjacentHTML(
    'beforeend',
    `<style>${STYLES}${hasScrollTimeline ? TIMELINE_STYLES : ''}</style>`,
);

document.addEventListener('livewire:initialized', start);
document.addEventListener('livewire:navigated', start);

window.addEventListener(
    'resize',
    () => {
        measureTopEdge();
        measureAll();
    },
    { passive: true },
);

if (!hasScrollTimeline) {
    window.addEventListener('scroll', shiftAll, { passive: true });
}
