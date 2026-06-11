<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;


    protected function beforeCreate(): void
    {
        $settings = \App\Models\SystemSetting::firstOrCreate(['id' => 1]);
        $user = \Illuminate\Support\Facades\Auth::user();

        // Check max per day
        $todayQuestionsCount = \App\Models\Question::where('user_id', $user->id)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();

        if ($todayQuestionsCount >= $settings->max_questions_per_day) {
            \Filament\Notifications\Notification::make()
                ->title('خطا در ثبت')
                ->body("شما به سقف ارسال مجاز روزانه ({$settings->max_questions_per_day} سوال) رسیده‌اید.")
                ->danger()
                ->send();

            $this->halt();
        }

        // Check cooldown
        $lastQuestion = \App\Models\Question::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if ($lastQuestion) {
            $secondsSinceLast = $lastQuestion->created_at->diffInSeconds(\Carbon\Carbon::now());
            if ($secondsSinceLast < $settings->question_cooldown_seconds) {
                $wait = $settings->question_cooldown_seconds - $secondsSinceLast;
                \Filament\Notifications\Notification::make()
                    ->title('لطفاً کمی صبر کنید')
                    ->body("برای ثبت سوال بعدی باید {$wait} ثانیه دیگر منتظر بمانید.")
                    ->warning()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
        return $data;
    }
}
