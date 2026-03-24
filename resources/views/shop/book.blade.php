@extends('layouts.shop')

@section('title', 'Книга')

@section('shop-page', 'book')

@push('body-data')
    data-book-id="{{ $bookId }}"
@endpush

@section('content')
    <div id="book-loading" class="text-stone-500">Загрузка…</div>
    <article id="book-root" class="hidden">
        <a href="{{ route('shop.index') }}" class="mb-4 inline-block text-sm text-amber-900 hover:underline">← К каталогу</a>
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:gap-10">
                <div id="book-carousel" class="flex h-48 w-full shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 text-6xl text-amber-900/40 lg:h-64 lg:w-48">
                    📖
                </div>
                <div class="min-w-0 flex-1">
                    <h1 id="book-title" class="text-3xl font-bold text-stone-900"></h1>
                    <p id="book-author" class="mt-1 text-lg text-stone-600"></p>
                    <p id="book-meta" class="mt-2 text-sm text-stone-500"></p>
                    <div id="book-genres" class="mt-3 flex flex-wrap gap-2"></div>
                    <p id="book-price" class="mt-6 text-2xl font-semibold text-amber-900"></p>
                    <p id="book-stock" class="mt-1 text-sm text-stone-600"></p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 text-sm">
                            <span class="text-stone-600">Кол-во</span>
                            <input type="number" id="book-qty" min="1" value="1" class="w-20 rounded-lg border border-stone-300 px-2 py-1">
                        </label>
                        <button type="button" id="book-add-cart" class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-medium text-white hover:bg-amber-900">В корзину</button>
                        <button type="button" id="book-toggle-fav" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-800 hover:bg-stone-50">В избранное</button>
                    </div>
                </div>
            </div>
            <div class="mt-10 border-t border-stone-100 pt-8">
                <h2 class="text-xl font-semibold text-stone-900">Описание</h2>
                <p id="book-description" class="mt-3 whitespace-pre-wrap text-stone-700 leading-relaxed"></p>
            </div>
        </div>

        <section class="mt-10">
            <h2 class="text-xl font-semibold text-stone-900"><a href="#book-reviews">Отзывы</a></h2>
            <form id="review-form" class="mt-4 hidden rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-stone-800">Оставить отзыв</p>
                <label class="mt-2 block text-sm text-stone-600">Оценка (1–5)</label>
                <select id="review-rating" class="mt-1 rounded-lg border border-stone-300 px-3 py-2">
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
                <label class="mt-3 block text-sm text-stone-600">Комментарий</label>
                <textarea id="review-comment" rows="3" class="mt-1 w-full max-w-xl rounded-lg border border-stone-300 px-3 py-2" placeholder="Необязательно"></textarea>
                <button type="submit" class="mt-3 rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800">Отправить</button>
            </form>
            <p id="review-login-hint" class="mt-4 text-sm text-stone-600"><a href="{{ route('shop.login') }}" class="text-amber-900 underline">Войдите</a>, чтобы оставить отзыв.</p>
            <ul id="book-reviews" class="mt-6 space-y-4"></ul>
            <nav id="reviews-pagination" class="mt-6 hidden flex flex-wrap justify-center gap-2"></nav>
        </section>
    </article>
@endsection
