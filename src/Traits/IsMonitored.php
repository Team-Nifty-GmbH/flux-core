<?php

namespace FluxErp\Traits;

use FluxErp\Models\QueueMonitor;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\QueueMonitor\QueueMonitorManager;
use Illuminate\Support\HtmlString;

trait IsMonitored
{
    private ?int $progressLastUpdated = null;

    private int $progressCurrentChunk = 0;

    public function getName(): string
    {
        return get_class($this);
    }

    public function queueUpdate(array $attributes): void
    {
        if ($this->isQueueProgressOnCooldown($progress = data_get($attributes, 'progress', 0))) {
            return;
        }

        if (! $monitor = $this->getQueueMonitor()) {
            return;
        }

        $progress = $progress / 100;
        $attributes['progress'] = $progress;

        $monitor->update($attributes);
    }

    public function queueProgress(int $progress): void
    {
        $progress = min(100, max(0, $progress));

        if ($this->isQueueProgressOnCooldown($progress)) {
            return;
        }

        if (! $monitor = $this->getQueueMonitor()) {
            return;
        }

        $monitor->update([
            'progress' => $progress / 100,
        ]);

        $this->progressLastUpdated = time();
    }

    public function queueProgressAdvance(int $step = 1): void
    {
        $this->queueProgress($this->getQueueMonitor()->progress * 100 + $step);
    }

    public function queueProgressChunk(int $total, int $perChunk): void
    {
        $this->queueProgress(
            ++$this->progressCurrentChunk * $perChunk / $total * 100
        );
    }

    public function queueData(array $data, bool $merge = false): void
    {
        if (! $monitor = $this->getQueueMonitor()) {
            return;
        }

        if ($merge) {
            $data = array_merge($monitor->data, $data);
        }

        $monitor->update([
            'data' => $data,
        ]);
    }

    public function accept(NotificationAction $action): void
    {
        $this->getQueueMonitor()->update([
            'accept' => serialize($action),
        ]);
    }

    public function reject(NotificationAction $reject): void
    {
        $this->getQueueMonitor()->update([
            'reject' => serialize($reject),
        ]);
    }

    public function message(HtmlString|string $message): void
    {
        $this->getQueueMonitor()->update([
            'message' => is_a($message, HtmlString::class, true)
                ? $message->toHtml()
                : $message,
        ]);
    }

    private function isQueueProgressOnCooldown(float|int $progress): bool
    {
        if (in_array($progress, [0, 25, 50, 75, 100])) {
            return false;
        }

        if (is_null($this->progressLastUpdated)) {
            return false;
        }

        return time() - $this->progressLastUpdated < $this->progressCooldown();
    }

    protected function deleteQueueMonitor(): void
    {
        if (! $monitor = $this->getQueueMonitor()) {
            return;
        }

        $monitor->delete();
    }

    protected function getQueueMonitor(): ?QueueMonitor
    {
        if (! property_exists($this, 'job')) {
            return null;
        }

        if (! $this->job) {
            return null;
        }

        if (! $jobId = QueueMonitorManager::getJobId($this->job)) {
            return null;
        }

        return app(QueueMonitor::class)
            ->where('job_id', $jobId)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    public static function keepMonitorOnSuccess(): bool
    {
        return true;
    }

    public function progressCooldown(): int
    {
        return 0;
    }
}
