import SignaturePad from "signature_pad";

export default function ($wire,$refs) {
    return {
        signaturePad:null,
        signature:null,
        init() {
            this.signaturePad = new SignaturePad($refs.canvas,{backgroundColor: 'rgba(255, 255, 255, 1)'});
        },
        clear() {
            this.signaturePad.clear();
            this.signature = null;
        },
        async save() {
            if(this.signaturePad.isEmpty()) return;
            this.signature =  await (await fetch(this.signaturePad.toDataURL())).blob();
            $wire.upload('signature.file',this.signature, async (response) => {
                $wire.save();
                this.signature = null;
                this.signaturePad.clear();
            });
        },
        // TODO: Add resizeCanvas method to resize listener
        resizeCanvas() {
            this.ratio = Math.max(window.devicePixelRatio || 1, 1);
            $refs.canvas.width = $refs.canvas.offsetWidth * this.ratio;
            $refs.canvas.height = $refs.canvas.offsetHeight * this.ratio;
            $refs.canvas.getContext('2d').scale(this.ratio, this.ratio);
        }
    }
}
