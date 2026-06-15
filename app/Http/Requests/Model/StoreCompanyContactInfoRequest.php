<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class StoreCompanyContactInfoRequest extends BasicRequest
{
    public function prepareForValidation(): void
    {
        if (auth('company')->check()) {
            $this->merge([
                'company_id' => auth('company')->id(),
            ]);
        }
    }

    public function rules(): array
    {
        $isAdmin = auth('admin')->check();

        return [
            'company_id' => $isAdmin
                ? 'required|integer|exists:companies,id'
                : 'sometimes|integer|exists:companies,id',

            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'x' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'contact_address' => 'nullable|string',
            'working_hours' => 'nullable|string|max:255',
        ];
    }
}
