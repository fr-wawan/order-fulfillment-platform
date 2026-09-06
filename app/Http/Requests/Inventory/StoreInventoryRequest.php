<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory;
use App\Models\Sku;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'sku_id' => [
                'required',
                Rule::exists(Sku::class, 'id'),
                Rule::unique(Inventory::class, 'sku_id')
                    ->where('warehouse_id', $this->route('warehouse')->id),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
