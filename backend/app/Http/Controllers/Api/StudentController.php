<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $students = Student::query()
            ->classSection($request->get('class'), $request->get('section'))
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return StudentResource::collection($students);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $photo = $request->file('photo');

        unset($data['photo']);

        if ($photo) {
            $data['photo_path'] = $this->storePhoto($photo);
        }

        $student = Student::create($data);

        return StudentResource::make($student)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): StudentResource
    {
        return StudentResource::make($student);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): StudentResource
    {
        $data = $request->validated();
        $photo = $request->file('photo');

        unset($data['photo']);

        if ($photo) {
            $data['photo_path'] = $this->storePhoto($photo, $student->photo_path);
        }

        $student->update($data);

        return StudentResource::make($student);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): Response
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return response()->noContent();
    }

    private function storePhoto(UploadedFile $file, ?string $existingPath = null): string
    {
        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->store('students', 'public');
    }
}
