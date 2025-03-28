<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'surname',
        'name',
        'patronymic',
        'sign_path',
        'full_name',
        'email'
    ];
    public function certificates():HasMany
    {
        return $this->hasMany(Certificate::class);
    }

}
