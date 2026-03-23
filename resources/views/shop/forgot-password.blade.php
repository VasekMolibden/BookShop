@extends('layouts.shop')

@section('title', 'Восстановление пароля')

@section('shop-page', 'forgot-password')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Забыли пароль?</h1>
    <p class="mt-1 text-stone-600">Укажите email — отправим ссылку для сброса пароля.</p>
    <p class="mt-2 text-sm text-stone-500">Помните пароль? <a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войти</a></p>

    <form id="forgot-form" class="mt-8 max-w-md space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <label class="block text-sm font-medium text-stone-700">Email</label>
        <input type="email" name="email" required autocomplete="email" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

        <button type="submit" class="w-full rounded-lg bg-amber-800 py-2.5 text-sm font-medium text-white hover:bg-amber-900">Отправить ссылку</button>
    </form>
@endsection
