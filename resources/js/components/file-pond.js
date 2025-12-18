import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin, setOptions } from 'filepond';

// Static imports for common locales - these are bundled
import localeDE from 'filepond/locale/de-de.js';
import localeEN from 'filepond/locale/en-en.js';

// Bundled locales - no additional requests needed
const bundledLocales = {
    de: localeDE,
    en: localeEN,
};

function loadLocale(lang) {
    return bundledLocales[lang] || bundledLocales['en'];
}

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
        async setCollection(collectionName, id = null) {
            if (collectionName !== null) {
                //  on selected - check if collection is single file upload
                this.multipleFileUpload = !(await $wire.hasSingleFile(
                    id,
                    collectionName,
                ));
                this.readOnly = await $wire.readOnly(id, collectionName);
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
        loadFilePond(fileCountGetter) {
            // Load the bundled locale (de or en, fallback to en)
            const moduleLanguage = loadLocale(lang);
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
            setOptions(moduleLanguage);

            // set initial label - on label change - translation will be discarded
            // need to persist default label
            this.uploadLabel = moduleLanguage.labelIdle;
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
