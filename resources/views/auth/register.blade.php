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
            <p class="text-gray-500 mt-2">به سامانه بانک سوالات بپیوندید</p>
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

        <form action="{{ url('/register') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label for="name" class="block text-gray-700 font-medium mb-2">نام و نام خانوادگی</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="مثال: علی محمدی">
            </div>

            <div class="mb-5">
                <label for="mobile" class="block text-gray-700 font-medium mb-2">شماره موبایل</label>
                <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" required dir="ltr"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left"
                    placeholder="09123456789">
                <p class="text-xs text-gray-400 mt-2">از این شماره برای ورود پیامکی (OTP) استفاده خواهد شد.</p>
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                تکمیل ثبت نام
            </button>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                قبلاً ثبت نام کرده‌اید؟ 
                <a href="{{ url('/designer/login') }}" class="text-blue-600 hover:underline font-bold">ورود به پنل</a>
            </div>
        </form>
    </div>

</body>
</html>
