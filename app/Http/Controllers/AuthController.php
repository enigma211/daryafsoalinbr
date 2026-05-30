<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'unique:users,mobile'],
        ], [
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است. لطفاً از بخش ورود وارد پنل شوید.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست (مثال: 09123456789).',
        ]);

        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            // Provide a random password and dummy email to satisfy DB constraints if any
            'email' => 'user_' . Str::random(8) . '@example.com',
            'password' => Hash::make(Str::random(16)), 
        ]);

        // Assign 'Question Designer' role by default
        $user->assignRole('Question Designer');

        return redirect('/designer/login')->with('success', 'ثبت نام شما با موفقیت انجام شد. اکنون می‌توانید با شماره موبایل خود وارد شوید.');
    }
}
