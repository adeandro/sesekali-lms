@extends('layouts.app')

@section('title', 'Manajemen Ujian - SesekaliCBT')

@section('page-title', 'Manajemen Ujian')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Manajemen Ujian</h2>
            <a href="{{ route('admin.exams.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Buat Ujian
            </a>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-green-800 hover:text-green-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex justify-between">
                <span>{{ $message }}</span>
                <button type="button" class="text-red-800 hover:text-red-600" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        @endif

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form action="{{ route('admin.exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Cari judul ujian..." 
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div>
                    <select name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Exams Table - Responsive -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Desktop view -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Soal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Waktu Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($exams as $exam)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ Str::limit($exam->title, 20) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ Str::limit($exam->subject->name, 15) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="px-3 py-1 rounded-full text-white text-xs font-semibold bg-blue-500">
                                        Kls {{ $exam->jenjang }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $exam->duration_minutes }}m</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $exam->questions_count }}/{{ $exam->total_questions }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 rounded-full text-white text-xs font-semibold 
                                        {{ $exam->status === 'published' ? 'bg-green-500' : ($exam->status === 'finished' ? 'bg-gray-500' : 'bg-yellow-500') }}">
                                        {{ $exam->status === 'published' ? 'Pub' : ($exam->status === 'finished' ? 'Selesai' : 'Draft') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $exam->start_time->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex gap-1 flex-wrap justify-center">
                                        <a href="{{ route('admin.exams.edit', $exam) }}" class="text-yellow-600 hover:text-yellow-800 text-xs" title="Ubah">✏️</a>
                                        <a href="{{ route('admin.exams.manage-questions', $exam) }}" class="text-blue-600 hover:text-blue-800 text-xs" title="Soal">📋</a>
                                        <a href="{{ route('admin.exams.print-credentials', $exam) }}" target="_blank" class="text-green-600 hover:text-green-800 text-xs" title="Kredensial">🎓</a>
                                        @if($exam->status === 'published')
                                            <a href="{{ route('admin.monitor.exams.index', $exam) }}" class="text-orange-600 hover:text-orange-800 text-xs font-bold" title="Monitor">📹</a>
                                        @endif
                                        @if($exam->status !== 'published' && $exam->canPublish())
                                            <form action="{{ route('admin.exams.publish', $exam) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs" title="Publikasikan">✓</button>
                                            </form>
                                        @endif
                                        @if($exam->status === 'published')
                                            <form action="{{ route('admin.exams.set-to-draft', $exam) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-800 text-xs" title="Draft">↺</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" style="display:inline;" id="deleteExamForm{{ $exam->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="text-red-600 hover:text-red-800 text-xs" onclick="deleteExam('{{ $exam->title }}', {{ $exam->id }})" title="Hapus">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-600">
                                    Tidak ada ujian ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile view -->
            <div class="md:hidden divide-y divide-gray-200">
                @forelse($exams as $exam)
                    <div class="p-4 space-y-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $exam->title }}</h3>
                                <p class="text-xs text-gray-600">{{ $exam->subject->name }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-white text-xs font-semibold 
                                {{ $exam->status === 'published' ? 'bg-green-500' : ($exam->status === 'finished' ? 'bg-gray-500' : 'bg-yellow-500') }}">
                                {{ $exam->status === 'published' ? 'Pub' : ($exam->status === 'finished' ? 'Selesai' : 'Draft') }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                            <div>Kelas: <span class="font-semibold">{{ $exam->jenjang }}</span></div>
                            <div>Durasi: <span class="font-semibold">{{ $exam->duration_minutes }}m</span></div>
                            <div>Soal: <span class="font-semibold">{{ $exam->questions_count }}/{{ $exam->total_questions }}</span></div>
                            <div>Mulai: <span class="font-semibold">{{ $exam->start_time->format('M d') }}</span></div>
                        </div>

                        <div class="flex gap-2 flex-wrap justify-start">
                            <a href="{{ route('admin.exams.edit', $exam) }}" class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs hover:bg-yellow-200">✏️ Ubah</a>
                            <a href="{{ route('admin.exams.manage-questions', $exam) }}" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200">📋 Soal</a>
                            <a href="{{ route('admin.exams.print-credentials', $exam) }}" target="_blank" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">🎓 Kredensial</a>
                            @if($exam->status !== 'published' && $exam->canPublish())
                                <form action="{{ route('admin.exams.publish', $exam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">✓ Pub</button>
                                </form>
                            @endif
                            @if($exam->status === 'published')
                                <form action="{{ route('admin.exams.set-to-draft', $exam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs hover:bg-orange-200">↺ Draft</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" style="display:inline;" id="deleteExamForm{{ $exam->id }}Mobile">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200" onclick="deleteExam('{{ $exam->title }}', {{ $exam->id }})">🗑️ Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-600">
                        Tidak ada ujian ditemukan
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $exams->links() }}
        </div>
    </div>
@endsection
