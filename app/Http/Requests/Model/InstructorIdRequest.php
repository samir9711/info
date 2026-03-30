<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;

class InstructorIdRequest extends BasicRequest
{
    public function rules(): array
    {
        return [
            'instructor_id' => ['required', 'integer', 'exists:instructors,id'],
        ];
    }
}
