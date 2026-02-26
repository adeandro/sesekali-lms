@extends('layouts.app')

@section('title', 'Kelola Token Ujian - SesekaliCBT')

@section('page-title', 'Kelola Token Ujian Global')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Kelola Token Ujian</h2>
                <p class="text-gray-600 text-sm mt-2">
                    Sistem Token Global: <span class="font-semibold">1 Ujian = 1 Token</span>
                </p>
                <div class="mt-3 grid grid-cols-3 gap-4">
                    <div class="bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                        <p class="text-xs text-gray-600">Status Token</p>
                        <p class="text-2xl font-bold text-green-600">{{ $exams->count() }}</p>
                    </div>
                    <div class="bg-blue-50 px-4 py-3 rounded-lg border border-blue-200">
                        <p class="text-xs text-gray-600">Ujian Aktif</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $exams->where('status', 'published')->count() }}</p>
                    </div>
                    <div class="bg-yellow-50 px-4 py-3 rounded-lg border border-yellow-200">
                        <p class="text-xs text-gray-600">Status Draft</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $exams->where('status', '!=', 'published')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($message = Session::get('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="text-lg">✅</span>
                <span>{{ $message }}</span>
            </div>
            <button type="button" class="text-green-800 hover:text-green-600 text-xl" onclick="this.parentElement.style.display='none';">×</button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <span>{{ $message }}</span>
            </div>
            <button type="button" class="text-red-800 hover:text-red-600 text-xl" onclick="this.parentElement.style.display='none';">×</button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('admin.tokens.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Cari Ujian</label>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Nama ujian atau token..." 
                    value="{{ request('search') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status Token</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Ujian</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Token Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Belum Ada Token</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    🔎 Cari
                </button>
                <a href="{{ route('admin.tokens.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    ↺
                </a>
            </div>
        </form>
    </div>

    <!-- Exams Table with Tokens -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($exams->count() > 0)
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-800 to-gray-900 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold">Ujian</th>
                        <th class="px-6 py-4 text-left text-sm font-bold">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-bold">Token</th>
                        <th class="px-6 py-4 text-left text-sm font-bold">Waktu Refresh</th>
                        <th class="px-6 py-4 text-left text-sm font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($exams as $exam)
                        <tr class="hover:bg-gray-50 transition">
                            <!-- Exam Info -->
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $exam->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $exam->subject->name ?? 'N/A' }} • {{ $exam->duration_minutes ?? 0 }} menit</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        📅 {{ $exam->start_time?->format('d M Y H:i') ?? 'Belum dijadwalkan' }}
                                    </p>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    @if($exam->status === 'published')
                                        bg-green-100 text-green-800
                                    @elseif($exam->status === 'ongoing')
                                        bg-blue-100 text-blue-800
                                    @elseif($exam->status === 'finished')
                                        bg-gray-100 text-gray-800
                                    @else
                                        bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    @php
                                        $statusLabel = [
                                            'draft' => '📝 Draft',
                                            'published' => '🟢 Published',
                                            'ongoing' => '⏱️ Ongoing',
                                            'finished' => '✅ Finished'
                                        ][$exam->status] ?? 'N/A';
                                    @endphp
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <!-- Token Display -->
                            <td class="px-6 py-4">
                                @if($exam->token && $exam->status === 'published')
                                    <div class="space-y-2">
                                        <div class="bg-gray-100 px-4 py-3 rounded-lg border-2 border-gray-300">
                                            <p class="text-xs text-gray-600 font-semibold mb-1">TOKEN AKTIF</p>
                                            <p class="font-mono font-bold text-lg text-blue-600 tracking-widest">{{ $exam->token }}</p>
                                        </div>
                                        
                                        <!-- Token Info -->
                                        <div class="text-xs text-gray-600">
                                            <p>
                                                <span class="font-semibold">Dibuat:</span> 
                                                {{ $exam->token_last_updated?->format('d M H:i') ?? 'Belum diketahui' }}
                                            </p>
                                            <p class="mt-1">
                                                <span class="font-semibold">Berlaku hingga:</span> 
                                                <span class="font-mono">{{ $exam->tokenRefreshTime()?->format('d M H:i') ?? '-' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @elseif($exam->status === 'published')
                                    <div class="bg-yellow-50 px-4 py-3 rounded-lg border border-yellow-200">
                                        <p class="text-xs text-yellow-700">
                                            ⚠️ <strong>Ujian published tapi belum ada token</strong>
                                        </p>
                                        <p class="text-xs text-yellow-600 mt-1">Generate token otomatis saat di-publish</p>
                                    </div>
                                @else
                                    <div class="bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                                        <p class="text-xs text-gray-600">
                                            — Token hanya tersedia saat ujian published
                                        </p>
                                    </div>
                                @endif
                            </td>

                            <!-- Refresh Info -->
                            <td class="px-6 py-4">
                                @if($exam->token && $exam->status === 'published')
                                    <div class="space-y-2">
                                        @if($exam->tokenNeedsRefresh())
                                            <div class="bg-red-50 px-3 py-2 rounded border border-red-200">
                                                <p class="text-xs text-red-700 font-bold">⏰ BUTUH REFRESH</p>
                                                <p class="text-xs text-red-600">Umur: {{ $exam->token_last_updated->diffInMinutes(now()) }} menit</p>
                                            </div>
                                        @else
                                            <div class="bg-green-50 px-3 py-2 rounded border border-green-200">
                                                <p class="text-xs text-green-700 font-bold">⏱️ WAKTU TERSISA</p>
                                                <p class="text-xs text-green-600">{{ $exam->minutesUntilTokenRefresh() }} menit lagi</p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500">—</div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($exam->token && $exam->status === 'published')
                                        <!-- Copy Token -->
                                        <button 
                                            onclick="copyTokenToClipboard('{{ $exam->token }}', '{{ $exam->title }}')"
                                            class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition font-semibold"
                                            title="Salin token ke clipboard"
                                        >
                                            📋 Salin
                                        </button>

                                        <!-- Refresh Token -->
                                        <button 
                                            onclick="refreshTokenModal('{{ $exam->id }}', '{{ $exam->title }}')"
                                            class="px-3 py-2 text-xs bg-orange-100 text-orange-700 rounded hover:bg-orange-200 transition font-semibold"
                                            title="Generate token baru (mengganti yang lama)"
                                        >
                                            🔄 Refresh
                                        </button>

                                        <!-- View Details -->
                                        <a 
                                            href="{{ route('admin.exams.show', $exam->id) }}"
                                            class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition font-semibold"
                                        >
                                            👁️ Detail
                                        </a>
                                    @elseif($exam->status === 'published')
                                        <!-- Generate Token (should auto-generate, but button for safety) -->
                                        <form 
                                            method="POST" 
                                            action="{{ route('admin.exams.generate-token', $exam->id) }}"
                                            onsubmit="return confirm('Generate token baru untuk ujian ini?')"
                                        >
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="px-3 py-2 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition font-semibold"
                                            >
                                                ⚡ Generate Token
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Publish ujian untuk mengaktifkan token</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $exams->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-3xl mb-2">📭</p>
                <p class="text-gray-600 font-semibold">Tidak ada ujian yang ditemukan</p>
            </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
        <div class="flex gap-4">
            <div class="text-2xl">ℹ️</div>
            <div>
                <h3 class="font-bold text-blue-900 mb-2">Informasi Sistem Token Global</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>✅ <strong>1 Ujian = 1 Token Global:</strong> Token otomatis di-generate saat ujian di-publish</li>
                    <li>✅ <strong>Auto-Refresh 20 Menit:</strong> Sistem otomatis regenerate token setiap 20 menit saat ada validasi student</li>
                    <li>✅ <strong>Manual Refresh:</strong> Admin bisa refresh token kapan saja dengan tombol 🔄 Refresh</li>
                    <li>✅ <strong>Token Baru Mengganti Lama:</strong> Saat refresh, token lama tidak valid lagi, token baru berlaku</li>
                    <li>✅ <strong>Session Persistence:</strong> Student validasi token sekali, session bertahan 120 menit</li>
                    <li>✅ <strong>Auto-Clear on Unpublish:</strong> Token dihapus otomatis saat ujian di-draft, student tidak bisa akses</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Refresh Token Modal -->
<div id="refreshTokenModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-900 mb-2">🔄 Refresh Token</h3>
        <p class="text-sm text-gray-600 mb-4">
            Ini akan generate token <strong>baru</strong> dan mengganti token yang lama. Token lama akan tidak valid.
        </p>
        
        <div id="examTitle" class="bg-blue-50 px-4 py-3 rounded-lg mb-4 border border-blue-200">
            <p class="text-sm text-gray-600">Ujian:</p>
            <p class="font-bold text-blue-900" id="examTitleText">—</p>
        </div>

        <div class="flex gap-2">
            <button 
                onclick="closeRefreshModal()" 
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold"
            >
                ❌ Batal
            </button>
            <button 
                onclick="confirmRefreshToken()" 
                class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-semibold"
            >
                ✅ Ya, Refresh
            </button>
        </div>
    </div>
</div>

<script>
let currentRefreshExamId = null;

function copyTokenToClipboard(token, examTitle) {
    navigator.clipboard.writeText(token).then(() => {
        showToast(`✅ Token "${token}" berhasil disalin!`, 'success');
    }).catch(() => {
        showToast('❌ Gagal menyalin token', 'error');
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    
    const toast = document.createElement('div');
    toast.className = `p-4 rounded-lg text-white font-semibold shadow-lg animate-slideIn ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.textContent = message;
    
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed bottom-4 right-4 space-y-2 z-50';
        document.body.appendChild(container);
        container.appendChild(toast);
    } else {
        toastContainer.appendChild(toast);
    }
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function refreshTokenModal(examId, examTitle) {
    currentRefreshExamId = examId;
    document.getElementById('examTitleText').textContent = examTitle;
    document.getElementById('refreshTokenModal').classList.remove('hidden');
}

function closeRefreshModal() {
    document.getElementById('refreshTokenModal').classList.add('hidden');
    currentRefreshExamId = null;
}

function confirmRefreshToken() {
    if (!currentRefreshExamId) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/exams/${currentRefreshExamId}/refresh-token`;
    form.innerHTML = '@csrf';
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
document.getElementById('refreshTokenModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('refreshTokenModal')) {
        closeRefreshModal();
    }
});
</script>

<style>
    table tr:nth-child(even) {
        background-color: #fafafa;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
</style>
@endsection
