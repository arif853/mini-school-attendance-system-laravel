<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'student' => StudentResource::make($this->resource['student'] ?? $this->resource->student ?? null),
            'present' => $this->resource['present'] ?? $this->present ?? 0,
            'absent' => $this->resource['absent'] ?? $this->absent ?? 0,
            'late' => $this->resource['late'] ?? $this->late ?? 0,
            'total_days' => $this->resource['total_days'] ?? $this->total_days ?? 0,
            'attendance_percentage' => $this->resource['attendance_percentage'] ?? $this->attendance_percentage ?? 0,
        ];
    }
}
