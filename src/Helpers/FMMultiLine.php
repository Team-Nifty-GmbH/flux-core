<?php

namespace FluxErp\Helpers;

use Exception;
use Illuminate\Support\Collection;

class FMMultiLine
{
    /**
     * @return mixed
     */
    public static function buildQuery(string $multiLine, object $class, $column = 'uuid'): Collection
    {
        if (empty($multiLine)) {
            return new Collection();
        }

        $query = $class->query();
        $uuids = preg_split('/\n|\r\n?/', $multiLine);
        if (count($uuids) > 0) {
            $query->where('uuid', $uuids[0]);
            array_shift($uuids);
            foreach ($uuids as $uuid) {
                $query->orWhere($column, $uuid);
            }
        } else {
            return new Collection();
        }

        try {
            $result = $query->get();
        } catch (Exception $e) {
            $result = new Collection();
        }

        return $result;
    }
}
