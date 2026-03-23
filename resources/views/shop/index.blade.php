@extends('layouts.shop')

@section('title', 'Каталог')

@section('shop-page', 'catalog')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-stone-900">Каталог</h1>
            <p class="mt-1 text-stone-600">Поиск по названию и автору, фильтр по жанру</p>
        </div>
    </div>

    <div class="mb-6 flex flex-col gap-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
        <label class="flex flex-1 flex-col gap-1 text-sm">
            <span class="font-medium text-stone-700">Поиск</span>
            <input type="search" id="catalog-search" placeholder="Название или автор…" class="rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">
        </label>
        <label class="flex w-full flex-col gap-1 text-sm sm:w-56">
            <span class="font-medium text-stone-700">Жанр</span>
            <select id="catalog-genre" class="rounded-lg border border-stone-300 px-3 py-2 focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-600">
                <option value="">Все жанры</option>
            </select>
        </label>
        <button type="button" id="catalog-apply" class="mt-6 rounded-lg bg-amber-800 px-4 py-2 text-sm font-medium text-white hover:bg-amber-900 sm:mt-0">Применить</button>
    </div>

    <div id="catalog-loading" class="text-stone-500">Загрузка…</div>
    <div id="catalog-empty" class="hidden rounded-xl border border-dashed border-stone-300 bg-white p-12 text-center text-stone-600">Ничего не найдено</div>
    <ul id="catalog-list" class="hidden grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></ul>

    <nav id="catalog-pagination" class="mt-8 hidden flex flex-wrap items-center justify-center gap-2"></nav>
@endsection
