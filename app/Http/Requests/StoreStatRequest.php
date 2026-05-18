<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('character'));
    }

    public function rules(): array
    {
        $character = $this->route('character');

        return [
            'stat.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stats', 'name')->where('character_id', $character->id),
            ],
            'stat.value' => [
                'required',
                'integer',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) use ($character) {
                    if ($character->stats()->sum('value') + $value > 100) {
                        $fail('Total stat points cannot exceed 100.');
                    }
                },
            ],
        ];
    }
}
