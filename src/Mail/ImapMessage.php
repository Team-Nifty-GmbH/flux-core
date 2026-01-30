<?php

namespace FluxErp\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\Attachment;
use Webklex\PHPIMAP\Message;

final readonly class ImapMessage
{
    public function __construct(
        public string $messageId,
        public int $uid,
        public string $subject,
        public string $from,
        public array $to,
        public array $cc,
        public array $bcc,
        public ?string $textBody,
        public ?string $htmlBody,
        public CarbonImmutable $date,
        public bool $isSeen,
        public array $flags,
        public array $attachments,
    ) {}

    public static function fromImapMessage(Message $message): ImapMessage
    {
        $message->parseBody();

        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            /** @var Attachment $attachment */
            $attachments[] = [
                'file_name' => Str::between($attachment->getName(), '=?', '=?'),
                'mime_type' => $attachment->getMimeType(),
                'name' => $attachment->getName(),
                'media_type' => 'string',
                'media' => $attachment->getContent(),
            ];
        }

        return new ImapMessage(
            messageId: $message->getMessageId()->toString(),
            uid: $message->getUid(),
            subject: $message->getSubject()->toString(),
            from: $message->getFrom()[0]->full,
            to: $message->getTo()->toArray(),
            cc: $message->getCc()->toArray(),
            bcc: $message->getBcc()->toArray(),
            textBody: $message->getTextBody(),
            htmlBody: $message->getHtmlBody(),
            date: CarbonImmutable::parse($message->getDate()->toDate()),
            isSeen: $message->hasFlag('seen'),
            flags: $message->getFlags()->toArray(),
            attachments: $attachments,
        );
    }
}
