@extends('layouts.admin')

@section('title', 'Авторы')

@section('admin-page', 'admin-authors')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Авторы</h1>
    <p class="mt-1 text-slate-600">Slug: латиница, цифры и дефисы (например <code class="rounded bg-slate-100 px-1">leo-tolstoy</code>)</p>

    <form id="author-form" class="mt-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-3">
        <input type="hidden" id="author-id" value="">
        <label class="block text-sm font-medium text-slate-700">
            Имя автора
            <input type="text" id="author-name" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block text-sm font-medium text-slate-700">
            Slug
            <input type="text" id="author-slug" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
        </label>
        <label class="block text-sm font-medium text-slate-700">
            Биография
            <textarea id="author-bio" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Краткая биография автора (необязательно)"></textarea>
        </label>
        <div class="flex items-end gap-2 lg:col-span-3">
            <button type="submit" id="author-submit" class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-medium text-white hover:bg-violet-800">Сохранить</button>
            <button type="button" id="author-reset" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Сброс</button>
        </div>
    </form>

    <div id="authors-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Имя</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium">Книг</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody id="authors-tbody"></tbody>
        </table>
    </div>
@endsection