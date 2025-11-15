<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForMonth($query, string $month)
    {
        $period = Carbon::parse($month . '-01');

        return $query
            ->whereYear('date', $period->year)
            ->whereMonth('date', $period->month);
    }

    public function scopeForClass($query, ?string $class)
    {
        if (! $class) {
            return $query;
        }

        return $query->whereHas('student', fn ($q) => $q->where('class', $class));
    }

    public function scopeForSection($query, ?string $section)
    {
        if (! $section) {
            return $query;
        }

        return $query->whereHas('student', fn ($q) => $q->where('section', $section));
    }
}
