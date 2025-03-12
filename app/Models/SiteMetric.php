<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'date',
        'unique_visitors',
        'page_views',
        'total_revenue',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
