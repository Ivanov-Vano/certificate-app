<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'short_name',
        'name',
        'address',
        'registration_number',
        'country_id'
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
