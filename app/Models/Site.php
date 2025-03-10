<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Site extends Model
{
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

}
