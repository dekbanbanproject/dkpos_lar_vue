<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffAuthController extends Controller
{
    public function showLogin() {
        return view('auth.staff_login');
    }

    public function login(Request $request) {
        $data = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('web')->attempt([$field => $data['login'], 'password' => $data['password'], 'is_active' => 1])) {
            $request->session()->regenerate();
            return redirect()->intended(route('pos'));
        }
        return back()->withErrors(['login' => 'บัญชีหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('login');
    }

    public function logout(Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
