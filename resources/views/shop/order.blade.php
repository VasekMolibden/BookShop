@extends('layouts.shop')

@section('title', 'Заказ')

@section('shop-page', 'order')

@push('body-data')
    data-order-id="{{ $orderId }}"
@endpush

@section('content')
    <a href="{{ route('shop.orders') }}" class="mb-4 inline-block text-sm text-amber-900 hover:underline">← Все заказы</a>

    <div id="order-loading" class="text-stone-500">Загрузка…</div>
    <div id="order-root" class="hidden rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h1 class="text-xl font-bold text-stone-900">Заказ №<span id="order-id"></span></h1>
            <span id="order-status" class="rounded-full bg-stone-100 px-3 py-1 text-sm font-medium text-stone-800"></span>
        </div>
        <p id="order-date" class="mt-2 text-sm text-stone-600"></p>
        <p id="order-total" class="mt-4 text-lg font-semibold text-amber-900"></p>
        <h2 class="mt-8 text-sm font-semibold uppercase tracking-wide text-stone-500">Состав</h2>
        <ul id="order-items" class="mt-3 divide-y divide-stone-100"></ul>
    </div>
@endsection
