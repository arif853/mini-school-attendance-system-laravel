<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            '10' => ['A', 'B'],
            '11' => ['A', 'B'],
            '12' => ['A'],
        ];

        foreach ($classes as $class => $sections) {
            foreach ($sections as $section) {
                Student::factory()
                    ->count(10)
                    ->create([
                        'class' => $class,
                        'section' => $section,
                    ]);
            }
        }
    }
}
