<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class ShowCompanyContactInfoRequest extends BasicRequest
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
        return [
            'company_id' => auth('admin')->check()
                ? 'required|integer|exists:companies,id'
                : 'sometimes|integer|exists:companies,id',
        ];
    }
}
