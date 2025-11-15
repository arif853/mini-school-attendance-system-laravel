<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_endpoint_creates_records_and_returns_resource(): void
    {
        $this->authenticate();
        Carbon::setTestNow('2025-05-05');

        $students = Student::factory()->count(2)->create([
            'class' => '10',
            'section' => 'C',
        ]);

        $payload = [
            'date' => now()->toDateString(),
            'class' => '10',
            'section' => 'C',
            'recorded_by' => 'Homeroom',
            'entries' => [
                ['student_id' => $students[0]->id, 'status' => Attendance::STATUS_PRESENT, 'note' => null],
                ['student_id' => $students[1]->id, 'status' => Attendance::STATUS_LATE, 'note' => 'Bus delay'],
            ],
        ];

        $response = $this->postJson('/api/attendance/bulk', $payload);

        $response->assertCreated();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.student.id', $students[0]->id);

        $this->assertTrue(
            Attendance::where('student_id', $students[1]->id)
                ->whereDate('date', now()->toDateString())
                ->where('status', Attendance::STATUS_LATE)
                ->where('recorded_by', 'Homeroom')
                ->exists()
        );
    }

    public function test_today_stats_endpoint_returns_expected_counts(): void
    {
        $this->authenticate();
        Carbon::setTestNow('2025-05-06');

        $students = Student::factory()->count(2)->create([
            'class' => '9',
        ]);

        Attendance::factory()->for($students[0])->create([
            'date' => now()->toDateString(),
            'status' => Attendance::STATUS_PRESENT,
        ]);

        Attendance::factory()->for($students[1])->create([
            'date' => now()->toDateString(),
            'status' => Attendance::STATUS_ABSENT,
        ]);

        $response = $this->getJson('/api/attendance/stats/today?class=9');

        $response->assertOk();
        $response->assertJson([
            'class' => '9',
            'total' => 2,
            'present' => 1,
            'absent' => 1,
            'late' => 0,
        ]);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }
}
