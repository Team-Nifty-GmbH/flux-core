<?php

namespace FluxErp\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;
use Webklex\PHPIMAP\Address;
use Webklex\PHPIMAP\Attachment;
use Webklex\PHPIMAP\Message;

readonly class ImapMessage
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
            subject: static::toUtf8($message->getSubject()->toString()),
            from: static::toUtf8($message->getFrom()[0]->full),
            to: static::toUtf8($message->getTo()->toArray()),
            cc: static::toUtf8($message->getCc()->toArray()),
            bcc: static::toUtf8($message->getBcc()->toArray()),
            textBody: $withBody
                ? static::toUtf8($message->getTextBody())
                : null,
            htmlBody: $withBody
                ? static::toUtf8($message->getHtmlBody())
                : null,
            date: CarbonImmutable::parse($message->getDate()->toDate()),
            isSeen: $message->hasFlag('seen'),
            flags: $message->getFlags()->toArray(),
            attachments: $attachments,
        );
    }

    protected static function toUtf8(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_check_encoding($value, 'UTF-8')
                ? $value
                : mb_convert_encoding($value, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item): mixed => static::toUtf8($item), $value);
        }

        if ($value instanceof Address) {
            foreach (['personal', 'mailbox', 'host', 'mail', 'full'] as $property) {
                $value->{$property} = static::toUtf8($value->{$property});
            }
        }

        return $value;
    }
}
