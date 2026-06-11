<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        
        <!-- Total Files Widget -->
        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-700 dark:text-gray-200">کل فایل‌های آپلود شده</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تعداد کل عکس‌ها در پوشه attachments</p>
                </div>
                <div class="p-3 bg-blue-100 text-blue-600 rounded-full dark:bg-blue-900 dark:text-blue-300">
                    <x-heroicon-o-folder class="w-8 h-8" />
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalFilesCount }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm mr-2">فایل</span>
            </div>
            <div class="mt-2 text-sm font-semibold text-blue-600 dark:text-blue-400">
                حجم کل: {{ $this->formatBytes($totalFilesSize) }}
            </div>
        </x-filament::section>

        <!-- Active Files Widget -->
        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-700 dark:text-gray-200">فایل‌های در حال استفاده</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">متصل به سوالاتِ موجود در دیتابیس</p>
                </div>
                <div class="p-3 bg-green-100 text-green-600 rounded-full dark:bg-green-900 dark:text-green-300">
                    <x-heroicon-o-check-circle class="w-8 h-8" />
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $activeFilesCount }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm mr-2">فایل</span>
            </div>
            <div class="mt-2 text-sm font-semibold text-green-600 dark:text-green-400">
                حجم درگیر: {{ $this->formatBytes($activeFilesSize) }}
            </div>
        </x-filament::section>

        <!-- Orphaned Files Widget -->
        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-700 dark:text-gray-200">فایل‌های یتیم (اضافی)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">بدون اتصال به هیچ سوالی (آماده حذف)</p>
                </div>
                <div class="p-3 bg-red-100 text-red-600 rounded-full dark:bg-red-900 dark:text-red-300">
                    <x-heroicon-o-trash class="w-8 h-8" />
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-black {{ $orphanedFilesCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $orphanedFilesCount }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm mr-2">فایل</span>
            </div>
            <div class="mt-2 text-sm font-semibold {{ $orphanedFilesCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                فضای قابل آزادسازی: {{ $this->formatBytes($orphanedFilesSize) }}
            </div>
        </x-filament::section>

    </div>

    @if($orphanedFilesCount > 0)
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">توجه!</span> تعداد {{ $orphanedFilesCount }} عکس در سرور پیدا شد که به هیچ سوالی متصل نیستند (احتمالاً سوال آن‌ها حذف شده است). با زدن دکمه «پاکسازی فایل‌های یتیم» در بالای صفحه می‌توانید <strong>{{ $this->formatBytes($orphanedFilesSize) }}</strong> از فضای هارد سرور را آزاد کنید.
        </div>
    @else
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">عالی!</span> هارد سرور شما کاملاً تمیز و بهینه است و هیچ فایل اضافی و یتیمی وجود ندارد.
        </div>
    @endif

</x-filament-panels::page>
