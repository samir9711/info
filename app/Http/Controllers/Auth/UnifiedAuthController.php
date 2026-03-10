<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Model\Auth\LoginRequest;
use App\Services\Functional\UnifiedAuthService;
use Illuminate\Http\JsonResponse;

class UnifiedAuthController extends Controller
{
    protected UnifiedAuthService $service;

    public function __construct(UnifiedAuthService $service)
    {
        $this->service = $service;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->service->login($request);
            return response()->json([
                'data' => $result,
                'status' => true
            ], 200);
        } catch (\Throwable $e) {
            // حافظ على شكل خطأ مطابق للنظام عندك
            $status = $e instanceof \Illuminate\Http\Exceptions\HttpResponseException ? 422 : 500;
            $msg = method_exists($e, 'getMessage') ? $e->getMessage() : 'error';
            return response()->json([
                'data' => null,
                'status' => false,
                'error' => $msg,
            ], $status);
        }
    }
}
