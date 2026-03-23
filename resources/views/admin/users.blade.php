@extends('layouts.admin')

@section('title', 'Пользователи')

@section('admin-page', 'admin-users')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Пользователи</h1>
    <p class="mt-1 text-slate-600">Назначение прав администратора. В системе всегда должен остаться хотя бы один админ.</p>

    <div id="users-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Имя</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Регистрация</th>
                    <th class="px-4 py-3 font-medium">Администратор</th>
                </tr>
            </thead>
            <tbody id="admin-users-tbody"></tbody>
        </table>
    </div>
    <nav id="admin-users-pagination" class="mt-6 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
