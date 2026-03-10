<?php

namespace App\Http\Requests\Model;

use App\Http\Requests\Basic\BasicRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends BasicRequest
{
    protected bool $isUpdate = false;
    protected $currentId = null;


    protected function prepareForValidation(): void
    {

        $id = $this->input('id') ?? $this->route('id') ?? $this->route('user') ?? $this->input('user.id') ?? null;


        if (is_object($id) && isset($id->id)) {
            $id = $id->id;
        }

        if (is_numeric($id)) {
            $id = (int) $id;
        } else {
            $id = null;
        }

        $this->currentId = $id;
        $this->isUpdate  = !empty($this->currentId);

        if ($this->isUpdate && !$this->has('id')) {
            $this->merge(['id' => $this->currentId]);
        }
    }

    public function rules(): array
    {

        $emailUniqueRule = Rule::unique('users', 'email');
        $phoneUniqueRule = Rule::unique('users', 'phone');

        if ($this->currentId) {
            $emailUniqueRule = $emailUniqueRule->ignore($this->currentId);
            $phoneUniqueRule = $phoneUniqueRule->ignore($this->currentId);
        }

        if ($this->isUpdate) {
            return [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'father_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'image' => 'nullable|string|max:255',
                'gender' => 'nullable|in:male,female',
                'birth_date' => 'nullable|date',
                'residence' => 'nullable|string|max:255',
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', $emailUniqueRule],
                'phone' => ['sometimes', 'required', 'string', 'max:255', $phoneUniqueRule],
                'email_verified_at' => 'nullable|date_format:Y-m-d H:i:s',
                'email_status' => 'nullable|boolean',
                'activated_at' => 'nullable|date_format:Y-m-d H:i:s',
                // عند التحديث: كلمة المرور اختيارية
                'password' => 'nullable|string|max:255',
                'status' => 'nullable|boolean',
                'otp_delivery_method' => 'nullable|in:sms,whatsapp,email',
                'remember_token' => 'nullable|string|max:100',
               

            ];
        }

        // إنشاء جديد
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'residence' => 'nullable|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', $emailUniqueRule],
            'phone' => ['nullable', 'string', 'max:255', $phoneUniqueRule],
            'email_verified_at' => 'nullable|date_format:Y-m-d H:i:s',
            'email_status' => 'nullable|boolean',
            'activated_at' => 'nullable|date_format:Y-m-d H:i:s',
            'password' => 'required|string|max:255',
            'status' => 'nullable|boolean',
            'otp_delivery_method' => 'nullable|in:sms,whatsapp,email',
            'remember_token' => 'nullable|string|max:100',


        ];
    }
}
