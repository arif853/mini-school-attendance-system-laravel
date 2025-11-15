<?php

namespace Tests\Unit;

use App\Events\AttendanceRecorded;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_bulk_upserts_and_dispatches_event(): void
    {
        Event::fake();
        Carbon::setTestNow('2025-05-01');

        $students = Student::factory()->count(2)->create([
            'class' => '10',
            'section' => 'A',
        ]);

        $service = app(AttendanceService::class);

        $payload = [
            'date' => now()->toDateString(),
            'recorded_by' => 'Dean',
            'class' => '10',
            'section' => 'A',
            'entries' => $students->map(function (Student $student, int $index) {
                return [
                    'student_id' => $student->id,
                    'status' => $index === 0 ? Attendance::STATUS_PRESENT : Attendance::STATUS_ABSENT,
                    'note' => $index === 0 ? null : 'Travel',
                ];
            })->all(),
        ];

        $records = $service->recordBulk($payload);

        $this->assertCount(2, $records);
        $this->assertTrue($records->every(fn ($attendance) => $attendance->relationLoaded('student')));

        $this->assertTrue(
            Attendance::where('student_id', $students[0]->id)
                ->whereDate('date', now()->toDateString())
                ->where('status', Attendance::STATUS_PRESENT)
                ->where('recorded_by', 'Dean')
                ->exists()
        );

        Event::assertDispatched(AttendanceRecorded::class, function (AttendanceRecorded $event) use ($payload) {
            return $event->date === $payload['date']
                && $event->class === $payload['class']
                && $event->section === $payload['section']
            && $event->attendances->count() === 2;
        });
    }

    public function test_today_stats_refresh_after_new_records(): void
    {
        Carbon::setTestNow('2025-05-02');

        $students = Student::factory()->count(2)->create([
            'class' => '10',
        ]);

        $service = app(AttendanceService::class);

        $service->recordBulk([
            'date' => now()->toDateString(),
            'class' => '10',
            'entries' => [
                ['student_id' => $students[0]->id, 'status' => Attendance::STATUS_PRESENT],
                ['student_id' => $students[1]->id, 'status' => Attendance::STATUS_ABSENT],
            ],
        ]);

        $initialStats = $service->getTodayStats('10');

        $this->assertSame(2, $initialStats['total']);
        $this->assertSame(1, $initialStats['present']);
        $this->assertSame(1, $initialStats['absent']);

        $newStudent = Student::factory()->create([
            'class' => '10',
        ]);

        $service->recordBulk([
            'date' => now()->toDateString(),
            'class' => '10',
            'entries' => [
                ['student_id' => $newStudent->id, 'status' => Attendance::STATUS_LATE],
            ],
        ]);

        $freshStats = $service->getTodayStats('10');

        $this->assertSame(3, $freshStats['total']);
        $this->assertSame(1, $freshStats['present']);
        $this->assertSame(1, $freshStats['absent']);
        $this->assertSame(1, $freshStats['late']);
        $this->assertSame(33.3, $freshStats['attendance_percentage']);
    }

    public function test_monthly_report_groups_records_and_calculates_percentages(): void
    {
        Carbon::setTestNow('2025-05-20');

        $student = Student::factory()->create([
            'class' => '12',
            'section' => 'B',
        ]);

        Attendance::factory()->for($student)->create([
            'date' => '2025-05-01',
            'status' => Attendance::STATUS_PRESENT,
        ]);
        Attendance::factory()->for($student)->create([
            'date' => '2025-05-02',
            'status' => Attendance::STATUS_LATE,
        ]);
        Attendance::factory()->for($student)->create([
            'date' => '2025-05-03',
            'status' => Attendance::STATUS_ABSENT,
        ]);

        $service = app(AttendanceService::class);

        $report = $service->getMonthlyReport('2025-05', '12', 'B');

        $this->assertCount(1, $report);
        $entry = $report->first();

        $this->assertSame(1, $entry->get('late'));
        $this->assertSame(1, $entry->get('absent'));
        $this->assertSame(1, $entry->get('present'));
        $this->assertSame(3, $entry->get('total_days'));
        $this->assertSame(33.3, $entry->get('attendance_percentage'));
        $this->assertSame($student->id, $entry->get('student')->id);
    }
}
