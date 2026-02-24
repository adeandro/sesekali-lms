@extends('layouts.app')

@section('title', 'Subject Management - SesekaliCBT')

@section('page-title', 'Subject Management')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Subject Management</h2>
            <a href="{{ route('admin.subjects.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Add Subject
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

        <!-- Subjects Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Questions</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $subject->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $subject->questions_count }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="display: inline;" onclick="return confirm('Delete subject?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                No subjects found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $subjects->links() }}
        </div>
    </div>
@endsection
