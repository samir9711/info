<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;use Illuminate\Foundation\Http\FormRequest;

class StoreOtpRequest extends BasicRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verifiable_type' => 'required|string|max:255',
            'verifiable_id' => 'required|integer',
            'otp' => 'required|string|max:6',
            'purpose' => 'required|string|max:50',
            'channel' => 'nullable|in:sms,whatsapp,email',
            'is_used' => 'required|boolean',
            'expires_at' => 'nullable|date_format:Y-m-d H:i:s',
            'verified_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

}
