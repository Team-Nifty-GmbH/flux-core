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

            try {
                UploadMedia::make($attachment)->validate()->execute();
            } catch (\Throwable) {
            }
        }

        if ($mailMessage->mailAccount->is_auto_assign) {
            if ($mailMessage->from_mail && $mailMessage->mailAccount->email !== $mailMessage->from_mail) {
                $addresses = app(Address::class)->query()
                    ->where('login_name', $mailMessage->from_mail)
                    ->get()
                    ->each(
                        fn (Address $address) => $address->mailMessages()->attach($mailMessage->id)
                    );

                app(ContactOption::class)->query()
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

            app(Order::class)->search(
                $mailMessage->subject,
                function (Indexes $meilisearch, string $query, array $options) {
                    return $meilisearch->search(
                        $query,
                        $options + ['attributesToSearchOn' => ['invoice_number', 'order_number', 'commission']]
                    );
                }
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
