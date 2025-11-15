<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkAttendanceRequest;
use App\Http\Resources\AttendanceReportResource;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::query()
            ->with('student')
            ->when($request->get('date'), fn ($q, $date) => $q->whereDate('date', $date))
            ->when($request->get('class'), fn ($q, $class) => $q->whereHas('student', fn ($student) => $student->where('class', $class)))
            ->when($request->get('section'), fn ($q, $section) => $q->whereHas('student', fn ($student) => $student->where('section', $section)))
            ->latest('date')
            ->paginate($request->integer('per_page', 25));

        return AttendanceResource::collection($attendances);
    }

    public function bulkStore(BulkAttendanceRequest $request, AttendanceService $service): JsonResponse
    {
        $records = $service->recordBulk($request->validated());

        return AttendanceResource::collection($records)
            ->response()
            ->setStatusCode(201);
    }

    public function monthlyReport(Request $request, AttendanceService $service)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $class = $request->get('class');
        $section = $request->get('section');

        $report = $service->getMonthlyReport($month, $class, $section);

        return AttendanceReportResource::collection($report);
    }

    public function todayStats(Request $request, AttendanceService $service): JsonResponse
    {
        $stats = $service->getTodayStats($request->get('class'));

        return response()->json($stats);
    }
}
