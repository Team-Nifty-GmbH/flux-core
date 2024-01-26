<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSubscriptionOrderJob implements Repeatable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    private OrderType $orderType;

    public function __construct(Order|int $order, OrderType|int $orderType)
    {
        $this->order = $order instanceof Order ?
            $order : Order::query()
                ->whereKey($order)
                ->firstOrFail();

        $this->orderType = $orderType instanceof OrderType ?
            $orderType : OrderType::query()
                ->whereKey($orderType)
                ->firstOrFail();
    }

    public function handle(): void
    {
        if (! in_array(
            $this->order->orderType->order_type_enum,
            [OrderTypeEnum::Subscription, OrderTypeEnum::PurchaseSubscription]
        )) {
            return;
        }

        // Update parent_id and performance period
        $latestChild = $this->order->children()
            ->select(['id', 'system_delivery_date_end'])
            ->orderBy('system_delivery_date_end', 'DESC')
            ->first();
        $this->order->parent_id = $this->order->id;
        $this->order->order_type_id = $this->orderType->id;
        $this->order->system_delivery_date = $latestChild?->system_delivery_date_end?->addDay() ??
            $this->order->system_delivery_date ?? $this->order->order_date;
        $this->order->system_delivery_date_end = now();

        ReplicateOrder::make($this->order)->validate()->execute();
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(self::class);
    }

    public static function description(): ?string
    {
        return 'Process given Subscription Order.';
    }

    public static function parameters(): array
    {
        return [
            'order' => null,
            'orderType' => null,
        ];
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }
}
