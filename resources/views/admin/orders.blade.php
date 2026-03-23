@extends('layouts.admin')

@section('title', 'Заказы')

@section('admin-page', 'admin-orders')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Все заказы</h1>
    <p class="mt-1 text-slate-600">Смена статуса сохраняется сразу</p>

    <div id="orders-loading" class="mt-8 text-slate-500">Загрузка…</div>
    <div class="mt-8 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">№</th>
                    <th class="px-4 py-3 font-medium">Покупатель</th>
                    <th class="px-4 py-3 font-medium">Дата</th>
                    <th class="px-4 py-3 font-medium">Сумма</th>
                    <th class="px-4 py-3 font-medium">Статус</th>
                </tr>
            </thead>
            <tbody id="admin-orders-tbody"></tbody>
        </table>
    </div>
    <nav id="admin-orders-pagination" class="mt-6 hidden flex flex-wrap justify-center gap-2"></nav>
@endsection
