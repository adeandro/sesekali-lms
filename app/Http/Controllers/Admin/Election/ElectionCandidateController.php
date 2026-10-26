<?php

namespace App\Http\Controllers\Admin\Election;

use App\Http\Controllers\Controller;
use App\Models\ElectionAuditLog;
use App\Models\ElectionCandidate;
use App\Models\ElectionPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ElectionCandidateController extends Controller
{
    public function create(ElectionPeriod $period)
    {
        $this->guardEditable($period);

        $classes = \App\Models\ClassRoom::active()->orderBy('name')->get();
        $grades = \App\Models\ClassRoom::active()
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->distinct()
            ->pluck('grade')
            ->sort()
            ->values();

        return view('admin.pilketos.candidates.form', [
            'period'    => $period,
            'candidate' => null,
            'classes'   => $classes,
            'grades'    => $grades,
        ]);
    }

    public function store(Request $request, ElectionPeriod $period)
    {
        $this->guardEditable($period);

        $validated = $request->validate([
            'number'       => "required|integer|min:1|max:99|unique:election_candidates,number,NULL,id,period_id,{$period->id}",
            'chairman_id'  => "required|integer|exists:users,id|unique:election_candidates,chairman_id,NULL,id,period_id,{$period->id}",
            'vice_id'      => "required|integer|exists:users,id|unique:election_candidates,vice_id,NULL,id,period_id,{$period->id}|different:chairman_id",
            'vision'       => 'nullable|string|max:2000',
            'mission'      => 'nullable|string|max:2000',
            'photo'        => 'nullable|image|max:2048',
        ]);

        $chairman = User::findOrFail($validated['chairman_id']);
        $vice     = User::findOrFail($validated['vice_id']);

        // Pastikan berbeda dan tidak dipakai paslon lain di periode ini
        $this->ensureNotUsedElsewhere($period, $chairman->id, $vice->id);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('election-candidates', 'public');
        }

        $candidate = ElectionCandidate::create([
            'period_id'     => $period->id,
            'number'        => $validated['number'],
            'chairman_id'   => $chairman->id,
            'chairman_name' => $chairman->name,
            'chairman_class' => $chairman->classroom?->name,
            'vice_id'       => $vice->id,
            'vice_name'     => $vice->name,
            'vice_class'    => $vice->classroom?->name,
            'vision'        => $validated['vision'] ?? null,
            'mission'       => $validated['mission'] ?? null,
            'photo_path'    => $photoPath,
        ]);

        ElectionAuditLog::record('candidate.created', $period->id, auth()->id(), [
            'paslon' => $candidate->label,
        ]);

        return redirect()->route('admin.pilketos.periods.show', $period)
            ->with('success', "Paslon {$candidate->number} berhasil ditambahkan.");
    }

    public function edit(ElectionPeriod $period, ElectionCandidate $candidate)
    {
        $this->guardEditable($period);
        $this->guardBelongs($period, $candidate);

        $candidate->load(['chairman', 'vice']);

        $classes = \App\Models\ClassRoom::active()->orderBy('name')->get();
        $grades = \App\Models\ClassRoom::active()
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->distinct()
            ->pluck('grade')
            ->sort()
            ->values();

        return view('admin.pilketos.candidates.form', compact('period', 'candidate', 'classes', 'grades'));
    }

    public function update(Request $request, ElectionPeriod $period, ElectionCandidate $candidate)
    {
        $this->guardEditable($period);
        $this->guardBelongs($period, $candidate);

        $validated = $request->validate([
            'number'       => "required|integer|min:1|max:99|unique:election_candidates,number,{$candidate->id},id,period_id,{$period->id}",
            'chairman_id'  => "required|integer|exists:users,id|unique:election_candidates,chairman_id,{$candidate->id},id,period_id,{$period->id}",
            'vice_id'      => "required|integer|exists:users,id|unique:election_candidates,vice_id,{$candidate->id},id,period_id,{$period->id}|different:chairman_id",
            'vision'       => 'nullable|string|max:2000',
            'mission'      => 'nullable|string|max:2000',
            'photo'        => 'nullable|image|max:2048',
        ]);

        $chairman = User::findOrFail($validated['chairman_id']);
        $vice     = User::findOrFail($validated['vice_id']);

        $this->ensureNotUsedElsewhere($period, $chairman->id, $vice->id, $candidate->id);

        $photoPath = $candidate->photo_path;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('election-candidates', 'public');
        }

        $candidate->update([
            'number'        => $validated['number'],
            'chairman_id'   => $chairman->id,
            'chairman_name' => $chairman->name,
            'chairman_class' => $chairman->classroom?->name,
            'vice_id'       => $vice->id,
            'vice_name'     => $vice->name,
            'vice_class'    => $vice->classroom?->name,
            'vision'        => $validated['vision'] ?? null,
            'mission'       => $validated['mission'] ?? null,
            'photo_path'    => $photoPath,
        ]);

        ElectionAuditLog::record('candidate.updated', $period->id, auth()->id());

        return redirect()->route('admin.pilketos.periods.show', $period)
            ->with('success', "Paslon {$candidate->number} berhasil diperbarui.");
    }

    public function destroy(ElectionPeriod $period, ElectionCandidate $candidate)
    {
        $this->guardEditable($period);
        $this->guardBelongs($period, $candidate);

        if ($candidate->photo_path && Storage::disk('public')->exists($candidate->photo_path)) {
            Storage::disk('public')->delete($candidate->photo_path);
        }

        $label = $candidate->label;
        $candidate->delete();
        ElectionAuditLog::record('candidate.deleted', $period->id, auth()->id(), ['paslon' => $label]);

        return redirect()->route('admin.pilketos.periods.show', $period)
            ->with('success', "Paslon dihapus.");
    }

    // ── JSON Search endpoint untuk siswa aktif ──────────────────────────────

    public function searchStudents(Request $request)
    {
        $q       = $request->input('q', '');
        $classId = $request->input('class_id');
        $grade   = $request->input('grade');

        $query = User::aktif()
            ->where('role', 'student')
            ->with('classroom');

        if (!empty($classId)) {
            $query->where('class_id', $classId);
        }

        if (!empty($grade)) {
            $query->whereHas('classroom', function ($sub) use ($grade) {
                $sub->where('grade', $grade);
            });
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $limit = (!empty($classId) || !empty($grade)) ? 200 : 30;

        $students = $query
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn ($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'nis'       => $s->nis,
                'class'     => $s->classroom?->name ?? '-',
                'class_id'  => $s->class_id,
                'grade'     => $s->classroom?->grade ?? '',
                'photo_url' => $s->avatar_upload && Storage::disk('public')->exists($s->avatar_upload)
                    ? '/storage/' . str_replace(' ', '%20', $s->avatar_upload)
                    : ($s->photo && Storage::disk('public')->exists('profiles/' . $s->photo)
                        ? '/storage/profiles/' . str_replace(' ', '%20', $s->photo)
                        : $s->photo_url),
            ]);

        return response()->json($students);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function guardEditable(ElectionPeriod $period): void
    {
        if (in_array($period->status, ['open', 'closed'])) {
            abort(403, 'Tidak dapat mengubah paslon saat pemilihan berlangsung atau sudah ditutup.');
        }
    }

    private function guardBelongs(ElectionPeriod $period, ElectionCandidate $candidate): void
    {
        if ($candidate->period_id !== $period->id) {
            abort(404);
        }
    }

    private function ensureNotUsedElsewhere(ElectionPeriod $period, int $chairmanId, int $viceId, ?int $excludeId = null): void
    {
        $query = ElectionCandidate::where('period_id', $period->id);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        $conflict = $query->where(function ($q) use ($chairmanId, $viceId) {
            $q->whereIn('chairman_id', [$chairmanId, $viceId])
              ->orWhereIn('vice_id', [$chairmanId, $viceId]);
        })->exists();

        if ($conflict) {
            abort(422, 'Salah satu siswa sudah terdaftar sebagai ketua atau wakil di paslon lain.');
        }
    }
}
