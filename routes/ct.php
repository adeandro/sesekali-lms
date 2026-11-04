<?php

use App\Http\Controllers\Ct\VaultController;
use App\Http\Controllers\Ct\AdminController;
use App\Http\Middleware\ElectionNoCache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CT (Computational Thinking) Public Routes
|--------------------------------------------------------------------------
| Loaded only when CT_ENABLED=true (see bootstrap/app.php).
| All routes are unauthenticated (public student access).
| X-Robots-Tag: noindex applied via ElectionNoCache middleware.
|--------------------------------------------------------------------------
*/

Route::prefix('ct')
    ->name('ct.')
    ->middleware([ElectionNoCache::class, 'throttle:ct'])
    ->group(function () {

        // 1. Class selection
        Route::get('/', [VaultController::class, 'index'])->name('index');

        // 2. Team selection for a class
        Route::get('/kelas/{class}', [VaultController::class, 'teams'])->name('teams');

        // 3. Vault form for a team
        Route::get('/kelas/{class}/tim/{team}', [VaultController::class, 'vault'])->name('vault');

        // 4. Submit vault (POST)
        Route::post('/buka', [VaultController::class, 'open'])->name('open');

        // 5. Success page (after correct code, before seeing certificate)
        Route::get('/berhasil/{team}', [VaultController::class, 'success'])->name('success');

        // 6. Certificate HTML (printable)
        Route::get('/sertifikat/{certificateNo}', [VaultController::class, 'certificate'])
            ->name('certificate')
            ->where('certificateNo', 'CT-[0-9]{4}-[A-Z0-9]{8}');

        // 7. Certificate PDF download
        Route::get('/sertifikat/{certificateNo}/pdf', [VaultController::class, 'certificatePdf'])
            ->name('certificate.pdf')
            ->where('certificateNo', 'CT-[0-9]{4}-[A-Z0-9]{8}');

        // 8. Verification page
        Route::get('/verifikasi/{certificateNo}', [VaultController::class, 'verify'])
            ->name('verify')
            ->where('certificateNo', 'CT-[0-9]{4}-[A-Z0-9]{8}');

        // 9. Hint (AJAX POST — returns JSON, no code/params in response)
        Route::post('/petunjuk', [VaultController::class, 'hint'])->name('hint');

        // 10. Leaderboard per class (public, no names by default)
        Route::get('/kelas/{class}/papan-skor', [VaultController::class, 'leaderboard'])->name('leaderboard');
    });


/*
|--------------------------------------------------------------------------
| CT Admin Routes
|--------------------------------------------------------------------------
| Requires auth + role:superadmin,teacher.
| Prefix: admin/ct  |  Name prefix: admin.ct.
|--------------------------------------------------------------------------
*/

Route::prefix('admin/ct')
    ->name('admin.ct.')
    ->middleware(['auth', 'role:superadmin,teacher'])
    ->group(function () {

        // ── Class management ────────────────────────────────────────────
        Route::get('/',                      [AdminController::class, 'index'])->name('index');
        Route::get('/kelas/tambah',          [AdminController::class, 'createClass'])->name('classes.create');
        Route::post('/kelas',                [AdminController::class, 'storeClass'])->name('classes.store');
        Route::get('/kelas/{ctClass}/edit',  [AdminController::class, 'editClass'])->name('classes.edit');
        Route::put('/kelas/{ctClass}',       [AdminController::class, 'updateClass'])->name('classes.update');
        Route::delete('/kelas/{ctClass}',    [AdminController::class, 'destroyClass'])->name('classes.destroy');

        // ── Team generation ─────────────────────────────────────────────
        Route::post('/kelas/{ctClass}/generate', [AdminController::class, 'generateTeams'])->name('classes.generate');

        // ── Team list & answer key ──────────────────────────────────────
        Route::get('/kelas/{ctClass}/tim',           [AdminController::class, 'teams'])->name('teams');
        Route::get('/kelas/{ctClass}/kunci-jawaban', [AdminController::class, 'answerKey'])->name('answer-key');

        // ── PDF downloads ───────────────────────────────────────────────
        Route::get('/kelas/{ctClass}/worksheet-semua',  [AdminController::class, 'worksheetsPdf'])->name('worksheets.all');
        Route::get('/kelas/{ctClass}/kunci-jawaban-pdf',[AdminController::class, 'answerKeyPdf'])->name('answer-key.pdf');
        Route::get('/tim/{ctTeam}/worksheet',           [AdminController::class, 'worksheetPdf'])->name('teams.worksheet');

        // ── Results & CSV ───────────────────────────────────────────────
        Route::get('/kelas/{ctClass}/hasil',         [AdminController::class, 'results'])->name('results');
        Route::get('/kelas/{ctClass}/export-csv',    [AdminController::class, 'exportCsv'])->name('export-csv');

        // ── Team actions ────────────────────────────────────────────────
        Route::post('/tim/{ctTeam}/reset',      [AdminController::class, 'resetTeam'])->name('teams.reset');
        Route::post('/tim/{ctTeam}/perbaiki-nama', [AdminController::class, 'fixNames'])->name('teams.fix-names');
    });
