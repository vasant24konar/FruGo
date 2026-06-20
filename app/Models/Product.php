<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'category',
        'description',
        'price',
        'currency',
        'stock',
        'image_path',
        'date_available',
        'created_by',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'date_available' => 'date',
            'approved_at'    => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_PENDING  => 'warning',
            self::STATUS_REJECTED => 'danger',
            default               => 'secondary',
        };
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%');
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereDate('date_available', '<=', now());
    }
}
