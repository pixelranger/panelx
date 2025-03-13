<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доход по сайту {{ $domain }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .revenue-info {
            margin-bottom: 20px;
        }
        .revenue-info h2 {
            color: #333;
        }
        .revenue-info p {
            font-size: 18px;
            color: #555;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Доход по сайту {{ $domain }}</h1>

    <div class="revenue-info">
        <h2>Сегодня</h2>
        @if ($revenueData['today'] > 0)
            <p>Доход: {{ number_format($revenueData['today'], 2) }} руб.</p>
        @else
            <p class="error">Данные за сегодня не найдены.</p>
        @endif
    </div>

    <div class="revenue-info">
        <h2>Вчера</h2>
        @if ($revenueData['yesterday'] > 0)
            <p>Доход: {{ number_format($revenueData['yesterday'], 2) }} руб.</p>
        @else
            <p class="error">Данные за вчера не найдены.</p>
        @endif
    </div>

    @if ($revenueData['today'] <= 0 && $revenueData['yesterday'] <= 0)
        <p class="error">Нет данных для отображения. Проверьте токен и параметры запроса.</p>
    @endif
</body>
</html>
