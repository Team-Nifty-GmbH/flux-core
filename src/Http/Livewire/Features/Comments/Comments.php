<?php

namespace FluxErp\Http\Livewire\Features\Comments;

use FluxErp\Models\Comment;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Services\CommentService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use WireUi\Traits\Actions;

class Comments extends Component
{
    use Actions, WithFileUploads;

    /** @var Model $this->modelType */
    public string $modelType = '';

    public int $modelId = 0;

    /**
     * Setting this to true means that the component is public used.
     * This means that the user has to decide if he wants non-users to see his comment.
     */
    public bool $isPublic = false;

    // TODO: refactor to array
    public Model $record;

    public array $stickyComments = [];

    public array $comments = [];

    public array $users = [];

    public array $roles = [];

    public $files;

    public int $commentId = 0;

    public int $commentPage = 1;

    public int $commentsLastPage = 1;

    public int $commentsPerPage = 10;

    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        $channel = (new $this->modelType)->broadcastChannel() . $this->modelId;

        return [
            'echo-private:' . $channel . ',.CommentCreated' => 'commentCreatedEvent',
            'echo-private:' . $channel . ',.CommentUpdated' => 'commentUpdatedEvent',
            'echo-private:' . $channel . ',.CommentDeleted' => 'commentDeletedEvent',
        ];
    }

    public function mount(): void
    {
        if ($this->modelId < 1) {
            return;
        }

        $record = $this->modelType::query()->whereKey($this->modelId)->firstOrFail();
        $this->loadComments($record);

        Comment::addGlobalScope('sticky', function (Builder $builder) {
            $builder->where('is_sticky', true);
        });

        $this->stickyComments = $record
            ->comments()
            ->get()
            ->each(function (Comment $comment) {
                $comment->is_current_user = $comment->createdBy?->is(Auth::user());
            })
            ->toArray();

        $this->loadUsersAndRoles();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.comments.comments');
    }

    public function saveComment(string $comment, bool $sticky, bool $internal = true): void
    {
        if (Auth::user()->getMorphClass() !== User::class) {
            $internal = false;
        }

        $comment = [
            'model_type' => class_basename($this->modelType),
            'model_id' => $this->modelId,
            'comment' => $comment,
            'is_sticky' => $sticky,
            'is_internal' => $internal,
            'parent_id' => $this->commentId ?: null,
        ];

        $commentService = new CommentService();
        $response = $commentService->create($comment);
        if ($response['status'] !== 201) {
            $this->notification()->error(
                title: __('Comment could not be saved'),
                description: implode(', ', Arr::flatten($response['errors']))
            );

            return;
        }

        /** @var Comment $comment */
        $comment = $response['data'];
        if ($this->filesArray) {
            $this->saveFileUploadsToMediaLibrary('files', $response['data']->id, Comment::class);
            $comment->load('media:id,name,model_type,model_id,disk');
        }

        $comment = $comment->toArray();
        $comment['user'] = Auth::user()->toArray();
        $comment['user']['avatar_url'] = Auth::user()->getAvatarUrl();

        $this->insertComment($comment, $this->commentId);

        $this->reset(['commentId']);

        $this->skipRender();
    }

    public function loadMoreComments(): void
    {
        $this->commentPage++;
        $this->loadComments();
    }

    public function updatedSticky(): void
    {
        $this->skipRender();
    }

    public function toggleSticky(int $id): void
    {
        $comment = Comment::query()->whereKey($id)->first();
        $commentService = new CommentService();

        $commentService->update([
            'id' => $comment->id,
            'is_sticky' => ! $comment->is_sticky,
        ]);
    }

    public function delete(int $id): void
    {
        $commentService = new CommentService();
        $commentService->delete($id);

        $index = array_search($id, array_column($this->comments['data'], 'id'));
        unset($this->comments['data'][$index]);
    }

    public function loadComments(Model $record = null): void
    {
        $record = $record ?: $this->modelType::query()
            ->whereKey($this->modelId)
            ->firstOrFail();

        Comment::addGlobalScope('media', function (Builder $query) {
            $query->with('media:id,name,model_type,model_id,disk');
        });

        $comments = $record
            ->comments()
            ->with(['media:id,name,model_type,model_id,disk'])
            ->when(
                ! Auth::user() instanceof User,
                function ($query) {
                    $query->where('is_internal', false);
                }
            )
            ->paginate($this->commentsPerPage * $this->commentPage);

        $data = $comments->getCollection()->each(function ($comment) {
            $comment->is_current_user = $comment->createdBy?->is(Auth::user());
        });

        $comments->setCollection($data);

        $this->comments = $comments->toArray();

        $this->comments['data'] = to_flat_tree($this->comments['data']);
    }

    public function commentCreatedEvent(array $data): void
    {
        $this->insertComment($data['model'], $data['model']['parent_id']);

        $this->skipRender();
    }

    public function commentUpdatedEvent(array $data): void
    {
        $index = array_search($data['model']['id'], array_column($this->comments['data'], 'id'));
        $data['model']['slug_position'] = $this->comments['data'][$index]['slug_position'];

        $this->comments['data'][$index] = $data['model'];
    }

    public function commentDeletedEvent(array $data): void
    {
        $index = array_search($data['model']['id'], array_column($this->comments['data'], 'id'));
        unset($this->comments['data'][$index]);
    }

    private function insertComment($comment, $parentId): void
    {
        if ($parentId) {
            $index = array_search($parentId, array_column($this->comments['data'], 'id'));
            $comment['slug_position'] = $this->comments['data'][$index]['slug_position'] . '.' . $comment['id'];
            array_splice($this->comments['data'], $index + 1, 0, [$comment]);
        } else {
            $comment['slug_position'] = (string) $comment['id'];
            array_unshift($this->comments['data'], $comment);
        }
    }

    public function updatedFiles(): void
    {
        $this->prepareForMediaLibrary('files', $this->modelId, $this->modelType);

        $this->skipRender();
    }

    public function updatedCommentId()
    {
        $this->skipRender();
    }

    private function loadUsersAndRoles(): void
    {
        if (! auth()->user()?->getMorphClass() === User::class) {
            return;
        }

        $this->users = User::query()
            ->orderBy('firstname')
            ->select('id', 'firstname', 'lastname')
            ->where('is_active', true)
            ->get()
            ->map(function (User $user) {
                return [
                    'key' => $user->name,
                    'value' => $user->id,
                    'type' => User::class,
                ];
            })
            ->toArray();

        $this->roles = Role::query()
            ->orderBy('name')
            ->select(['id', 'name'])
            ->whereRelation('users', 'is_active', true)
            ->get()
            ->map(function (Role $role) {
                return [
                    'key' => $role->name,
                    'value' => $role->id,
                    'type' => Role::class,
                ];
            })
            ->toArray();
    }
}
