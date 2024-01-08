<?php

namespace FluxErp\Actions\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Http\Requests\CreateMailMessageRequest;
use FluxErp\Models\Address;
use FluxErp\Models\ContactOption;
use FluxErp\Models\MailMessage;
use FluxErp\Models\Order;
use Illuminate\Support\Arr;
use Meilisearch\Endpoints\Indexes;

class CreateMailMessage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateMailMessageRequest())->rules();
    }

    public static function models(): array
    {
        return [MailMessage::class];
    }

    public function performAction(): mixed
    {
        $tags = Arr::pull($this->data, 'tags');
        $attachments = Arr::pull($this->data, 'attachments', []);

        $mailMessage = new MailMessage($this->data);
        $mailMessage->save();

        if ($tags) {
            $mailMessage->syncTags($tags);
        }

        foreach ($attachments as $attachment) {
            $attachment['model_id'] = $mailMessage->id;
            $attachment['model_type'] = MailMessage::class;
            $attachment['collection_name'] = 'attachments';
            $attachment['media_type'] = 'string';

            UploadMedia::make($attachment)->execute();
        }

        if ($mailMessage->mailAccount->is_auto_assign) {
            if ($mailMessage->from_mail && $mailMessage->mailAccount->email !== $mailMessage->from_mail) {
                $addresses = Address::query()
                    ->where('login_name', $mailMessage->from_mail)
                    ->get()
                    ->each(
                        fn (Address $address) => $address->mailMessages()->attach($mailMessage->id)
                    );

                ContactOption::query()
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

            Order::search(
                $mailMessage->subject,
                function (Indexes $meilisearch, string $query, array $options) {
                    return $meilisearch->search(
                        $query,
                        $options + ['attributesToSearchOn' => ['invoice_number', 'order_number', 'commission']]
                    );
                }
            )
                ->first()
                ?->mailMessages()
                ->attach($mailMessage->id);
        }

        return $mailMessage->withoutRelations()->fresh();
    }
}
