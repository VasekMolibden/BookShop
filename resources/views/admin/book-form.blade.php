@extends('layouts.admin')

@section('title', $bookId ? 'Редактирование книги' : 'Новая книга')

@section('admin-page', 'admin-book-form')

@push('body-data')
    @if($bookId)
        data-book-id="{{ $bookId }}"
    @endif
@endpush

@section('content')
    <a href="{{ route('admin.books') }}" class="text-sm text-violet-700 hover:underline">← К списку книг</a>
    <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ $bookId ? 'Редактирование книги' : 'Новая книга' }}</h1>

    <form id="book-form" class="mt-8 max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <label class="block text-sm font-medium text-slate-700">Название *</label>
        <input type="text" id="bf-title" required class="w-full rounded-lg border border-slate-300 px-3 py-2">

        <label class="block text-sm font-medium text-slate-700">Slug (пусто — сгенерировать)</label>
        <input type="text" id="bf-slug" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder="оставьте пустым при создании">

        <label class="block text-sm font-medium text-slate-700">Автор *</label>
        <select id="bf-author" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">Выберите автора</option>
        </select>

        <label class="block text-sm font-medium text-slate-700">Описание</label>
        <textarea id="bf-description" rows="5" class="w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>

        <label class="block text-sm font-medium text-slate-700">Цена *</label>
        <input type="number" id="bf-price" required min="0" step="0.01" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2">

        <label class="block text-sm font-medium text-slate-700">ISBN</label>
        <input type="text" id="bf-isbn" class="w-full max-w-md rounded-lg border border-slate-300 px-3 py-2">

        <label class="block text-sm font-medium text-slate-700">Остаток на складе *</label>
        <input type="number" id="bf-stock" required min="0" step="1" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2">

        <label class="block text-sm font-medium text-slate-700">Дата выхода</label>
        <input type="date" id="bf-published" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2">

        <fieldset class="border-t border-slate-100 pt-4">
            <legend class="text-sm font-medium text-slate-700">Жанры * (минимум один)</legend>
            <div id="bf-genres" class="mt-3 flex flex-wrap gap-3"></div>
        </fieldset>

        <button type="submit" class="rounded-lg bg-violet-700 px-6 py-2.5 text-sm font-medium text-white hover:bg-violet-800">Сохранить</button>
    </form>

    @if($bookId)
        <!-- Блок управления картинками (только для редактирования) -->
        <div id="images-section" class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-6 text-lg font-bold text-slate-900">Картинки книги</h2>

            <!-- Загрузка новой картинки -->
            <div class="mb-8 rounded-lg border-2 border-dashed border-slate-300 p-6">
                <div id="image-upload-zone" class="text-center">
                    <svg class="mx-auto mb-3 h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-14-12l-4 4m4-4l4 4m-4-4v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <p class="text-slate-600">Выберите картинку или перетащите её сюда</p>
                    <p class="text-xs text-slate-400 mt-1">Форматы: JPEG, PNG, WebP, GIF (max 5 MB)</p>
                </div>
                <input type="file" id="image-file-input" accept="image/*" class="hidden" />
                <div class="mt-4 space-y-3">
                    <input 
                        type="text" 
                        id="image-alt-text" 
                        placeholder="Альтернативный текст (опционально)" 
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                    <button 
                        type="button" 
                        id="upload-image-btn" 
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        Загрузить картинку
                    </button>
                </div>
            </div>

            <!-- Список загруженных картинок -->
            <div id="images-list" class="space-y-4"></div>

            <div id="image-alert" class="hidden mt-4 rounded-lg border px-4 py-3 text-sm"></div>
        </div>
    @endif
@endsection
