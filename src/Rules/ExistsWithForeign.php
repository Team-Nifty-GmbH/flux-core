<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;

class ExistsWithForeign implements DataAwareRule, InvokableRule
{
    protected array $data;

    /**
     * @return $this|ExistsWithForeign
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

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
        public string $foreignAttribute,
        public string $table,
        public string $column = 'id',
        public ?string $baseTable = null,
        public ?string $attributeColumn = null,
        public ?string $throughTable = null,
        public string $throughLocal = 'id',
        public ?string $throughForeign = null
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
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
            }

            $foreignAttributeValue = $record->{$this->foreignAttribute};
        } else {
            $foreignAttributeValue = $data[$this->foreignAttribute] ?? null;

            if (! $foreignAttributeValue) {
                $fail('validation.required')->translate(['attribute' => $this->foreignAttribute]);
            }
        }

        $query = DB::table($this->table);

        // If not attributeColumn is set we expect that the attributeColumn has the same name as
        // the foreignAttribute.
        $this->attributeColumn = $this->attributeColumn ?: $this->foreignAttribute;

        // If $throughLocal is NOT id its a BelongsTo.
        // If $throughLocal is id its a Has relation.
        if ($this->throughLocal !== 'id' && ! $this->throughForeign) {
            // Its a BelongsTo, the default related key is id.
            $this->throughForeign = 'id';
        }

        if (! $this->throughTable) {
            $query->where($this->attributeColumn, $foreignAttributeValue)->where($this->column, $value);
        } else {
            $query->where($this->table . '.' . $this->column, $value);
            $query->join(
                $this->throughTable,
                function ($join) use ($foreignAttributeValue) {
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
