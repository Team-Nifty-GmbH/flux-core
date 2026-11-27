<?php

use FluxErp\Livewire\Ticket\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Settings\CoreSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Comments::class)
        ->assertOk();
});

test('renders with ticket model id', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->assertOk();
});

test('can save a comment on a ticket', function (): void {
    $commentText = 'This is a test comment';

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('saveComment', ['comment' => $commentText])
        ->assertReturned(function (?array $result) use ($commentText): true {
            expect($result)->not->toBeNull()
                ->and($result['comment'])->toBe($commentText);

            return true;
        });

    $this->assertDatabaseHas('comments', [
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'comment' => $commentText,
    ]);
});

test('save comment returns null on validation failure', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('saveComment', ['comment' => null])
        ->assertReturned(null);
});

test('can load comments for a ticket', function (): void {
    Comment::factory()->count(3)->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            expect(data_get($comments, 'data'))->toHaveCount(3);

            return true;
        });
});

test('can load sticky comments for a ticket', function (): void {
    Comment::factory()->count(2)->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'is_sticky' => true,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('loadStickyComments')
        ->assertReturned(function (array $stickyComments): true {
            expect($stickyComments)->toHaveCount(1);

            return true;
        });
});

test('can toggle sticky on a comment', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'is_sticky' => false,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('toggleSticky', $comment->getKey());

    $this->assertDatabaseHas('comments', [
        'id' => $comment->getKey(),
        'is_sticky' => true,
    ]);
});

test('can delete a comment', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('delete', $comment->getKey())
        ->assertReturned(true);

    $this->assertSoftDeleted('comments', [
        'id' => $comment->getKey(),
    ]);
});

test('load comments returns empty for no model id', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class)
        ->call('loadComments')
        ->assertReturned([]);
});

test('the notification of a comment is sent after its files are attached', function (): void {
    config([
        'media-library.queue_conversions_by_default' => false,
        'queue.default' => 'sync',
    ]);

    $address = Address::factory()
        ->for(Contact::factory()->create())
        ->create([
            'is_active' => true,
            'is_main_address' => true,
        ]);
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->getKey(),
    ]);
    $address->subscribeNotificationChannel($ticket->broadcastChannel());

    $settings = app(CoreSettings::class);
    $settings->customer_comment_models = [morph_alias(Ticket::class)];
    $settings->save();

    $mediaCounts = [];
    Event::listen(NotificationSending::class, function (NotificationSending $event) use (&$mediaCounts): bool {
        $mediaCounts[] = $event->notification->model->getMedia()->count();

        return false;
    });

    $component = Livewire::withoutLazyLoading()
        ->actingAs($this->user)
        ->test(Comments::class, ['modelId' => $ticket->getKey()])
        ->set('files', [UploadedFile::fake()->image('attachment.png')]);

    $component->call(
        'saveComment',
        ['comment' => 'with a file', 'is_internal' => false],
        [data_get($component->get('files'), '0')->getFilename()]
    );

    expect($mediaCounts)->not->toBeEmpty()
        ->and($mediaCounts)->each->toBe(1);
});

test('a ticket not enabled for answers to the customer sends no notification to the customer', function (): void {
    config(['queue.default' => 'sync']);

    $address = Address::factory()
        ->for(Contact::factory()->create())
        ->create([
            'is_active' => true,
            'is_main_address' => true,
        ]);
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->getKey(),
    ]);
    $address->subscribeNotificationChannel($ticket->broadcastChannel());

    $notified = [];
    Event::listen(NotificationSending::class, function (NotificationSending $event) use (&$notified): bool {
        $notified[] = $event->notifiable->getMorphClass();

        return false;
    });

    Livewire::withoutLazyLoading()
        ->actingAs($this->user)
        ->test(Comments::class, ['modelId' => $ticket->getKey()])
        ->assertSet('canAnswerCustomer', false)
        ->call('saveComment', ['comment' => 'not for the customer', 'is_internal' => false]);

    expect($notified)->not->toContain(morph_alias(Address::class));
    $this->assertDatabaseHas('comments', [
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
        'comment' => 'not for the customer',
        'is_internal' => true,
    ]);
});

test('a comment is kept internal once its model is no longer enabled for answers to the customer', function (): void {
    $settings = app(CoreSettings::class);
    $settings->customer_comment_models = [morph_alias(Ticket::class)];
    $settings->save();

    $component = Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->assertSet('canAnswerCustomer', true);

    $settings->customer_comment_models = [];
    $settings->save();

    $component->call('saveComment', ['comment' => 'after the switch', 'is_internal' => false]);

    $this->assertDatabaseHas('comments', [
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'comment' => 'after the switch',
        'is_internal' => true,
    ]);
});
