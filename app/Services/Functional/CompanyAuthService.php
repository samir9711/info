<?php

namespace App\Services\Functional;

use App\Http\Requests\Basic\BasicRequest;
use App\Http\Resources\Model\CompanyResource;
use App\Models\Company;
use App\Services\Basic\BaseAuthService;
use App\Support\OtpChannelHelper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class CompanyAuthService extends BaseAuthService
{
    protected function setVariables(): void
    {
        $this->model    = Company::class;
        $this->key      = 'company';
        $this->resource = CompanyResource::class;
        $this->guard    = 'company';
    }

    public function login(BasicRequest $request): array
    {
        $email  = $request->input('email');
        $pass   = $request->input('password');

        $company = $this->model::where('email', $email)->first();
        if (!$company || !$company->password || !Hash::check($pass, $company->password)) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_credentials')));
        }

        [$token] = $this->issueToken($company, $request->input('token_device'));
        return ['token' => $token, $this->key => $this->resource::make($company)];
    }

    public function verifyOtp(BasicRequest $request): array
    {
        /*
         * note: no need for manage this here you can override it in son needed service or even not put the route in the api
         * if ($this->guard !== 'user') {
            throw new HttpResponseException($this->requiredField(__('messages.unavailable_operation')));
        }*/

        if($request->purpose === "phone_change")
            return $this->verifyPhoneChangeOtp($request);

        $company = $this->model::where("email",$request->input('email'))->firstOrFail();
        $channel = $company->otp_delivery_method?? "email";

        $ok = OtpChannelHelper::verify($company, $request->input("purpose"), (string) $request->input('otp'), $channel);
        if (!$ok) {
            throw new HttpResponseException($this->requiredField(__('messages.invalid_otp')));
        }

        return [
            'message' => "",
            $this->key => $this->resource::make($company->fresh()),
        ];
    }
}
