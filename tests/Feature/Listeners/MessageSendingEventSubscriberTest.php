<?php

use FluxErp\Listeners\MessageSendingEventSubscriber;
use FluxErp\Models\Communication;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;

test('attachment given as path is stored with the file content', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'attachment_');
    file_put_contents($path, 'real file content');

    app(MessageSendingEventSubscriber::class)->handle(new MessageSending(
        (new Email())->from('sender@example.com')->to('recipient@example.com')->html('<p>Hello</p>'),
        [
            'mailMessageForm' => [
                'subject' => 'Invoice',
                'attachments' => [
                    ['name' => 'invoice.txt', 'path' => $path],
                ],
            ],
        ]
    ));

    $media = resolve_static(Communication::class, 'query')
        ->latest('id')
        ->first()
        ->getFirstMedia('attachments');

    expect($media)->not->toBeNull()
        ->and(file_get_contents($media->getPath()))->toBe('real file content');
});
