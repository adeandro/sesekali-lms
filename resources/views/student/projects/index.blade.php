@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--brand-primary)">
                <i class="fas fa-code mr-2"></i>Tugas Project
            </h1>
            <p class="text-sm text-gray-500 mt-1">Kumpulkan tugas project berbasis web dan pamerkan karyamu di Galeri Sekolah</p>
        </div>
        <a href="{{ route('gallery.index') }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 shadow-xs hover:bg-gray-50 transition">
            <i class="fas fa-external-link-alt text-indigo-500"></i> Kunjungi Galeri Publik
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-2xl p-4 text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="rounded-2xl p-4 text-sm font-medium bg-blue-50 text-blue-800 border border-blue-200 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>{{ session('info') }}
        </div>
    @endif

    {{-- Grid Assignments --}}
    @if($assignments->isEmpty())
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Belum Ada Tugas Project</h3>
            <p class="text-sm text-gray-400 mt-1 max-w-md mx-auto">Saat ini belum ada tugas project yang ditugaskan untuk kelas Anda. Periksa kembali nanti saat guru memberikan penugasan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($assignments as $assignment)
            @php
                $mySubmissionsCount = $assignment->submissions->count();
                $isCompleted = ($mySubmissionsCount >= $assignment->max_slots);
            @endphp
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    {{-- Top badge --}}
                    <div class="flex items-center justify-between gap-2 mb-3">
                        @if($assignment->subject)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                {{ $assignment->subject->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                Umum
                            </span>
                        @endif

                        @if($isCompleted)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-check-circle text-[10px]"></i> Selesai
                            </span>
                        @elseif($mySubmissionsCount > 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-clock text-[10px]"></i> {{ $mySubmissionsCount }}/{{ $assignment->max_slots }} Slot
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Belum Upload
                            </span>
                        @endif
                    </div>

                    <h2 class="text-lg font-bold text-gray-900 leading-snug mb-2">{{ $assignment->title }}</h2>

                    @if($assignment->description)
                        <p class="text-xs text-gray-500 line-clamp-2 mb-4">{{ $assignment->description }}</p>
                    @endif

                    <div class="space-y-1.5 text-xs text-gray-500 border-t border-gray-50 pt-3">
                        <div class="flex items-center justify-between">
                            <span><i class="fas fa-layer-group text-slate-400 mr-1.5"></i>Jumlah Slot:</span>
                            <span class="font-bold text-gray-700">{{ $assignment->max_slots }} Tugas</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span><i class="fas fa-file-archive text-slate-400 mr-1.5"></i>Maksimal File:</span>
                            <span class="font-bold text-gray-700">{{ $assignment->max_file_size_mb }} MB (.zip)</span>
                        </div>
                        @if($assignment->ends_at)
                        <div class="flex items-center justify-between">
                            <span><i class="fas fa-calendar-alt text-slate-400 mr-1.5"></i>Deadline:</span>
                            <span class="font-bold {{ $assignment->ends_at < now() ? 'text-rose-600' : 'text-indigo-600' }}">
                                {{ $assignment->ends_at->format('d M Y, H:i') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a href="{{ route('gallery.show', $assignment) }}" target="_blank"
                       class="text-xs font-semibold text-gray-500 hover:text-indigo-600 transition flex items-center gap-1"
                       title="Lihat Galeri Kelas">
                        <i class="fas fa-eye"></i> Galeri
                    </a>

                    <a href="{{ route('student.projects.show', $assignment) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-bold text-white shadow-md transition hover:opacity-90"
                       style="background: var(--brand-primary)">
                        @if($mySubmissionsCount > 0)
                            <i class="fas fa-folder-open"></i> Kelola Tugas
                        @else
                            <i class="fas fa-upload"></i> Upload Tugas
                        @endif
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
