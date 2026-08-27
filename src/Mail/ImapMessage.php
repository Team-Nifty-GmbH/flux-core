<?php

namespace FluxErp\Mail;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;
use Webklex\PHPIMAP\Address;
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
            subject: self::scrub($message->getSubject()->toString()),
            from: self::scrub($message->getFrom()[0]->full),
            to: self::scrub($message->getTo()->toArray()),
            cc: self::scrub($message->getCc()->toArray()),
            bcc: self::scrub($message->getBcc()->toArray()),
            textBody: $withBody
                ? self::scrub($message->getTextBody())
                : null,
            htmlBody: $withBody
                ? self::scrub($message->getHtmlBody())
                : null,
            date: CarbonImmutable::parse($message->getDate()->toDate()),
            isSeen: $message->hasFlag('seen'),
            flags: $message->getFlags()->toArray(),
            attachments: $attachments,
        );
    }

    /**
     * A mail header may carry any bytes at all, and a sender that announces one
     * encoding while writing another is common enough. The address fields are
     * stored as json, which refuses invalid UTF-8 outright, so a single such
     * message would otherwise stop the whole folder from syncing.
     */
    protected static function scrub(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_scrub($value, 'UTF-8');
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item) => self::scrub($item), $value);
        }

        if ($value instanceof Address) {
            foreach (['personal', 'mailbox', 'host', 'mail', 'full'] as $property) {
                $value->{$property} = mb_scrub($value->{$property}, 'UTF-8');
            }
        }

        return $value;
    }
}
