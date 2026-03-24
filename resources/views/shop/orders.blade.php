@extends('layouts.shop')

@section('title', 'Заказы')

@section('shop-page', 'orders')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Мои заказы</h1>
    <p id="orders-guest" class="mt-2 hidden text-stone-600"><a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войдите</a>, чтобы видеть заказы.</p>

    <div id="orders-loading" class="mt-6 text-stone-500">Загрузка…</div>
    <div id="orders-empty" class="mt-6 hidden rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600">Нет заказов</div>
    <ul id="orders-list" class="mt-6 hidden space-y-3"></ul>
    <nav id="orders-pagination" class="mt-8 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
