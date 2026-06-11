<?php

namespace App\Filament\Pages\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\SMS;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SmsPasswordReset extends SimplePage
{
    use WithRateLimiting;



    public bool $isOtpSent = false;
    public string $mobile = '';
    public string $otp_code = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public function getTitle(): string 
    {
        return 'بازیابی رمز عبور';
    }

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

                TextInput::make('password')
                    ->label('رمز عبور جدید')
                    ->password()
                    ->required()
                    ->visible($this->isOtpSent)
                    ->extraInputAttributes(['dir' => 'ltr']),

                TextInput::make('passwordConfirmation')
                    ->label('تکرار رمز عبور جدید')
                    ->password()
                    ->required()
                    ->same('password')
                    ->visible($this->isOtpSent)
                    ->extraInputAttributes(['dir' => 'ltr']),
            ])
            ->statePath('data');
    }

    public function authenticate()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
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

        // OTP is valid, change password
        $user->password = Hash::make($data['password']);
        $user->save();

        $otpRecord->delete(); // consume the code

        Notification::make()
            ->title('رمز عبور با موفقیت تغییر کرد.')
            ->success()
            ->send();

        return redirect(Filament::getLoginUrl());
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

        // Send via SMS
        if (config('melipayamak.username') && config('melipayamak.password')) {
            SMS::sendOTP($mobile, $code);
        }

        Log::info("Password Reset OTP Code for {$mobile} is: {$code}");

        $this->isOtpSent = true;

        Notification::make()
            ->title('کد تایید ارسال شد')
            ->body('کد تایید ۶ رقمی به شماره موبایل شما پیامک شد.')
            ->success()
            ->send();

        return null;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return SchemaForm::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Actions::make($this->getFormActions())
                    ->fullWidth(true)
                    ->key('form-actions'),
            ]);
    }

    public function hasLogo(): bool
    {
        return true;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('authenticate')
                ->label(fn () => $this->isOtpSent ? 'تغییر رمز عبور' : 'ارسال کد تایید پیامکی')
                ->submit('authenticate'),
        ];
    }
}
