import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin, setOptions } from 'filepond';

const BASE_LANGUAGE_PATH = '/node_modules/filepond/locale/';

// load all available languages from filepond
const availableLanguages = import.meta.glob(
    '/node_modules/filepond/locale/*.js',
);

//  TODO: error on tree refresh - renderLevel undefined - and is called several times

export default function (
    $wire,
    $ref,
    lang,
    modalTranslations,
    inputTranslation,
) {
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
                this.multipleFileUpload =
                    !(await $wire.hasSingleFile(collectionName));
                this.readOnly = await $wire.readOnly(collectionName);
                //  check if collection is read-only - disable file upload if true
            } else {
                // on deselect - reset to default on init
                this.multipleFileUpload = true;
                this.readOnly = false;
            }

            this.pond.setOptions({
                allowMultiple: this.multipleFileUpload,
                labelFileProcessingComplete: inputTranslation.readyForUpload,
                labelFileProcessing: inputTranslation.pending,
                labelIdle: this.readOnly
                    ? inputTranslation.uploadDisabled
                    : this.uploadLabel,
                disabled: this.readOnly,
            });
            this.selectedCollection = collectionName;
        },
        async loadFilePond(fileCountGetter) {
            // getting a specific language path - based on a selected language
            const languageKey =
                lang === null
                    ? undefined
                    : Object.keys(availableLanguages).find((key) =>
                          key.split('/').pop().includes(lang),
                      );
            // fallback is english
            const moduleLanguage =
                languageKey !== undefined
                    ? await availableLanguages[languageKey]()
                    : await availableLanguages[
                          `${BASE_LANGUAGE_PATH}en-en.js`
                      ]();
            // return file-count for the selected folder
            this.fileCount = fileCountGetter.bind(this);
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('#filepond-drop');

            if (!inputElement) {
                return;
            }

            this.pond = create(inputElement, {
                credits: false,
                onaddfilestart: (file) => {
                    this.isLoadingFiles.push(file.id);
                },
                onremovefile: (error, file) => {
                    if (error) return;

                    const ids = this.pond.getFiles().map((f) => f.serverId);

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

                    // if single file upload and error is null, show the confirm dialog - to replace file
                    if (!this.multipleFileUpload && error === null) {
                        //  check if a single file folder is not empty
                        if (
                            this.fileCount !== null &&
                            this.fileCount() !== undefined &&
                            this.fileCount() > 0
                        ) {
                            $interaction('dialog')
                                .wireable($wire.id)
                                .question(
                                    modalTranslations.title,
                                    modalTranslations.description,
                                )
                                .confirm(modalTranslations.labelAccept)
                                .cancel(modalTranslations.labelReject, () => {
                                    this.pond.removeFile(file.id);
                                })
                                .send();
                        }
                    }
                },
                server: {
                    process: async (
                        fieldName,
                        file,
                        metadata,
                        load,
                        error,
                        progress,
                        abort,
                        transfer,
                        options,
                    ) => {
                        const onSuccess = async (tempFileId) => {
                            if (
                                await $wire.validateOnDemand(
                                    tempFileId,
                                    this.selectedCollection,
                                )
                            ) {
                                this.tempFilesId.push(tempFileId);
                                load(tempFileId);
                            } else {
                                error(tempFileId);
                            }
                        };

                        const onError = () => {
                            error();
                        };

                        await $wire.upload(
                            'files',
                            file,
                            onSuccess,
                            onError,
                            progress,
                        );
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
                this.clearPond();
            }
        },
        async submitFiles(
            collectionName,
            successCallback,
            modelType = null,
            modelId = null,
        ) {
            const response = await $wire.submitFiles(
                collectionName,
                this.tempFilesId,
                modelType,
                modelId,
            );

            if (response && this.pond !== null) {
                this.clearPond();
                await successCallback.bind(this)(this.multipleFileUpload);
            }
        },
        clearPond() {
            this.tempFilesId = [];
            this.pond.removeFiles();
        },
    };
}
