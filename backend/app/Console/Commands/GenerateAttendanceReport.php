<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report {month : Target month in YYYY-MM format} {class? : Class code to filter} {--section=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a cached monthly attendance report and store it as JSON.';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $service): int
    {
        $month = $this->argument('month');
        $class = $this->argument('class');
        $section = $this->option('section');

        $report = $service->getMonthlyReport($month, $class, $section)
            ->map(fn ($row) => $row->toArray())
            ->all();

        $payload = json_encode([
            'month' => $month,
            'class' => $class,
            'section' => $section,
            'generated_at' => now()->toDateTimeString(),
            'rows' => $report,
        ], JSON_PRETTY_PRINT);

        Storage::disk('local')->makeDirectory('reports');
        $filename = sprintf('reports/attendance_%s_%s.json', $class ?? 'all', $month);
        Storage::disk('local')->put($filename, $payload);

        $this->info("Report saved to storage/app/{$filename}");

        return self::SUCCESS;
    }
}
