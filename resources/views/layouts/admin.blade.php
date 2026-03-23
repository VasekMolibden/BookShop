<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Админка') — {{ config('app.name', 'BookShop') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-slate-50 text-slate-900 antialiased"
    data-admin="1"
    data-page="@yield('admin-page')"
    @stack('body-data')
>
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 backdrop-blur-sm">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-violet-900">Админ-панель</a>
                <nav class="flex flex-wrap gap-1 text-sm">
                    <a href="{{ route('admin.genres') }}" class="rounded-md px-2 py-1 text-slate-700 hover:bg-slate-100">Жанры</a>
                    <a href="{{ route('admin.books') }}" class="rounded-md px-2 py-1 text-slate-700 hover:bg-slate-100">Книги</a>
                    <a href="{{ route('admin.orders') }}" class="rounded-md px-2 py-1 text-slate-700 hover:bg-slate-100">Заказы</a>
                    <a href="{{ route('admin.reviews') }}" class="rounded-md px-2 py-1 text-slate-700 hover:bg-slate-100">Отзывы</a>
                    <a href="{{ route('admin.users') }}" class="rounded-md px-2 py-1 text-slate-700 hover:bg-slate-100">Пользователи</a>
                </nav>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm text-violet-700 hover:underline">На сайт →</a>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        <div id="admin-gate" class="mb-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950">
            <p class="font-medium">Нет доступа к админ-панели.</p>
            <p class="mt-2">Нужен вход под учётной записью с правами администратора.</p>
            <a href="{{ route('shop.login') }}" class="mt-3 inline-block font-medium text-violet-800 underline">Войти</a>
        </div>

        <div id="admin-alert" class="mb-6 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"></div>

        <div id="admin-content">
            @yield('content')
        </div>
    </main>
</body>
</html>
