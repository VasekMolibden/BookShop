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
    class="min-h-screen flex flex-col bg-stone-50 text-stone-900 antialiased"
    data-page="@yield('shop-page')"
    @stack('body-data')
>
    <header class="border-b border-stone-200 bg-white/90 backdrop-blur-sm sticky top-0 z-10">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('shop.index') }}" class="text-lg font-semibold tracking-tight text-amber-900">
                {{ config('app.name', 'BookShop') }}
            </a>
            <nav class="flex items-center gap-2 text-sm">
                <div id="shop-nav-search" class="relative flex items-center">
                    <button
                        type="button"
                        id="shop-nav-search-toggle"
                        class="rounded-md p-2 text-stone-700 hover:bg-stone-100"
                        aria-label="Поиск"
                        title="Поиск"
                        aria-expanded="false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3m1.3-5.2a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <form id="shop-nav-search-form" class="absolute right-[calc(100%+0.5rem)] top-1/2 z-20 hidden w-56 -translate-y-1/2 lg:w-72" action="{{ route('shop.search') }}" method="GET">
                        <div class="flex items-center gap-2 rounded-md border border-stone-200 bg-white p-1 shadow-sm">
                            <input
                                id="shop-nav-search-input"
                                type="search"
                                name="search"
                                placeholder="Найти книгу или автора…"
                                class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm outline-none ring-amber-300 focus:ring"
                            >
                            <button id="shop-nav-search-submit" type="button" class="rounded-md bg-amber-800 px-3 py-2 text-xs font-medium text-white hover:bg-amber-900">
                                Найти
                            </button>
                        </div>
                    </form>
                </div>
                <a
                    id="shop-nav-cart"
                    href="{{ route('shop.cart') }}"
                    data-auth-only
                    class="hidden relative rounded-md p-2 text-stone-700 hover:bg-stone-100"
                    aria-label="Корзина"
                    title="Корзина"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.2 6h12.4M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                    </svg>
                    <span id="shop-nav-cart-count" class="absolute -right-1 -top-1 hidden min-w-4 rounded-full bg-amber-800 px-1.5 text-center text-[10px] font-semibold leading-4 text-white">0</span>
                </a>
                <a id="shop-nav-admin" href="{{ route('admin.dashboard') }}" class="hidden rounded-md px-3 py-2 font-medium text-violet-800 hover:bg-violet-50">Админка</a>
                <span id="shop-nav-auth" class="flex items-center gap-2 border-l border-stone-200 pl-3 ml-1">
                    <div id="shop-user-menu" class="relative hidden">
                        <button
                            type="button"
                            id="shop-user-menu-trigger"
                            class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <span class="inline-flex items-center gap-1.5">
                                <span id="shop-nav-user" class="text-stone-600"></span>
                                <svg id="shop-user-menu-arrow" class="h-4 w-4 text-stone-500 transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.13l3.71-3.9a.75.75 0 111.08 1.04l-4.25 4.47a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                        <div class="absolute right-0 top-full z-20 mt-1 hidden w-44 rounded-lg border border-stone-200 bg-white p-1 shadow-lg" id="shop-user-dropdown">
                            <a href="{{ route('shop.favorites') }}" data-auth-only class="hidden block rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Избранное</a>
                            <a href="{{ route('shop.orders') }}" data-auth-only class="hidden block rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Заказы</a>
                            <a href="{{ route('shop.reviews') }}" data-auth-only class="hidden block rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Мои отзывы</a>
                            <a id="shop-nav-settings" href="{{ route('shop.settings') }}" class="hidden block rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">Настройки</a>
                            <button type="button" id="shop-nav-logout" class="hidden w-full rounded-md px-3 py-2 text-left text-stone-700 hover:bg-stone-100">Выйти</button>
                        </div>
                    </div>
                    <a id="shop-nav-login" href="{{ route('shop.login') }}" class="rounded-md px-3 py-2 font-medium text-amber-900 hover:bg-amber-50">Войти</a>
                    <a id="shop-nav-register" href="{{ route('shop.register') }}" class="rounded-md bg-amber-800 px-3 py-2 font-medium text-white hover:bg-amber-900">Регистрация</a>
                </span>
            </nav>
        </div>
    </header>

    <main class="flex-1 mx-auto max-w-6xl px-4 py-8 w-full">
        <div
            id="shop-alert"
            class="fixed right-4 top-20 z-50 hidden w-[min(28rem,calc(100vw-2rem))] rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg"
            role="alert"
        >
            <div class="flex items-start gap-3">
                <p id="shop-alert-message" class="flex-1 pr-1"></p>
                <button
                    type="button"
                    id="shop-alert-close"
                    class="rounded p-1 leading-none opacity-70 transition hover:bg-black/5 hover:opacity-100"
                    aria-label="Закрыть уведомление"
                >
                    ✕
                </button>
            </div>
        </div>
        @yield('content')
    </main>

    <footer class="mt-auto border-t border-stone-200 bg-white py-6 text-center text-sm text-stone-500">
        {{ config('app.name') }} · Книжный магазин &copy; {{ date('Y') }}
        <br>
        <code class="rounded bg-stone-100 px-1">/api/v1</code>
    </footer>
</body>
</html>
