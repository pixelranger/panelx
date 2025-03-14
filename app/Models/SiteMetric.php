<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\MoonShine\Resources\SiteMetricResource;

class SiteMetric extends Model
{
    use HasFactory;

    protected $table = 'sites_metrics';

    // protected $fillable = [
    //     'site_id',
    //     'date',
    //     'unique_visitors',
    //     'page_views',
    //     'total_revenue',
    // ];

    // public function site()
    // {
    //     return $this->belongsTo(Site::class);
    // }

    // public function resources(): array
    // {
    //     return [
    //         SiteMetricResource::class,
    //     ];
    // }

    use HasFactory;

    protected $fillable = ['site_id', 'date', 'unique_visitors', 'page_views', 'total_revenue'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

}
