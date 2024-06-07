import SignaturePad from "signature_pad";

export default function ($wire,$refs) {
    return {
        signaturePad:null,
        isEmpty:true,
        publicUrl:null,
        init() {
            // TODO: if signature is already saved - display it - on canvas and disable signature pad
            if($wire.signature.stagedFiles.length > 0) {
                this.publicUrl = $wire.signature.stagedFiles[0].preview_url;
                return;
            }
            this.signaturePad = new SignaturePad($refs.canvas,{backgroundColor: 'rgba(255, 255, 255, 1)'});
            this.signaturePad.addEventListener('afterUpdateStroke', this.strokeHandler.bind(this));
        },
        clear() {
            console.log('clear');
            this.signaturePad.clear();
            this.isEmpty = true;
        },
        strokeHandler(){
          if(this.isEmpty){
            this.isEmpty = false;
          }
        },
        async upload(_) {
            const res = await $wire.save();
            this.clear();
            // TODO: if upload successful - add image to the signature and disable
            // signature pad to prevent further drawing
            if(res !== null) {
                this.publicUrl = res;
            }
        },
        async save() {
            if(this.signaturePad.isEmpty()) return;
            const data =  await (await fetch(this.signaturePad.toDataURL())).blob();
            await $wire.upload('signature.file',data, this.upload.bind(this));
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
