<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrForm extends Model
{
    protected $fillable = ['type', 'title', 'data', 'created_by'];
    protected $casts = ['data' => 'array'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'dayoff'   => 'Change Day-Off Form',
            'absences' => 'Absences Report Form',
            'voucher'  => 'Allowance Voucher ARCS',
            default    => 'HR Form',
        };
    }
}
