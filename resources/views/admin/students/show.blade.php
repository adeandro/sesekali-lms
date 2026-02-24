@extends('layouts.app')

@section('title', 'Student Details - SesekaliCBT')

@section('content')
    <div>
        <div class="mb-8">
            <a href="{{ route('admin.students.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Students</a>
            <h2 class="text-3xl font-bold text-gray-900 mt-2">Student Details</h2>
        </div>

        @if ($message = Session::get('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                {{ $message }}
                @if ($password = Session::get('password'))
                    <p class="mt-2 font-mono bg-white p-2 rounded">Password: <strong>{{ $password }}</strong></p>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-8 max-w-2xl">
            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <p class="text-sm text-gray-600">NIS</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->nis }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Class</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->class }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $student->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Joined</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $student->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="flex gap-4 pt-4 border-t">
                <a href="{{ route('admin.students.edit', $student) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Edit
                </a>
                <form action="{{ route('admin.students.resetPassword', $student) }}" method="POST" style="display: inline;" onclick="return confirm('Reset password?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                        Reset Password
                    </button>
                </form>
                <a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">
                    Back
                </a>
            </div>
        </div>
    </div>
@endsection
