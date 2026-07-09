<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Validation\Rule;

class ChangeCompanyJobApplicationStatusRequest extends BasicRequest
{
    public function rules(): array
    {
        return [

            'id' => [
                'required',
                'exists:company_job_applications,id'
            ],

            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'reviewing',
                    'accepted',
                    'rejected',
                    'withdrawn',
                ]),
            ],

            'company_note' => [
                'nullable',
                'string',
            ],

        ];
    }
}
