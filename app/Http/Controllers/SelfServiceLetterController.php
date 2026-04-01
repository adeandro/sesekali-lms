<?php
namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SelfServiceLetterController extends Controller
{
    // ── Helper: generate nomor surat ─────────────────
    private function generateLetterNumber(
        string $typeCode
    ): array {
        $year  = (int) date('Y');
        $month = (int) date('m');

        $lastInDb = Letter::where('year', $year)
            ->max('sequence_number') ?? 0;
        $sequenceStart = (int) Setting::get(
            'letter_sequence_start_' . $year, 0
        );
        $sequence = max($lastInDb, $sequenceStart) + 1;

        $romanMonths = [
            1=>'I',   2=>'II',  3=>'III', 4=>'IV',
            5=>'V',   6=>'VI',  7=>'VII', 8=>'VIII',
            9=>'IX', 10=>'X',  11=>'XI', 12=>'XII',
        ];
        $letterCode = Setting::get('letter_code', 'SMK');

        $letterNumber = sprintf('%03d', $sequence)
            . '/' . $typeCode
            . '/' . $romanMonths[$month]
            . '/' . $year;

        return [
            'letter_number'   => $letterNumber,
            'sequence_number' => $sequence,
            'year'            => $year,
        ];
    }

    // ── Helper: replace placeholders ─────────────────
    private function replacePlaceholders(
        string $body, array $data
    ): string {
        foreach ($data as $key => $value) {
            $body = str_replace(
                '[' . $key . ']',
                (string) ($value ?? ''),
                $body
            );
        }
        return $body;
    }

    // ── SPPD GURU ────────────────────────────────────

    public function sppdForm()
    {
        $user = auth()->user();
        $query = LetterTemplate::where('code', 'SPPD');
        
        // Superadmin and TU can see it even if inactive (for setup)
        if (!in_array($user->role, ['superadmin', 'tu'])) {
            $query->where('is_active', true);
        }

        $template = $query->first();

        if (!$template) {
            return redirect()->back()
                ->with('error', 'Template SPPD belum dikonfigurasi atau tidak aktif. Silakan hubungi admin.');
        }

        $teacher = $user;
        return view('self-service.sppd-form',
            compact('teacher', 'template'));
    }

    public function sppdGenerate(Request $request)
    {
        $request->validate([
            'tujuan'             => 'required|string|max:255',
            'keperluan'          => 'required|string|max:500',
            'tanggal_berangkat'  => 'required|date',
            'tanggal_kembali'    => 'required|date|after_or_equal:tanggal_berangkat',
            'kendaraan'          => 'required|string|max:100',
            'jabatan'         => 'required|string|max:100',
        ]);

        $teacher  = auth()->user();
        $template = LetterTemplate::where('code', 'SPPD')
            ->firstOrFail();
        $configs  = Setting::pluck('value', 'key')
            ->toArray();
        $principal = User::where('role', 'principal')
            ->first();

        // Build principal name (sama seperti LetterController)
        $principalName = null;
        $principalNip  = null;
        if ($principal) {
            $parts = [];
            if ($principal->title_ahead)
                $parts[] = trim($principal->title_ahead);
            $parts[] = trim($principal->name);
            $principalName = implode(' ', $parts);
            if ($principal->title_behind)
                $principalName .= ', '
                    . trim($principal->title_behind);
            $principalNip = $principal->nip
                ?? $principal->niy ?? null;
        }

        $numberData = $this->generateLetterNumber('SPPD');

        // Data placeholder — gabungan buildTeacherData
        // + custom fields SPPD
        $data = [
            'nomor_surat'       => $numberData['letter_number'],
            'nama_guru'         => $teacher->name,
            'nip_guru'          => $teacher->nip
                                   ?? $teacher->niy ?? '-',
            'jabatan_guru'      => $request->jabatan,
            'tujuan'            => $request->tujuan,
            'keperluan'         => $request->keperluan,
            'tanggal_berangkat' => Carbon::parse(
                $request->tanggal_berangkat)
                ->locale('id')
                ->translatedFormat('d F Y'),
            'tanggal_kembali'   => Carbon::parse(
                $request->tanggal_kembali)
                ->locale('id')
                ->translatedFormat('d F Y'),
            'kendaraan'         => $request->kendaraan,
            'nama_sekolah'      => $configs['school_name']
                                   ?? '',
            'alamat_sekolah'    => $configs['school_address']
                                   ?? '',
            'tahun_ajaran'      => $configs['academic_year']
                                   ?? '',
            'nama_kepsek'       => $principalName ?? '',
            'nip_kepsek'        => $principalNip ?? '',
            'tanggal_surat'     => Carbon::now()
                ->locale('id')
                ->translatedFormat('d F Y'),
        ];

        $bodyRendered = $this->replacePlaceholders(
            $template->body, $data
        );

        // Simpan ke letters
        $letter = Letter::create([
            'template_id'     => $template->id,
            'letter_number'   => $numberData['letter_number'],
            'sequence_number' => $numberData['sequence_number'],
            'year'            => $numberData['year'],
            'recipient_type'  => 'teacher',
            'recipient_id'    => $teacher->id,
            'recipient_name'  => $teacher->name,
            'body_rendered'   => $bodyRendered,
            'created_by'      => $teacher->id,
            'issued_date'     => now()->toDateString(),
        ]);

        // Render PDF dengan view yang SAMA seperti
        // sistem surat admin (reuse pdf.blade.php)
        $recipient = $teacher;
        $pdf = Pdf::loadView(
            'admin.letters.generate.pdf',
            compact(
                'letter', 'template', 'recipient',
                'bodyRendered', 'configs', 'principal'
            )
        )->setPaper('a4', 'portrait');

        $filename = str_replace(
            '/', '-', $numberData['letter_number']
        ) . '_' . str_replace(' ', '_', $teacher->name)
          . '.pdf';

        return $pdf->download($filename);
    }

    // ── SURAT KETERANGAN AKTIF SISWA ─────────────────

    public function skForm()
    {
        $user = auth()->user();
        $query = LetterTemplate::where('code', 'SKS-A');

        // Superadmin and TU can see it even if inactive
        if (!in_array($user->role, ['superadmin', 'tu'])) {
            $query->where('is_active', true);
        }

        $template = $query->first();

        if (!$template) {
            return redirect()->back()
                ->with('error', 'Template Surat Keterangan Aktif belum dikonfigurasi atau tidak aktif.');
        }

        $student = $user->load('classroom');
        return view('self-service.sk-form',
            compact('student', 'template'));
    }

    public function skGenerate(Request $request)
    {
        $request->validate([
            'keperluan' => 'required|string|max:500',
            'ditujukan' => 'required|string|max:255',
        ]);

        $student  = auth()->user()->load('classroom');
        $template = LetterTemplate::where('code', 'SKS-A')
            ->firstOrFail();
        $configs  = Setting::pluck('value', 'key')
            ->toArray();
        $principal = User::where('role', 'principal')
            ->first();

        $principalName = null;
        $principalNip  = null;
        if ($principal) {
            $parts = [];
            if ($principal->title_ahead)
                $parts[] = trim($principal->title_ahead);
            $parts[] = trim($principal->name);
            $principalName = implode(' ', $parts);
            if ($principal->title_behind)
                $principalName .= ', '
                    . trim($principal->title_behind);
            $principalNip = $principal->nip
                ?? $principal->niy ?? null;
        }

        $numberData = $this->generateLetterNumber('SK');

        $data = [
            'nomor_surat'   => $numberData['letter_number'],
            'nama_siswa'    => $student->name,
            'nis'           => $student->nis ?? '-',
            'nisn'          => $student->nisn ?? '-',
            'kelas'         => $student->classroom->name
                               ?? '-',
            'jenis_kelamin' => $student->gender ?? '-',
            'tempat_lahir'  => $student->place_of_birth
                               ?? '-',
            'tanggal_lahir' => $student->date_of_birth
                ? Carbon::parse($student->date_of_birth)
                    ->locale('id')
                    ->translatedFormat('d F Y')
                : '-',
            'nama_sekolah'  => $configs['school_name']
                               ?? '',
            'alamat_sekolah'=> $configs['school_address']
                               ?? '',
            'tahun_ajaran'  => $configs['academic_year']
                               ?? '',
            'keperluan'     => $request->keperluan,
            'ditujukan'     => $request->ditujukan,
            'nama_kepsek'   => $principalName ?? '',
            'nip_kepsek'    => $principalNip ?? '',
            'tanggal_surat' => Carbon::now()
                ->locale('id')
                ->translatedFormat('d F Y'),
        ];

        $bodyRendered = $this->replacePlaceholders(
            $template->body, $data
        );

        $letter = Letter::create([
            'template_id'     => $template->id,
            'letter_number'   => $numberData['letter_number'],
            'sequence_number' => $numberData['sequence_number'],
            'year'            => $numberData['year'],
            'recipient_type'  => 'student',
            'recipient_id'    => $student->id,
            'recipient_name'  => $student->name,
            'body_rendered'   => $bodyRendered,
            'created_by'      => $student->id,
            'issued_date'     => now()->toDateString(),
        ]);

        $recipient = $student;
        $pdf = Pdf::loadView(
            'admin.letters.generate.pdf',
            compact(
                'letter', 'template', 'recipient',
                'bodyRendered', 'configs', 'principal'
            )
        )->setPaper('a4', 'portrait');

        $filename = str_replace(
            '/', '-', $numberData['letter_number']
        ) . '_' . str_replace(' ', '_', $student->name)
          . '.pdf';

        return $pdf->download($filename);
    }
}
