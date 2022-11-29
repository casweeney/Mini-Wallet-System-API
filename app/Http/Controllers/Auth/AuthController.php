<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Wallet;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $this->validate($request, [
            'firstname'  => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        try {
            DB::beginTransaction();

            $user = User::create($request);
            
            $wallet = new Wallet;
            $wallet->user_id = $user->id;
            $wallet->balance = 0;
            $wallet->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return "login successful";    
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return "logged out";
    }
}
