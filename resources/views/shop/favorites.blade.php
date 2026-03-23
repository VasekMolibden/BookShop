@extends('layouts.shop')

@section('title', 'Избранное')

@section('shop-page', 'favorites')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Избранное</h1>
    <p id="favorites-guest" class="mt-2 hidden text-stone-600"><a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войдите</a>, чтобы видеть избранное.</p>

    <div id="favorites-loading" class="mt-6 text-stone-500">Загрузка…</div>
    <div id="favorites-empty" class="mt-6 hidden rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600">Пока пусто</div>
    <ul id="favorites-list" class="mt-6 hidden grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></ul>
    <nav id="favorites-pagination" class="mt-8 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
