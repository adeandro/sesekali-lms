<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LetterController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────

    private function generateLetterNumber(
        ?string $letterTypeCode = null,
        string $formatType = 'simple'
    ): array {
        $year  = (int) date('Y');
        $month = (int) date('m');

        // Sequence global per tahun (semua jenis digabung)
        $lastInDb = Letter::where('year', '=', $year)
            ->max('sequence_number') ?? 0;

        // Nomor awal yang dikonfigurasi admin (setting per tahun)
        $sequenceStart = (int) Setting::get(
            'letter_sequence_start_' . $year, 0
        );

        $lastSequence = max($lastInDb, $sequenceStart);
        $sequence     = $lastSequence + 1;

        $romanMonths = [
            1=>'I',   2=>'II',  3=>'III', 4=>'IV',
            5=>'V',   6=>'VI',  7=>'VII', 8=>'VIII',
            9=>'IX', 10=>'X',  11=>'XI', 12=>'XII',
        ];

        $letterCode  = Setting::get('letter_code', 'SMK');
        $romanMonth  = $romanMonths[$month];
        $typeCode    = $letterTypeCode ?? 'L'; // fallback ke Surat Lain

        // Format: simple   → 003/SEd/002/I/2021
        // Format: with_institution → 003/SEd.SMK.A/002/I/2021
        if ($formatType === 'with_institution') {
            $numberStr = sprintf('%03d', $sequence)
                . '/' . $typeCode . '.' . $letterCode . '.A'
                . '/' . sprintf('%03d', $sequence)
                . '/' . $romanMonth
                . '/' . $year;
        } else {
            $numberStr = sprintf('%03d', $sequence)
                . '/' . $typeCode
                . '/' . sprintf('%03d', $sequence)
                . '/' . $romanMonth
                . '/' . $year;
        }

        return [
            'letter_number'   => $numberStr,
            'sequence_number' => $sequence,
            'year'            => $year,
        ];
    }

    private function replacePlaceholders(string $body, array $data): string
    {
        foreach ($data as $key => $value) {
            // Format baru: [nama_siswa]
            $body = str_replace('[' . $key . ']', $value ?? '', $body);

            // Format lama fallback: {{nama_siswa}} (backward compat)
            $body = str_replace('{{' . $key . '}}', $value ?? '', $body);

            // Format Blade-escaped fallback: PHP echo syntax
            $body = str_replace(
                '<' . '?php echo e(' . $key . '); ?' . '>', 
                $value ?? '', 
                $body
            );
        }
        return $body;
    }

    private function buildStudentData(User $student): array
    {
        $configs = Setting::pluck('value', 'key')->toArray();
        $principal = User::where('role', '=', 'principal')->first();

        $principalName = null;
        if ($principal) {
            $parts = [];
            if ($principal->title_ahead) 
                $parts[] = trim($principal->title_ahead);
            $parts[] = trim($principal->name);
            $principalName = implode(' ', $parts);
            if ($principal->title_behind) 
                $principalName .= ', ' . trim($principal->title_behind);
        }

        $principalNip = null;
        if ($principal) {
            if (!empty($principal->nip)) 
                $principalNip = $principal->nip;
            elseif (!empty($principal->niy)) 
                $principalNip = $principal->niy;
        }

        return [
            'nama_siswa'    => $student->name,
            'nis'           => $student->nis ?? '',
            'nisn'          => $student->nisn ?? '',
            'kelas'         => $student->classroom->name ?? '',
            'jenis_kelamin' => $student->gender ?? '',
            'tempat_lahir'  => $student->place_of_birth ?? '',
            'tanggal_lahir' => $student->date_of_birth 
                ? Carbon::parse($student->date_of_birth)
                    ->locale('id')
                    ->translatedFormat('d F Y') 
                : '',
            'nama_sekolah'  => $configs['school_name'] ?? '',
            'alamat_sekolah'=> $configs['school_address'] ?? '',
            'kode_lembaga'  => $configs['letter_code'] ?? '',
            'tahun_ajaran'  => $configs['academic_year'] ?? '',
            'nama_kepsek'   => $principalName ?? '',
            'nip_kepsek'    => $principalNip ?? '',
            'tanggal_surat' => Carbon::now()
                ->locale('id')
                ->translatedFormat('d F Y'),
        ];
    }

    private function buildTeacherData(User $teacher): array
    {
        $configs = Setting::pluck('value', 'key')->toArray();
        $principal = User::where('role', '=', 'principal')->first();

        $principalName = null;
        if ($principal) {
            $parts = [];
            if ($principal->title_ahead) 
                $parts[] = trim($principal->title_ahead);
            $parts[] = trim($principal->name);
            $principalName = implode(' ', $parts);
            if ($principal->title_behind) 
                $principalName .= ', ' . trim($principal->title_behind);
        }

        return [
            'nama_guru'     => $teacher->name,
            'nip_guru'      => $teacher->nip ?? $teacher->niy ?? '',
            'jabatan_guru'  => 'Guru',
            'nama_sekolah'  => $configs['school_name'] ?? '',
            'alamat_sekolah'=> $configs['school_address'] ?? '',
            'kode_lembaga'  => $configs['letter_code'] ?? '',
            'tahun_ajaran'  => $configs['academic_year'] ?? '',
            'nama_kepsek'   => $principalName ?? '',
            'nip_kepsek'    => $principal?->nip ?? $principal?->niy ?? '',
            'tanggal_surat' => Carbon::now()
                ->locale('id')
                ->translatedFormat('d F Y'),
        ];
    }

    // ── Halaman utama generator ───────────────────────────────────

    public function index()
    {
        $templates = LetterTemplate::active()
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.letters.generate.index', 
            compact('templates'));
    }

    // ── Form generate (pilih penerima) ────────────────────────────

    public function form(Request $request, LetterTemplate $template)
    {
        $academicYear = Setting::get('academic_year', '2024/2025');

        // Ambil data penerima berdasarkan kategori template
        $recipients = collect();
        if ($template->category === 'siswa') {
            $recipients = User::where('role', 'student')
                ->where('status', 'Aktif')
                ->with('classroom')
                ->orderBy('name')
                ->get()
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'name'  => $s->name,
                    'info'  => ($s->nis ?? '-') . ' • ' . 
                               ($s->classroom->name ?? 'Tanpa Kelas'),
                ]);
        } elseif ($template->category === 'guru') {
            $recipients = User::whereIn('role', ['teacher', 'principal'])
                ->orderBy('name')
                ->get()
                ->map(fn($t) => [
                    'id'   => $t->id,
                    'name' => $t->name,
                    'info' => $t->nip ?? $t->niy ?? '-',
                ]);
        }

        return view('admin.letters.generate.form', compact(
            'template', 'recipients', 'academicYear'
        ));
    }

    // ── Preview surat (1 penerima) ────────────────────────────────

    public function preview(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $recipient = User::with('classroom')->find($request->recipient_id);

        if (!$recipient) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        // Build data placeholder
        $data = $template->category === 'guru'
            ? $this->buildTeacherData($recipient)
            : $this->buildStudentData($recipient);

        // Generate nomor surat sementara untuk preview
        $romanMonths = [
            1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI',
            7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII'
        ];
        $data['nomor_surat'] = 'XXX/' . Setting::get('letter_code', 'SMK') 
            . '/' . $romanMonths[(int)date('m')] . '/' 
            . date('Y');

        $bodyRendered = $this->replacePlaceholders($template->body, $data);

        // Deteksi format baru [placeholder] dan format lama {{placeholder}}
        preg_match_all('/\[([^\]]+)\]|\{\{([^}]+)\}\}/', $bodyRendered, $matches);
        $unreplacedPlaceholders = array_unique(
            array_filter(array_merge($matches[1] ?? [], $matches[2] ?? []))
        );

        $formatType = $request->input('format_type', 'simple');

        // Jika masih ada placeholder yang belum ter-replace,
        // kembalikan ke form dengan daftar field yang perlu diisi
        if (!empty($unreplacedPlaceholders) && !$request->has('custom_fields')) {
            $configs = Setting::pluck('value', 'key')->toArray();
            $principal = User::where('role', '=', 'principal')->first();
            return view('admin.letters.generate.custom-fields', compact(
                'template', 'recipient', 'unreplacedPlaceholders', 'configs', 'principal', 'formatType'
            ));
        }

        // ── Data Rendering ──
        $bodyRendered = $this->replacePlaceholders($template->body, $data);

        // Jika ada custom_fields, bersihkan dan format sebelum di-replace
        if ($request->has('custom_fields')) {
            $customData = [];
            foreach ($request->custom_fields as $key => $value) {
                $lowerKey = strtolower($key);
                // Deteksi field tanggal dan konversi format ke Indonesia
                if ((str_contains($lowerKey, 'tanggal') || str_contains($lowerKey, 'date')) && 
                    preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $value = \Carbon\Carbon::parse($value)
                        ->locale('id')
                        ->translatedFormat('d F Y');
                }
                // Konversi waktu dari HH:MM ke HH.MM WIB
                if ((str_contains($lowerKey, 'waktu') || str_contains($lowerKey, 'time') || str_contains($lowerKey, 'jam')) && 
                    preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $value = str_replace(':', '.', $value) . ' WIB';
                }
                $customData[$key] = $value;
            }
            $bodyRendered = $this->replacePlaceholders($bodyRendered, $customData);
        }

        $customFields = $request->custom_fields ?? [];

        $configs = Setting::pluck('value', 'key')->toArray();
        $principal = User::where('role', '=', 'principal')->first();

        return view('admin.letters.generate.preview', compact(
            'template', 'recipient', 'bodyRendered', 
            'configs', 'principal', 'data', 'customFields', 'formatType'
        ));
    }

    // ── Generate & download PDF (1 penerima) ─────────────────────

    public function generate(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'recipient_id'    => 'required|exists:users,id',
            'custom_fields'   => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:500',
        ]);

        $recipient = User::with('classroom')->find($request->recipient_id);

        if (!$recipient) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        // Generate nomor surat asli
        $letterTypeCode = $template->letterType?->code;
        $formatType     = $request->input('format_type', 'simple');
        $numberData     = $this->generateLetterNumber($letterTypeCode, $formatType);

        // Build data placeholder
        $data = $template->category === 'guru'
            ? $this->buildTeacherData($recipient)
            : $this->buildStudentData($recipient);

        $data['nomor_surat'] = $numberData['letter_number'];

        $bodyRendered = $this->replacePlaceholders($template->body, $data);

        // Jika ada custom_fields, bersihkan dan format sebelum di-replace
        if ($request->has('custom_fields')) {
            $customData = [];
            foreach ($request->custom_fields as $key => $value) {
                $lowerKey = strtolower($key);
                // Deteksi field tanggal dan konversi format ke Indonesia
                if ((str_contains($lowerKey, 'tanggal') || str_contains($lowerKey, 'date')) && 
                    preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $value = \Carbon\Carbon::parse($value)
                        ->locale('id')
                        ->translatedFormat('d F Y');
                }
                // Konversi waktu dari HH:MM ke HH.MM WIB
                if ((str_contains($lowerKey, 'waktu') || str_contains($lowerKey, 'time') || str_contains($lowerKey, 'jam')) && 
                    preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $value = str_replace(':', '.', $value) . ' WIB';
                }
                $customData[$key] = $value;
            }
            $bodyRendered = $this->replacePlaceholders($bodyRendered, $customData);
        }

        // Simpan ke tabel letters
        $letter = Letter::create([
            'template_id'     => $template->id,
            'letter_type_id'  => $template->letter_type_id,
            'format_type'     => $formatType,
            'letter_number'   => $numberData['letter_number'],
            'sequence_number' => $numberData['sequence_number'],
            'year'            => $numberData['year'],
            'recipient_type'  => $template->category === 'guru' 
                                 ? 'teacher' : 'student',
            'recipient_id'    => $recipient->id,
            'recipient_name'  => $recipient->name,
            'body_rendered'   => $bodyRendered,
            'created_by'      => auth()->id(),
            'issued_date'     => now(),
        ]);

        $configs = Setting::pluck('value', 'key')->toArray();
        $principal = User::where('role', '=', 'principal')->first();

        $pdf = Pdf::loadView('admin.letters.generate.pdf', compact(
            'letter', 'template', 'recipient', 
            'bodyRendered', 'configs', 'principal'
        ))->setPaper('a4', 'portrait');

        $filename = $numberData['letter_number'] === '' 
            ? 'surat.pdf'
            : str_replace('/', '-', $numberData['letter_number']) 
              . '_' . str_replace(' ', '_', $recipient->name) . '.pdf';

        return $pdf->download($filename);
    }

    // ── Bulk generate (banyak penerima → zip) ────────────────────

    public function bulkForm(Request $request, LetterTemplate $template)
    {
        $academicYear = Setting::get('academic_year', '2024/2025');
        $selectedClass = $request->class_id;
        $selectedLevel = $request->level;

        // Map X, XI, XII ke 10, 11, 12 untuk query database
        $levelMap = [
            'X'   => '10',
            'XI'  => '11',
            'XII' => '12',
        ];
        $dbLevel = $levelMap[strtoupper($selectedLevel)] ?? $selectedLevel;

        $classesQuery = \App\Models\ClassRoom::active()
            ->orderBy('name');

        if ($dbLevel) {
            $classesQuery->where('grade', '=', $dbLevel);
        }

        $classes = $classesQuery->get();

        $recipients = collect();
        if ($template->category === 'siswa') {
            $query = User::where('role', '=', 'student')
                ->where('status', '=', 'Aktif')
                ->with('classroom')
                ->orderBy('name');

            // Filter per angkatan (X, XI, XII)
            if ($dbLevel) {
                $query->whereHas('classroom', function($q) use ($dbLevel) {
                    $q->where('grade', '=', $dbLevel);
                });
            }

            // Filter per kelas jika dipilih
            if ($selectedClass) {
                $query->where('class_id', '=', $selectedClass);
            }

            $recipients = $query->get()->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'info'  => ($s->nis ?? '-') . ' • ' .
                           ($s->classroom->name ?? 'Tanpa Kelas'),
                'class' => $s->classroom->name ?? 'Tanpa Kelas',
            ]);
        } elseif ($template->category === 'guru') {
            $recipients = User::whereIn('role', ['teacher', 'principal'])
                ->orderBy('name')
                ->get()
                ->map(fn($t) => [
                    'id'    => $t->id,
                    'name'  => $t->name,
                    'info'  => $t->nip ?? $t->niy ?? '-',
                    'class' => '-',
                ]);
        }

        return view('admin.letters.generate.bulk-form', compact(
            'template', 'recipients', 'academicYear',
            'classes', 'selectedClass', 'selectedLevel'
        ));
    }

    public function bulkGenerate(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'recipient_ids'   => 'required|array|min:1',
            'recipient_ids.*' => 'exists:users,id',
        ]);

        $configs    = Setting::pluck('value', 'key')->toArray();
        $principal  = User::where('role', '=', 'principal')->first();
        $recipients = User::with('classroom')
            ->whereIn('id', $request->recipient_ids)
            ->get();

        if ($recipients->isEmpty()) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        // ── Deteksi Manual Fields (Placeholder [field] yang tersisa) ──
        // Kita cek menggunakan satu sampel penerima (pertama)
        $sampleRecipient = $recipients->first();
        $sampleData = $template->category === 'guru'
            ? $this->buildTeacherData($sampleRecipient)
            : $this->buildStudentData($sampleRecipient);
        
        // Tambahkan placeholder nomor surat dummy untuk deteksi
        $sampleData['nomor_surat'] = 'XXX/XXX/XXX/XXXX';
        
        $bodyRenderedSample = $this->replacePlaceholders($template->body, $sampleData);
        
        // Cari [placeholder] atau {{placeholder}} yang belum terisi
        preg_match_all('/\[([^\]]+)\]|\{\{([^}]+)\}\}/', $bodyRenderedSample, $matches);
        $unreplacedPlaceholders = array_unique(
            array_filter(array_merge($matches[1] ?? [], $matches[2] ?? []))
        );

        // Jika ada manual fields dan belum ada di request, redirect ke form pengisian
        if (!empty($unreplacedPlaceholders) && !$request->has('custom_fields')) {
            $recipientIds = $request->recipient_ids;
            return view('admin.letters.generate.bulk-custom-fields', compact(
                'template', 'recipients', 'unreplacedPlaceholders', 'recipientIds', 'configs', 'principal'
            ));
        }

        // Jika manual fields sudah diisi (atau tidak ada), redirect ke progress page
        // untuk diproses via AJAX
        return redirect()->route('admin.letters.bulk.progress-page', $template)
            ->with('bulk_data', [
                'recipient_ids' => $request->recipient_ids,
                'custom_fields' => $request->custom_fields ?? [],
                'format_type'   => $request->input('format_type', 'simple'),
            ]);
    }

    /**
     * Halaman progress bar bulk generate
     */
    public function bulkProgressPage(Request $request, LetterTemplate $template)
    {
        // Ambil data dari session atau request
        $bulkData     = session('bulk_data', []);
        $recipientIds = $request->recipient_ids ?? $bulkData['recipient_ids'] ?? [];
        $customFields = $request->custom_fields ?? $bulkData['custom_fields'] ?? [];
        $formatType   = $request->format_type ?? $bulkData['format_type'] ?? 'simple';
        
        $totalCount    = count($recipientIds);
        $batchId       = str_replace('.', '_', uniqid('batch_', true));

        return view('admin.letters.generate.bulk-progress', compact(
            'template', 'recipientIds', 'customFields', 'formatType',
            'totalCount', 'batchId'
        ));
    }

    /**
     * Generate PDF bulk via AJAX — return JSON progress
     */
    public function bulkProgress(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'recipient_ids'   => 'required|array|min:1',
            'recipient_ids.*' => 'exists:users,id',
            'custom_fields'   => 'nullable|array',
            'batch_id'        => 'required|string',
            'format_type'     => 'nullable|string',
        ]);

        set_time_limit(600);
        ini_set('memory_limit', '1G');

        $configs    = Setting::pluck('value', 'key')->toArray();
        $principal  = User::where('role', '=', 'principal')->first();
        $batchId    = $request->batch_id;
        $formatType = $request->input('format_type', 'simple');

        // Direktori temp per batch
        $tmpDir = storage_path('app/temp/letters/' . $batchId);
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $recipients = User::with('classroom')
            ->whereIn('id', $request->recipient_ids)
            ->get();

        $total     = $recipients->count();
        $processed = 0;
        $files     = [];

        // Pre-format custom fields jika ada
        $customData = [];
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $key => $value) {
                $lowerKey = strtolower($key);
                // Tanggal Indonesia
                if ((str_contains($lowerKey, 'tanggal') || str_contains($lowerKey, 'date')) && 
                    preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $value = Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
                }
                // Waktu WIB
                if ((str_contains($lowerKey, 'waktu') || str_contains($lowerKey, 'time') || str_contains($lowerKey, 'jam')) && 
                    preg_match('/^\d{2}:\d{2}$/', $value)) {
                    $value = str_replace(':', '.', $value) . ' WIB';
                }
                $customData[$key] = $value;
            }
        }

        foreach ($recipients as $recipient) {
            $letterTypeCode = $template->letterType?->code;
            $numberData = $this->generateLetterNumber($letterTypeCode, $formatType);
            $data = $template->category === 'guru'
                ? $this->buildTeacherData($recipient)
                : $this->buildStudentData($recipient);

            $data['nomor_surat'] = $numberData['letter_number'];

            $bodyRendered = $this->replacePlaceholders($template->body, $data);
            
            if (!empty($customData)) {
                $bodyRendered = $this->replacePlaceholders($bodyRendered, $customData);
            }

            // Simpan Record History
            $letter = Letter::create([
                'template_id'     => $template->id,
                'letter_type_id'  => $template->letter_type_id,
                'format_type'     => $formatType,
                'letter_number'   => $numberData['letter_number'],
                'sequence_number' => $numberData['sequence_number'],
                'year'            => $numberData['year'],
                'recipient_type'  => $template->category === 'guru' ? 'teacher' : 'student',
                'recipient_id'    => $recipient->id,
                'recipient_name'  => $recipient->name,
                'body_rendered'   => $bodyRendered,
                'created_by'      => auth()->id(),
                'issued_date'     => now(),
            ]);

            $pdf = Pdf::loadView('admin.letters.generate.pdf', [
                'letter'       => $letter,
                'template'     => $template,
                'recipient'    => $recipient,
                'bodyRendered' => $bodyRendered,
                'configs'      => $configs,
                'principal'    => $principal,
            ])->setPaper('a4', 'portrait');

            $safeRecipientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $recipient->name);
            $safeLetterNumber  = str_replace('/', '-', $numberData['letter_number']);
            $filename = $safeLetterNumber . '_' . $safeRecipientName . '.pdf';
            $filePath = $tmpDir . '/' . $filename;
            
            $pdf->save($filePath);
            $files[] = $filePath;
            $processed++;
        }

        $zip = new \ZipArchive();
        $zipFilename = 'surat_bulk_' . $batchId . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFilename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        Log::info('Bulk Progress: Creating ZIP', ['path' => $zipPath, 'file_count' => count($files)]);

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            Log::info('Bulk Progress: ZIP Created Successfully', ['path' => $zipPath]);
        } else {
            Log::error('Bulk Progress: Failed to create ZIP', ['path' => $zipPath]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat file ZIP.']);
        }

        // Cleanup temp PDFs
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($tmpDir);

        Log::info('Bulk Progress: Success Response', ['zip_filename' => $zipFilename]);

        return response()->json([
            'success'      => true,
            'total'        => $total,
            'processed'    => $processed,
            'zip_filename' => $zipFilename,
            'download_url' => route('admin.letters.bulk.download', ['filename' => $zipFilename]),
        ]);
    }

    /**
     * Download ZIP yang sudah digenerate
     */
    public function bulkDownload(Request $request)
    {
        $filename = $request->filename;
        Log::info('Bulk Download Attempt', ['filename' => $filename]);

        if (!preg_match('/^surat_bulk_[a-zA-Z0-9_.]+\.zip$/', $filename)) {
            Log::warning('Bulk Download: Regex mismatch', ['filename' => $filename]);
            abort(404);
        }
        $zipPath = storage_path('app/temp/' . $filename);
        Log::info('Bulk Download: Zip Path', ['path' => $zipPath, 'exists' => file_exists($zipPath)]);

        if (!file_exists($zipPath)) {
            Log::error('Bulk Download: File not found', ['path' => $zipPath]);
            abort(404, 'File tidak ditemukan.');
        }
        return response()->download($zipPath, $filename);
    }

    // ── Halaman riwayat surat ────────────────────────────────────

    public function history(Request $request)
    {
        $query = Letter::with(['template', 'recipient', 'creator'])
            ->latest();

        // Filter by template
        if ($request->filled('template_id')) {
            $query->where('template_id', '=', $request->template_id);
        }

        // Filter by recipient_type
        if ($request->filled('recipient_type')) {
            $query->where('recipient_type', '=', $request->recipient_type);
        }

        // Filter by tahun
        if ($request->filled('year')) {
            $query->where('year', '=', $request->year);
        }

        // Filter by tanggal range
        if ($request->filled('date_from')) {
            $query->whereDate('issued_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issued_date', '<=', $request->date_to);
        }

        // Search by recipient name atau nomor surat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('letter_number', 'like', "%{$search}%");
            });
        }

        $letters = $query->paginate(15)->withQueryString();

        $templates = LetterTemplate::orderBy('name')->get();
        
        // Ambil tahun yang tersedia untuk filter
        $years = Letter::distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.letters.history', compact(
            'letters', 'templates', 'years'
        ));
    }

    // Download ulang PDF dari riwayat
    public function redownload(Letter $letter)
    {
        $configs   = Setting::pluck('value', 'key')->toArray();
        $principal = User::where('role', '=', 'principal')->first();
        $recipient = $letter->recipient;
        $template  = $letter->template;

        $pdf = Pdf::loadView('admin.letters.generate.pdf', [
            'letter'       => $letter,
            'template'     => $template,
            'recipient'    => $recipient,
            'bodyRendered' => $letter->body_rendered,
            'configs'      => $configs,
            'principal'    => $principal,
        ])->setPaper('a4', 'portrait');

        $filename = str_replace('/', '-', $letter->letter_number)
            . '_' . str_replace(' ', '_', $letter->recipient_name)
            . '.pdf';

        return $pdf->download($filename);
    }

    // Hapus surat dari riwayat
    public function deleteLetter(Letter $letter)
    {
        $letter->delete();
        return back()->with('success', 'Surat berhasil dihapus dari riwayat.');
    }

    // Hapus semua surat dari riwayat
    public function deleteAllHistory()
    {
        try {
            $count = Letter::count();
            // Use query()->delete() for soft deletes support if the model uses it, 
            // or truncation for full reset if applicable.
            Letter::query()->delete(); 
            
            return redirect()->route('admin.letters.history')
                ->with('success', "Riwayat terbit surat ({$count} data) berhasil dihapus permanen.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus riwayat: ' . $e->getMessage());
        }
    }
}
