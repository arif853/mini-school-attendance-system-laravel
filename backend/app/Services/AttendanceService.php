<?php

namespace App\Services;

use App\Events\AttendanceRecorded;
use App\Models\Attendance;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private ?CacheRepository $cache = null)
    {
        $this->cache = $cache ?: Cache::store(config('cache.default', 'file'));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordBulk(array $payload): Collection
    {
        $date = Carbon::parse($payload['date'])->toDateString();
        $entries = collect($payload['entries']);
        $recordedBy = $payload['recorded_by'] ?? 'system';

        $saved = DB::transaction(function () use ($entries, $date, $recordedBy) {
            return $entries->map(function (array $entry) use ($date, $recordedBy) {
                $attendance = Attendance::query()->updateOrCreate(
                    [
                        'student_id' => $entry['student_id'],
                        'date' => $date,
                    ],
                    [
                        'status' => $entry['status'],
                        'note' => $entry['note'] ?? null,
                        'recorded_by' => $recordedBy,
                    ]
                );

                return $attendance->load('student');
            });
        });

        $class = $payload['class'] ?? null;
        $section = $payload['section'] ?? null;
        $this->invalidateStatsCache($date, $class);

        AttendanceRecorded::dispatch($date, $class, $section, $saved);

        return $saved;
    }

    public function getTodayStats(?string $class = null): array
    {
        $date = Carbon::today()->toDateString();

        return $this->cache->remember($this->statsCacheKey($date, $class), now()->addMinutes(15), function () use ($date, $class) {
            $query = Attendance::query()->whereDate('date', $date)->with('student');

            if ($class) {
                $query->whereHas('student', fn ($q) => $q->where('class', $class));
            }

            $counts = $query->get()->groupBy('status')->map->count();
            $total = $counts->sum();

            $percentage = $total > 0 ? round(($counts[Attendance::STATUS_PRESENT] ?? 0) / $total * 100, 1) : 0;

            return [
                'date' => $date,
                'class' => $class,
                'total' => $total,
                'present' => $counts[Attendance::STATUS_PRESENT] ?? 0,
                'absent' => $counts[Attendance::STATUS_ABSENT] ?? 0,
                'late' => $counts[Attendance::STATUS_LATE] ?? 0,
                'attendance_percentage' => $percentage,
            ];
        });
    }

    public function getMonthlyReport(string $month, ?string $class = null, ?string $section = null): Collection
    {
        $start = Carbon::parse($month . '-01');
        $end = (clone $start)->endOfMonth();

        $records = Attendance::query()
            ->with('student')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($class, fn ($q) => $q->whereHas('student', fn ($student) => $student->where('class', $class)))
            ->when($section, fn ($q) => $q->whereHas('student', fn ($student) => $student->where('section', $section)))
            ->get()
            ->groupBy('student_id')
            ->map(function (Collection $rows) {
                $student = $rows->first()->student;
                $tallies = [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                ];

                foreach ($rows as $row) {
                    $tallies[$row->status]++;
                }

                $totalDays = array_sum($tallies);
                $percentage = $totalDays > 0 ? round($tallies['present'] / $totalDays * 100, 1) : 0;

                return collect([
                    'student' => $student,
                    'present' => $tallies['present'],
                    'absent' => $tallies['absent'],
                    'late' => $tallies['late'],
                    'total_days' => $totalDays,
                    'attendance_percentage' => $percentage,
                ]);
            })
            ->values();

        return $records;
    }

    private function statsCacheKey(string $date, ?string $class): string
    {
        return sprintf('attendance:stats:%s:%s', $date, $class ?? 'all');
    }

    private function invalidateStatsCache(string $date, ?string $class): void
    {
        $this->cache->forget($this->statsCacheKey($date, $class));
        $this->cache->forget($this->statsCacheKey($date, null));
    }
}
