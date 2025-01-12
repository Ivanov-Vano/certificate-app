<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'alias',
        'status',
        'note',

    ];

    /**
     * Перечисления статуса заказов
     *
     * @var class-string[]
     */
    protected $casts = [
        'status' => LeadStatus::class,
        'aliases' => 'array', // приведение поля aliases к типу array
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

}
