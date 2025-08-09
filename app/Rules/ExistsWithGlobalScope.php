<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Exists that uses the eloquent query builder to apply global scope.
 */
class ExistsWithGlobalScope implements ValidationRule
{
    /**
     * The model to run the query against.
     *
     * @var class-string<Model>
     */
    protected $modelClass;

    /**
     * The column to check on.
     *
     * @var string
     */
    protected $column;

    /**
     * Create a new rule instance.
     *
     * @param string $modelClass
     * @param string $column
     */
    public function __construct(string $modelClass, string $column)
    {
        $this->column = $column;
        $this->modelClass = $modelClass;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_subclass_of($this->modelClass, Model::class)) {
            throw new \InvalidArgumentException(sprintf('Model Class "%s" must be a subclass of %s', $this->modelClass, Model::class));
        };

        $query = $this->modelClass::query();

        $query->where($this->column, $value);

        if (!$query->exists()) {
            $fail("The selected {$attribute} is invalid.");
        }
    }
}
