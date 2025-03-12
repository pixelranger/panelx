@extends('layouts.app')

@section('title', 'Сайты и метрики')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Сайты и их метрики</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Домен</th>
                <th>Дата</th> <!-- Добавлен столбец для даты -->
                <th>Уникальные посетители</th>
                <th>Просмотры страниц</th>
                <th>Доход (₽)</th>
                <th>Обновлено</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sites as $site)
                <tr>
                    <td>{{ $site->id }}</td>
                    <td>
                        <a href="http://{{ $site->domain }}" target="_blank">{{ $site->domain }}</a>
                    </td>
                    <td>{{ $site->metrics_today->date ?? '—' }}</td> <!-- Дата -->
                    <td>{{ $site->metrics_today->unique_visitors ?? 0 }}</td>
                    <td>{{ $site->metrics_today->page_views ?? 0 }}</td>
                    <td>{{ $site->metrics_today->total_revenue ?? 0.00 }} ₽</td>
                    <td>{{ $site->metrics_today->updated_at ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
