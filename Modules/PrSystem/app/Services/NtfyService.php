<?php

namespace Modules\PrSystem\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NtfyService
 *
 * Sends push notifications via ntfy.sh (or a self-hosted ntfy instance).
 * Configure NTFY_BASE_URL and NTFY_TOPIC in your .env file.
 *
 * .env example:
 *   NTFY_BASE_URL=https://ntfy.sh
 *   NTFY_TOPIC=pr-system-notifications
 *   NTFY_TOKEN=            # leave blank if your topic is public / no auth required
 */
class NtfyService
{
    protected string $baseUrl;
    protected string $topic;
    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('NTFY_BASE_URL', 'https://ntfy.sh'), '/');
        $this->topic   = env('NTFY_TOPIC', 'pr-system-notifications');
        $this->token   = env('NTFY_TOKEN', null);
    }

    /**
     * Send a notification to the ntfy topic.
     *
     * @param  string       $message  Main body text.
     * @param  string|null  $title    Optional notification title (shown in bold).
     * @param  string|null  $tags     Comma-separated emoji tags, e.g. "bell,memo".
     * @param  int          $priority 1-5 (5 = urgent). Default: 3 (default).
     * @return bool  true on success, false on failure.
     */
    public function send(string $message, string $title = null, string $tags = null, int $priority = 3): bool
    {
        $url = "{$this->baseUrl}/{$this->topic}";

        $headers = [
            'Content-Type' => 'text/plain',
            'Priority'     => (string) $priority,
        ];

        if ($title) {
            $headers['Title'] = $title;
        }

        if ($tags) {
            $headers['Tags'] = $tags;
        }

        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        try {
            Log::info("Sending ntfy notification to {$url}: {$title}");

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->withBody($message, 'text/plain')
                ->post($url);

            if ($response->successful()) {
                Log::info('ntfy response: ' . $response->body());
                return true;
            }

            Log::error('ntfy Error: HTTP ' . $response->status() . ' — ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('ntfy Exception: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Send a notification to a specific user's personal topic.
     * Topic format: {NTFY_TOPIC}-user-{userId}
     * Each user subscribes to their own topic in the ntfy app.
     *
     * @param  int          $userId   The user's database ID.
     * @param  string       $message  Main body text.
     * @param  string|null  $title    Optional notification title.
     * @param  string|null  $tags     Comma-separated emoji tags.
     * @param  int          $priority 1-5. Default: 3.
     * @return bool
     */
    public function sendToUser(int $userId, string $message, string $title = null, string $tags = null, int $priority = 3): bool
    {
        // Override topic to a per-user channel so only that user receives it
        $originalTopic  = $this->topic;
        $this->topic    = $this->topic . '-user-' . $userId;
        $result         = $this->send($message, $title, $tags, $priority);
        $this->topic    = $originalTopic; // Restore
        return $result;
    }
}
