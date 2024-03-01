<?php

namespace FluxErp\Helpers;

use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ModelFilter
{
    private string $subject;

    private $query;

    private array $operators;

    private string $searchString = '';

    private array $collectionFilters = [];

    private array $queryFilters = [];

    private ?array $includes = null;

    private array $sorts = [];

    private array $allowedFilters;

    private array $allowedIncludes;

    private array $allowedSorts;

    public static function filterModel(string $model,
        ?array $allowedFilters = null,
        ?array $allowedSorts = null,
        array|string|null $search = null,
        array|string|null $filter = null,
        array|string|null $include = null,
        array|string|null $sort = null): array
    {
        $modelFilter = ModelFilter::for($model);

        if (! $modelFilter) {
            return [];
        }

        $modelInstance = app($model);

        if (! is_null($allowedFilters)) {
            $modelFilter->allowedFilters($allowedFilters);
        }

        $includes = array_diff(
            array_keys($modelInstance->relationships()),
            ['additionalColumns', 'relatedModel', 'relatedBy']
        );
        if (count($includes) > 0) {
            $modelFilter->allowedIncludes($includes);
        }

        $relatedAllowedFilters = [];
        foreach ($includes as $includeItem) {
            $includeAttributes = array_diff(
                Schema::getColumnListing($modelInstance->$includeItem()->getRelated()->getTable()),
                $modelInstance->$includeItem()->getRelated()->getHidden()
            );

            array_walk($includeAttributes, function (&$item, $key, $prefix) {
                $item = $prefix . $item;
            }, $includeItem . '.');
            $relatedAllowedFilters = array_merge($relatedAllowedFilters, $includeAttributes);
        }

        $modelFilter->addAllowedFilters($relatedAllowedFilters);

        if (! is_null($allowedSorts)) {
            $modelFilter->allowedSorts($allowedSorts);
        }
        $modelFilter->addAllowedSorts($relatedAllowedFilters);

        if (! is_null($include)) {
            $modelFilter->addIncludes($include);
        }

        if (! is_null($search)) {
            $modelFilter->addSearchString($search);
        }

        if (! is_null($filter)) {
            $additionalColumns = app(AdditionalColumn::class)->query()
                ->where('model_type', $model)
                ->get()
                ->pluck('name')
                ->toArray();
            $modelFilter->addFilters($filter, $additionalColumns);
        }

        if (! is_null($sort)) {
            $modelFilter->addSorts($sort);
        }

        $urlParams = self::calculateUrlParameters($modelFilter);

        return [
            'urlParams' => $urlParams ?: null,
            'data' => $modelFilter->filter(),
        ];
    }

    public function __construct(string $subject, array $operators)
    {
        $allowedOperators = ['=', '!', '<', '>', '<>', '><', '%'];
        $this->subject = $subject;
        $this->operators = array_intersect($allowedOperators, $operators);
        $this->query = null;

        $model = app($subject);
        $this->allowedFilters = array_values(array_diff(
            Schema::getColumnListing($model->getTable()),
            $model->getHidden()
        ));
        $this->allowedSorts = array_values($this->allowedFilters);
        $this->allowedIncludes = [];
    }

    public static function for(string $subject, array $allowedOperators = ['=', '!', '<', '>', '<>', '><', '%']): ?self
    {
        if (is_subclass_of($subject, Model::class)) {
            return new static($subject, $allowedOperators);
        }

        return null;
    }

    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    public function getAllowedIncludes(): array
    {
        return $this->allowedIncludes;
    }

    public function getAllowedSorts(): array
    {
        return $this->allowedSorts;
    }

    public function getCollectionFilters(): array
    {
        return $this->collectionFilters;
    }

    public function getQueryFilters(): array
    {
        return $this->queryFilters;
    }

    public function getIncludes(): ?array
    {
        return $this->includes;
    }

    public function getSearch(): string
    {
        return $this->searchString;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public function allowedFilters(array $allowedFilters): void
    {
        $this->allowedFilters = $allowedFilters;
    }

    public function allowedIncludes(array $allowedIncludes): void
    {
        $this->allowedIncludes = $allowedIncludes;
    }

    public function allowedSorts(array $allowedSorts): void
    {
        $this->allowedSorts = $allowedSorts;
    }

    public function addAllowedFilters(array $allowedFilters): void
    {
        $this->allowedFilters(array_unique(array_merge($this->allowedFilters, $allowedFilters)));
    }

    public function addAllowedIncludes(array $allowedIncludes): void
    {
        $this->allowedIncludes(array_unique(array_merge($this->allowedIncludes, $allowedIncludes)));
    }

    public function addAllowedSorts(array $allowedSorts): void
    {
        $this->allowedSorts(array_unique(array_merge($this->allowedSorts, $allowedSorts)));
    }

    public function addSearchString(string $searchString): void
    {
        if ($searchString) {
            $this->searchString = $searchString;
        }
    }

    public function addFilters(array|string $filters, array $additionalColumns = []): void
    {
        if (is_string($filters)) {
            $filters = [$filters];
        }

        $this->queryFilters = [];
        $this->collectionFilters = [];
        $filterParams = [];
        foreach ($filters as $filter) {
            $params = explode(',', $filter);
            array_walk($params, function (&$item) {
                $item = explode('|', $item);
            });

            $filterParams[] = $this->sanitizeFilters($params);
        }

        if ($this->searchString) {
            $this->collectionFilters = array_merge($this->collectionFilters, $filterParams);
        } else {
            foreach ($filterParams as $fKey => $filterParam) {
                foreach ($filterParam as $param) {
                    if (str_contains($param[0], '.') || in_array($param[0], $additionalColumns)) {
                        $this->collectionFilters[] = $filterParam;
                        unset($filterParams[$fKey]);
                        break;
                    }
                }
            }

            $this->queryFilters = array_merge($this->queryFilters, $filterParams);
        }
    }

    public function addQueryFilters($filters): void
    {
        if (! $filters) {
            $filters = [];
        }

        foreach ($filters as $filter) {
            $params = explode(',', $filter);
            array_walk($params, function (&$item) {
                $item = explode('|', $item);
            });

            $this->queryFilters[] = $this->sanitizeFilters($params);
        }
    }

    public function addCollectionFilters($filters): void
    {
        if (! $filters) {
            $filters = [];
        }

        foreach ($filters as $filter) {
            $params = explode(',', $filter);
            array_walk($params, function (&$item) {
                $item = explode('|', $item);
            });

            $this->collectionFilters[] = $this->sanitizeFilters($params);
        }
    }

    public function addIncludes(array|string $includes): void
    {
        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        $this->includes = array_intersect($this->allowedIncludes, $includes);
    }

    public function addSorts(array|string $sorts): void
    {
        $this->sorts = $this->sanitizeSorts(is_string($sorts) ? [$sorts] : $sorts);
    }

    public function filter(): Collection
    {
        if ($this->searchString) {
            $collection = app($this->subject)->search($this->searchString);

            if (! is_null($this->includes) && count($this->includes) > 0) {
                $collection->query(fn ($query) => $query->with($this->includes));
            }

            $collection = $collection->get();
        } else {
            $this->query = app($this->subject)->query();

            if (count($this->queryFilters) > 0) {
                $or = false;
                foreach ($this->queryFilters as $queryFilter) {
                    $this->query = $this->addWhereClauses($this->query, $queryFilter, $or);
                }
            }

            if (! is_null($this->includes) && count($this->includes) > 0) {
                $this->query = $this->query->with($this->includes);
            }

            $collection = $this->query->get();
        }

        if (count($this->collectionFilters) > 0) {
            $filteredCollection = new Collection();
            foreach ($this->collectionFilters as $collectionFilter) {
                $filteredCollection = $filteredCollection->merge(
                    $this->addWhereClauses(query: $collection, params: $collectionFilter, collection: true)
                );
            }
            $collection = $filteredCollection->unique();
        }

        return $collection->sortBy($this->sorts);
    }

    private function addWhereClauses($query, array $params, bool &$or = false, bool $collection = false): mixed
    {
        if ($collection) {
            foreach ($params as $param) {
                if (count($param) < 3) {
                    continue;
                }

                if ($param[1] === 'LIKE') {
                    $query = $query->filter(function ($value, $key) use ($param) {
                        $search = trim($param[2], '%');
                        $explodedParam = explode('.', $param[0]);

                        if (count($explodedParam) === 2) {
                            if (str_starts_with($param[2], '%') && str_ends_with($param[2], '%')) {
                                return str_contains($value->{$explodedParam[0]}->{$explodedParam[1]}, $search);
                            } elseif (str_starts_with($param[2], '%')) {
                                return str_ends_with($value->{$explodedParam[0]}->{$explodedParam[1]}, $search);
                            } elseif (str_ends_with($param[2], '%')) {
                                return str_starts_with($value->{$explodedParam[0]}->{$explodedParam[1]}, $search);
                            } else {
                                return $value->{$explodedParam[0]}->{$explodedParam[1]} === $search;
                            }
                        } elseif (count($explodedParam) === 1) {
                            if (str_starts_with($param[2], '%') && str_ends_with($param[2], '%')) {
                                return str_contains($value->{$explodedParam[0]}, $search);
                            } elseif (str_starts_with($param[2], '%')) {
                                return str_ends_with($value->{$explodedParam[0]}, $search);
                            } elseif (str_ends_with($param[2], '%')) {
                                return str_starts_with($value->{$explodedParam[0]}, $search);
                            } else {
                                return $value->{$explodedParam[0]} === $search;
                            }
                        }

                        return false;
                    });
                } else {
                    $query = $query->where($param[0], $param[1], $param[2]);
                }
            }

            return $query;
        } else {
            if (! $or) {
                $or = true;

                $query->where(function ($query) use ($params) {
                    foreach ($params as $param) {
                        if (count($param) < 3) {
                            continue;
                        }

                        $query->where($param[0], $param[1], $param[2]);
                    }

                    return $query;
                });
            } else {
                $query->orWhere(function ($query) use ($params) {
                    foreach ($params as $param) {
                        $count = count($param);

                        if ($count < 3) {
                            continue;
                        }

                        $query->where($param[0], $param[1], $param[2]);
                    }

                    return $query;
                });
            }
        }

        return $query;
    }

    private function sanitizeFilters(array $params): array
    {
        if (in_array('=', $this->operators)) {
            array_walk($params, function (&$item) {
                if (count($item) === 2) {
                    $item[2] = $item[1];
                    $item[1] = '=';
                }
            });
        }

        foreach ($params as $key => $param) {
            if (! in_array($param[0], $this->allowedFilters)) {
                unset($params[$key]);

                continue;
            }

            if (count($param) < 3) {
                unset($params[$key]);

                continue;
            }

            if (! in_array($param[1], $this->operators)) {
                unset($params[$key]);

                continue;
            }

            if (str_contains($param[0], '.') &&
                ! in_array(explode('.', $param[0])[0], $this->includes ?? [])) {
                unset($params[$key]);

                continue;
            }

            switch ($param[1]) {
                case '!':
                    $params[$key][1] = '!=';
                    break;
                case '<>':
                    $params[$key][1] = '<=';
                    break;
                case '><':
                    $params[$key][1] = '>=';
                    break;
                case '%':
                    $params[$key][1] = 'LIKE';
                    break;
                default:
                    break;
            }
        }

        return $params;
    }

    private function sanitizeSorts(array $sorts): array
    {
        $sortDirections = ['asc', 'desc'];
        $sanitizedSorts = [];
        foreach ($sorts as $sort) {
            $sort = explode('|', $sort, 2);
            if (in_array($sort[0], $this->allowedSorts) && in_array(strtolower($sort[1]), $sortDirections)) {
                $sanitizedSorts[] = $sort;
            }
        }

        return $sanitizedSorts;
    }

    private static function calculateUrlParameters(ModelFilter $modelFilter): string
    {
        $urlParams = '';
        $i = 0;
        $filters = array_merge($modelFilter->getQueryFilters(), $modelFilter->getCollectionFilters());
        $includes = $modelFilter->getIncludes();
        $searchString = $modelFilter->getSearch();
        $sorts = $modelFilter->getSorts();

        foreach ($filters as $filter) {
            $conditions = '';
            foreach ($filter as $condition) {
                $conditions .= implode('|', $condition) . ',';
            }

            $urlParams .= 'filter[' . $i . ']=' . rtrim($conditions, ',') . '&';
            $i++;
        }

        if (! is_null($includes) && count($includes) > 0) {
            $urlParams .= 'include=' . implode(',', $includes) . '&';
        }

        if ($searchString) {
            $urlParams .= 'search=' . $searchString . '&';
        }

        if (count($sorts) === 1) {
            $urlParams .= 'sort=' . implode('|', $sorts[0]);
        } elseif (count($sorts) > 1) {
            $j = 0;
            foreach ($sorts as $sort) {
                $args = implode('|', $sort) . ',';
                $urlParams .= 'sort[' . $j . ']=' . rtrim($args, ',') . '&';
                $j++;
            }
        }

        if ($urlParams) {
            $urlParams = '?' . rtrim($urlParams, '&');
        }

        return $urlParams;
    }
}
