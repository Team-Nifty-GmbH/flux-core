<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateMailMessage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCommunicationRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): Communication
    {
        $tags = Arr::pull($this->data, 'tags');
        $attachments = Arr::pull($this->data, 'attachments', []);
        $this->data['html_body'] = is_string(data_get($this->data, 'html_body'))
            ? iconv_mime_decode(data_get($this->data, 'html_body'), 2, 'UTF-8')
            : data_get($this->data, 'html_body');
        $this->data['subject'] = is_string(data_get($this->data, 'subject'))
            ? iconv_mime_decode(data_get($this->data, 'subject'), 2, 'UTF-8')
            : data_get($this->data, 'subject');
        $this->data['from'] = Str::replace('"', '', data_get($this->data, 'from'));

        $mailMessage = app(Communication::class, ['attributes' => $this->data]);
        $mailMessage->save();

        if ($tags) {
            $mailMessage->syncTags($tags);
        }

        // the maximum file size for mail messages should be managed on the mail server
        $maxFileSize = config('media-library.max_file_size');
        config(['media-library.max_file_size' => 1024 * 1024 * 500]);
        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $mailMessage->id;
            $attachment['model_type'] = app(Communication::class)->getMorphClass();
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            UploadMedia::make($attachment)
                ->validate()
                ->execute();
        }
        config(['media-library.max_file_size' => $maxFileSize]);

        if ($mailMessage->mailAccount->is_auto_assign) {
            $connectedMailAddresses = resolve_static(MailAccount::class, 'query')
                ->pluck('email')
                ->toArray();
            $mailAddresses = array_diff(
                (array) $mailMessage->mail_addresses,
                $connectedMailAddresses
            );

            $mailMessage->autoAssign('email', $mailAddresses);
        }

        return $mailMessage->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        unset($this->rules['communicatable_type'], $this->rules['communicatable_id']);
    }
}
