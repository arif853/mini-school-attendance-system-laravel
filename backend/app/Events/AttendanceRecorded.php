<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AttendanceRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $date,
        public ?string $class,
        public ?string $section,
        public Collection $attendances,
    ) {
    }
}
