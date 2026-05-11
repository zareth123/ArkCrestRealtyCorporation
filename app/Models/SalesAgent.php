<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesAgent extends Model
{
    protected $fillable = ['team_id', 'user_id', 'employee_id', 'name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function team()
    {
        return $this->belongsTo(SalesTeam::class, 'team_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
