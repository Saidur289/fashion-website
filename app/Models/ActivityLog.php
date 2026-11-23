<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'subject_label', 'description', 'properties', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?string $subjectLabel = null,
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel ?? ($subject ? ($subject->number ?? $subject->name ?? $subject->code ?? '#'.$subject->getKey()) : null),
            'description' => $description,
            'properties' => $properties,
            'ip' => request()?->ip(),
        ]);
    }
}
