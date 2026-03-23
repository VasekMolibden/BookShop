<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Книжный магазин') — {{ config('app.name', 'BookShop') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-stone-50 text-stone-900 antialiased"
    data-page="@yield('shop-page')"
    @stack('body-data')
>
    <header class="border-b border-stone-200 bg-white/90 backdrop-blur-sm sticky top-0 z-10">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('shop.index') }}" class="text-lg font-semibold tracking-tight text-amber-900">
                {{ config('app.name', 'BookShop') }}
            </a>
            <nav class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('shop.index') }}" class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Каталог</a>
                <a href="{{ route('shop.cart') }}" class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Корзина</a>
                <a href="{{ route('shop.favorites') }}" class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Избранное</a>
                <a href="{{ route('shop.orders') }}" class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Заказы</a>
                <a id="shop-nav-settings" href="{{ route('shop.settings') }}" class="hidden rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Настройки</a>
                <a id="shop-nav-admin" href="{{ route('admin.dashboard') }}" class="hidden rounded-md px-3 py-2 font-medium text-violet-800 hover:bg-violet-50">Админка</a>
                <span id="shop-nav-auth" class="flex items-center gap-2 border-l border-stone-200 pl-3 ml-1">
                    <span id="shop-nav-user" class="hidden text-stone-600"></span>
                    <a id="shop-nav-login" href="{{ route('shop.login') }}" class="rounded-md px-3 py-2 font-medium text-amber-900 hover:bg-amber-50">Войти</a>
                    <a id="shop-nav-register" href="{{ route('shop.register') }}" class="rounded-md bg-amber-800 px-3 py-2 font-medium text-white hover:bg-amber-900">Регистрация</a>
                    <button type="button" id="shop-nav-logout" class="hidden rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Выйти</button>
                </span>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        <div id="shop-alert" class="mb-6 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"></div>
        @yield('content')
    </main>

    <footer class="mt-auto border-t border-stone-200 bg-white py-6 text-center text-sm text-stone-500">
        {{ config('app.name') }} · REST API <code class="rounded bg-stone-100 px-1">/api/v1</code>
    </footer>
</body>
</html>
