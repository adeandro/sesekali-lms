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
        $templates = LetterTemplate::orderBy('sort_order')
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
        return view('admin.letters.templates.create');
    }

    // Simpan template baru
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:20|unique:letter_templates',
            'category' => 'required|in:siswa,guru,custom',
            'body'     => 'required|string',
        ]);

        LetterTemplate::create([
            'name'       => $request->name,
            'code'       => strtoupper($request->code),
            'category'   => $request->category,
            'body'       => $request->body,
            'is_active'  => true,
            'sort_order' => LetterTemplate::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.letters.templates.index')
            ->with('success', 'Template surat berhasil dibuat.');
    }

    // Form edit template
    public function edit(LetterTemplate $template)
    {
        return view('admin.letters.templates.edit', 
            compact('template'));
    }

    // Update template
    public function update(Request $request, LetterTemplate $template)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:20|unique:letter_templates,code,' . $template->id,
            'category' => 'required|in:siswa,guru,custom',
            'body'     => 'required|string',
        ]);

        $template->update([
            'name'     => $request->name,
            'code'     => strtoupper($request->code),
            'category' => $request->category,
            'body'     => $request->body,
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
