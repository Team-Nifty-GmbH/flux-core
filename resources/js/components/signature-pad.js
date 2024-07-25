import SignaturePad from 'signature_pad';

// resizing canvas - data get lost - hence need for tempData during drawing
// and existingData for already saved signature
export default function($wire, $refs) {
    return {
        signaturePad: null,
        isEmpty: true,
        prevWidth: 700,
        debounceId: null,
        error: false,
        id: null,
        resizeFuncRef: null,
        async init() {
            // init signature pad
            this.signaturePad = new SignaturePad($refs.canvas, { backgroundColor: 'rgba(255, 255, 255, 1)' });

            // if signature is already saved - order->print will inject it
            if ($wire.signature.stagedFiles.length > 0) {
                this.id = $wire.signature.id;
                this.signaturePad.off();
                return;
            }
            // resize func ref - to remove event listener
            this.resizeFuncRef = this.resizeCanvas.bind(this);
            // resize event listener
            window.addEventListener('resize', this.resizeFuncRef);
            this.resizeCanvas();
            // if signature is not saved - allow to draw and clear after first stroke
            this.signaturePad.addEventListener('afterUpdateStroke', this.strokeHandler.bind(this));
        },
        destroy() {
            if (this.resizeFuncRef !== null) {
                window.removeEventListener('resize', this.resizeFuncRef);
            }
        },
        clear() {
            this.signaturePad.clear();
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
        },
        async upload(_) {
            const res = await $wire.save();
            if (res) {
                window.location.reload();
            } else {
                this.error = true;
            }
        },
        async save() {
            if (this.signaturePad.isEmpty()) {
                return;
            }
            const data = await (await fetch(this.signaturePad.toDataURL())).blob();
            await $wire.upload('signature.file', data, this.upload.bind(this));
        },
        resizeCanvas() {
            if (window.innerWidth < 700) {
                // going to small screen - resize canvas to fit the screen
                const width = $refs.canvas.width;
                $refs.canvas.width = (width) - (this.prevWidth - window.innerWidth);
                this.prevWidth = window.innerWidth;
                const ctx = $refs.canvas.getContext('2d');
                ctx.scale(1, 1);
                ctx.fillStyle = 'rgba(255, 255, 255, 1)';
                ctx.fillRect(0, 0, $refs.canvas.width, $refs.canvas.height);
                // redraw signature on resize - since canvas removes all data on resize
                this.debounce();
            } else if (window.innerWidth >= 700 && this.prevWidth !== 700) {
                // going to big screen - resize canvas to default size
                this.prevWidth = 700;
                $refs.canvas.width = 500;
                const ctx = $refs.canvas.getContext('2d');
                ctx.scale(1, 1);
                ctx.fillStyle = 'rgba(255, 255, 255, 1)';
                ctx.fillRect(0, 0, $refs.canvas.width, $refs.canvas.height);
                this.debounce();
            }
        },
        debounce() {
            if (this.debounceId) {
                clearTimeout(this.debounceId);
            }
            this.debounceId = setTimeout(this.refreshCanvas.bind(this), 200);
        },
        async refreshCanvas() {
            if (!this.signaturePad.isEmpty()) {
                this.clear();
            }
        }
    };
}
