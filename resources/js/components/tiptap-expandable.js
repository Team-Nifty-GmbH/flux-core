export default function () {
    return {
        expanded: false,
        toggle() {
            this.expanded = !this.expanded;
        },
    };
}
