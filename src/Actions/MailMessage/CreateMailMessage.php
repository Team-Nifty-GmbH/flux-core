<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateMailMessage extends FluxAction
{
    public static function models(): array
    {
        return [Communication::class];
    }

    protected static function decodeMimeHeader(mixed $value): mixed
    {
        if (! is_string($value) || ! preg_match('/=\\?[^?]*\\?[BbQq]\\?[^?]*\\?=/', $value)) {
            return $value;
        }

        return mb_decode_mimeheader($value);
    }

    protected function getRulesets(): string|array
    {
        return CreateCommunicationRuleset::class;
    }

    public function performAction(): Communication
    {
        $tags = Arr::pull($this->data, 'tags');
        $attachments = Arr::pull($this->data, 'attachments', []);
        $this->data['html_body'] = static::decodeMimeHeader(data_get($this->data, 'html_body'));
        $this->data['subject'] = static::decodeMimeHeader(data_get($this->data, 'subject'));
        $this->data['from'] = Str::replace('"', '', data_get($this->data, 'from'));

        $mailMessage = app(Communication::class, ['attributes' => $this->data]);
        $mailMessage->save();

        if ($tags) {
            $mailMessage->attachTags(
                resolve_static(Tag::class, 'query')
                    ->whereKey($tags)
                    ->get(),
                morph_alias(Communication::class)
            );
        }

        // the maximum file size for mail messages should be managed on the mail server
        $maxFileSize = config('media-library.max_file_size');
        config(['media-library.max_file_size' => 1024 * 1024 * 500]);
        try {
            foreach ($attachments as $attachment) {
                $attachment['model_id'] = $mailMessage->id;
                $attachment['model_type'] = app(Communication::class)->getMorphClass();
                $attachment['collection_name'] = 'attachments';
                $attachment['media_type'] ??= is_file(data_get($attachment, 'media', ''))
                    ? null
                    : 'string';

                UploadMedia::make($attachment)
                    ->validate()
                    ->execute();
            }
        } finally {
            config(['media-library.max_file_size' => $maxFileSize]);
        }

        if ($mailMessage->mailAccount->has_auto_assign) {
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
