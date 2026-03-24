@extends('layouts.shop')

@section('title', 'Мои отзывы')

@section('shop-page', 'reviews')

@section('content')
    <h1 class="text-2xl font-bold text-stone-900">Мои отзывы</h1>
    <p id="reviews-guest" class="mt-2 hidden text-stone-600"><a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войдите</a>, чтобы видеть свои отзывы.</p>

    <div id="reviews-loading" class="mt-6 text-stone-500">Загрузка…</div>
    <div id="reviews-empty" class="mt-6 hidden rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600">У вас пока нет отзывов</div>
    <ul id="reviews-list" class="mt-6 hidden space-y-6"></ul>
    <nav id="reviews-pagination" class="mt-8 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection