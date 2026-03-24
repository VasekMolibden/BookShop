@extends('layouts.shop')

@section('title', 'Поиск книг')

@section('shop-page', 'search')

@section('content')
    <div class="flex gap-8">
        <!-- Filters Sidebar -->
        <aside class="w-80 flex-shrink-0">
            <div class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-stone-900 mb-4">Фильтры</h2>

                <form id="search-form" class="space-y-6">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-stone-700 mb-2">Поиск</label>
                        <input type="text" id="search" name="search" placeholder="Название или автор"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>

                    <!-- Genre Filter -->
                    <div>
                        <label for="genre" class="block text-sm font-medium text-stone-700 mb-2">Жанр</label>
                        <select id="genre" name="genre"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <option value="">Все жанры</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->slug }}">{{ $genre->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Author Filter -->
                    <div>
                        <label for="author" class="block text-sm font-medium text-stone-700 mb-2">Автор</label>
                        <select id="author" name="author"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <option value="">Все авторы</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->slug }}">{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Цена</label>
                        <div class="flex gap-2">
                            <input type="number" id="min_price" name="min_price" placeholder="От"
                                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <input type="number" id="max_price" name="max_price" placeholder="До"
                                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div>
                        <label for="min_rating" class="block text-sm font-medium text-stone-700 mb-2">Минимальный рейтинг</label>
                        <select id="min_rating" name="min_rating"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <option value="">Любой рейтинг</option>
                            <option value="4.5">4.5+ звезд</option>
                            <option value="4">4+ звезд</option>
                            <option value="3.5">3.5+ звезд</option>
                            <option value="3">3+ звезд</option>
                        </select>
                    </div>

                    <!-- Stock Filter -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="in_stock" name="in_stock"
                                   class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                            <span class="ml-2 text-sm text-stone-700">Только в наличии</span>
                        </label>
                    </div>

                    <!-- Year Filter -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-stone-700 mb-2">Год издания</label>
                        <input type="number" id="year" name="year" placeholder="Например: 2020"
                               min="1800" max="{{ date('Y') + 1 }}"
                               class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    </div>

                    <!-- Sort -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-stone-700 mb-2">Сортировка</label>
                        <select id="sort" name="sort"
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <option value="title">По названию</option>
                            <option value="price_asc">Цена: по возрастанию</option>
                            <option value="price_desc">Цена: по убыванию</option>
                            <option value="rating_desc">Рейтинг: высокий сначала</option>
                            <option value="reviews_desc">Отзывы: больше всего</option>
                            <option value="newest">Сначала новые</option>
                            <option value="popularity">Популярные</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button type="button" id="clear-filters"
                            class="w-full rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">
                        Сбросить фильтры
                    </button>
                </form>
            </div>
        </aside>

        <!-- Results -->
        <main class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-stone-900">Результаты поиска</h1>
                <!--<div id="results-count" class="text-sm text-stone-600">Загрузка...</div>-->
            </div>

            <div id="search-loading" class="text-stone-500 mb-6">Загрузка результатов...</div>
            <div id="search-empty" class="hidden rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600 mb-6">
                Ничего не найдено. Попробуйте изменить фильтры.
            </div>

            <div id="search-results" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-8"></div>
            <nav id="search-pagination" class="flex flex-wrap justify-center gap-2"></nav>
        </main>
    </div>
@endsection