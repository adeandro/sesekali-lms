<?php

namespace Tests\Feature;

use App\Models\ElectionPeriod;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class StudentPhotoAndProtectionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Helper untuk membuat ZIP in-memory/file temporary
     */
    private function createZip(array $files): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'test_zip_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        return $zipPath;
    }

    /**
     * Helper untuk membuat binary gambar valid kecil via GD
     */
    private function createTestImage(int $w = 50, int $h = 50, string $format = 'jpg'): string
    {
        $im = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $w, $h, $white);

        ob_start();
        if ($format === 'png') {
            imagepng($im);
        } elseif ($format === 'webp') {
            imagewebp($im);
        } else {
            imagejpeg($im);
        }
        $data = ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    public function test_upload_photos_name_matching_case_insensitive()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis = '991' . rand(100000, 999999);
        $student = User::factory()->create([
            'name' => "Zulfa Unique {$uid}",
            'nis' => $nis,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);

        $imgData = $this->createTestImage();
        $zipPath = $this->createZip([
            "ZULFA UNIQUE {$uid}.JPG" => $imgData,
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $response->assertSessionHas('success');
        $student->refresh();

        $this->assertEquals("{$nis}.jpg", $student->photo);
        $this->assertTrue(Storage::disk('public')->exists("profiles/{$nis}.jpg"));
    }

    public function test_upload_photos_with_double_spaces_and_nis_filename()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis1 = '992' . rand(100000, 999999);
        $nis2 = '993' . rand(100000, 999999);
        $student1 = User::factory()->create([
            'name' => "Budi Santoso {$uid}",
            'nis' => $nis1,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);
        $student2 = User::factory()->create([
            'name' => "Citra Lestari {$uid}",
            'nis' => $nis2,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);

        $imgData = $this->createTestImage();
        $zipPath = $this->createZip([
            "budi   santoso   {$uid}.jpg" => $imgData,
            "{$nis2}.png" => $this->createTestImage(50, 50, 'png'),
        ]);

        $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $student1->refresh();
        $student2->refresh();

        $this->assertEquals("{$nis1}.jpg", $student1->photo);
        $this->assertEquals("{$nis2}.png", $student2->photo);
        $this->assertTrue(Storage::disk('public')->exists("profiles/{$nis1}.jpg"));
        $this->assertTrue(Storage::disk('public')->exists("profiles/{$nis2}.png"));
    }

    public function test_upload_photos_with_apostrophe_variants()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis = '994' . rand(100000, 999999);
        $student = User::factory()->create([
            'name' => "INTAN QORI'ATUL {$uid}",
            'nis' => $nis,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);

        $imgData = $this->createTestImage();
        // File uses curved apostrophe ’
        $zipPath = $this->createZip([
            "INTAN QORI’ATUL {$uid}.jpg" => $imgData,
        ]);

        $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $student->refresh();
        $this->assertEquals("{$nis}.jpg", $student->photo);
        $this->assertTrue(Storage::disk('public')->exists("profiles/{$nis}.jpg"));
    }

    public function test_upload_photos_ambiguous_name_not_saved()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis1 = '995' . rand(100000, 999999);
        $nis2 = '996' . rand(100000, 999999);
        User::factory()->create([
            'name' => "Kembar Rizki {$uid}",
            'nis' => $nis1,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);
        User::factory()->create([
            'name' => "Kembar Rizki {$uid}",
            'nis' => $nis2,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);

        $imgData = $this->createTestImage();
        $zipPath = $this->createZip([
            "kembar rizki {$uid}.jpg" => $imgData,
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $response->assertSessionHas('photo_report');
        $report = session('photo_report');

        $this->assertCount(1, $report['ambigu']);
        $this->assertEquals(0, $report['berhasil']);
        $this->assertFalse(Storage::disk('public')->exists("profiles/{$nis1}.jpg"));
        $this->assertFalse(Storage::disk('public')->exists("profiles/{$nis2}.jpg"));
    }

    public function test_upload_photos_not_found_not_saved()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);

        $imgData = $this->createTestImage();
        $zipPath = $this->createZip([
            'orang asing tidak terdaftar ' . uniqid() . '.jpg' => $imgData,
        ]);

        $response = $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $response->assertSessionHas('photo_report');
        $report = session('photo_report');

        $this->assertCount(1, $report['tidak_ditemukan']);
        $this->assertEquals(0, $report['berhasil']);
    }

    public function test_upload_photos_skips_macosx_and_dot_underscore()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis = '997' . rand(100000, 999999);
        $student = User::factory()->create([
            'name' => "Dewi Unique {$uid}",
            'nis' => $nis,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => null,
        ]);

        $imgData = $this->createTestImage();
        $zipPath = $this->createZip([
            "__MACOSX/._dewi unique {$uid}.jpg" => 'junk data',
            "._dewi unique {$uid}.jpg" => 'junk data',
            "dewi unique {$uid}.jpg" => $imgData,
        ]);

        $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $student->refresh();
        $this->assertEquals("{$nis}.jpg", $student->photo);
        $this->assertTrue(Storage::disk('public')->exists("profiles/{$nis}.jpg"));
        $this->assertFalse(Storage::disk('public')->exists("profiles/._dewi unique {$uid}.jpg"));
    }

    public function test_upload_photos_reupload_deletes_old_file_but_protects_custom_and_teacher()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $uid = uniqid();
        $nis = '998' . rand(100000, 999999);
        
        // Put protected files in storage
        Storage::disk('public')->put('profiles/1773027095_1.png', 'student self upload');
        Storage::disk('public')->put('profiles/teacher_200_abc.jpg', 'teacher photo');
        Storage::disk('public')->put('profiles/old_photo_unique.jpg', 'old regular photo');

        $student = User::factory()->create([
            'name' => "Eko Unique {$uid}",
            'nis' => $nis,
            'role' => 'student',
            'status' => 'Aktif',
            'photo' => 'old_photo_unique.jpg',
        ]);

        $imgData = $this->createTestImage(50, 50, 'png');
        $zipPath = $this->createZip([
            "eko unique {$uid}.png" => $imgData,
        ]);

        $this->actingAs($superadmin)->post(route('admin.students.upload-photos.post'), [
            'zip_file' => new UploadedFile($zipPath, 'test.zip', 'application/zip', null, true),
        ]);

        @unlink($zipPath);

        $student->refresh();
        $this->assertEquals("{$nis}.png", $student->photo);
        // Old photo deleted
        $this->assertFalse(Storage::disk('public')->exists('profiles/old_photo_unique.jpg'));
        // Protected files intact
        $this->assertTrue(Storage::disk('public')->exists('profiles/1773027095_1.png'));
        $this->assertTrue(Storage::disk('public')->exists('profiles/teacher_200_abc.jpg'));
    }

    public function test_confirm_delete_all_students_page_loads()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        User::factory()->count(3)->create(['role' => 'student', 'status' => 'Aktif']);

        $response = $this->actingAs($superadmin)->get(route('admin.students.deleteAll.confirm'));
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Hapus Semua Siswa');
        $response->assertSee('HAPUS SEMUA SISWA');
        $response->assertSee('Saya mengerti semua data di atas ikut terhapus permanen.');
    }

    public function test_delete_all_students_requires_exact_text_and_checkbox()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        
        // Close any open election and end active sessions temporarily for this test
        ElectionPeriod::where('status', 'open')->update(['status' => 'closed']);
        ExamSession::where('status', 'active')->update(['status' => 'inactive']);
        ExamAttempt::where('status', 'in_progress')->update(['status' => 'submitted']);
        DB::table('hall_of_fame')->delete();

        $initialCount = User::where('role', 'student')->count();
        User::factory()->count(2)->create(['role' => 'student', 'status' => 'Aktif']);
        $this->assertEquals($initialCount + 2, User::where('role', 'student')->count());

        // Wrong text
        $response = $this->actingAs($superadmin)->delete(route('admin.students.deleteAll'), [
            'confirm_text' => 'hapus',
            'understand' => '1',
        ]);
        $response->assertSessionHas('error');
        $this->assertEquals($initialCount + 2, User::where('role', 'student')->count());

        // Missing checkbox
        $response2 = $this->actingAs($superadmin)->delete(route('admin.students.deleteAll'), [
            'confirm_text' => 'HAPUS SEMUA SISWA',
        ]);
        $response2->assertSessionHas('error');
        $this->assertEquals($initialCount + 2, User::where('role', 'student')->count());

        // Correct
        $response3 = $this->actingAs($superadmin)->delete(route('admin.students.deleteAll'), [
            'confirm_text' => 'HAPUS SEMUA SISWA',
            'understand' => '1',
        ]);
        $response3->assertSessionHas('success');
        $this->assertEquals(0, User::where('role', 'student')->count());
    }

    public function test_delete_all_students_rejected_if_pilketos_open()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Aktif']);
        $initialCount = User::where('role', 'student')->count();
        User::factory()->create(['role' => 'student', 'status' => 'Aktif']);

        // Ensure active open election exists
        ElectionPeriod::firstOrCreate(
            ['status' => 'open'],
            ['title' => 'Pilketos 2026', 'event_date' => now()->toDateString()]
        );

        $response = $this->actingAs($superadmin)->delete(route('admin.students.deleteAll'), [
            'confirm_text' => 'HAPUS SEMUA SISWA',
            'understand' => '1',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Pemilihan sedang berlangsung', session('error'));
        $this->assertEquals($initialCount + 1, User::where('role', 'student')->count());
    }
}
