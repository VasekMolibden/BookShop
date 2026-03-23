<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\Genre;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Сброс пароля — '.config('app.name'))
                ->line('Вы получили это письмо, потому что для вашего аккаунта запрошен сброс пароля.')
                ->action('Сбросить пароль', $url)
                ->line("Ссылка действительна {$minutes} минут.")
                ->line('Если вы не запрашивали сброс, ничего делать не нужно.');
        });

        Route::bind('genre', function (string $value) {
            return Genre::query()->whereKey($value)->firstOrFail();
        });

        Route::bind('book', function (string $value) {
            $query = Book::query()
                ->with(['genres:id,name,slug'])
                ->withCount('reviews');

            return is_numeric($value)
                ? $query->findOrFail($value)
                : $query->where('slug', $value)->firstOrFail();
        });

        Route::bind('cartItem', function (string $value) {
            $item = CartItem::query()
                ->with(['cart', 'book.genres'])
                ->findOrFail($value);

            if ((int) auth()->id() !== (int) $item->cart->user_id) {
                abort(404);
            }

            return $item;
        });

        Route::bind('order', function (string $value) {
            $order = Order::query()
                ->with(['items.book.genres'])
                ->whereKey($value)
                ->firstOrFail();

            $user = auth()->user();

            if ($user && $user->is_admin) {
                return $order;
            }

            if ((int) auth()->id() !== (int) $order->user_id) {
                abort(404);
            }

            return $order;
        });

        Route::bind('review', function (string $value) {
            return Review::query()
                ->with(['user:id,name'])
                ->findOrFail($value);
        });
    }
}
