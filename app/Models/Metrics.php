<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Metrics extends Model
{
    use HasFactory;

    protected $table = 'sites_metrics';

    protected $fillable = [
        'created_at',
        'site_id',
        'unique_visitors',
        'page_views',
        'total_revenue',
        'date',
    ];

    // Связь с моделью Site
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
