<?php

namespace FluxErp\Invokable;

use Cron\CronExpression;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\Printing;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Traits\LogsActivity;
use Throwable;

class ProcessSubscriptionOrder implements Repeatable
{
    public function __invoke(
        int|string $orderId,
        int|string $orderTypeId,
        ?array $printLayouts = null,
        ?bool $autoPrintAndSend = false,
        ?int $emailTemplateId = null
    ): bool {
        $order = Order::query()
            ->whereKey($orderId)
            ->first();

        $orderType = OrderType::query()
            ->whereKey($orderTypeId)
            ->first();

        if (! $order || ! $orderType) {
            return false;
        }

        if (! in_array(
            $order->orderType->order_type_enum,
            [OrderTypeEnum::Subscription, OrderTypeEnum::PurchaseSubscription]
        )) {
            return false;
        }

        // Update parent_id and performance period
        $latestChild = $order->children()
            ->select(['id', 'system_delivery_date_end'])
            ->orderBy('system_delivery_date_end', 'DESC')
            ->first();
        $order->parent_id = $order->id;
        $order->order_type_id = $orderType->id;
        $order->system_delivery_date = $latestChild?->system_delivery_date_end?->addDay() ??
            $order->system_delivery_date ?? $order->order_date;

        $currentDate = now();
        if ($currentDate->startOfDay()->equalTo($order->system_delivery_date)) {
            $order->system_delivery_date_end = $currentDate->addDay();
        } elseif ($currentDate->startOfDay()->isBefore($order->system_delivery_date)) {
            $order->system_delivery_date_end = $order->system_delivery_date->copy()->addDay();
        } else {
            $order->system_delivery_date_end = $currentDate;
        }

        try {
            $newOrder = ReplicateOrder::make($order)->validate()->execute();

            if ($autoPrintAndSend && $printLayouts && count($printLayouts) > 0) {
                $this->processPrintAndSend($newOrder, $printLayouts, $emailTemplateId);
            }
        } catch (Throwable $e) {
            $activity = activity()
                ->event(static::class)
                ->byAnonymous();

            if (in_array(LogsActivity::class, class_uses_recursive($order))) {
                $activity->performedOn($order);
            }

            if ($e instanceof ValidationException) {
                $activity->withProperties(['data' => $order, 'errors' => $e->errors()]);
            }

            $activity->log(class_basename($e));

            return false;
        }

        return true;
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }

    public static function description(): ?string
    {
        return 'Process given Subscription Order.';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(static::class);
    }

    public static function parameters(): array
    {
        return [
            'orderId' => null,
            'orderTypeId' => null,
            'printLayouts' => null,
            'autoPrintAndSend' => false,
            'emailTemplateId' => null,
        ];
    }

    protected function processPrintAndSend(Order $order, array $printLayouts, ?int $emailTemplateId = null): array
    {
        $availableViews = $order->resolvePrintViews();
        $attachments = [];
        $result = [];

        foreach ($printLayouts as $layoutName) {
            if (! array_key_exists($layoutName, $availableViews)) {
                continue;
            }

            $media = Printing::make([
                'model_type' => $order->getMorphClass(),
                'model_id' => $order->getKey(),
                'view' => $layoutName,
                'html' => false,
                'preview' => false,
            ])
                ->validate()
                ->execute()
                ->attachToModel($order);

            if ($media) {
                $attachments[] = [
                    'id' => $media->getKey(),
                    'name' => $media->file_name,
                ];
            }
        }

        if ($emailTemplateId && count($attachments) > 0 && $order->contact) {
            $address = in_array('invoice', $printLayouts) && $order->contact->invoiceAddress
                ? $order->contact->invoiceAddress
                : $order->contact->mainAddress;

            $to = $address->mail_addresses ?? [];

            if (array_diff($printLayouts, ['invoice'])) {
                $to[] = $order->contact->mainAddress?->email_primary;
            }

            $to = array_values(array_unique(array_filter($to)));

            if (! $to) {
                $result = SendMail::make([
                    'template_id' => $emailTemplateId,
                    'to' => $to,
                    'attachments' => $attachments,
                    'blade_parameters' => [
                        'order' => $order,
                        'contact' => $order->contact,
                    ],
                    'communicatables' => [
                        [
                            'model_type' => $order->getMorphClass(),
                            'model_id' => $order->getKey(),
                        ],
                    ],
                ])
                    ->validate()
                    ->execute();

                if (! data_get($result, 'success') ?? false) {
                    activity()
                        ->event('subscription_email_failed')
                        ->performedOn($order)
                        ->withProperties([
                            'error' => data_get($result, 'error'),
                            'message' => data_get($result, 'message'),
                            'email_template_id' => $emailTemplateId,
                            'to' => $to,
                        ])
                        ->log('Subscription email failed');
                }
            }
        }

        return $result;
    }
}
