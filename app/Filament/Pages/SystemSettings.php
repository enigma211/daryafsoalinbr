<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SystemSettings extends Page implements \Filament\Forms\Contracts\HasForms
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'تنظیمات سیستم';
    }

    public static function getNavigationLabel(): string
    {
        return 'تنظیمات سیستم';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'مدیریت';
    }

    public ?array $data = [];

    protected string $view = 'filament.pages.system-settings';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user && $user->hasRole('Super Admin');
    }

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->hasRole('Super Admin'), 403);
        $settings = \App\Models\SystemSetting::firstOrCreate(['id' => 1]);
        $this->form->fill($settings->toArray());
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('محدودیت‌های ارسال سوال (Anti-Spam)')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('question_cooldown_seconds')
                            ->label('فاصله زمانی مجاز بین هر ارسال (ثانیه)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('مدت زمانی که یک طراح باید صبر کند تا بتواند سوال بعدی را ثبت کند. (مثلاً ۳۰ ثانیه)'),
                        
                        \Filament\Forms\Components\TextInput::make('max_questions_per_day')
                            ->label('حداکثر سوالات مجاز در روز')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('حداکثر تعداد سوالاتی که یک طراح می‌تواند در یک شبانه‌روز ارسال کند.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = \App\Models\SystemSetting::firstOrCreate(['id' => 1]);
        $settings->update($this->form->getState());

        \Filament\Notifications\Notification::make()
            ->title('تنظیمات با موفقیت ذخیره شد.')
            ->success()
            ->send();
    }
}
