<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'number',
        'accepted_at',
        'cost',
        'organization_id',
        'deliveryman_id',
        'is_pickup',
        'delivered_at'
    ];
    public function deliveryman():BelongsTo
    {
        return $this->belongsTo(Deliveryman::class);
    }
    public function organization():BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
    public function certificates():HasMany
    {
        return $this->hasMany(Certificate::class);
    }
    /**
     * Получить все теги для доставки
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

}
