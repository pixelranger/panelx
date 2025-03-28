<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthTokens extends Model
{
    protected $fillable = [
        'moonshine_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];
}
