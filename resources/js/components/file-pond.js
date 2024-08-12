import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import { create, registerPlugin } from 'filepond';

export default function($wire, $ref, label) {
    return {
        selectedFiles: [],
        loadFilePond() {
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('#filepond-drop');
            if (!inputElement) {
                return;
            }
            create(inputElement, {
                allowMultiple: true,
                labelIdle: label,
                onaddfile: (error, file) => {
                    this.selectedFiles.push(file);
                },
                onremovefile: (error, file) => {
                    this.selectedFiles = this.selectedFiles.filter((f) => f.id !== file.id);
                },
            });

        },
        get isEmpty() {
            return this.selectedFiles.length === 0;
        },
    };
}
