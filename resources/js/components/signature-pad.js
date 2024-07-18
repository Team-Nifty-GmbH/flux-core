import SignaturePad from 'signature_pad';

export default function($wire, $refs) {
    return {
        signaturePad: null,
        isEmpty: true,
        existingData: null,
        tempData: null,
        prevWidth: 700,
        debounceId: null,
        error: false,
        id: null,
        async init() {
            // init signature pad
            this.signaturePad = new SignaturePad($refs.canvas, { backgroundColor: 'rgba(255, 255, 255, 1)' });
            // resize event listener
            window.addEventListener('resize', this.resizeCanvas.bind(this));
            this.resizeCanvas();
            // if signature is already saved - just display it on canvas but don't allow to draw
            if ($wire.signature.stagedFiles.length > 0) {
                this.id = $wire.signature.id;
                this.existingData = await $wire.downloadSignatureAsUrlData(this.id);
                await this.signaturePad.fromDataURL(this.existingData);
                this.signaturePad.off();
                return;
            }
            // if signature is not saved - allow to draw and clear after first stroke
            this.signaturePad.addEventListener('afterUpdateStroke', this.strokeHandler.bind(this));
        },
        destroy() {
            window.removeEventListener('resize', this.resizeCanvas.bind(this));
        },
        clear() {
            this.signaturePad.clear();
            this.tempData = null;
            this.isEmpty = true;
        },
        get iconName() {
            if (this.error) {
                return 'exclamation';
            } else {
                return 'check';
            }
        },
        strokeHandler() {
            if (this.isEmpty) {
                this.isEmpty = false;
            }
            // save temp data on stroke end for resize canvas purposes
            if (!this.signaturePad.isEmpty()) {
                this.tempData = this.signaturePad.toDataURL();
            }
        },
        async upload(_) {
            const res = await $wire.save();
            if (res) {
                this.id = $wire.entangle('signature.id');
                this.error = false;
                // clear buttons for save and clean
                this.isEmpty = true;
                // disable signature pad on successful upload
                this.signaturePad.off();
            } else {
                this.error = true;
            }
        },
        async save() {
            if (this.signaturePad.isEmpty()) {
                return;
            }
            this.existingData = this.signaturePad.toDataURL();
            const data = await (await fetch(this.existingData)).blob();
            await $wire.upload('signature.file', data, this.upload.bind(this));
        },
        resizeCanvas() {
            if (window.innerWidth < 700) {
                const width = $refs.canvas.offsetWidth;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                $refs.canvas.width = (width * ratio) - (this.prevWidth - window.innerWidth);
                $refs.canvas.height = $refs.canvas.offsetHeight * ratio;
                this.prevWidth = window.innerWidth;
                const ctx = $refs.canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.fillStyle = 'rgba(255, 255, 255, 1)';
                ctx.fillRect(0, 0, $refs.canvas.width, $refs.canvas.height);
                this.debounce();
            } else if (window.innerWidth >= 700 && this.prevWidth !== 700) {
                this.prevWidth = 700;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                $refs.canvas.width = 500 * ratio;
                $refs.canvas.height = $refs.canvas.offsetHeight * ratio;
                const ctx = $refs.canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.fillStyle = 'rgba(255, 255, 255, 1)';
                ctx.fillRect(0, 0, $refs.canvas.width, $refs.canvas.height);
                this.debounce();
            }
        },
        debounce() {
            if (this.debounceId) {
                clearTimeout(this.debounceId);
            }
            this.debounceId = setTimeout(this.refreshCanvas.bind(this), 500);
        },
        async refreshCanvas() {
            if (this.existingData !== null) {
                await this.signaturePad.fromDataURL(this.existingData);
            } else if (!this.signaturePad.isEmpty() && this.tempData !== null) {
                await this.signaturePad.fromDataURL(this.tempData);
            }
        }
    };
}
