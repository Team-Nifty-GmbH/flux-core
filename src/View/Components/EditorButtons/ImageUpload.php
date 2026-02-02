<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\Editor\EditorButtonTrait;
use Illuminate\View\Component;

class ImageUpload extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function command(): ?string
    {
        $errorTitle = __('Error');
        $errorMessage = __('Image upload failed.');

        return <<<JS
            (() => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    \$wire.upload(
                        'editorImage',
                        file,
                        () => {
                            \$wire.processEditorImage().then(url => {
                                if (!url) return;
                                editor().chain().focus().setImage({ src: url }).run();
                            });
                        },
                        () => {
                            \$interaction('toast')
                                .error('{$errorTitle}', '{$errorMessage}')
                                .send();
                        }
                    );
                    input.value = '';
                };
                input.click();
            })()
            JS;
    }

    public function isActive(): ?string
    {
        return 'false';
    }

    public function icon(): ?string
    {
        return 'photo';
    }

    public function tooltip(): ?string
    {
        return 'Insert Image';
    }
}
