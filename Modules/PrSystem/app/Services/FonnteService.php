<?php

namespace Modules\PrSystem\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
    }

    public function sendMessage($target, $message, $delay = null, $countryCode = '62')
    {
        if (empty($this->token)) {
            Log::warning('Fonnte Token is not set.');
            return false;
        }

        if (empty($target)) {
            Log::warning('Fonnte Target is empty.');
             return false;
        }

        try {
            $body = [
                'target' => $target,
                'message' => $message,
                'countryCode' => $countryCode,
            ];

            // Note: Fonnte API does not support a 'delay' parameter in the request body.
            // Staggered delays should be managed by the caller (e.g., using sleep()).

            Log::info("Sending Fonnte WA to $target: $message");

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->withoutVerifying()
              ->timeout(30)
              ->post('https://api.fonnte.com/send', $body);

            if ($response->successful()) {
                $json = $response->json();
                Log::info('Fonnte Response: ' . $response->body());
                
                // Even if HTTP is 200, check the Fonnte-specific 'status' flag
                if (isset($json['status']) && $json['status'] === false) {
                    $reason = $json['reason'] ?? 'Unknown Fonnte Error';
                    throw new \Exception("Fonnte API returned false status: $reason");
                }
                
                return $json;
            } else {
                Log::error('Fonnte Error: ' . $response->body());
                throw new \Exception("HTTP Error: " . $response->status());
            }

        } catch (\Exception $e) {
             Log::error('Fonnte Exception: ' . $e->getMessage());
             return false;
        }
    }
}
