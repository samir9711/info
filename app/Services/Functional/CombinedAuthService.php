<?php

namespace App\Services\Functional;

use App\Http\Resources\Model\InstructorResource;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Resources\Model\AdminResource;
use App\Http\Resources\Model\UserResource;

class CombinedAuthService
{
    public function login(Request $request): array
    {
        $email = $request->input('email');
        $password = $request->input('password');

        
        $admin = Admin::where('email', $email)->first();
        if ($admin && $admin->password && Hash::check($password, $admin->password)) {
            $token = $admin->createToken('access-token')->plainTextToken;
            $admin->loadMissing(['roles','permissions']);
            return ['token' => $token, 'admin' => AdminResource::make($admin)];
        }


        $instructor = Instructor::where('email', $email)->first();
        if ($instructor && $instructor->password && Hash::check($password, $instructor->password)) {
            $token = $instructor->createToken('access-token')->plainTextToken;
            return ['token' => $token, 'instructor' => InstructorResource::make($instructor)];
        }

        throw new HttpResponseException(response()->json([
            'message' => __('messages.invalid_credentials')
        ], 422));
    }
}
