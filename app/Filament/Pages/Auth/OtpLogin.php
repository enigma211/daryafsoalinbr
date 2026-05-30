<?php

namespace App\Filament\Pages\Auth;

use App\Models\OtpCode;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OtpLogin extends BaseLogin
{
    public bool $isOtpSent = false;
    public string $mobile = '';
    public string $otp_code = '';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                TextInput::make('mobile')
                    ->label('شماره موبایل')
                    ->placeholder('09123456789')
                    ->required()
                    ->readOnly($this->isOtpSent)
                    ->extraInputAttributes(['dir' => 'ltr']),
                    
                TextInput::make('otp_code')
                    ->label('کد تایید ۶ رقمی')
                    ->placeholder('123456')
                    ->required()
                    ->visible($this->isOtpSent)
                    ->length(6)
                    ->extraInputAttributes(['dir' => 'ltr']),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (! $this->isOtpSent) {
            return $this->sendOtp($data['mobile']);
        }

        // Verify OTP
        $user = User::where('mobile', $data['mobile'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.mobile' => 'کاربری با این شماره موبایل یافت نشد.',
            ]);
        }

        $otpRecord = OtpCode::where('mobile', $data['mobile'])
            ->where('code', $data['otp_code'])
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            throw ValidationException::withMessages([
                'data.otp_code' => 'کد تایید نامعتبر است یا منقضی شده.',
            ]);
        }

        // OTP is valid
        Auth::login($user, true); // login with remember
        $otpRecord->delete(); // consume the code

        session()->regenerate();

        return app(\Filament\Auth\Http\Responses\Contracts\LoginResponse::class);
    }

    protected function sendOtp(string $mobile)
    {
        $user = User::where('mobile', $mobile)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.mobile' => 'کاربری با این شماره موبایل در سیستم ثبت نشده است.',
            ]);
        }

        // Generate 6-digit code
        $code = (string) random_int(100000, 999999);

        // Save to DB
        OtpCode::updateOrCreate(
            ['mobile' => $mobile],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
            ]
        );

        // Here you would integrate with an SMS gateway (e.g., Kavenegar, FarazSMS)
        Log::info("OTP Code for {$mobile} is: {$code}");

        $this->isOtpSent = true;

        Notification::make()
            ->title('کد تایید ارسال شد')
            ->body('کد تایید ۶ رقمی به شماره موبایل شما پیامک شد. (به دلیل اینکه در محیط توسعه هستیم، کد در لاگ سیستم ذخیره شد: ' . $code . ')')
            ->success()
            ->send();

        return null;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('authenticate')
                ->label(fn () => $this->isOtpSent ? 'ورود به پنل' : 'ارسال کد تایید')
                ->submit('authenticate'),
        ];
    }
}
