import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin } from 'filepond';

export default function($wire, $ref, label) {
    return {
        tempFilesId: [],
        pond:null,
        loadFilePond() {
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('#filepond-drop');
            if (!inputElement) {
                return;
            }
            this.pond = create(inputElement, {
                allowMultiple: true,
                labelIdle: label,
                onremovefile: (error,file) => {
                    if(error) return;
                    const ids =  this.pond.getFiles().map(f => f.serverId);
                    if(ids.length === 0) {
                        this.tempFilesId = [];
                    } else {
                        this.tempFilesId = this.tempFilesId.filter((item) => {
                            return ids.includes(item);
                        });
                    }
                },
                server:{
                    process: async (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        const onSuccess = (tempFile) => {
                            console.log('SUCCESS' ,tempFile);
                            this.tempFilesId.push(tempFile);
                            load(tempFile);
                        }

                        const onError = ()=>{
                            console.log('ERROR');
                            error();
                        }

                        const response = await $wire.upload('files', file, onSuccess, onError, progress);

                    },
                    revert: null,
                    remove: null,
                }
            });
        },
        async submitFiles(collectionName){
            await $wire.submitFiles(collectionName,this.tempFilesId);
            console.log('submitFiles');
            await $wire.$refresh();
        }
    };
}
