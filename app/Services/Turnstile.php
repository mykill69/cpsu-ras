<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cloudflare Turnstile verification.
 * https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 */
class Turnstile
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    /** Ask Cloudflare whether the token from the widget is valid. Fails closed. */
    public static function verify(?string $token, ?string $ip = null, ?string $expectedHost = null): bool
    {
        if (blank($token) || strlen($token) > 2048) {
            return false;
        }

        if (self::usingTestKeys() && app()->environment('production')) {
            Log::critical('Turnstile is using Cloudflare TEST keys in production: replace TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY in .env.');
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post(self::VERIFY_URL, array_filter([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (Throwable $e) {
            Log::warning('Turnstile verification unreachable: ' . $e->getMessage());

            return false;
        }

        if (! $response->ok() || $response->json('success') !== true) {
            Log::info('Turnstile verification failed', ['errors' => $response->json('error-codes')]);

            return false;
        }

        // A valid token must also have been issued for this form and for the host
        // the login page was served from, so a token solved on another page or
        // domain can't be replayed here. (Cloudflare itself only issues tokens for
        // the hostnames listed on the widget.)
        if (! self::usingTestKeys()) {
            if ($response->json('action') !== 'login'
                || ($expectedHost && strcasecmp((string) $response->json('hostname'), $expectedHost) !== 0)) {
                Log::warning('Turnstile token for wrong action/host', [
                    'action' => $response->json('action'),
                    'hostname' => $response->json('hostname'),
                    'expected_host' => $expectedHost,
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Cloudflare's test keys (1x…, 2x…, 3x…) pass or fail without checking
     * anyone. They must never be used on the live site.
     */
    public static function usingTestKeys(): bool
    {
        return (bool) preg_match('/^[123]x0{10}/', (string) config('services.turnstile.site_key'))
            || (bool) preg_match('/^[123]x0{10}/', (string) config('services.turnstile.secret_key'));
    }
}
