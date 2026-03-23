@extends('layouts.admin')

@section('title', 'Отзывы')

@section('admin-page', 'admin-reviews')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Модерация отзывов</h1>
    <p class="mt-1 text-slate-600">Удаление необратимо</p>

    <div id="reviews-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Книга</th>
                    <th class="px-4 py-3 font-medium">Автор</th>
                    <th class="px-4 py-3 font-medium">Оценка</th>
                    <th class="px-4 py-3 font-medium">Текст</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody id="admin-reviews-tbody"></tbody>
        </table>
    </div>
    <nav id="admin-reviews-pagination" class="mt-6 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
