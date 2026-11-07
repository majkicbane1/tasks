<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

/** Platform wide key/value settings (mail, Google login, registration). Secrets are stored encrypted. */
class Setting extends Model
{
    protected $table = 'app_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    /** Keys whose values are encrypted at rest and never shown back in forms. */
    public const SECRETS = ['mail.password', 'google.client_secret'];

    private static ?array $cache = null;

    public static function everything(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            if (! Schema::hasTable('app_settings')) {
                return self::$cache = [];
            }

            return self::$cache = static::query()->get()->mapWithKeys(function (self $row) {
                $value = $row->value;
                if ($value !== null && in_array($row->key, self::SECRETS, true)) {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (Throwable) {
                        $value = null; // APP_KEY changed
                    }
                }

                return [$row->key => $value];
            })->all();
        } catch (Throwable) {
            return self::$cache = [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::everything()[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        return $value === null ? $default : in_array($value, ['1', 1, true, 'true'], true);
    }

    public static function put(string $key, mixed $value): void
    {
        if ($value !== null && $value !== '' && in_array($key, self::SECRETS, true)) {
            $value = Crypt::encryptString((string) $value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        self::$cache = null;
    }

    public static function googleEnabled(): bool
    {
        return self::bool('google.enabled') && self::get('google.client_id') && self::get('google.client_secret');
    }

    /** Pushes the stored mail / Google settings into the runtime config. Called on every boot. */
    public static function applyToConfig(): void
    {
        $s = self::everything();
        if (! $s) {
            return;
        }

        if (($s['mail.mailer'] ?? null) === 'smtp' && ! empty($s['mail.host'])) {
            $encryption = $s['mail.encryption'] ?? 'tls';
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $s['mail.host'],
                'mail.mailers.smtp.port' => (int) ($s['mail.port'] ?? 587),
                'mail.mailers.smtp.username' => $s['mail.username'] ?? null,
                'mail.mailers.smtp.password' => $s['mail.password'] ?? null,
                // symfony: "smtps" = implicit SSL (465), "smtp" negotiates STARTTLS when offered
                'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.url' => null,
            ]);
        } elseif (($s['mail.mailer'] ?? null) === 'log') {
            config(['mail.default' => 'log']);
        }

        if (! empty($s['mail.from_address'])) {
            config([
                'mail.from.address' => $s['mail.from_address'],
                'mail.from.name' => $s['mail.from_name'] ?? config('mail.from.name'),
            ]);
        }

        config([
            'services.google.client_id' => $s['google.client_id'] ?? null,
            'services.google.client_secret' => $s['google.client_secret'] ?? null,
        ]);
    }
}
