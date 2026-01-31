<?php

namespace FluxErp\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;
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

    public static function fromImapMessage(Message $message, bool $withBody = true): ImapMessage
    {
        $attachments = [];

        if ($withBody) {
            $message->parseBody();

            foreach ($message->getAttachments() as $attachment) {
                /** @var Attachment $attachment */
                $tempPath = tempnam(sys_get_temp_dir(), 'imap_');

                if (! $tempPath || file_put_contents($tempPath, $attachment->getContent()) === false) {
                    report(new RuntimeException('Failed to write IMAP attachment to temporary file'));

                    continue;
                }

                $attachments[] = [
                    'file_name' => Str::between($attachment->getName(), '=?', '=?'),
                    'mime_type' => $attachment->getMimeType(),
                    'name' => $attachment->getName(),
                    'media' => $tempPath,
                ];
            }
        }

        return new ImapMessage(
            messageId: $message->getMessageId()->toString(),
            uid: $message->getUid(),
            subject: $message->getSubject()->toString(),
            from: $message->getFrom()[0]->full,
            to: $message->getTo()->toArray(),
            cc: $message->getCc()->toArray(),
            bcc: $message->getBcc()->toArray(),
            textBody: $withBody
                ? $message->getTextBody()
                : null,
            htmlBody: $withBody
                ? $message->getHtmlBody()
                : null,
            date: CarbonImmutable::parse($message->getDate()->toDate()),
            isSeen: $message->hasFlag('seen'),
            flags: $message->getFlags()->toArray(),
            attachments: $attachments,
        );
    }
}
