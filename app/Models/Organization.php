<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'short_name',
        'name',
        'inn',
        'phone',
        'address',
        'additional_number',
        'delivery_price',
        'discount'
    ];
    public function clients():HasMany
    {
        return $this->hasMany(Client::class);
    }
}
