@extends('layouts.admin')

@section('title', 'Книги')

@section('admin-page', 'admin-books')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Книги</h1>
            <p class="mt-1 text-slate-600">Список из каталога API</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-medium text-white hover:bg-violet-800">Новая книга</a>
    </div>

    <div id="books-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Название</th>
                    <th class="px-4 py-3 font-medium">Автор</th>
                    <th class="px-4 py-3 font-medium">Цена</th>
                    <th class="px-4 py-3 font-medium">Склад</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody id="books-tbody"></tbody>
        </table>
    </div>
    <nav id="books-pagination" class="mt-6 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
