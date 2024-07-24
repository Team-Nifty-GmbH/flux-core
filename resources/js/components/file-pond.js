import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import { create, registerPlugin } from "filepond";

export default function($ref) {
    return {
        init() {
            registerPlugin(FilePondPluginImagePreview);

            const inputElement = $ref.querySelector('input[type="file"]');

            // wait for Alpine to finish rendering the component - next event loop tick
                create(inputElement, {
                    allowMultiple: true
                });
        }
    };
}
