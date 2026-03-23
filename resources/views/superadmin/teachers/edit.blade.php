@extends('layouts.app')

@section('title', 'Ubah Guru - SesekaliCBT')

@section('page-title', 'Ubah Guru')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('superadmin.teachers.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-3xl font-bold text-gray-900">Ubah Data Guru</h2>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <form action="{{ route('superadmin.teachers.update', $teacher) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Peran -->
                    <div class="space-y-2 col-span-full">
                        <label for="role" class="block text-sm font-semibold text-gray-700">Peran / Role</label>
                        <select name="role" id="role" required {{ $teacher->role === 'superadmin' ? 'disabled' : '' }}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror disabled:bg-gray-50 disabled:text-gray-500">
                            <option value="teacher" {{ old('role', $teacher->role) == 'teacher' ? 'selected' : '' }}>Guru</option>
                            <option value="principal" {{ old('role', $teacher->role) == 'principal' ? 'selected' : '' }}>Kepala Sekolah</option>
                            <option value="tu" {{ old('role', $teacher->role) == 'tu' ? 'selected' : '' }}>Tata Usaha</option>
                            @if($teacher->role === 'superadmin')
                                <option value="superadmin" selected>Super Admin</option>
                            @endif
                        </select>
                        @if($teacher->role === 'superadmin')
                            <input type="hidden" name="role" value="superadmin">
                            <p class="text-[10px] text-amber-600 font-bold uppercase italic mt-1">Role Super Admin tidak dapat diubah dari sini</p>
                        @endif
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Gelar Depan -->
                    <div class="space-y-2">
                        <label for="title_ahead" class="block text-sm font-semibold text-gray-700">Gelar Depan</label>
                        <input type="text" name="title_ahead" id="title_ahead" value="{{ old('title_ahead', $teacher->title_ahead) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title_ahead') border-red-500 @enderror">
                        @error('title_ahead') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nama -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">Nama Lengkap </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $teacher->name) }}" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Gelar Belakang -->
                    <div class="space-y-2">
                        <label for="title_behind" class="block text-sm font-semibold text-gray-700">Gelar Belakang </label>
                        <input type="text" name="title_behind" id="title_behind" value="{{ old('title_behind', $teacher->title_behind) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title_behind') border-red-500 @enderror">
                        @error('title_behind') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email Utama</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $teacher->email) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- NIP -->
                    <div class="space-y-2">
                        <label for="nip" class="block text-sm font-semibold text-gray-700">NIP</label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip', $teacher->nip) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nip') border-red-500 @enderror">
                        @error('nip') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- NIY -->
                    <div class="space-y-2">
                        <label for="niy" class="block text-sm font-semibold text-gray-700">NIY</label>
                        <input type="text" name="niy" id="niy" value="{{ old('niy', $teacher->niy) }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('niy') border-red-500 @enderror">
                        @error('niy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- NIP/NIS -->
                    <div class="space-y-2">
                        <label for="nis" class="block text-sm font-semibold text-gray-700">Kode Guru / Username</label>
                        <input type="text" name="nis" id="nis" value="{{ old('nis', $teacher->nis) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nis') border-red-500 @enderror">
                        @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="col-span-full space-y-4">
                        <label class="block text-sm font-semibold text-gray-700">Mata Pelajaran yang Diampu</label>
                        
                        <div class="space-y-6 bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                            @php
                                $categories = \App\Models\Subject::categories();
                                $groupedSubjects = $subjects->groupBy('category');
                                $assignedSubjects = old('subject_ids', $teacher->subjects->pluck('id')->toArray());
                            @endphp

                            @foreach($categories as $key => $label)
                                @if(isset($groupedSubjects[$key]))
                                    <div x-data="{ 
                                        selectedCount: {{ count(array_intersect($groupedSubjects[$key]->pluck('id')->toArray(), $assignedSubjects)) }},
                                        totalInCategory: {{ $groupedSubjects[$key]->count() }},
                                        get allSelected() { return this.selectedCount === this.totalInCategory },
                                        toggleAll() {
                                            const checkboxes = $el.closest('.category-group').querySelectorAll('.subject-checkbox');
                                            const shouldCheck = !this.allSelected;
                                            checkboxes.forEach(cb => {
                                                if (cb.checked !== shouldCheck) {
                                                    cb.checked = shouldCheck;
                                                    cb.dispatchEvent(new Event('change'));
                                                }
                                            });
                                        }
                                    }" class="category-group space-y-3">
                                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                            <h4 class="text-[11px] font-black uppercase tracking-widest text-indigo-600">{{ $label }}</h4>
                                            <button type="button" @click="toggleAll" 
                                                class="text-[10px] font-bold text-gray-400 hover:text-indigo-600 transition-colors flex items-center gap-1.5 uppercase tracking-wider">
                                                <i class="fas" :class="allSelected ? 'fa-check-double' : 'fa-plus-circle'"></i>
                                                <span x-text="allSelected ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($groupedSubjects[$key] as $subject)
                                                <label class="relative group cursor-pointer">
                                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                                        class="subject-checkbox hidden"
                                                        @change="selectedCount = $el.closest('.category-group').querySelectorAll('.subject-checkbox:checked').length"
                                                        {{ in_array($subject->id, $assignedSubjects) ? 'checked' : '' }}>
                                                    
                                                    <div class="flex items-center gap-3 p-3 bg-white rounded-xl border-2 border-transparent group-hover:bg-indigo-50/30 transition-all duration-300 peer-checked:bg-white transition-all shadow-sm group-hover:shadow-md"
                                                        :class="$el.previousElementSibling.checked ? 'border-indigo-500 bg-white ring-4 ring-indigo-50 shadow-indigo-100' : 'border-gray-100'">
                                                        <div class="w-5 h-5 rounded-md border-2 border-gray-200 flex items-center justify-center transition-colors overflow-hidden shrink-0"
                                                            :class="$el.previousElementSibling.checked ? 'bg-indigo-500 border-indigo-500' : 'bg-white'">
                                                            <i class="fas fa-check text-white text-[10px] transition-transform duration-300"
                                                                :class="$el.parentElement.previousElementSibling.checked ? 'scale-100' : 'scale-0'"></i>
                                                        </div>
                                                        <span class="text-xs font-bold transition-colors"
                                                            :class="$el.previousElementSibling.previousElementSibling.checked ? 'text-indigo-900' : 'text-gray-600'">
                                                            {{ $subject->name }}
                                                        </span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        
                        @error('subject_ids')
                            <p class="text-red-500 text-xs mt-1 font-medium italic">⚠️ {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (Opsional) -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Ulangi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Status Aktif -->
                <div class="flex items-center gap-2">
                    <input type="hidden" name="status" value="Nonaktif">
                    <input type="checkbox" name="status" id="status" value="Aktif" {{ old('status', $teacher->status) === 'Aktif' ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="status" class="text-sm font-medium text-gray-700">Akun ini aktif</label>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('superadmin.teachers.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold shadow-blue-200 shadow-lg">Perbarui Guru</button>
                </div>
            </form>
        </div>
    </div>
@endsection
