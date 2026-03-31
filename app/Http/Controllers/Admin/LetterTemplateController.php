<?php
 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class LetterTemplateController extends Controller
{
    // List semua template
    public function index()
    {
        $templates = LetterTemplate::with('letterType')
            ->orderBy('sort_order')
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        $allSettings = \App\Models\Setting::all()->pluck('value', 'key');

        return view('admin.letters.templates.index', 
            compact('templates', 'allSettings'));
    }

    // Form create template baru
    public function create()
    {
        $letterTypes = \App\Models\LetterType::orderBy('sort_order')->get();
        return view('admin.letters.templates.create', compact('letterTypes'));
    }

    // Simpan template baru
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:20|unique:letter_templates',
            'letter_type_id' => 'nullable|exists:letter_types,id',
            'category'       => 'required|in:siswa,guru,custom',
            'body'           => 'required|string',
        ]);

        LetterTemplate::create([
            'name'           => $request->name,
            'code'           => strtoupper($request->code),
            'letter_type_id' => $request->letter_type_id,
            'category'       => $request->category,
            'body'           => $request->body,
            'is_active'  => true,
            'sort_order' => LetterTemplate::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.letters.templates.index')
            ->with('success', 'Template surat berhasil dibuat.');
    }

    // Form edit template
    public function edit(LetterTemplate $template)
    {
        $letterTypes = \App\Models\LetterType::orderBy('sort_order')->get();
        return view('admin.letters.templates.edit', 
            compact('template', 'letterTypes'));
    }

    // Update template
    public function update(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:20|unique:letter_templates,code,' . $template->id,
            'letter_type_id' => 'nullable|exists:letter_types,id',
            'category'       => 'required|in:siswa,guru,custom',
            'body'           => 'required|string',
        ]);

        $template->update([
            'name'           => $request->name,
            'code'           => strtoupper($request->code),
            'letter_type_id' => $request->letter_type_id,
            'category'       => $request->category,
            'body'           => $request->body,
        ]);

        return redirect()->route('admin.letters.templates.index')
            ->with('success', 'Template surat berhasil diperbarui.');
    }

    // Toggle aktif/nonaktif
    public function toggleActive(LetterTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Status template diperbarui.');
    }

    // Hapus template
    public function destroy(LetterTemplate $template)
    {
        if ($template->letters()->exists()) {
            return back()->with('error', 
                'Template tidak bisa dihapus karena sudah digunakan.');
        }
        $template->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }
}
