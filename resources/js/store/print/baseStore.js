export default function () {
    return {
        pxPerCm: 0,
        pyPerCm: 0,
        observer: null,
        _selectedElement: {
            id: null,
            // x and y are just for UI purposes
            x: null,
            y: null,
            ref: null,
            startX: null,
            startY: null,
        },
        visibleElements: [],
        elementsOutOfView: [],
        _component: null,
        onInit(pxPerCm, pyPerCm) {
            if (typeof pyPerCm === 'number' && pyPerCm > 0) {
                this.pyPerCm = pyPerCm;
            } else {
                this.pyPerCm = 37.79527559055118; // 1 cm in pixels, based on 96 DPI
            }

            if (typeof pxPerCm === 'number' && pxPerCm > 0) {
                this.pxPerCm = pxPerCm;
            } else {
                this.pxPerCm = 37.79527559055118; // 1 cm in pixels, based on 96 DPI
            }
        },
    };
}
