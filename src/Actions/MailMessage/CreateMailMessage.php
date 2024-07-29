<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\ContactOption;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Meilisearch\Endpoints\Indexes;

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

        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $mailMessage->id;
            $attachment['model_type'] = app(Communication::class)->getMorphClass();
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            UploadMedia::make($attachment)
                ->validate()
                ->execute();
        }

        if ($mailMessage->mailAccount->is_auto_assign) {
            if ($mailMessage->from_mail && $mailMessage->mailAccount->email !== $mailMessage->from_mail) {
                $addresses = resolve_static(Address::class, 'query')
                    ->where('email', $mailMessage->from_mail)
                    ->get()
                    ->each(
                        fn (Address $address) => $address->mailMessages()->attach($mailMessage->id)
                    );

                resolve_static(ContactOption::class, 'query')
                    ->where('value', $mailMessage->from_mail)
                    ->whereIntegerNotInRaw('address_id', $addresses->pluck('id')->toArray())
                    ->with('address')
                    ->get()
                    ->each(
                        fn (ContactOption $contactOption) => $contactOption
                            ->address
                            ->mailMessages()
                            ->attach($mailMessage->id)
                    );
            }

            resolve_static(
                Order::class,
                'search',
                [
                    'query' => $mailMessage->subject,
                    'callback' => function (Indexes $meilisearch, string $query, array $options) {
                        return $meilisearch->search(
                            $query,
                            $options + ['attributesToSearchOn' => ['invoice_number', 'order_number', 'commission']]
                        );
                    },
                ]
            )
                ->first()
                ?->communications()
                ->attach($mailMessage->id);
        }

        return $mailMessage->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        unset($this->rules['communicatable_type'], $this->rules['communicatable_id']);
    }
}
