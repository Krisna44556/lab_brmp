<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function sendMessage($target, $message)
    {
        $token = env('FONNTE_TOKEN');

        if (!$token) {
            \Log::error('WA Error: FONNTE_TOKEN kosong');
            return false;
        }

        // Bersihkan format nomor HP
        $target = preg_replace('/[^0-9]/', '', $target);

        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $target,
                'message' => $message,
            ]);

            $result = $response->json();
            
            // CATAT HASIL DARI FONNTE KE LOG
            \Log::info("RESPON FONNTE [{$target}]: ", $result ?? []);

            return $result;

        } catch (\Exception $e) {
            \Log::error("HTTP Exception WA: " . $e->getMessage());
            return false;
        }
    }
}