<?php

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Models\Comment;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->task = Task::factory()->create();
});

test('create comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'This is a test comment',
    ])->validate()->execute();

    expect($comment)
        ->comment->toContain('test comment')
        ->is_internal->toBeTruthy();
});

test('create comment defaults to internal', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Internal note',
    ])->validate()->execute();

    expect($comment->is_internal)->toBeTruthy();
});

test('create comment requires model_type model_id and comment', function (): void {
    CreateComment::assertValidationErrors([], ['model_type', 'model_id', 'comment']);
});

test('update comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    $updated = UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => 'Updated comment',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    expect($updated->comment)->not->toBeNull();
});

test('editing the comment text marks it as edited', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
    ])->validate()->execute();

    expect($comment->edited_at)->toBeNull();

    $updated = UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => 'Edited text',
    ])->validate()->execute();

    expect($updated->comment)->toBe('Edited text')
        ->and($updated->edited_at)->not->toBeNull();
});

test('toggling sticky does not mark the comment as edited', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
    ])->validate()->execute();

    $updated = UpdateComment::make([
        'id' => $comment->getKey(),
        'is_sticky' => true,
    ])->validate()->execute();

    expect($updated->is_sticky)->toBeTrue()
        ->and($updated->edited_at)->toBeNull();
});

test('editing another users comment text is rejected', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
    ])->validate()->execute();

    $this->be(User::factory()->create(), 'web');

    UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => 'Hijacked',
    ])->validate()->execute();
})->throws(ValidationException::class);

test('an authorless comment text can not be edited', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
    ])->validate()->execute();

    Comment::query()->whereKey($comment->getKey())->toBase()->update(['created_by' => null]);

    UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => 'Edited',
    ])->validate()->execute();
})->throws(ValidationException::class);

test('delete comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'To delete',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    expect(DeleteComment::make(['id' => $comment->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('comment keeps editor markup and drops active content', function (string $input, string $expected): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => str_replace('{user}', $this->user->getKey(), $input),
    ])->validate()->execute();

    expect($comment->comment)->toBe(str_replace('{user}', $this->user->getKey(), $expected));
})->with([
    'mention pill' => [
        '<p>Hallo <a class="mention" href="/users/{user}" data-type="mention" data-id="user:{user}" data-label="Slupi" data-mention-suggestion-char="@" data-mention-type="User">@Slupi</a></p>',
        '<p>Hallo <a class="mention" href="/users/{user}" data-type="mention" data-id="user:{user}" data-label="Slupi" data-mention-suggestion-char="@" data-mention-type="User">@Slupi</a></p>',
    ],
    'formatting' => [
        '<p style="text-align: center"><span style="color: #ff0000; font-size: 18px">rot</span> <strong>fett</strong> <em>kursiv</em> <u>unten</u> <s>weg</s></p><ul><li><p>eins</p></li></ul><blockquote><p>zitat</p></blockquote><pre><code class="language-php">echo 1;</code></pre>',
        '<p style="text-align: center"><span style="color: #ff0000; font-size: 18px">rot</span> <strong>fett</strong> <em>kursiv</em> <u>unten</u> <s>weg</s></p><ul><li><p>eins</p></li></ul><blockquote><p>zitat</p></blockquote><pre><code class="language-php">echo 1;</code></pre>',
    ],
    'image and table' => [
        '<p><img src="/media/1/bild.png" width="200" height="100" alt="Bild" /></p><table style="min-width: 50px"><colgroup><col style="width: 120px" /></colgroup><tbody><tr><td class="p-2" colspan="2" colwidth="120"><p>Zelle</p></td></tr></tbody></table>',
        '<p><img src="/media/1/bild.png" width="200" height="100" alt="Bild" /></p><table style="min-width: 50px"><colgroup><col style="width: 120px" /></colgroup><tbody><tr><td class="p-2" colspan="2" colwidth="120"><p>Zelle</p></td></tr></tbody></table>',
    ],
    'plain mention token and code' => [
        '<p>@user:{user} siehe `a = b + c`</p>',
        '<p>@user:{user} siehe `a = b + c`</p>',
    ],
    'link' => [
        '<p><a class="text-primary-600 dark:text-primary-400 underline" href="https://example.com" target="_blank" rel="noopener noreferrer nofollow">Link</a></p>',
        '<p><a class="text-primary-600 dark:text-primary-400 underline" href="https://example.com" target="_blank" rel="noopener noreferrer nofollow">Link</a></p>',
    ],
    'overlay styles and foreign classes' => [
        '<p id="app"><a class="fixed inset-0 z-50 mention" style="position: fixed; inset: 0; color: red; background: url(https://example.com/x.png)" href="https://example.com">Link</a></p>',
        '<p><a class="mention" style="color: red" href="https://example.com">Link</a></p>',
    ],
    'active content' => [
        '<p onclick="alert(1)">Text<img src="x" onerror="alert(2)"><script>alert(3)</script><a href="javascript:alert(4)" x-on:click="alert(5)">Link</a><iframe src="https://example.com"></iframe></p>',
        '<p>Text<img src="x" /><a>Link</a></p>',
    ],
]);

test('updated comment drops active content', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => '<p>Original</p>',
    ])->validate()->execute();

    $updated = UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => '<p>Neu<img src="x" onerror="alert(1)"></p>',
    ])->validate()->execute();

    expect($updated->comment)->toBe('<p>Neu<img src="x" /></p>');
});

test('comment mentions still resolve after sanitizing', function (): void {
    $quoted = User::factory()->create();

    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => '<p>@user:' . $this->user->getKey() . ' und `@user:' . $quoted->getKey() . '`</p>',
    ])->validate()->execute();

    expect(
        FluxErp\Models\Mention::query()
            ->where('mention_source_type', $comment->getMorphClass())
            ->where('mention_source_id', $comment->getKey())
            ->pluck('user_id')
            ->all()
    )->toBe([$this->user->getKey()]);
});
