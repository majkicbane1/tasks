<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // mail / Google keys saved by the platform admin override .env
        Setting::applyToConfig();

        VerifyEmail::toMailUsing(fn ($notifiable, string $url) => (new MailMessage)
            ->subject('Potvrdi email adresu')
            ->greeting('Zdravo '.$notifiable->name.',')
            ->line('Klikni na dugme da potvrdiš email adresu za '.config('app.name').'.')
            ->action('Potvrdi email', $url)
            ->line('Ako nisi ti napravio nalog, ignoriši ovaj mejl.'));

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()], false));
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Promena lozinke')
                ->greeting('Zdravo '.$notifiable->name.',')
                ->line('Dobili smo zahtev za promenu lozinke.')
                ->action('Postavi novu lozinku', $url)
                ->line("Link važi {$minutes} minuta. Ako nisi tražio promenu, ne moraš ništa da radiš.");
        });

        View::share('money', fn ($amount, $currency = 'EUR') => number_format((float) $amount, 2, ',', '.') . ' ' . $currency);
    }
}
