<?php

namespace App\Libraries;

use Auth;
use Illuminate\Support\Str;
use App\Models\Token;
use Illuminate\Support\Facades\App;

class JWT
{
    private $token;

    public function __construct(string $key = null)
    {
        $this->token = $key;
    }

    public static function sign(string $aud, int $sub, string $exp, array $extras, string $creator, $kid): string
    {
        $headerData = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payloadData = array_merge(['iss' => env('APP_URL'), 'aud' => $aud, 'sub' => $sub, 'iat' => strtotime("now"), 'exp' => strtotime($exp), 'jti' => uniqid(mt_rand(), true)], $extras);

        if (isset($kid)) {
            $secret = md5(env('JWT_SECRET') . $kid);

            $headerData['kid'] = $kid;
        } else {
            $secret = env('JWT_SECRET');
        }

        $header = self::base64UrlEncode(json_encode($headerData));
        $payload = self::base64UrlEncode(json_encode($payloadData));
        $signature = self::base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));

        $key = $header . '.' . $payload . '.' . $signature;

        Token::create([
            'authable_type' => $aud,
            'authable_id' => $sub,
            'key' => $key,
            'status' => 'created',
        ]);

        return $key;
    }

    public static function verify(string $token, bool $checkStatus = false): bool
    {
        if ($checkStatus === true) {
            $key = Token::with('authable')->where('key', $token)->first();

            if (!isset($key) or $key->status === "revoked") {
                return false;
            }
        }

        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $header = $parts[0];
            $headerData = json_decode(self::base64UrlDecode($header));

            if (isset($headerData->kid)) {
                $secret = md5(env('JWT_SECRET') . $headerData->kid);
            } else {
                $secret = env('JWT_SECRET');
            }

            isset($parts[1]) ? $payload = $parts[1] : $payload = '';
            isset($parts[2]) ? $signature = $parts[2] : $signature = '';

            $generatedSignature = self::base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));
            if (hash_equals($generatedSignature, $signature)) {
                $payload = json_decode(self::base64UrlDecode($payload));

                if (!isset($payload->exp)) {
                    $response = true;
                } else if ($payload->exp - time() > 0) {
                    $response = true;
                }
            }
        }

        if (isset($response) and $response === true) {
            if (isset($key)) {
                $user = $key->authable;
                $authed = Auth::guard(Str::snake($key->authable_type))->login($user);
                //App::setLocale('en');

                App::setLocale($user->language);
            }

            return true;
        } else {
            return false;
        }
    }

    public function getUserId(): int
    {
        $parts = explode('.', $this->token);
        if (count($parts) === 3) {
            $payload = $parts[1];
            $payloadData = json_decode(self::base64UrlDecode($payload));

            if (isset($payloadData)) {
                return $payloadData->sub;
            }
        }

        return false;
    }

    public static function revoke(string $token): bool
    {
        $oldKey = Token::where('key', $token)->first();
        if (isset($oldKey)) {
            $oldKey->status = 'revoked';
            $oldKey->save();
        }

        return true;
    }

    protected static function base64UrlEncode(string $data): string
    {
        $b64 = base64_encode($data);

        if ($b64 === false) {
            return false;
        }
        $url = strtr($b64, '+/', '-_');

        return rtrim($url, '=');
    }

    protected static function base64UrlDecode(string $data, bool $strict = false): string
    {
        $b64 = strtr($data, '-_', '+/');

        return base64_decode($b64, $strict);
    }
}
