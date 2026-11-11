<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Language;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Notifications\Comment\CommentCreatedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $language = Language::factory()->create();

    $this->user = User::factory()->create(['language_id' => $language->id]);

    $this->task = Task::factory()->create([
        'name' => 'Test Task',
        'responsible_user_id' => $this->user->id,
    ]);

    $this->ticket = Ticket::factory()->create([
        'title' => 'Test Ticket',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
    ]);

    $this->taskComment = Comment::factory()->create([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->id,
        'comment' => 'Test comment on task',
    ]);

    $this->ticketComment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->id,
        'comment' => 'Test comment on ticket',
    ]);
});

test('getRoute returns parent model detail route for task comment', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $route = $notification->getRoute();

    expect($route)->toBe($this->task->detailRoute());
});

test('getRoute returns parent model detail route for ticket comment', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = $this->ticketComment;

    $route = $notification->getRoute();

    expect($route)->toBe($this->ticket->detailRoute());
});

test('getRoute returns null when comment has no parent model', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Task::class),
        'model_id' => 99999,
        'comment' => 'Orphan comment',
    ]);

    $notification = new CommentCreatedNotification();
    $notification->model = $comment;

    $route = $notification->getRoute();

    expect($route)->toBeNull();
});

test('getRoute returns null when notification model is null', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = null;

    $route = $notification->getRoute();

    expect($route)->toBeNull();
});

test('toArray includes url in accept action', function (): void {
    $this->actingAs($this->user);

    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $array = $notification->toArray($this->user);

    expect($array)
        ->toHaveKey('accept')
        ->and($array['accept'])
        ->toHaveKey('url')
        ->and($array['accept']['url'])
        ->toBe($this->task->detailRoute());
});

test('toMail attaches the media of the comment', function (): void {
    config(['media-library.queue_conversions_by_default' => false]);

    $media = $this->taskComment
        ->addMedia(UploadedFile::fake()->createWithContent('extension.zip', 'zip payload'))
        ->toMediaCollection();

    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment->refresh();

    $mail = $notification->toMail($this->user);

    expect($mail->rawAttachments)->toHaveCount(1)
        ->and(data_get($mail->rawAttachments, '0.name'))->toBe('extension.zip')
        ->and(data_get($mail->rawAttachments, '0.options.mime'))->toBe($media->mime_type)
        ->and(data_get($mail->rawAttachments, '0.data'))->toBe('zip payload');
});

test('toMail carries no attachments when the comment has no media', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $mail = $notification->toMail($this->user);

    expect($mail->attachments)->toBeEmpty()
        ->and($mail->rawAttachments)->toBeEmpty();
});

test('broadcast of a long comment stays below the reverb message limit', function (): void {
    Event::fake([BroadcastNotificationCreated::class]);
    $this->actingAs($this->user);

    $comment = str_repeat('<p>Ein längerer Kommentar mit <strong>viel</strong> Text</p>', 400);
    $this->taskComment->update(['comment' => $comment]);

    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $this->user->notifyNow($notification, [BroadcastChannel::class, DatabaseChannel::class]);

    Event::assertDispatched(
        BroadcastNotificationCreated::class,
        function (BroadcastNotificationCreated $event): bool {
            expect(strlen(json_encode($event->broadcastWith())))->toBeLessThan(10_000)
                ->and($event->data['description'])->not->toContain('<strong>')
                ->and($event->data['description'])->toStartWith('Ein längerer Kommentar');

            return true;
        }
    );

    expect($this->user->notifications()->first()->data['description'])->toBe($comment);
});

test('broadcast of a short comment keeps its markup', function (): void {
    Event::fake([BroadcastNotificationCreated::class]);
    $this->actingAs($this->user);

    $this->taskComment->update(['comment' => '<p>Kurzer <strong>Kommentar</strong></p>']);

    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $this->user->notifyNow($notification, [BroadcastChannel::class]);

    Event::assertDispatched(
        BroadcastNotificationCreated::class,
        fn (BroadcastNotificationCreated $event): bool => $event->data['description']
            === '<p>Kurzer <strong>Kommentar</strong></p>'
    );
});

test('comment model event broadcasts only its key', function (): void {
    expect($this->taskComment->broadcastWith())
        ->toBe(['model' => ['id' => $this->taskComment->getKey()]]);
});

describe('queued for a comment that is gone afterwards', function (): void {
    beforeEach(function (): void {
        config(['queue.default' => 'database']);
        $this->actingAs($this->user);

        $notification = new CommentCreatedNotification();
        $notification->model = $this->taskComment;
        $notification->event = $this->taskComment;

        $this->user->notify($notification);

        expect(DB::table('jobs')->count())->toBeGreaterThan(0);
    });

    test('force deleted comment ends the job without failing it', function (): void {
        $this->taskComment->forceDelete();

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--memory' => 4096, '--sleep' => 0]);

        expect(DB::table('jobs')->count())->toBe(0)
            ->and(DB::table('failed_jobs')->count())->toBe(0)
            ->and($this->user->notifications()->count())->toBe(0);
    });

    test('soft deleted comment sends nothing', function (): void {
        $this->taskComment->delete();

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--memory' => 4096, '--sleep' => 0]);

        expect(DB::table('jobs')->count())->toBe(0)
            ->and(DB::table('failed_jobs')->count())->toBe(0)
            ->and($this->user->notifications()->count())->toBe(0);
    });

    test('deleted parent model sends nothing', function (): void {
        $this->task->delete();

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--memory' => 4096, '--sleep' => 0]);

        expect(DB::table('jobs')->count())->toBe(0)
            ->and(DB::table('failed_jobs')->count())->toBe(0)
            ->and($this->user->notifications()->count())->toBe(0);
    });

    test('existing comment is still delivered', function (): void {
        Artisan::call('queue:work', ['--stop-when-empty' => true, '--memory' => 4096, '--sleep' => 0]);

        expect(DB::table('failed_jobs')->count())->toBe(0)
            ->and($this->user->notifications()->count())->toBeGreaterThan(0);
    });
});
