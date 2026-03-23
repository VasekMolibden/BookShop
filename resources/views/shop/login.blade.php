@extends('layouts.shop')

@section('title', 'Вход')

@section('shop-page', 'login')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Вход</h1>
    <p class="mt-1 text-stone-600">Нет аккаунта? <a href="{{ route('shop.register') }}" class="text-amber-900 underline">Регистрация</a></p>

    <form id="login-form" class="mt-8 max-w-md space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <label class="block text-sm font-medium text-stone-700">Email</label>
        <input type="email" name="email" required autocomplete="email" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

        <label class="block text-sm font-medium text-stone-700">Пароль</label>
        <input type="password" name="password" required autocomplete="current-password" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">
        <p class="text-right text-sm">
            <a href="{{ route('password.request') }}" class="text-amber-900 hover:underline">Забыли пароль?</a>
        </p>

        <button type="submit" class="w-full rounded-lg bg-amber-800 py-2.5 text-sm font-medium text-white hover:bg-amber-900">Войти</button>
    </form>
@endsection
