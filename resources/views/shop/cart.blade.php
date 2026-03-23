@extends('layouts.shop')

@section('title', 'Корзина')

@section('shop-page', 'cart')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Корзина</h1>
    <p id="cart-guest" class="mt-2 hidden text-stone-600"><a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войдите</a>, чтобы увидеть корзину.</p>

    <div id="cart-loading" class="mt-6 text-stone-500">Загрузка…</div>
    <div id="cart-empty" class="mt-6 hidden rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600">Корзина пуста</div>

    <div id="cart-root" class="mt-6 hidden">
        <ul id="cart-items" class="space-y-4"></ul>
        <div class="mt-8 flex flex-col gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-stone-600">Итого</p>
                <p id="cart-total" class="text-2xl font-semibold text-amber-900"></p>
            </div>
            <button type="button" id="cart-checkout" class="rounded-lg bg-amber-800 px-6 py-3 text-sm font-medium text-white hover:bg-amber-900">Оформить заказ</button>
        </div>
    </div>
@endsection
