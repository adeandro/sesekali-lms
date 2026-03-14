@extends('layouts.app')
@section('title', 'Buat Season Baru')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.gamification.seasons.index') }}" class="text-gray-400 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-black text-gray-900 uppercase tracking-tight">Buat Season Baru</h1>
    </div>
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 space-y-5">
        <form action="{{ route('admin.gamification.seasons.store') }}" method="POST">
            @csrf
            @include('admin.gamification.seasons._form', ['season' => null])
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-violet-500 text-white font-black text-sm uppercase tracking-widest rounded-2xl shadow hover:shadow-indigo-300/50 transition-all">
                <i class="fas fa-plus mr-2"></i> Buat Season
            </button>
        </form>
    </div>
</div>
@endsection
