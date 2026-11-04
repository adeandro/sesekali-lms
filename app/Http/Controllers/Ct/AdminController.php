<?php

namespace App\Http\Controllers\Ct;

use App\Http\Controllers\Controller;
use App\Models\CtClass;
use App\Models\CtTeam;
use App\Models\CtAttempt;
use App\Services\CaseGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * AdminController — CT Escape Room admin panel.
 *
 * Routes prefix: admin/ct
 * Route name prefix: admin.ct.
 * Middleware: auth, role:superadmin,teacher
 */
class AdminController extends Controller
{
    // ── Class CRUD ──────────────────────────────────────────────────────────

    /**
     * List all classes with team count and open/closed status.
     */
    public function index()
    {
        $classes = CtClass::withCount('teams')
            ->withCount(['teams as opened_count' => fn($q) => $q->whereNotNull('opened_at')])
            ->orderBy('name')
            ->get();

        return view('ct.admin.index', compact('classes'));
    }

    /**
     * Show create-class form.
     */
    public function createClass()
    {
        return view('ct.admin.classes.create');
    }

    /**
     * Store a new class.
     */
    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:30', Rule::unique('ct_classes', 'name')],
            'opens_at'   => ['nullable', 'date'],
            'closes_at'  => ['nullable', 'date', 'after_or_equal:opens_at'],
            'is_active'  => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        CtClass::create($data);

        return redirect()->route('admin.ct.index')
            ->with('success', "Kelas {$data['name']} berhasil dibuat.");
    }

    /**
     * Show edit-class form.
     */
    public function editClass(CtClass $ctClass)
    {
        return view('ct.admin.classes.edit', compact('ctClass'));
    }

    /**
     * Update a class.
     */
    public function updateClass(Request $request, CtClass $ctClass)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:30', Rule::unique('ct_classes', 'name')->ignore($ctClass->id)],
            'opens_at'   => ['nullable', 'date'],
            'closes_at'  => ['nullable', 'date', 'after_or_equal:opens_at'],
            'is_active'  => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $ctClass->update($data);

        return redirect()->route('admin.ct.index')
            ->with('success', "Kelas {$ctClass->name} berhasil diperbarui.");
    }

    /**
     * Delete a class and all its teams/attempts (cascade).
     */
    public function destroyClass(CtClass $ctClass)
    {
        $name = $ctClass->name;
        $ctClass->delete();

        return redirect()->route('admin.ct.index')
            ->with('success', "Kelas {$name} dan semua datanya telah dihapus.");
    }

    // ── Team generation ─────────────────────────────────────────────────────

    /**
     * Generate teams for a class.
     * POST admin/ct/classes/{class}/generate
     */
    public function generateTeams(Request $request, CtClass $ctClass)
    {
        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:30'],
            'force' => ['boolean'],
        ]);

        $count = (int) $request->input('count');
        $force = $request->boolean('force');

        // Safety: refuse to overwrite unopened teams without --force
        $existing = $ctClass->teams()->count();
        if ($existing > 0 && ! $force) {
            return back()->with('error',
                "Kelas {$ctClass->name} sudah memiliki {$existing} tim. "
                . "Centang \"Timpa data lama\" untuk menghapus dan membuat ulang."
            );
        }

        if ($force) {
            $ctClass->teams()->delete();
        }

        $generator = app(CaseGenerator::class);
        $generated = 0;
        $errors    = [];

        for ($i = 1; $i <= $count; $i++) {
            try {
                $params   = $generator->generate();
                $code     = $generator->deriveCode($params);
                $codeHash = hash_hmac('sha256', $code, config('app.key'));

                // Ensure uniqueness
                if (CtTeam::where('code_hash', $codeHash)->exists()) {
                    // Retry once
                    $params   = $generator->generate();
                    $code     = $generator->deriveCode($params);
                    $codeHash = hash_hmac('sha256', $code, config('app.key'));
                }

                $ctClass->teams()->create([
                    'number'     => $i,
                    'params'     => $params,
                    'code_hash'  => $codeHash,
                    'wrong_count' => 0,
                    'hint_count'  => 0,
                ]);
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Tim {$i}: " . $e->getMessage();
            }
        }

        $msg = "Berhasil membuat {$generated} tim untuk kelas {$ctClass->name}.";
        if ($errors) {
            $msg .= ' Gagal: ' . implode('; ', $errors);
        }

        return redirect()->route('admin.ct.teams', $ctClass)
            ->with($errors ? 'error' : 'success', $msg);
    }

    // ── Team list ───────────────────────────────────────────────────────────

    /**
     * List teams for a class with status and attempt count.
     */
    public function teams(CtClass $ctClass)
    {
        $teams = $ctClass->teams()
            ->withCount('attempts')
            ->orderBy('number')
            ->get();

        $generator = app(CaseGenerator::class);

        return view('ct.admin.teams.index', compact('ctClass', 'teams', 'generator'));
    }

    // ── Answer key ──────────────────────────────────────────────────────────

    /**
     * Show answer key for all teams in a class.
     */
    public function answerKey(CtClass $ctClass)
    {
        $teams     = $ctClass->teams()->orderBy('number')->get();
        $generator = app(CaseGenerator::class);

        return view('ct.admin.teams.answer-key', compact('ctClass', 'teams', 'generator'));
    }

    // ── Worksheet PDF ────────────────────────────────────────────────────────

    /**
     * Download PDF worksheet for one team.
     * GET admin/ct/teams/{team}/worksheet
     */
    public function worksheetPdf(CtTeam $ctTeam)
    {
        $ctTeam->load('ctClass');
        $generator = app(CaseGenerator::class);
        $params    = $ctTeam->params;

        $log          = $generator->renderLog($params);
        $cipher       = $generator->renderCipher($params);
        $folderFiles  = $generator->renderFolderFiles($params);
        $answerKey    = $generator->answerKey($params);

        $pdf = Pdf::loadView('ct.admin.pdf.worksheet', compact(
            'ctTeam', 'params', 'log', 'cipher', 'folderFiles', 'answerKey'
        ))->setPaper('a4', 'portrait');

        $filename = "worksheet-{$ctTeam->ctClass->name}-tim{$ctTeam->number}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Download PDF worksheets for ALL teams in a class (one PDF, one team per 2-page section).
     * GET admin/ct/classes/{class}/worksheets
     */
    public function worksheetsPdf(CtClass $ctClass)
    {
        $teams     = $ctClass->teams()->orderBy('number')->get();
        $generator = app(CaseGenerator::class);

        $teamsData = $teams->map(function ($team) use ($generator) {
            $p = $team->params;
            return [
                'team'        => $team,
                'log'         => $generator->renderLog($p),
                'cipher'      => $generator->renderCipher($p),
                'folderFiles' => $generator->renderFolderFiles($p),
                'answerKey'   => $generator->answerKey($p),
            ];
        });

        $pdf = Pdf::loadView('ct.admin.pdf.worksheets-all', compact('ctClass', 'teamsData'))
            ->setPaper('a4', 'portrait');

        $filename = "worksheets-{$ctClass->name}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Download answer key PDF for a class.
     * GET admin/ct/classes/{class}/answer-key-pdf
     */
    public function answerKeyPdf(CtClass $ctClass)
    {
        $teams     = $ctClass->teams()->orderBy('number')->get();
        $generator = app(CaseGenerator::class);

        $pdf = Pdf::loadView('ct.admin.pdf.answer-key', compact('ctClass', 'teams', 'generator'))
            ->setPaper('a4', 'landscape');

        $filename = "kunci-jawaban-{$ctClass->name}.pdf";
        return $pdf->download($filename);
    }

    // ── Attempt results ──────────────────────────────────────────────────────

    /**
     * View attempt history for all teams in a class.
     */
    public function results(CtClass $ctClass)
    {
        $teams = $ctClass->teams()
            ->with(['attempts' => fn($q) => $q->orderBy('created_at')])
            ->orderBy('number')
            ->get();

        return view('ct.admin.teams.results', compact('ctClass', 'teams'));
    }

    /**
     * Reset a team (clear opened_at, member_names, certificate_no, wrong_count, cooldown).
     * POST admin/ct/teams/{team}/reset
     */
    public function resetTeam(CtTeam $ctTeam)
    {
        $ctTeam->update([
            'opened_at'       => null,
            'member_names'    => null,
            'certificate_no'  => null,
            'wrong_count'     => 0,
            'hint_count'      => 0,
            'cooldown_until'  => null,
        ]);
        $ctTeam->attempts()->delete();

        return back()->with('success', "Tim {$ctTeam->number} berhasil direset.");
    }

    /**
     * Fix member names for an opened team (admin override).
     * POST admin/ct/teams/{team}/fix-names
     */
    public function fixNames(Request $request, CtTeam $ctTeam)
    {
        $data = $request->validate([
            'names'   => ['required', 'array', 'size:4'],
            'names.*' => ['required', 'string', 'max:60'],
        ]);

        $ctTeam->update(['member_names' => $data['names']]);

        return back()->with('success', "Nama anggota Tim {$ctTeam->number} berhasil diperbarui.");
    }

    // ── CSV Export ───────────────────────────────────────────────────────────

    /**
     * Export results for a class as CSV.
     * GET admin/ct/classes/{class}/export-csv
     */
    public function exportCsv(CtClass $ctClass)
    {
        $teams = $ctClass->teams()
            ->withCount('attempts')
            ->orderBy('number')
            ->get();

        $rows   = [];
        $rows[] = ['Kelas', 'Tim', 'Status', 'Percobaan', 'Salah', 'Waktu Buka', 'No. Sertifikat', 'Anggota 1', 'Anggota 2', 'Anggota 3', 'Anggota 4'];

        foreach ($teams as $team) {
            $names = $team->member_names ?? [];
            $rows[] = [
                $ctClass->name,
                $team->number,
                $team->isOpened() ? 'Terbuka' : 'Belum',
                $team->attempts_count,
                $team->wrong_count,
                $team->opened_at?->format('d/m/Y H:i') ?? '-',
                $team->certificate_no ?? '-',
                $names[0] ?? '',
                $names[1] ?? '',
                $names[2] ?? '',
                $names[3] ?? '',
            ];
        }

        $filename = "hasil-ct-{$ctClass->name}-" . now()->format('Ymd') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens correctly
            fputs($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
