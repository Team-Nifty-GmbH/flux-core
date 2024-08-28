import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin } from 'filepond';

export default function($wire, $ref, label) {
    return {
        tempFilesId: [],
        isLoadingFiles: [],
        selectedCollection: null,
        pond:null,
        setCollection(collectionName){
            this.selectedCollection = collectionName;
        },
        loadFilePond() {
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('#filepond-drop');
            if (!inputElement) {
                return;
            }
            this.pond = create(inputElement, {
                allowMultiple: true,
                labelIdle: label,
                onaddfilestart: (file) => {
                    this.isLoadingFiles.push(file.id);
                },
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
                onprocessfile: (error, file) => {
                    if(error){
                        this.pond.removeFile(file.id);
                    }

                    this.isLoadingFiles = this.isLoadingFiles.filter((item) => {
                        return item !== file.id;
                    });

                },
                server:{
                    process: async (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        const onSuccess = async (tempFileId) => {
                            if(await $wire.validateOnDemand(tempFileId)) {
                                this.tempFilesId.push(tempFileId);
                                load(tempFileId);
                            } else {
                                error(tempFileId);
                            }
                        }

                        const onError = ()=>{
                            error();
                        }

                        await $wire.upload('files', file, onSuccess, onError, progress);

                    },
                    revert: null,
                    remove: null,
                }
            });
        },
        async submitFiles(collectionName,successCallback){
            const response = await $wire.submitFiles(collectionName,this.tempFilesId);
            if(response && this.pond !== null){
                this.tempFilesId = [];
                this.pond.removeFiles()
                // TODO: make it like side effect - this belongs to x-data - cannot pass as argument
                // it loses referance to this keyword
                await (successCallback.bind(this))();
            } else {

            }
        }
    };
}
