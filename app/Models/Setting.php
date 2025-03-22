<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;
    protected $casts = [
        'columns_visibility' => 'array',
    ];

    protected $fillable =[
        'role_name',
        'columns_visibility',
    ];

}
