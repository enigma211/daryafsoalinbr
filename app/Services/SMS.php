<?php

namespace App\Services;

use Melipayamak\Laravel\Facade as Melipayamak;
use Illuminate\Support\Facades\Log;

class SMS
{
    /**
     * ارسال پیامک متنی ساده
     */
    public static function send(string $to, string $text): bool
    {
        try {
            $sms = Melipayamak::sms();
            $from = env('MELIPAYAMAK_SENDER', '5000...');
            $response = $sms->send($to, $from, $text);
            
            Log::info("SMS sent to {$to}", ['response' => $response]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ارسال کد تایید (OTP) از طریق پترن خط خدماتی اشتراکی
     * این روش برای عبور از بلکلیست مخابرات ضروری است
     */
    public static function sendOTP(string $to, string $code): bool
    {
        try {
            $username = config('melipayamak.username');
            $password = config('melipayamak.password');
            $bodyId = env('MELIPAYAMAK_OTP_BODY_ID', '429194'); 
            
            // ارسال مستقیم ریکوئست به API با cURL برای رفع مشکل پکیج در متد BaseServiceNumber
            $data = [
                'username' => $username,
                'password' => $password,
                'to' => $to,
                'bodyId' => $bodyId,
                'text' => $code // متغیرهای پترن (کد تایید)
            ];

            $ch = curl_init('https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            Log::info("OTP SMS sent to {$to}", [
                'code' => $code, 
                'http_code' => $httpcode, 
                'response' => $response
            ]);

            $decodedResponse = json_decode($response, true);
            // RetStatus: 1 به معنای موفقیت‌آمیز بودن است
            if (isset($decodedResponse['RetStatus']) && $decodedResponse['RetStatus'] == 1) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP SMS to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
