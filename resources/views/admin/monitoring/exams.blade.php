@extends('layouts.app')

@section('title', 'Pantau Ujian - SesekaliCBT')

@section('page-title', 'Pantau Ujian')

@section('content')
    <div>
        <!-- Header with Info -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900">📹 Pantau Ujian</h2>
            <p class="text-gray-600 text-sm mt-1">Monitor jalannya ujian secara real-time, lihat aktivitas siswa, dan kontrol ujian dari sini</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm font-semibold text-blue-900">🟢 Aktif Sekarang</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $activeExamsCount }}</p>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-sm font-semibold text-yellow-900">⏰ Akan Datang</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $upcomingExamsCount }}</p>
                </div>
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                    <p class="text-sm font-semibold text-purple-900">✅ Selesai</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $finishedExamsCount }}</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="{{ route('admin.monitor-exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Cari Ujian</label>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Nama ujian..." 
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">📚 Mata Pelajaran</label>
                    <select name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        🔍 Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Exams List for Monitoring -->
        <div class="space-y-4">
            @forelse($exams as $exam)
                @php
                    $now = now();
                    $isActive = $exam->start_time <= $now && $exam->end_time >= $now;
                    $isUpcoming = $exam->start_time > $now;
                    $isFinished = $exam->end_time < $now;
                    
                    $statusBadge = $isActive ? ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => '🟢 Sedang Berlangsung'] :
                                   ($isUpcoming ? ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => '⏰ Belum Dimulai'] :
                                   ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => '✅ Selesai']);
                    
                    $activeStudents = $exam->sessions()->where('status', 'active')->count();
                    $totalStudents = $exam->attempts()->count();
                @endphp
                
                <div class="bg-white rounded-lg shadow hover:shadow-md transition">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-center">
                            <!-- Exam Info -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $exam->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    📚 {{ $exam->subject->name }} | Kls {{ $exam->jenjang }} | ⏱️ {{ $exam->duration_minutes }}min | 
                                    <strong>{{ $exam->questions_count }}/{{ $exam->total_questions }}</strong> soal
                                </p>
                                <p class="text-xs text-gray-500 mt-2">
                                    📅 {{ $exam->start_time->format('d M Y H:i') }} - {{ $exam->end_time->format('H:i') }}
                                </p>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>

                            <!-- Students Count -->
                            <div class="flex flex-col items-center">
                                <p class="text-xs text-gray-600">Siswa</p>
                                @if($isActive)
                                    <p class="text-2xl font-bold text-green-600">{{ $activeStudents }}/{{ $totalStudents }}</p>
                                    <p class="text-xs text-gray-500">sedang mengerjakan</p>
                                @else
                                    <p class="text-2xl font-bold text-gray-400">{{ $totalStudents }}</p>
                                    <p class="text-xs text-gray-500">total siswa</p>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col gap-2 md:col-span-1">
                                @if($isActive || $isFinished)
                                    <a href="{{ route('admin.monitor.exams.index', $exam) }}" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center text-sm font-semibold">
                                        📹 Monitor Sekarang
                                    </a>
                                @else
                                    <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded-lg text-center text-sm font-semibold cursor-not-allowed">
                                        ⏳ Belum Dimulai
                                    </button>
                                @endif
                                
                                @if(auth()->user()->role === 'superadmin')
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="w-full px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-center text-sm font-semibold">
                                        ✏️ Edit
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Bar for Active Exams -->
                        @if($isActive && $totalStudents > 0)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-600">Progres:</span>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" 
                                             style="width: {{ ($activeStudents / $totalStudents) * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-600">{{ round(($activeStudents / $totalStudents) * 100) }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-600 text-lg">📭 Tidak ada ujian ditemukan</p>
                    <p class="text-gray-500 text-sm mt-2">Buat dan publikasikan ujian terlebih dahulu di menu <strong>Manajemen Ujian</strong></p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($exams->hasPages())
            <div class="mt-8">
                {{ $exams->links() }}
            </div>
        @endif

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-bold text-blue-900 mb-3">ℹ️ Panduan Monitoring Ujian</h3>
            <ul class="space-y-2 text-sm text-blue-800">
                <li>✓ Klik <strong>"📹 Monitor Sekarang"</strong> pada ujian yang sedang berlangsung untuk melihat aktivitas siswa secara real-time</li>
                <li>✓ Di dashboard monitoring, Anda dapat melihat progres setiap siswa, aktivitas keyboard, dan pelanggaran</li>
                <li>✓ Gunakan tombol <strong>"⏹️ Hentikan"</strong> untuk menghentikan ujian siswa jika diperlukan</li>
                <li>✓ Gunakan tombol <strong>"🚪 Logout"</strong> untuk mengeluarkan siswa dari ruang ujian</li>
                <li>✓ Semua aksi monitoring dicatat dalam log audit untuk keamanan dan transparansi</li>
            </ul>
        </div>
    </div>
@endsection
