<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Unit;

class IntegerIfStepOne implements ValidationRule
{
    protected $unitId;

    public function __construct($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $unit = Unit::find($this->unitId);
        
        if (!$unit) {
            return;
        }

        // If unit step is 1 (integer unit like "Adet"), value must be integer
        if ($unit->step == 1 && $value != floor($value)) {
            $fail('The :attribute must be an integer when using unit "' . $unit->text . '".');
        }
    }
}
