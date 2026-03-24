@extends('layouts.admin')

@section('title', 'Панель')

@section('admin-page', 'admin-dashboard')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Обзор</h1>
    <p class="mt-1 text-slate-600">Управление каталогом, заказами и отзывами</p>

    <ul class="mt-8 grid gap-4 sm:grid-cols-2">
        <li>
            <a href="{{ route('admin.genres') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Жанры</h2>
                <p class="mt-1 text-sm text-slate-600">Создание, редактирование, удаление</p>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.authors') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Авторы</h2>
                <p class="mt-1 text-sm text-slate-600">Управление авторами книг</p>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.books') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Книги</h2>
                <p class="mt-1 text-sm text-slate-600">Каталог и карточки товаров</p>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.orders') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Заказы</h2>
                <p class="mt-1 text-sm text-slate-600">Статусы и суммы</p>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reviews') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Отзывы</h2>
                <p class="mt-1 text-sm text-slate-600">Модерация</p>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.users') }}" class="block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300">
                <h2 class="font-semibold text-slate-900">Пользователи</h2>
                <p class="mt-1 text-sm text-slate-600">Роли администратора</p>
            </a>
        </li>
    </ul>
@endsection
