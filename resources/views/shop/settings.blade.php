@extends('layouts.shop')

@section('title', 'Настройки профиля')

@section('shop-page', 'settings')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Настройки</h1>
    <p class="mt-1 text-stone-600">Имя, email и смена пароля</p>

    <div id="settings-guest" class="mt-6 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950">
        <a href="{{ route('shop.login') }}" class="font-medium text-amber-900 underline">Войдите</a>, чтобы изменить профиль.
    </div>

    <div id="settings-root" class="mt-8 hidden max-w-lg space-y-10">
        <section>
            <h2 class="text-lg font-semibold text-stone-900">Профиль</h2>
            <form id="profile-form" class="mt-4 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <label class="block text-sm font-medium text-stone-700">Имя</label>
                <input type="text" id="settings-name" name="name" required autocomplete="name" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

                <label class="block text-sm font-medium text-stone-700">Email</label>
                <input type="email" id="settings-email" name="email" required autocomplete="email" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

                <button type="submit" class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-medium text-white hover:bg-amber-900">Сохранить профиль</button>
            </form>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-stone-900">Смена пароля</h2>
            <p class="mt-1 text-sm text-stone-500">После смены нужно войти заново на всех устройствах.</p>
            <form id="password-form" class="mt-4 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <label class="block text-sm font-medium text-stone-700">Текущий пароль</label>
                <input type="password" name="current_password" required autocomplete="current-password" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

                <label class="block text-sm font-medium text-stone-700">Новый пароль</label>
                <input type="password" name="password" required autocomplete="new-password" minlength="8" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

                <label class="block text-sm font-medium text-stone-700">Подтверждение</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

                <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-900">Сменить пароль</button>
            </form>
        </section>
    </div>
@endsection
