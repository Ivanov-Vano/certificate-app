<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'type_id',
        'chamber_id',
        'organization_id',
        'company_id',
        'expert_id',
        'scan_issued',
        'invoice_issued',
        'paid',
        'date'
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

    public function organization():BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
