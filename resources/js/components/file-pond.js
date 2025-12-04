import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin, setOptions } from 'filepond';

import amET from 'filepond/locale/am-et.js';
import arAR from 'filepond/locale/ar-ar.js';
import azAZ from 'filepond/locale/az-az.js';
import caCA from 'filepond/locale/ca-ca.js';
import csCZ from 'filepond/locale/cs-cz.js';
import cyCY from 'filepond/locale/cy-cy.js';
import daDK from 'filepond/locale/da-dk.js';
import deDE from 'filepond/locale/de-de.js';
import elEL from 'filepond/locale/el-el.js';
import enEN from 'filepond/locale/en-en.js';
import esES from 'filepond/locale/es-es.js';
import etEE from 'filepond/locale/et-ee.js';
import faIR from 'filepond/locale/fa_ir.js';
import fiFI from 'filepond/locale/fi-fi.js';
import frFR from 'filepond/locale/fr-fr.js';
import heHE from 'filepond/locale/he-he.js';
import hrHR from 'filepond/locale/hr-hr.js';
import huHU from 'filepond/locale/hu-hu.js';
import idID from 'filepond/locale/id-id.js';
import itIT from 'filepond/locale/it-it.js';
import jaJA from 'filepond/locale/ja-ja.js';
import kmKM from 'filepond/locale/km-km.js';
import koKR from 'filepond/locale/ko-kr.js';
import kuCKB from 'filepond/locale/ku-ckb.js';
import kurCKB from 'filepond/locale/kur-ckb.js';
import ltLT from 'filepond/locale/lt-lt.js';
import lusLUS from 'filepond/locale/lus-lus.js';
import lvLV from 'filepond/locale/lv-lv.js';
import nlNL from 'filepond/locale/nl-nl.js';
import noNB from 'filepond/locale/no_nb.js';
import plPL from 'filepond/locale/pl-pl.js';
import ptBR from 'filepond/locale/pt-br.js';
import ptPT from 'filepond/locale/pt-pt.js';
import roRO from 'filepond/locale/ro-ro.js';
import ruRU from 'filepond/locale/ru-ru.js';
import skSK from 'filepond/locale/sk-sk.js';
import slSI from 'filepond/locale/sl-si.js';
import svSE from 'filepond/locale/sv_se.js';
import trTR from 'filepond/locale/tr-tr.js';
import ukUA from 'filepond/locale/uk-ua.js';
import urUR from 'filepond/locale/ur-ur.js';
import viVI from 'filepond/locale/vi-vi.js';
import zhCN from 'filepond/locale/zh-cn.js';
import zhHK from 'filepond/locale/zh-hk.js';
import zhTW from 'filepond/locale/zh-tw.js';

const availableLanguages = {
    am: amET,
    ar: arAR,
    az: azAZ,
    ca: caCA,
    cs: csCZ,
    cy: cyCY,
    da: daDK,
    de: deDE,
    el: elEL,
    en: enEN,
    es: esES,
    et: etEE,
    fa: faIR,
    fi: fiFI,
    fr: frFR,
    he: heHE,
    hr: hrHR,
    hu: huHU,
    id: idID,
    it: itIT,
    ja: jaJA,
    km: kmKM,
    ko: koKR,
    ku: kuCKB,
    kur: kurCKB,
    lt: ltLT,
    lus: lusLUS,
    lv: lvLV,
    nl: nlNL,
    no: noNB,
    pl: plPL,
    'pt-br': ptBR,
    pt: ptPT,
    ro: roRO,
    ru: ruRU,
    sk: skSK,
    sl: slSI,
    sv: svSE,
    tr: trTR,
    uk: ukUA,
    ur: urUR,
    vi: viVI,
    'zh-cn': zhCN,
    'zh-hk': zhHK,
    'zh-tw': zhTW,
};

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
        async loadFilePond(fileCountGetter) {
            // Get language module (fallback to English)
            const moduleLanguage = availableLanguages[lang] || availableLanguages.en;
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
