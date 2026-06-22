<?php

namespace App\Livewire\Forms\Commercial;

use Illuminate\Validation\Rule;
use Livewire\Form;

class VendorForm extends Form
{
    public string $companyId = '';

    public string $name = '';

    public string $contactName = '';

    public string $email = '';

    public string $phone = '';

    public function rules(): array
    {
        return [
            'companyId' => ['required', 'integer', Rule::exists('companies', 'id')],
            'name' => ['required', 'string', 'max:180'],
            'contactName' => ['nullable', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'contactName', 'email', 'phone']);
    }
}
