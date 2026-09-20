<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ProjectAssignment;
use App\Models\ProjectSubmission;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ProjectGalleryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'sesekalicbt_db',
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test directories in storage
        if (File::isDirectory(storage_path('app/projects'))) {
            File::deleteDirectory(storage_path('app/projects'));
        }
        if (File::isDirectory(storage_path('app/tmp'))) {
            File::deleteDirectory(storage_path('app/tmp'));
        }
        parent::tearDown();
    }

    private function createZip(array $files): string
    {
        $tmpZipPath = tempnam(sys_get_temp_dir(), 'test_zip_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $tmpZipPath;
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'status' => 'Aktif',
        ], $attributes));
    }

    public function test_admin_can_create_project_assignment_with_auto_slug(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);

        $subject = Subject::firstOrCreate(['name' => 'Pemrograman Web Testing']);
        $class = ClassRoom::firstOrCreate(['name' => 'XII RPL 1 Testing'], ['grade' => 'XII', 'academic_year' => '2023/2024']);

        $title = 'Projek Web Portofolio Siswa ' . uniqid();
        $expectedSlug = \Illuminate\Support\Str::slug($title);

        $response = $this->actingAs($admin)->post(route('admin.project-assignments.store'), [
            'title'             => $title,
            'description'       => 'Buat portofolio pribadi responsif',
            'subject_id'        => $subject->id,
            'max_file_size_mb'  => 15,
            'max_slots'         => 2,
            'class_restriction' => [$class->id],
            'is_active'         => '1',
        ]);

        $response->assertRedirect(route('admin.project-assignments.index'));

        $this->assertDatabaseHas('project_assignments', [
            'title'            => $title,
            'slug'             => $expectedSlug,
            'max_file_size_mb' => 15,
            'max_slots'        => 2,
            'is_active'        => 1,
            'created_by'       => $admin->id,
        ]);
    }

    public function test_admin_can_toggle_assignment_active_status(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);
        $assignment = ProjectAssignment::create([
            'title'            => 'Ujian Desain Web',
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.project-assignments.toggle', $assignment));
        $this->assertFalse($assignment->fresh()->is_active);

        $response2 = $this->actingAs($admin)->post(route('admin.project-assignments.toggle', $assignment));
        $this->assertTrue($assignment->fresh()->is_active);
    }

    public function test_student_upload_rejected_if_no_index_html(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $class = ClassRoom::firstOrCreate(['name' => 'XII RPL 2 Testing'], ['grade' => 'XII', 'academic_year' => '2023/2024']);
        $student = $this->createUser([
            'role'     => 'student',
            'class_id' => $class->id,
        ]);

        $assignment = ProjectAssignment::create([
            'title'             => 'Tugas HTML CSS',
            'max_file_size_mb'  => 10,
            'max_slots'         => 1,
            'class_restriction' => [$class->id],
            'is_active'         => true,
            'created_by'        => $teacher->id,
        ]);

        // Create zip without index.html
        $zipPath = $this->createZip([
            'style.css' => 'body { color: red; }',
            'readme.txt' => 'Ini file teks tanpa index.html',
        ]);

        $uploadedFile = new UploadedFile($zipPath, 'invalid.zip', 'application/zip', null, true);

        $response = $this->actingAs($student)->post(route('student.projects.upload', $assignment), [
            'title'        => 'Tugas Pertama',
            'slot_number'  => 1,
            'project_file' => $uploadedFile,
        ]);

        $response->assertSessionHasErrors('project_file');
        @unlink($zipPath);
    }

    public function test_student_upload_flow_with_valid_zip_preview_and_confirm(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $class = ClassRoom::firstOrCreate(['name' => 'XII RPL 1 Testing'], ['grade' => 'XII', 'academic_year' => '2023/2024']);
        $student = $this->createUser([
            'name'     => 'Budi Prakoso',
            'role'     => 'student',
            'class_id' => $class->id,
        ]);

        $assignment = ProjectAssignment::create([
            'title'             => 'Karya Website Interaktif',
            'max_file_size_mb'  => 10,
            'max_slots'         => 1,
            'class_restriction' => null,
            'is_active'         => true,
            'created_by'        => $teacher->id,
        ]);

        // Create zip with index.html, style.css, script.js, image.png
        $zipPath = $this->createZip([
            'index.html'    => '<!DOCTYPE html><html><head><link rel="stylesheet" href="css/style.css"></head><body><h1>Halo Dunia</h1><script src="js/app.js"></script></body></html>',
            'css/style.css' => 'body { background: #fff; }',
            'js/app.js'     => 'console.log("hello");',
            'img/logo.png'  => 'fake-image-bytes',
        ]);

        $uploadedFile = new UploadedFile($zipPath, 'karya_budi.zip', 'application/zip', null, true);

        // Step 1: Upload to temporary extraction & preview
        $response = $this->actingAs($student)->post(route('student.projects.upload', $assignment), [
            'title'        => 'Website Profil Budi',
            'slot_number'  => 1,
            'project_file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('student.projects.preview', $assignment));
        $this->assertTrue(session()->has('upload_preview'));

        $previewData = session('upload_preview');
        $this->assertEquals('Website Profil Budi', $previewData['title']);
        $this->assertTrue($previewData['meta']['has_css']);
        $this->assertTrue($previewData['meta']['has_js']);
        $this->assertTrue($previewData['meta']['has_images']);

        // Step 2: Confirm upload to finalize
        $confirmResponse = $this->actingAs($student)->post(route('student.projects.confirm', $assignment));
        $confirmResponse->assertRedirect(route('student.projects.show', $assignment));

        // Step 3: Verify record in database
        $this->assertDatabaseHas('project_submissions', [
            'assignment_id'     => $assignment->id,
            'student_id'        => $student->id,
            'slot_number'       => 1,
            'title'             => 'Website Profil Budi',
            'original_filename' => 'karya_budi.zip',
            'has_css'           => 1,
            'has_js'            => 1,
            'has_images'        => 1,
        ]);

        // Step 4: Verify files exist in storage/app/projects/{assignment_id}/{student_id}/1/
        $storedIndex = storage_path("app/projects/{$assignment->id}/{$student->id}/1/index.html");
        $this->assertFileExists($storedIndex);
        $this->assertStringContainsString('<h1>Halo Dunia</h1>', file_get_contents($storedIndex));

        @unlink($zipPath);
    }

    public function test_public_gallery_view_and_secure_file_serving(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $class = ClassRoom::firstOrCreate(['name' => 'XII RPL 1 Testing'], ['grade' => 'XII', 'academic_year' => '2023/2024']);
        $student = $this->createUser([
            'name'     => 'Siti Rahma',
            'role'     => 'student',
            'class_id' => $class->id,
        ]);

        $assignment = ProjectAssignment::create([
            'title'            => 'Showcase Web 2026',
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $teacher->id,
        ]);

        // Store a submission directly
        $storageDir = storage_path("app/projects/{$assignment->id}/{$student->id}/1");
        File::makeDirectory($storageDir, 0755, true, true);
        file_put_contents("{$storageDir}/index.html", '<!DOCTYPE html><html><body><h1>Project Siti</h1></body></html>');
        file_put_contents("{$storageDir}/style.css", 'h1 { color: blue; }');

        $submission = ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student->id,
            'slot_number'       => 1,
            'title'             => 'Portfolio Animasi Siti',
            'original_filename' => 'siti_port.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student->id}/1/",
            'file_size_bytes'   => 1024,
            'has_css'           => true,
            'has_js'            => false,
            'has_images'        => false,
            'has_audio'         => false,
            'has_video'         => false,
            'uploaded_at'       => now(),
        ]);

        // 1. Check public gallery list
        $galleryIndex = $this->get(route('gallery.index'));
        $galleryIndex->assertStatus(200);
        $galleryIndex->assertSee('Showcase Web 2026');

        // 2. Check assignment gallery page
        $galleryShow = $this->get(route('gallery.show', $assignment));
        $galleryShow->assertStatus(200);
        $galleryShow->assertSee('Portfolio Animasi Siti');
        $galleryShow->assertSee('Siti Rahma');

        // 3. Check secure file serving
        $serveResponse = $this->get(route('gallery.serve', [
            'assignment' => $assignment->slug,
            'studentId'  => $student->id,
            'slot'       => 1,
            'filePath'   => 'index.html',
        ]));

        $serveResponse->assertStatus(200);
        $serveResponse->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->assertTrue($serveResponse->headers->has('Content-Security-Policy'));
        $this->assertStringContainsString('<h1>Project Siti</h1>', file_get_contents($serveResponse->baseResponse->getFile()->getPathname()));

        // 4. Check CSS serving with correct content-type
        $cssResponse = $this->get(route('gallery.serve', [
            'assignment' => $assignment->slug,
            'studentId'  => $student->id,
            'slot'       => 1,
            'filePath'   => 'style.css',
        ]));
        $cssResponse->assertStatus(200);
        $this->assertStringContainsString('text/css', $cssResponse->headers->get('Content-Type'));

        // 5. Test path traversal prevention
        $traversalResponse = $this->get("/gallery/{$assignment->slug}/{$student->id}/1/../../../../etc/passwd");
        $traversalResponse->assertStatus(404);
    }

    public function test_clean_tmp_uploads_command_removes_expired_directories(): void
    {
        $tmpBase = storage_path('app/tmp');
        $oldDir = "{$tmpBase}/old_test_folder";
        $newDir = "{$tmpBase}/new_test_folder";

        File::makeDirectory($oldDir, 0755, true, true);
        File::makeDirectory($newDir, 0755, true, true);

        // Make old directory modified 3 hours ago
        touch($oldDir, time() - 3 * 3600);

        $this->artisan('gallery:clean-tmp')
            ->expectsOutputToContain('Cleaned 1 expired temporary upload directory(ies).')
            ->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($oldDir);
        $this->assertDirectoryExists($newDir);
    }

    public function test_student_upload_with_single_subfolder_unpacks_correctly(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $class = ClassRoom::firstOrCreate(['name' => 'XII RPL 1 Testing'], ['grade' => 'XII', 'academic_year' => '2023/2024']);
        $student = $this->createUser([
            'role'     => 'student',
            'class_id' => $class->id,
        ]);

        $assignment = ProjectAssignment::create([
            'title'            => 'Assignment Subfolder ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $teacher->id,
        ]);

        // Student zipped a whole parent folder "my-portfolio/"
        $zipPath = $this->createZip([
            'my-portfolio/index.html'    => '<!DOCTYPE html><html><body><h1>Inside Subfolder</h1></body></html>',
            'my-portfolio/css/style.css' => 'h1 { font-size: 24px; }',
        ]);

        $uploadedFile = new UploadedFile($zipPath, 'my_portfolio.zip', 'application/zip', null, true);

        $response = $this->actingAs($student)->post(route('student.projects.upload', $assignment), [
            'title'        => 'Project Dalam Subfolder',
            'slot_number'  => 1,
            'project_file' => $uploadedFile,
        ]);

        $response->assertRedirect(route('student.projects.preview', $assignment));
        $this->assertTrue(session()->has('upload_preview'));

        $previewData = session('upload_preview');
        $this->assertTrue($previewData['meta']['has_css']);
        // index.html should be shifted to root of tmp_path
        $this->assertFileExists($previewData['tmp_path'] . '/index.html');

        @unlink($zipPath);
    }

    public function test_student_cannot_access_assignment_restricted_to_other_classes(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classA = ClassRoom::firstOrCreate(['name' => 'XII RPL A ' . uniqid()], ['grade' => 'XII', 'academic_year' => '2023/2024']);
        $classB = ClassRoom::firstOrCreate(['name' => 'XII RPL B ' . uniqid()], ['grade' => 'XII', 'academic_year' => '2023/2024']);

        $studentB = $this->createUser([
            'role'     => 'student',
            'class_id' => $classB->id,
        ]);

        // Assignment only for class A
        $assignment = ProjectAssignment::create([
            'title'             => 'Khusus Kelas A ' . uniqid(),
            'max_file_size_mb'  => 10,
            'max_slots'         => 1,
            'class_restriction' => [$classA->id],
            'is_active'         => true,
            'created_by'        => $teacher->id,
        ]);

        $response = $this->actingAs($studentB)->get(route('student.projects.show', $assignment));
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_student_submission(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);
        $student = $this->createUser(['name' => 'Budi Testing', 'role' => 'student']);

        $assignment = ProjectAssignment::create([
            'title'            => 'Assignment Delete Test ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $admin->id,
        ]);

        $storageDir = storage_path("app/projects/{$assignment->id}/{$student->id}/1");
        File::makeDirectory($storageDir, 0755, true, true);
        file_put_contents("{$storageDir}/index.html", '<h1>Salah Upload</h1>');

        $submission = ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student->id,
            'slot_number'       => 1,
            'title'             => 'Salah Upload File',
            'original_filename' => 'salah.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student->id}/1/",
            'file_size_bytes'   => 500,
            'uploaded_at'       => now(),
        ]);

        $this->assertFileExists("{$storageDir}/index.html");

        $response = $this->actingAs($admin)->delete(route('admin.project-assignments.submissions.destroy', [$assignment, $submission]));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Database record must be deleted
        $this->assertDatabaseMissing('project_submissions', [
            'id' => $submission->id,
        ]);

        // Physical folder must be deleted
        $this->assertDirectoryDoesNotExist($storageDir);
    }

    public function test_student_can_delete_own_submission(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);

        $assignment = ProjectAssignment::create([
            'title'            => 'Tugas Siswa ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $teacher->id,
        ]);

        $storageDir = storage_path("app/projects/{$assignment->id}/{$student->id}/1");
        File::makeDirectory($storageDir, 0755, true, true);
        file_put_contents("{$storageDir}/index.html", '<h1>File Lama</h1>');

        $submission = ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student->id,
            'slot_number'       => 1,
            'title'             => 'File Lama Mau Dihapus',
            'original_filename' => 'lama.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student->id}/1/",
            'file_size_bytes'   => 500,
            'uploaded_at'       => now(),
        ]);

        $response = $this->actingAs($student)->delete(route('student.projects.destroy', [$assignment, $submission]));
        $response->assertRedirect(route('student.projects.show', $assignment));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('project_submissions', [
            'id' => $submission->id,
        ]);
        $this->assertDirectoryDoesNotExist($storageDir);
    }

    public function test_student_cannot_delete_other_student_submission(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $studentA = $this->createUser(['role' => 'student']);
        $studentB = $this->createUser(['role' => 'student']);

        $assignment = ProjectAssignment::create([
            'title'            => 'Tugas Bersama ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $teacher->id,
        ]);

        $submissionA = ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $studentA->id,
            'slot_number'       => 1,
            'title'             => 'Tugas Milik A',
            'original_filename' => 'tugas_a.zip',
            'storage_path'      => "projects/{$assignment->id}/{$studentA->id}/1/",
            'file_size_bytes'   => 500,
            'uploaded_at'       => now(),
        ]);

        // Student B tries to delete student A's submission
        $response = $this->actingAs($studentB)->delete(route('student.projects.destroy', [$assignment, $submissionA]));
        $response->assertStatus(403);

        $this->assertDatabaseHas('project_submissions', [
            'id' => $submissionA->id,
        ]);
    }

    public function test_admin_can_download_archive_zip_of_all_submissions(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);
        $student1 = $this->createUser(['name' => 'Budi Santoso', 'nis' => '12345', 'role' => 'student']);
        $student2 = $this->createUser(['name' => 'Dewi Lestari', 'nis' => '12346', 'role' => 'student']);

        $assignment = ProjectAssignment::create([
            'title'            => 'Assignment Archive Test ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $admin->id,
        ]);

        // Student 1 project
        $dir1 = storage_path("app/projects/{$assignment->id}/{$student1->id}/1");
        File::makeDirectory($dir1, 0755, true, true);
        file_put_contents("{$dir1}/index.html", '<h1>Project Budi</h1>');
        file_put_contents("{$dir1}/script.js", 'console.log("budi");');

        ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student1->id,
            'slot_number'       => 1,
            'title'             => 'Portfolio Budi',
            'original_filename' => 'budi.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student1->id}/1/",
            'file_size_bytes'   => 500,
            'uploaded_at'       => now(),
        ]);

        // Student 2 project
        $dir2 = storage_path("app/projects/{$assignment->id}/{$student2->id}/1");
        File::makeDirectory($dir2, 0755, true, true);
        file_put_contents("{$dir2}/index.html", '<h1>Project Dewi</h1>');

        ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student2->id,
            'slot_number'       => 1,
            'title'             => 'Portfolio Dewi',
            'original_filename' => 'dewi.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student2->id}/1/",
            'file_size_bytes'   => 400,
            'uploaded_at'       => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.project-assignments.archive', $assignment));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/zip', $response->headers->get('Content-Type') ?? '');
        $this->assertStringContainsString('.zip', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_admin_cannot_archive_when_no_submissions(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);
        $assignment = ProjectAssignment::create([
            'title'            => 'Assignment Kosong ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.project-assignments.archive', $assignment));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_clear_all_submissions(): void
    {
        $admin = $this->createUser(['role' => 'superadmin']);
        $student = $this->createUser(['name' => 'Siswa Test Clear', 'role' => 'student']);

        $assignment = ProjectAssignment::create([
            'title'            => 'Assignment Clear All ' . uniqid(),
            'max_file_size_mb' => 10,
            'max_slots'        => 1,
            'is_active'        => true,
            'created_by'       => $admin->id,
        ]);

        $dir = storage_path("app/projects/{$assignment->id}/{$student->id}/1");
        File::makeDirectory($dir, 0755, true, true);
        file_put_contents("{$dir}/index.html", '<h1>Project Mau Dibersihkan</h1>');

        $submission = ProjectSubmission::create([
            'assignment_id'     => $assignment->id,
            'student_id'        => $student->id,
            'slot_number'       => 1,
            'title'             => 'Karya Siswa Clear',
            'original_filename' => 'clear.zip',
            'storage_path'      => "projects/{$assignment->id}/{$student->id}/1/",
            'file_size_bytes'   => 600,
            'uploaded_at'       => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.project-assignments.submissions.clear-all', $assignment));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('project_submissions', [
            'assignment_id' => $assignment->id,
        ]);
        $this->assertDirectoryDoesNotExist(storage_path("app/projects/{$assignment->id}"));
    }
}
