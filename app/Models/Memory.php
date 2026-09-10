<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'source_type',
        'language',
        'status',
        'occurred_at',
        'due_at',
        'expiry_at',
        'currency_code',
        'amount',
        'local_file_reference',
        'ai_summary',
        'ai_confidence',
        'user_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'due_at' => 'datetime',
            'expiry_at' => 'datetime',
            'amount' => 'decimal:2',
            'ai_confidence' => 'decimal:4',
            'user_confirmed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}