<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'text' => $this->text,
            'step' => $this->formatStep($this->step),
            'is_active' => $this->is_active,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
        ];
    }

    /**
     * Format step value to remove unnecessary decimal places
     */
    private function formatStep($step)
    {
        $numericStep = (float) $step;
        
        // If step is a whole number, return as integer
        if ($numericStep == (int) $numericStep) {
            return (int) $numericStep;
        }
        
        // Otherwise, return as float without trailing zeros
        return (float) $numericStep;
    }
}
