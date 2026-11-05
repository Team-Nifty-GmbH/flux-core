<?php

namespace FluxErp\Traits\Action;

use FluxErp\Models\Calendar;

trait ChecksCalendarAccess
{
    public function checkPermission(): static
    {
        parent::checkPermission();

        resolve_static(
            Calendar::class,
            'ensureWritable',
            ['calendarIds' => array_filter($this->calendarIds(), 'is_scalar')]
        );

        return $this;
    }

    protected function calendarIds(): array
    {
        return array_filter([
            $this->getData('id'),
            $this->getData('parent_id'),
        ]);
    }
}
