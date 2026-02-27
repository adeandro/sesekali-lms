<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentImport;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Reset all student passwords
     */
    public function resetAllPasswords()
    {
        try {
            $students = User::where('role', 'student')->get();
            $count = 0;

            foreach ($students as $student) {
                $newPassword = StudentService::resetPassword($student);
                $count++;
            }

            return redirect()->route('admin.students.index')
                ->with('success', "All {$count} student passwords have been reset successfully. New passwords generated.");
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Error resetting passwords: ' . $e->getMessage());
        }
    }

    /**
     * Delete all students
     */
    public function deleteAllStudents()
    {
        try {
            $students = User::where('role', 'student')->get();
            $count = $students->count();

            foreach ($students as $student) {
                $student->delete();
            }

            return redirect()->route('admin.students.index')
                ->with('success', "All {$count} students have been permanently deleted.");
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Error deleting students: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }

        // Get grades for filter dropdown
        $classes = User::where('role', 'student')
            ->distinct()
            ->whereNotNull('grade')
            ->orderBy('grade')
            ->pluck('grade');

        // Pagination
        $students = $query->orderBy('nis')->paginate(15);

        return view('admin.students.index', compact('students', 'classes'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created student
     */
    public function store(StoreStudentRequest $request)
    {
        $result = StudentService::createStudent($request->validated());

        return redirect()->route('admin.students.show', $result['student'])
            ->with('success', 'Student created successfully')
            ->with('password', $result['password'])
            ->with('nis', $result['student']->nis);
    }

    /**
     * Display the specified student
     */
    public function show(User $student)
    {
        $this->authorize('view', $student);

        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(User $student)
    {
        $this->authorize('update', $student);

        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student
     */
    public function update(UpdateStudentRequest $request, User $student)
    {
        $this->authorize('update', $student);

        $data = $request->validated();

        // Only update fields that are not email-related
        // Email is auto-generated and shouldn't be updated
        unset($data['email']);

        $student->update($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully');
    }

    /**
     * Delete the specified student
     */
    public function destroy(User $student)
    {
        $this->authorize('delete', $student);

        $nis = $student->nis;
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "Student {$nis} deleted successfully");
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('admin.students.import');
    }

    /**
     * Import students from Excel
     */
    public function import(ImportStudentRequest $request)
    {
        $importer = new StudentImport();
        Excel::import($importer, $request->file('file'));

        return redirect()->route('admin.students.importResult')->with('import_data', [
            'success_count' => $importer->successCount,
            'skipped_count' => $importer->skippedCount,
            'failure_count' => $importer->failureCount,
            'errors' => $importer->errors,
            'students' => $importer->students,
            'skipped' => $importer->skipped,
        ]);
    }

    /**
     * Show import result
     */
    public function importResult()
    {
        $data = session('import_data');

        if (!$data) {
            return redirect()->route('admin.students.importForm');
        }

        return view('admin.students.import_result', $data);
    }

    /**
     * Export students to Excel
     */
    public function export()
    {
        return Excel::download(new StudentExport(), 'students-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Reset password for a student
     */
    public function resetPassword(User $student)
    {
        $this->authorize('update', $student);

        $newPassword = StudentService::resetPassword($student);

        return back()
            ->with('success', 'Password reset successfully')
            ->with('password', $newPassword)
            ->with('nis', $student->nis);
    }

    /**
     * Toggle student active status
     */
    public function toggleActive(User $student)
    {
        $this->authorize('update', $student);

        $student->update([
            'is_active' => !$student->is_active,
        ]);

        $status = $student->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Student {$status} successfully");
    }
}
