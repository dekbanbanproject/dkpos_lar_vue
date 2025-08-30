<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public function showLogin() {
        return view('auth.customer_login');
    }

    public function login(Request $request) {
        $data = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // อนุญาตทั้งอีเมลและเบอร์โทร
        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::guard('customer')->attempt([$field => $data['login'], 'password' => $data['password']])) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.home'));
        }
        return back()->withErrors(['login' => 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง'])->onlyInput('login');
    }

    public function logout(Request $request) {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}
