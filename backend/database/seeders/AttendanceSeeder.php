<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            return;
        }

        $dates = collect();
        $cursor = Carbon::today();

        while ($dates->count() < 15) {
            if (! $cursor->isWeekend()) {
                $dates->push($cursor->toDateString());
            }

            $cursor->subDay();
        }

        foreach ($dates as $date) {
            foreach ($students as $student) {
                $roll = random_int(1, 100);
                $status = match (true) {
                    $roll <= 5 => Attendance::STATUS_LATE,
                    $roll <= 20 => Attendance::STATUS_ABSENT,
                    default => Attendance::STATUS_PRESENT,
                };

                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $date,
                    ],
                    [
                        'status' => $status,
                        'note' => null,
                        'recorded_by' => 'Seeder Bot',
                    ]
                );
            }
        }
    }
}
