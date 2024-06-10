import SignaturePad from "signature_pad";
import {entangle} from "alpinejs/src/entangle";

export default function ($wire,$refs) {
    return {
        signaturePad:null,
        isEmpty:true,
        error:false,
        id:null,
        async init() {
            // init signature pad
            this.signaturePad = new SignaturePad($refs.canvas,{backgroundColor: 'rgba(255, 255, 255, 1)'});
            // if signature is already saved - just display it on canvas but don't allow to draw
            if($wire.signature.stagedFiles.length > 0) {
                this.id = $wire.signature.id;
                await this.signaturePad.fromDataURL(await $wire.downloadSignatureAsUrlData(this.id));
                this.signaturePad.off()
                return;
            }
            // if signature is not saved - allow to draw and clear after first stroke
            this.signaturePad.addEventListener('afterUpdateStroke', this.strokeHandler.bind(this));
        },
        clear() {
            this.signaturePad.clear();
            this.isEmpty = true;
        },
        get iconName() {
            if(this.error) {
                return 'exclamation';
            } else {
              return  'check';
            }
        },
        strokeHandler(){
          if(this.isEmpty){
            this.isEmpty = false;
          }
        },
        async upload(_) {
            const res = await $wire.save();
            if(res !== null) {
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
