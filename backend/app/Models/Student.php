<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'student_id',
        'class',
        'section',
        'photo_path',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope a query to filter students by class and section.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $class
     * @param string|null $section
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClassSection(\Illuminate\Database\Eloquent\Builder $query, ?string $class, ?string $section): \Illuminate\Database\Eloquent\Builder
    {
        if ($class) {
            $query->where('class', $class);
        }

        if ($section) {
            $query->where('section', $section);
        }

        return $query;
    }
}
