<?php

namespace App\Http\Requests\Warehouse;

use App\Enums\Warehouse\WarehouseStatus;
use App\Models\Warehouse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique(Warehouse::class, 'code')],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(WarehouseStatus::class)],
        ];
    }
}
