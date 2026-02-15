<?php

namespace Skywalker\Impersonate\Models;

use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    protected $guarded = [];

    public function getTable()
    {
        return config('laravel-impersonate.log_table', 'impersonation_logs');
    }

    public function impersonator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'impersonator_id');
    }

    public function impersonated()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'impersonated_id');
    }
}
