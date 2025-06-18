@extends('layouts.app')

@section('title', 'Сайты и метрики')

@section('content')
<div class="container mt-4">
    <h2>Список сайтов</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Домен</th>
                <th>Уникальные посетители</th>
                <th>Просмотры страниц</th>
                <th>Доход</th>
                <th>Окончание регистрации</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sites as $site)
                @php
                    $metric = $site->metrics->first();
                    $changeVisitors = $metric ? $metric->unique_visitors - $metric->prev_unique_visitors : 0;
                    $changePageViews = $metric ? $metric->page_views - $metric->prev_page_views : 0;
                    $changeRevenue = $metric ? $metric->total_revenue - $metric->prev_total_revenue : 0;
                @endphp
                <tr>
                    <td>{{ $site->title }}</td>
                    <td>
                        {{ $metric->unique_visitors ?? 0 }}
                        <span style="color: {{ $changeVisitors >= 0 ? 'green' : 'red' }};">
                            {{ $changeVisitors > 0 ? '+' : '' }}{{ $changeVisitors }}
                        </span>
                    </td>
                    <td>
                        {{ $metric->page_views ?? 0 }}
                        <span style="color: {{ $changePageViews >= 0 ? 'green' : 'red' }};">
                            {{ $changePageViews > 0 ? '+' : '' }}{{ $changePageViews }}
                        </span>
                    </td>
                    <td>
                        {{ number_format($metric->total_revenue ?? 0, 2) }}
                        <span style="color: {{ $changeRevenue >= 0 ? 'green' : 'red' }};">
                            {{ $changeRevenue > 0 ? '+' : '' }}{{ number_format($changeRevenue, 2) }}
                        </span>
                    </td>
                    <td>{{ $site->domain_expiration_date ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
