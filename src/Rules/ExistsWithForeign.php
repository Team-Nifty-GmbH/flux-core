<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsWithForeign implements DataAwareRule, ValidationRule
{
    protected array $data;

    /**
     * @param  string  $foreignAttribute  Example: client_id, the value is retrieved from the validation data array
     *                                    and has to match $attributeColumn on $table (or $throughTable if set).
     * @param  string  $table  Example: addresses, table used for the regular exists.
     * @param  string  $column  Used for the exists statement, the attribute value has to match the column value
     * @param  string|null  $baseTable  If $this->data doesnt have the $foreignAttribute key
     *                                  retrieve the value from the record.
     * @param  string|null  $attributeColumn  The column name that has to match the
     *                                        value from $foreignAttribute in $table (or $throughTable if set).
     * @param  string|null  $throughTable  Table name to join.
     * @param  string  $throughLocal  The local key from $table that has to match on throughForeign.
     * @param  string|null  $throughForeign  The foreign key on $throughTable that has to match on $throughLocal.
     */
    public function __construct(
        protected string $foreignAttribute,
        protected string $table,
        protected string $column = 'id',
        protected ?string $baseTable = null,
        protected ?string $attributeColumn = null,
        protected ?string $throughTable = null,
        protected string $throughLocal = 'id',
        protected ?string $throughForeign = null
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $prefix = strpos($attribute, '.') ? pathinfo($attribute, PATHINFO_FILENAME) : null;
        $data = data_get($this->data, $prefix);

        if (! ($data[$this->foreignAttribute] ?? false) && ($data['id'] ?? false)) {
            // If the foreignAttribute is not present in $data we have to gather it from the database
            $record = DB::table($this->baseTable)
                ->select($this->foreignAttribute)
                ->where('id', $data['id'])
                ->first();
            if (! $record) {
                $fail('validation.exists')->translate();

                return;
            }

            $foreignAttributeValue = $record->{$this->foreignAttribute};
        } else {
            $foreignAttributeValue = $data[$this->foreignAttribute] ?? null;

            if (! $foreignAttributeValue) {
                $fail('validation.required')->translate(['attribute' => $this->foreignAttribute]);

                return;
            }
        }

        $query = DB::table($this->table);

        // If not attributeColumn is set we expect that the attributeColumn has the same name as
        // the foreignAttribute.
        $this->attributeColumn = $this->attributeColumn ?: $this->foreignAttribute;

        // If $throughLocal is NOT id it's a BelongsTo.
        // If $throughLocal is id it's a Has relation.
        if ($this->throughLocal !== 'id' && ! $this->throughForeign) {
            // It's a BelongsTo, the default related key is id.
            $this->throughForeign = 'id';
        }

        if (! $this->throughTable) {
            $query->where($this->attributeColumn, $foreignAttributeValue)->where($this->column, $value);
        } else {
            $query->where($this->table . '.' . $this->column, $value);
            $query->join(
                $this->throughTable,
                function ($join) use ($foreignAttributeValue): void {
                    $join->on(
                        $this->table . '.' . $this->throughLocal,
                        '=',
                        $this->throughTable . '.' . $this->throughForeign
                    );
                    $join->where(
                        $this->throughTable . '.' . $this->attributeColumn,
                        $foreignAttributeValue
                    );
                }
            );
            $query->select($this->throughTable . '.*');
        }

        if (! $query->count()) {
            if (! $this->throughTable) {
                $fail(
                    sprintf(
                        'Doesnt exist with foreign key \'%s\'.',
                        $this->attributeColumn
                    )
                )->translate();
            } else {
                $fail(
                    sprintf(
                        'Doesnt exist with foreign key \'%s\' on table \'%s\'.',
                        $this->attributeColumn,
                        $this->throughTable
                    )
                )->translate();
            }
        }
    }
}
