<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DispatchAttendanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $counts = $event->attendances->groupBy('status')->map->count();

        Log::info('Attendance recorded', [
            'date' => $event->date,
            'class' => $event->class,
            'section' => $event->section,
            'present' => $counts['present'] ?? 0,
            'absent' => $counts['absent'] ?? 0,
            'late' => $counts['late'] ?? 0,
        ]);
    }
}
