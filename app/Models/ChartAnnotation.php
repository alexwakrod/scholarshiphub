<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartAnnotation extends Model
{
    protected $table = 'chart_annotations';

    protected $fillable = [
        'user_id',
        'chart_id',
        'position_x',
        'position_y',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'decimal:2',
            'position_y' => 'decimal:2',
        ];
    }
}