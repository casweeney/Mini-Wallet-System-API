<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname'  => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'error' => $validator->errors()->first()
            ], 406);
        }

        try {
            DB::beginTransaction();

            $user = new User;
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->save();

            $wallet = new Wallet;
            $wallet->user_id = $user->id;
            $wallet->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'signup successful'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'error' => "Request failed"
            ], 400);
        }
    }

    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'error' => $validator->errors()->first()
            ], 406);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'status' => 'success',
                'message' => 'login successful'
            ], 200);   
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return "logged out";
    }
}
