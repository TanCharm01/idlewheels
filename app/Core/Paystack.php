<?php
declare(strict_types=1);

namespace App\Core;

class Paystack
{
    private const API = 'https://api.paystack.co';

    public static function configured(): bool
    {
        return (string) config('paystack.secret_key', '') !== '';
    }

    public static function isTestMode(): bool
    {
        return str_starts_with((string) config('paystack.secret_key', ''), 'sk_test_');
    }

    public static function initialize(string $email, float $amount, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $res = self::request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => (int) round($amount * 100),
            'currency' => currency(),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'channels' => array_values((array) config('paystack.channels', ['card', 'mobile_money'])),
            'metadata' => $metadata,
        ]);
        if (empty($res['status']) || empty($res['data']['authorization_url'])) {
            throw new \RuntimeException($res['message'] ?? 'Paystack could not start the payment.');
        }
        return ['authorization_url' => $res['data']['authorization_url'], 'reference' => $res['data']['reference'] ?? $reference];
    }

    public static function verify(string $reference): array
    {
        $res = self::request('GET', '/transaction/verify/' . rawurlencode($reference));
        if (empty($res['status']) || empty($res['data'])) {
            throw new \RuntimeException($res['message'] ?? 'Paystack could not verify the payment.');
        }
        $d = $res['data'];
        return [
            'status' => (string) ($d['status'] ?? ''),
            'amount' => ((int) ($d['amount'] ?? 0)) / 100,
            'currency' => (string) ($d['currency'] ?? ''),
            'channel' => (string) ($d['channel'] ?? ''),
            'reference' => (string) ($d['reference'] ?? $reference),
            'paid_at' => $d['paid_at'] ?? null,
        ];
    }

    public static function validSignature(string $body, string $signature): bool
    {
        $secret = (string) config('paystack.secret_key', '');
        return $secret !== '' && $signature !== '' && hash_equals(hash_hmac('sha512', $body, $secret), $signature);
    }

    public static function newReference(string $prefix): string
    {
        return strtoupper($prefix) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
    }

    private static function request(string $method, string $path, ?array $body = null): array
    {
        if (!self::configured()) {
            throw new \RuntimeException('Online payments are not set up yet. Add your Paystack keys to config/config.php.');
        }
        $ch = curl_init(self::API . $path);
        $headers = ['Authorization: Bearer ' . config('paystack.secret_key'), 'Cache-Control: no-cache'];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            throw new \RuntimeException('Could not reach Paystack: ' . $err);
        }
        $json = json_decode((string) $raw, true);
        return is_array($json) ? $json : ['status' => false, 'message' => 'Unexpected response from Paystack.'];
    }
}
