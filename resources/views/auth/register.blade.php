<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت نام طراح سوال</title>
    <link href="{{ asset('fonts/vazirmatn/Vazirmatn-font-face.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">ثبت نام طراح سوال</h1>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 2rem; text-align: center; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                <svg style="width: 5rem; height: 5rem; color: #22c55e; margin: 0 auto 1rem auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.75rem; color: #166534;">ثبت نام موفق</h3>
                <p style="margin-bottom: 2rem; color: #15803d; font-weight: 500; font-size: 1.1rem;">{{ session('success') }}</p>
                <a href="{{ url('/designer/login') }}" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300" style="display: block;">
                    ورود به پنل کاربری طراحان
                </a>
            </div>
        @else
            <form action="{{ url('/register') }}" method="POST">
                @csrf
                
                <div class="mb-5">
                    <label for="name" class="block text-gray-700 font-medium mb-2">نام و نام خانوادگی</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="مثال: علی محمدی">
                </div>

                <div class="mb-5">
                    <label for="email" class="block text-gray-700 font-medium mb-2">ایمیل</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required dir="ltr"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left"
                        placeholder="example@mail.com">
                </div>

                <div class="mb-5">
                    <label for="mobile" class="block text-gray-700 font-medium mb-2">شماره موبایل</label>
                    <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" required dir="ltr"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left"
                        placeholder="09123456789">
                    <p class="text-xs text-gray-400 mt-2">از این شماره برای بازیابی رمز عبور پیامکی استفاده خواهد شد.</p>
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-gray-700 font-medium mb-2">رمز عبور</label>
                    <input type="password" id="password" name="password" required dir="ltr"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left"
                        placeholder="رمز عبور">
                    <p class="text-xs text-gray-500 font-medium mt-2">توجه: رمز عبور باید ترکیب حروف و عدد باشد و حداقل 8 رقم.</p>
                </div>

                <div class="mb-5">
                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">تکرار رمز عبور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required dir="ltr"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left"
                        placeholder="تکرار رمز عبور">
                </div>

                <button type="submit" 
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                    تکمیل ثبت نام
                </button>
                
                <div class="mt-6 text-center text-sm text-gray-600">
                    قبلاً ثبت نام کرده‌اید؟ 
                    <a href="{{ url('/designer/login') }}" class="text-blue-600 hover:underline font-bold">ورود به پنل</a>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ url('/') }}" class="text-gray-500 hover:text-gray-800 hover:underline text-sm font-medium transition duration-150 ease-in-out">
                        بازگشت به صفحه اصلی &rarr;
                    </a>
                </div>
            </form>
        @endif
    </div>

</body>
</html>
