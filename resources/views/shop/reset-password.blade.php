@extends('layouts.shop')

@section('title', 'Новый пароль')

@section('shop-page', 'reset-password')

@push('body-data')
    data-reset-token="{{ e($token) }}"
    data-reset-email="{{ e($email) }}"
@endpush

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Новый пароль</h1>
    <p class="mt-1 text-stone-600">Придумайте новый пароль для входа.</p>

    <form id="reset-form" class="mt-8 max-w-md space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <label class="block text-sm font-medium text-stone-700">Новый пароль</label>
        <input type="password" name="password" required autocomplete="new-password" minlength="8" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

        <label class="block text-sm font-medium text-stone-700">Подтверждение пароля</label>
        <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">

        <button type="submit" class="w-full rounded-lg bg-amber-800 py-2.5 text-sm font-medium text-white hover:bg-amber-900">Сохранить пароль</button>
    </form>
@endsection
