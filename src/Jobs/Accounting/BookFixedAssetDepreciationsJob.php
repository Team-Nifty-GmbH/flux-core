<?php

namespace FluxErp\Jobs\Accounting;

use Cron\CronExpression;
use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class BookFixedAssetDepreciationsJob implements Repeatable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __invoke(): void
    {
        $this->handle();
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('0 2 1 * *');
    }

    public static function description(): ?string
    {
        return 'Book the depreciations of fixed assets that are due';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }

    public static function repeatableType(): RepeatableTypeEnum
    {
        return RepeatableTypeEnum::Invokable;
    }

    public function handle(): void
    {
        BookFixedAssetDepreciations::make()
            ->validate()
            ->execute();
    }
}
