<?php

namespace App\Models;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected $fillable =[
        'type_id',
        'chamber_id',
        'company_id',
        'expert_id',
        'invoice_issued',
        'paid',
        'date',
        'extended_page',
        'payer_id',
        'sender_id',
        'scan_path',
        'cost',
        'delivery_id',
        'number',
        'rec',
        'second_invoice',
    ];

    public function chamber():BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function company():BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function expert():BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }

    public function payer():BelongsTo
    {
        return $this->belongsTo(Organization::class, 'payer_id');
    }
    public function sender():BelongsTo
    {
        return $this->belongsTo(Organization::class, 'sender_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
    /**
     * Получить все теги для сертификатов.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

}
