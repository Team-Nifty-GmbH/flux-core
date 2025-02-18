<?php

namespace FluxErp\Livewire\Features\Comments;

use FluxErp\Livewire\Forms\CommentForm;
use FluxErp\Models\Comment;
use FluxErp\Models\Role;
use FluxErp\Models\Scopes\FamilyTreeScope;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Comments extends Component
{
    use Actions, WithFilePond, WithPagination;

    /** @var Model $this->modelType */
    public string $modelType = '';

    #[Modelable]
    public int $modelId = 0;

    /**
     * Setting this to true means that the component is public used.
     * This means that the user has to decide if he wants non-users to see his comment.
     */
    #[Locked]
    public bool $isPublic = true;

    public CommentForm $commentForm;

    public int $commentPage = 1;

    public int $commentsLastPage = 1;

    public int $commentsPerPage = 10;

    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        $channel = app($this->modelType)->broadcastChannel() . $this->modelId;

        return [
            'echo-private:' . $channel . ',.CommentCreated' => 'loadComments',
            'echo-private:' . $channel . ',.CommentUpdated' => 'loadComments',
            'echo-private:' . $channel . ',.CommentDeleted' => 'loadComments',
        ];
    }

    public function mount(): void
    {
        if ($this->modelId < 1) {
            return;
        }

        if (! Auth::check()) {
            $this->isPublic = false;
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.comments.comments', $this->loadUsersAndRoles());
    }

    #[Renderless]
    public function saveComment(array $comment, array $files = []): ?array
    {
        $this->commentForm->reset();
        $this->commentForm->fill(array_merge(
            $comment,
            [
                'model_type' => morph_alias($this->modelType),
                'model_id' => $this->modelId,
                'is_internal' => auth()->user()->getMorphClass() !== morph_alias(User::class)
                    ? false
                    : data_get($comment, 'is_internal', true),
            ]
        )
        );

        try {
            $this->commentForm->save();
            $this->submitFiles(
                'default',
                $files,
                morph_alias(Comment::class),
                $this->commentForm->id
            );
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        $this->commentForm->getActionResult()->load('media:id,name,model_type,model_id,disk');
        $comment = $this->commentForm->getActionResult()->toArray();
        $comment['user']['avatar_url'] = Auth::user()?->getAvatarUrl();

        return $comment;
    }

    #[Renderless]
    public function loadMoreComments(): array
    {
        $this->commentPage++;

        return $this->loadComments();
    }

    #[Renderless]
    public function toggleSticky(int $id): void
    {
        $this->commentForm->reset();
        $this->commentForm->fill([
            'id' => $id,
            'is_sticky' => ! resolve_static(Comment::class, 'query')
                ->whereKey($id)
                ->value('is_sticky'),
        ]);

        try {
            $this->commentForm->save();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function delete(int $id): bool
    {
        $this->commentForm->reset();
        $this->commentForm->id = $id;

        try {
            $this->commentForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    #[Renderless]
    public function loadComments(): array
    {
        /** @var Model $record */
        $record = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->firstOrFail();

        resolve_static(Comment::class, 'addGlobalScopes', [
            'scopes' => [
                'media' => function (Builder $query) {
                    $query->with('media');
                },
                'ordered' => function (Builder $query) {
                    $query->orderBy('id', 'desc');
                },
                FamilyTreeScope::class,
            ],
        ]);

        $comments = $record
            ->comments()
            ->whereNull('parent_id')
            ->when(
                ! Auth::user() instanceof User,
                function ($query) {
                    $query->where('is_internal', false);
                }
            )
            ->paginate(page: $this->commentPage);

        $data = $comments->getCollection()->map(function ($comment) {
            $comment->is_current_user = $comment->getCreatedBy()?->is(Auth::user());

            return $comment;
        });

        return $comments->setCollection($data)->toArray();
    }

    #[Renderless]
    public function loadStickyComments(): array
    {
        resolve_static(Comment::class, 'addGlobalScopes', [
            'scopes' => [
                'media' => function (Builder $query) {
                    $query->with('media:id,name,model_type,model_id,disk');
                },
                'ordered' => function (Builder $query) {
                    $query->orderBy('id', 'desc');
                },
            ],
        ]);

        return resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->firstOrFail()
            ->comments()
            ->when(
                ! Auth::user() instanceof User,
                function ($query) {
                    $query->where('is_internal', false);
                }
            )
            ->where('is_sticky', true)
            ->get()
            ->map(function (Comment $comment) {
                $comment->is_current_user = $comment->getCreatedBy()?->is(auth()->user());

                return $comment;
            })
            ->toArray();
    }

    protected function loadUsersAndRoles(): array
    {
        if (! auth()->user()?->getMorphClass() === app(User::class)->getMorphClass()) {
            return [];
        }

        $result = [];
        $result['users'] = resolve_static(User::class, 'query')
            ->select('id', 'name')
            ->where('is_active', true)
            ->orderBy('firstname')
            ->get()
            ->map(function (User $user) {
                return [
                    'key' => $user->name,
                    'value' => $user->id,
                    'type' => app(User::class)->getMorphClass(),
                ];
            })
            ->toArray();

        $result['roles'] = resolve_static(Role::class, 'query')
            ->select(['id', 'name'])
            ->whereRelation('users', 'is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                return [
                    'key' => $role->name,
                    'value' => $role->id,
                    'type' => app(Role::class)->getMorphClass(),
                ];
            })
            ->toArray();

        return $result;
    }
}
