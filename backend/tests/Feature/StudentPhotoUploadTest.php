<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_upload_on_create_stores_file_and_returns_url(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $response = $this->post('/api/students', [
            'name' => 'Photo Student',
            'student_id' => 'PHOTO-1',
            'class' => '10',
            'section' => 'A',
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated();
        $photoPath = $response->json('data.photo_path');
        $photoUrl = $response->json('data.photo_url');

        $this->assertNotEmpty($photoPath);
        $this->assertNotEmpty($photoUrl);
        Storage::disk('public')->assertExists($photoPath);
        $this->assertDatabaseHas('students', [
            'student_id' => 'PHOTO-1',
            'photo_path' => $photoPath,
        ]);
    }

    public function test_updating_student_replaces_previous_photo(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $student = Student::factory()->create([
            'photo_path' => UploadedFile::fake()->image('old.jpg')->store('students', 'public'),
        ]);

        $response = $this->put("/api/students/{$student->id}", [
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk();
        $newPath = $response->json('data.photo_path');

        $this->assertNotSame($student->photo_path, $newPath);
        Storage::disk('public')->assertMissing($student->photo_path);
        Storage::disk('public')->assertExists($newPath);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'photo_path' => $newPath,
        ]);
    }
}
