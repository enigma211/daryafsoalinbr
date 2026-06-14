<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'unique:users,mobile'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است. لطفاً از بخش ورود وارد پنل شوید.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست (مثال: 09123456789).',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور باید حداقل 8 رقم باشد.',
            'password.letters' => 'رمز عبور باید حداقل شامل یک حرف باشد.',
            'password.numbers' => 'رمز عبور باید حداقل شامل یک عدد باشد.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password), 
        ]);

        // Assign 'Question Designer' role by default
        $user->assignRole('Question Designer');

        return back()->with('success', 'ثبت نام با موفقیت انجام شد. از طریق لینک زیر به پنل کاربری وارد شوید:');
    }
}
