<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliveryman extends Model
{
    use HasFactory;

    protected $fillable =[
        'surname',
        'name',
        'patronymic',
        'full_name',
        'phone',
        'email'
    ];

}
