<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Metrics;
use Illuminate\Support\Facades\Http;
use Iodev\Whois\Factory as Whois;


class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'url',
        'url_admin',
        'income_forecast',
        'domain_expiration_date',
        'created_by',
    ];

    // Аксессор для фавиконки
    public function getFaviconAttribute(): string
    {
        return "<img src='{$this->url}/favicon.png' width='32' height='32' />";
    }

    // Аксессор для уникальных посетителей
    public function getUniqueVisitorsAttribute(): int
    {
        return $this->metricsToday()?->unique_visitors ?? 0;
    }

    // Аксессор для просмотров страниц
    public function getPageViewsAttribute(): int
    {
        return $this->metricsToday()?->page_views ?? 0;
    }

    // Аксессор для дохода
    public function getTotalRevenueAttribute(): float
    {
        return $this->metricsToday()?->total_revenue ?? 0;
    }

    // Предполагаемый метод для получения метрик за сегодня
    public function metricsToday()
    {
        return $this->hasOne(Metrics::class)->whereDate('created_at', today())->first();
    }

    // Устанавливаем значение created_by автоматически
    public static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            $site->created_by = auth()->id() ?? 1;  // Если авторизованный пользователь отсутствует, ставим 1 (администратор по умолчанию)
        });

        static::created(function ($site) {
            // Создание записи метрик для нового сайта
            Metrics::create([
                'site_id' => $site->id,
                'unique_visitors' => 0,  // Установите начальное значение
                'page_views' => 0,       // Установите начальное значение
                'total_revenue' => 0.0,  // Установите начальное значение
                'created_at' => now(),
                'updated_at' => now(),
                'date' => now()->toDateString(),
            ]);
        });
    }

    // Метод для получения даты окончания регистрации домена
    public function getDomainExpirationDate(string $domain): ?string
    {
        try {
            // Создание объекта WHOIS
            $whois = Whois::get()->createWhois();
            $info = $whois->loadDomainInfo($domain);

            // Проверка наличия данных и даты окончания регистрации
            if ($info && $info->expirationDate) {
                return date('Y-m-d', $info->expirationDate);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка получения WHOIS для $domain: " . $e->getMessage());
        }

        return null;
    }

    public function getYesterdayMetrics(): ?Metrics
    {
        return $this->hasOne(Metrics::class)
                    ->whereDate('date', today()->subDay())
                    ->first();
    }

    public function getMetricDifference(string $metric): ?string
    {
        $todayValue = $this->metricsToday()?->$metric ?? 0;
        $yesterdayValue = $this->getYesterdayMetrics()?->$metric ?? 0;

        $diff = $todayValue - $yesterdayValue;

        if ($diff > 0) {
            return "<span style='color: green;'>+{$diff}</span>";
        } elseif ($diff < 0) {
            return "<span style='color: red;'>{$diff}</span>";
        }

        return "<span style='color: gray;'>0</span>";
    }

}
