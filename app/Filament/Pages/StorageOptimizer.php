<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class StorageOptimizer extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'بهینه‌سازی فضای ذخیره‌سازی';
    protected static ?string $title = 'مدیریت هوشمند رسانه‌ها و پاکسازی سرور';
    protected static \UnitEnum|string|null $navigationGroup = 'مدیریت';
    protected static ?int $navigationSort = 110;

    protected string $view = 'filament.pages.storage-optimizer';

    public $totalFilesCount = 0;
    public $totalFilesSize = 0;
    
    public $activeFilesCount = 0;
    public $activeFilesSize = 0;
    
    public $orphanedFilesCount = 0;
    public $orphanedFilesSize = 0;
    
    public $orphanedFilesList = [];

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && $user->hasRole('Super Admin');
    }

    public function mount()
    {
        $this->analyzeStorage();
    }

    public function analyzeStorage()
    {
        $directory = 'public/question-attachments';
        
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $allFiles = Storage::files($directory);
        
        $this->totalFilesCount = count($allFiles);
        $this->totalFilesSize = 0;
        
        $this->orphanedFilesList = [];
        $this->activeFilesCount = 0;
        $this->activeFilesSize = 0;
        $this->orphanedFilesCount = 0;
        $this->orphanedFilesSize = 0;

        // Fetch all legitimate file paths from the attachments table
        $dbPaths = DB::table('attachments')->pluck('file_path')->toArray();
        // Since DB stores it like 'question-attachments/filename.jpg', but Storage::files('public/...') returns 'public/question-attachments/filename.jpg'
        // We need to map them to the same format
        $mappedDbPaths = array_map(function($path) {
            return 'public/' . $path;
        }, $dbPaths);

        foreach ($allFiles as $file) {
            $size = Storage::size($file);
            $this->totalFilesSize += $size;

            if (in_array($file, $mappedDbPaths)) {
                $this->activeFilesCount++;
                $this->activeFilesSize += $size;
            } else {
                $this->orphanedFilesCount++;
                $this->orphanedFilesSize += $size;
                $this->orphanedFilesList[] = $file;
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('اسکن مجدد')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->analyzeStorage();
                    Notification::make()
                        ->title('اسکن با موفقیت انجام شد')
                        ->success()
                        ->send();
                }),
                
            Action::make('clean_orphans')
                ->label('پاکسازی فایل‌های یتیم')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('هشدار: حذف فیزیکی فایل‌ها')
                ->modalDescription('آیا مطمئن هستید؟ این عملیات تمام عکس‌هایی که به هیچ سوالی متصل نیستند را برای همیشه از روی هارد سرور پاک می‌کند تا فضا آزاد شود.')
                ->action(function () {
                    $this->cleanOrphanedFiles();
                })
                ->disabled(fn () => $this->orphanedFilesCount === 0),
        ];
    }

    public function cleanOrphanedFiles()
    {
        $this->analyzeStorage(); // Re-scan just to be safe before deleting

        $deletedCount = 0;
        $freedSpace = $this->orphanedFilesSize;

        foreach ($this->orphanedFilesList as $file) {
            if (Storage::delete($file)) {
                $deletedCount++;
            }
        }

        Notification::make()
            ->title('پاکسازی با موفقیت انجام شد')
            ->body("$deletedCount فایل یتیم پاک شد و " . $this->formatBytes($freedSpace) . " فضا آزاد گردید.")
            ->success()
            ->send();

        $this->analyzeStorage(); // Refresh stats
    }

    public function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
