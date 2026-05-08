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

// Files larger than this use the chunked upload endpoint to bypass
// PHP's post_max_size / Octane FrankenPHP request body limits.
const CHUNK_THRESHOLD = 4 * 1024 * 1024;
const CHUNK_SIZE = 4 * 1024 * 1024;

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

async function readJsonSafe(response) {
    try {
        return await response.json();
    } catch (e) {
        return null;
    }
}

async function chunkedUpload(file, progress) {
    const initResponse = await fetch('/file-pond/chunk', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Upload-Length': String(file.size),
            'Upload-Name': btoa(
                String.fromCharCode(...new TextEncoder().encode(file.name)),
            ),
        },
    });

    const initBody = await readJsonSafe(initResponse);

    if (!initResponse.ok) {
        throw new Error(
            initBody?.statusMessage || 'Chunked upload init failed',
        );
    }

    const transferId = initBody?.data?.transfer_id;
    if (!transferId) {
        throw new Error('Chunked upload init returned no transfer id');
    }

    const patchUrl = '/file-pond/chunk?patch=' + encodeURIComponent(transferId);

    let offset = 0;
    while (offset < file.size) {
        const end = Math.min(offset + CHUNK_SIZE, file.size);
        const chunk = file.slice(offset, end);

        const patchResponse = await fetch(patchUrl, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/offset+octet-stream',
                'Upload-Offset': String(offset),
                'Upload-Length': String(file.size),
                'Upload-Name': btoa(
                    String.fromCharCode(...new TextEncoder().encode(file.name)),
                ),
            },
            body: chunk,
        });

        const patchBody = await readJsonSafe(patchResponse);

        if (!patchResponse.ok) {
            throw new Error(patchBody?.statusMessage || 'Chunk upload failed');
        }

        offset = patchBody?.data?.offset ?? end;
        progress?.(true, offset, file.size);
    }

    return transferId;
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
                            $tsui
                                .interaction('dialog')
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
                        if (file.size > CHUNK_THRESHOLD) {
                            try {
                                const signedPath = await chunkedUpload(
                                    file,
                                    progress,
                                );

                                const tempFileId =
                                    await $wire.acceptChunkedUpload(
                                        signedPath,
                                        this.selectedCollection,
                                    );

                                if (tempFileId) {
                                    this.tempFilesId.push(tempFileId);
                                    load(tempFileId);
                                } else {
                                    error('Chunked upload rejected');
                                }
                            } catch (e) {
                                error(e?.message || 'Chunked upload failed');
                            }

                            return;
                        }

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
