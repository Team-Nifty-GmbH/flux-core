export default function () {
    let links = [
        ...document.querySelectorAll(
            'a[href]:not([wire\\:navigate]):not([target="_blank"])',
        ),
    ].filter((link) => {
        let hrefValue = link.getAttribute("href").trim();
        return (
            hrefValue !== "" &&
            hrefValue !== "#" &&
            (hrefValue.startsWith(window.location.origin) ||
                hrefValue.startsWith("/"))
        );
    });

    links.forEach((link) => {
        link.setAttribute("wire:navigate", "true");
    });
}
