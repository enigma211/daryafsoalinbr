<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه بانک سوالات مقررات ملی ساختمان</title>
    <link href="{{ asset('fonts/vazirmatn/Vazirmatn-font-face.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="glass-panel w-full max-w-2xl rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
        <!-- Decorative Shapes -->
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-red-500 rounded-full mix-blend-multiply filter blur-2xl opacity-20"></div>

        <!-- Logo -->
        <div class="flex justify-center mb-8 relative z-10">
            <!-- Using a placeholder logo or the uploaded logo -->
            <img src="{{ asset('images/logo.png') }}" alt="لوگو مقررات ملی" class="w-32 h-32 object-contain bg-white rounded-full p-2 shadow-lg">
        </div>

        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4 relative z-10">
            سامانه بانک سوالات
        </h1>
        <h2 class="text-xl text-gray-600 mb-10 relative z-10">
            دفتر مقررات ملی و کنترل ساختمان
        </h2>

        <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
            <a href="{{ url('/designer/login') }}" class="px-8 py-4 bg-blue-600 text-white rounded-xl font-bold text-lg hover:bg-blue-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                ورود به پنل کاربری
            </a>
            <a href="{{ url('/register') }}" class="px-8 py-4 bg-white text-blue-600 border-2 border-blue-600 rounded-xl font-bold text-lg hover:bg-blue-50 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                ثبت نام طراحان سوال
            </a>
        </div>

        <div class="mt-12 text-sm text-gray-500 relative z-10">
            کلیه حقوق برای دفتر مقررات ملی ساختمان محفوظ است.
        </div>
    </div>

</body>
</html>
