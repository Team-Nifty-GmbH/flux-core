import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import {create, registerPlugin, setOptions} from 'filepond';

const BASE_LANGUAGE_PATH = '../../../../../node_modules/filepond/locale/';

// load all available languages from filepond
const availableLanguages = import.meta.glob('../../../../../node_modules/filepond/locale/*.js');

//  TODO: error on tree refresh - renderLevel undefined - and is called several times

export default function ($wire, $ref, lang, modalTranslations, inputTranslation){
    return {
        tempFilesId: [],
        isLoadingFiles: [],
        uploadLabel: null,
        selectedCollection: null,
        readOnly: false,
        fileCount: null,
        pond: null,
        multipleFileUpload: true,
        async setCollection(collectionName) {

            if (collectionName !== null) {
                //  on selected - check if collection is single file upload
                this.multipleFileUpload = !await $wire.hasSingleFile(collectionName);
                this.readOnly = await $wire.readOnly(collectionName);
                //  check if collection is read-only - disable file upload if true
            } else {
                // on deselect - reset to default on init
                this.multipleFileUpload = true;
                this.readOnly = false;
            }

            this.pond.setOptions({allowMultiple: this.multipleFileUpload,
                labelIdle: this.readOnly ? inputTranslation.uploadDisabled : this.uploadLabel,
                disabled: this.readOnly});
            this.selectedCollection = collectionName;
        },
       async loadFilePond(fileCountGetter) {
           // getting specific language path - based on selected language
           const languageKey = lang === null  ? undefined : Object.keys(availableLanguages).find((key) => key.split('/').pop().includes(lang));
           // fallback is english
           const moduleLanguage= languageKey !== undefined ?  await availableLanguages[languageKey]() : await availableLanguages[`${BASE_LANGUAGE_PATH}en-en.js`]();
           // return file-count for selected folder
            this.fileCount = fileCountGetter.bind(this);
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('#filepond-drop');
            if (!inputElement) {
                return;
            }
            this.pond = create(inputElement, {
                onaddfilestart: (file) => {
                    this.isLoadingFiles.push(file.id);
                },
                onremovefile: (error, file) => {
                    if (error) return;

                    const ids = this.pond.getFiles().map(f => f.serverId);

                    if (ids.length === 0) {
                        this.tempFilesId = [];
                    } else {
                        this.tempFilesId = this.tempFilesId.filter((item) => {
                            return ids.includes(item);
                        });
                    }
                },
                onprocessfile: (error, file) => {
                    if (error) {
                        this.pond.removeFile(file.id);
                    }

                    this.isLoadingFiles = this.isLoadingFiles.filter((item) => {
                        return item !== file.id;
                    });

                    // if single file upload and error is null, show confirm dialog - to replace file
                    if (!this.multipleFileUpload && error === null) {
                        //  check if single file folder is not empty
                        if (this.fileCount !== null && this.fileCount() !== undefined && this.fileCount() > 0) {
                            window.$wireui.confirmDialog({
                                title: modalTranslations.title,
                                description: modalTranslations.description,
                                icon: 'error',
                                accept: {
                                    label: modalTranslations.labelAccept,
                                },
                                reject: {
                                    execute: () => {
                                        this.pond.removeFile(file.id);
                                    },
                                    label: modalTranslations.labelReject,
                                }
                            }, $wire.__instance.id);
                        }
                    }
                },
                server: {
                    process: async (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                        const onSuccess = async (tempFileId) => {
                            if (await $wire.validateOnDemand(tempFileId, this.selectedCollection)) {
                                this.tempFilesId.push(tempFileId);
                                load(tempFileId);
                            } else {
                                error(tempFileId);
                            }
                        }

                        const onError = () => {
                            error();
                        }

                        await $wire.upload('files', file, onSuccess, onError, progress);

                    },
                    revert: null,
                    remove: null,
                },
                allowMultiple: this.multipleFileUpload,
            });

                // set language
                setOptions(moduleLanguage.default);

                // set initial label - on label change - translation will be discarded
                // need to persist default label
                this.uploadLabel = moduleLanguage.default.labelIdle;
        },
        clearFilesOnLeave() {
            if (this.pond !== null && this.tempFilesId.length > 0) {
                this.tempFilesId = [];
                this.pond.removeFiles()
            }
        },
        async submitFiles(collectionName, successCallback) {
            const response = await $wire.submitFiles(collectionName, this.tempFilesId);
            if (response && this.pond !== null) {
                this.tempFilesId = [];
                this.pond.removeFiles()
                await (successCallback.bind(this))(this.multipleFileUpload);
            }
        }
    };
}
