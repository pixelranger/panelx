<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Metrics;
use Illuminate\Support\Facades\Http;

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

    // 🔹 Автоматическое обновление URL админки при создании/обновлении
    public static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            $site->url_admin = $site->fetchAdminUrl() ?? 'https://example.com/admin'; // Заменить на дефолтный URL
            $site->domain_expiration_date = $site->fetchDomainExpiration();
            $site->created_by = auth()->id() ?? 1;
        });

        static::updating(function ($site) {
            if (!$site->url_admin) {
                $site->url_admin = $site->fetchAdminUrl();
            }
            if (!$site->domain_expiration_date) {
                $site->domain_expiration_date = $site->fetchDomainExpiration();
            }
        });
    }

    // 🔹 Метод для получения URL админки из Яндекс.Метрики
    public function fetchAdminUrl(): ?string
    {
        try {
            $response = Http::get("https://api-metrika.yandex.net/stat/v1/data", [
                'ids' => $this->counter_id, // ID счётчика метрики
                'metrics' => 'ym:pv:adminUrl', // Подставить реальный параметр метрики
                'oauth_token' => config('services.yandex_metrika.token'),
            ]);

            if ($response->successful()) {
                return $response->json()['data'][0]['adminUrl'] ?? null;
            }
        } catch (\Exception $e) {
            \Log::error("Ошибка получения URL админки: " . $e->getMessage());
        }

        return null;
    }

    // 🔹 Метод для получения даты окончания регистрации домена из WHOIS
    public function fetchDomainExpiration(): ?string
    {
        try {
            $response = Http::get("https://whois-service.com/api", [
                'domain' => parse_url($this->url, PHP_URL_HOST),
                'apiKey' => config('services.whois.api_key'),
            ]);

            if ($response->successful()) {
                return $response->json()['expiration_date'] ?? null;
            }
        } catch (\Exception $e) {
            \Log::error("Ошибка получения даты окончания регистрации домена: " . $e->getMessage());
        }

        return null;
    }
}

