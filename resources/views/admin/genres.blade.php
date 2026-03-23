@extends('layouts.admin')

@section('title', 'Жанры')

@section('admin-page', 'admin-genres')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Жанры</h1>
    <p class="mt-1 text-slate-600">Slug: латиница, цифры и дефисы (например <code class="rounded bg-slate-100 px-1">sci-fi</code>)</p>

    <form id="genre-form" class="mt-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" id="genre-id" value="">
        <label class="block text-sm font-medium text-slate-700 sm:col-span-2">
            Название
            <input type="text" id="genre-name" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block text-sm font-medium text-slate-700 sm:col-span-2">
            Slug
            <input type="text" id="genre-slug" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
        </label>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" id="genre-submit" class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-medium text-white hover:bg-violet-800">Сохранить</button>
            <button type="button" id="genre-reset" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Сброс</button>
        </div>
    </form>

    <div id="genres-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Название</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody id="genres-tbody"></tbody>
        </table>
    </div>
@endsection
