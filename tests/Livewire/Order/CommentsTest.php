<?php

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\Settings\CoreSettingsForm;
use FluxErp\Livewire\Order\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Settings\CoreSettings;
use FluxErp\Traits\Model\Commentable;
use FluxErp\Traits\Model\HasParentMorphClass;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $addresses = Address::factory()->count(2)->create([
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $priceList = PriceList::factory()->create();

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $addresses->random()->id,
        'address_delivery_id' => $addresses->random()->id,
        'is_locked' => false,
    ]);

    $comments = Comment::factory()
        ->count(3)
        ->create([
            'model_type' => morph_alias(Order::class),
            'model_id' => $this->order->id,
        ]);

    $comments->first()->update(['is_sticky' => true]);
});

test('loadComments returns is_internal as boolean', function (): void {
    // Create one internal and one non-internal comment
    Comment::query()->where('model_id', $this->order->id)->delete();
    Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->id,
        'is_internal' => true,
    ]);
    Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->id,
        'is_internal' => false,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            $data = data_get($comments, 'data');
            expect($data)->toHaveCount(2);

            foreach ($data as $comment) {
                // Must be actual boolean, not string "0"/"1"
                // JS treats "0" as truthy which breaks x-bind:class
                expect($comment['is_internal'])->toBeIn([true, false]);
            }

            $internal = collect($data)->where('is_internal', true);
            $public = collect($data)->where('is_internal', false);
            expect($internal)->toHaveCount(1)
                ->and($public)->toHaveCount(1);

            return true;
        });
});

test('updateComment edits the text and marks the comment as edited', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Order::class),
        'model_id' => $this->order->id,
        'comment' => 'Original',
    ]);

    Livewire::test(Comments::class, ['modelId' => $this->order->id])
        ->call('updateComment', $comment->getKey(), 'Edited text')
        ->assertReturned(function (?array $updated): true {
            expect(data_get($updated, 'comment'))->toBe('Edited text');
            expect(data_get($updated, 'edited_at'))->not->toBeNull();

            return true;
        })
        ->assertHasNoErrors();

    expect(Comment::query()->whereKey($comment->getKey())->value('comment'))
        ->toBe('Edited text');
});

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->assertOk()
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            expect(data_get($comments, 'data'))->toHaveCount(3);
            expect(data_get($comments, 'current_page'))->toEqual(1);
            expect(data_get($comments, 'total'))->toEqual(3);

            return true;
        })
        ->call('loadStickyComments')
        ->assertReturned(function (array $stickyComments): true {
            expect($stickyComments)->toHaveCount(1, 'data');

            return true;
        });
});

test('only models enabled in the core settings offer an answer to the customer', function (): void {
    Comment::query()->where('model_id', $this->order->id)->delete();

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->assertSet('canAnswerCustomer', false)
        ->assertDontSee(__('Answer to customer'))
        ->call('saveComment', ['comment' => 'kept internal', 'is_internal' => false]);

    $settings = app(CoreSettings::class);
    $settings->customer_comment_models = [morph_alias(Order::class)];
    $settings->save();

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->assertSet('canAnswerCustomer', true)
        ->assertSee(__('Answer to customer'))
        ->call('saveComment', ['comment' => 'to the customer', 'is_internal' => false]);

    expect(Comment::query()->where('model_id', $this->order->id)->pluck('is_internal', 'comment')->all())
        ->toEqual(['kept internal' => true, 'to the customer' => false]);
});

test('the core settings offer every commentable model', function (): void {
    expect(collect(CoreSettingsForm::commentModelOptions())->pluck('value'))
        ->toContain(morph_alias(Order::class))
        ->not->toContain(morph_alias(Currency::class));
});

test('the core settings offer a model whose customization added comments', function (): void {
    $customized = get_class(new class() extends Currency
    {
        use Commentable, HasParentMorphClass;

        protected $table = 'currencies';
    });
    $this->app->bind(Currency::class, $customized);

    expect(collect(CoreSettingsForm::commentModelOptions())->pluck('value'))
        ->toContain(morph_alias(Currency::class));
});

test('staff on a model without answer to the customer still need the permission to comment', function (): void {
    Permission::findOrCreate('action.' . CreateComment::name(), 'web');

    $inputs = fn (): int => substr_count(
        Livewire::withoutLazyLoading()->test(Comments::class, ['modelId' => $this->order->id])->html(),
        'class="comment-input"'
    );

    $settings = app(CoreSettings::class);
    $settings->customer_comment_models = [morph_alias(Order::class)];
    $settings->save();
    $withoutPermission = $inputs();

    $settings->customer_comment_models = [];
    $settings->save();

    expect($inputs())->toBe($withoutPermission);

    $this->user->givePermissionTo('action.' . CreateComment::name());

    expect($inputs())->toBeGreaterThan($withoutPermission);
});

test('staff on a model without answer to the customer still see which comments are internal', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->order->id])
        ->assertSet('canAnswerCustomer', false)
        ->assertSee(__('Internal'));
});
