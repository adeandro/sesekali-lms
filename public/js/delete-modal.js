/**
 * Delete Modal Helper Functions
 * Provides reusable SweetAlert2 confirmations for delete operations
 */

// Show delete confirmation modal
function showDeleteModal(title = 'Hapus Item', message = 'Anda yakin akan menghapus item ini?', confirmCallback = null) {
    Swal.fire({
        title: title,
        html: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        didOpen: (modal) => {
            modal.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    Swal.getConfirmButton().click();
                }
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (confirmCallback && typeof confirmCallback === 'function') {
                confirmCallback();
            }
        }
    });
}

// Delete single item via form
function deleteWithModal(formSelector, title, message) {
    showDeleteModal(title, message, () => {
        const form = document.querySelector(formSelector);
        if (form) {
            form.submit();
        }
    });
    return false;
}

// Delete student with specific info
function deleteStudent(studentName, studentId) {
    showDeleteModal(
        '🗑️ Hapus Siswa',
        `<div class="text-left">
            <p class="mb-2"><strong>Nama:</strong> ${studentName}</p>
            <p class="text-red-600 mt-4">⚠️ Tindakan ini tidak dapat dibatalkan. Semua data siswa akan dihapus.</p>
        </div>`,
        () => {
            document.getElementById(`deleteStudentForm${studentId}`).submit();
        }
    );
    return false;
}

// Delete question with info
function deleteQuestion(questionText, questionId) {
    const preview = questionText.length > 100 ? questionText.substring(0, 100) + '...' : questionText;
    showDeleteModal(
        '🗑️ Hapus Soal',
        `<div class="text-left">
            <p class="mb-3"><strong>Soal:</strong> ${preview}</p>
            <p class="text-red-600 mt-4">⚠️ Tindakan ini tidak dapat dibatalkan.</p>
        </div>`,
        () => {
            document.getElementById(`deleteQuestionForm${questionId}`).submit();
        }
    );
    return false;
}

// Delete exam with info
function deleteExam(examTitle, examId) {
    showDeleteModal(
        '🗑️ Hapus Ujian',
        `<div class="text-left">
            <p class="mb-2"><strong>Ujian:</strong> ${examTitle}</p>
            <p class="text-red-600 mt-4">⚠️ Tindakan ini tidak dapat dibatalkan. Semua data ujian akan dihapus.</p>
        </div>`,
        () => {
            document.getElementById(`deleteExamForm${examId}`).submit();
        }
    );
    return false;
}

// Delete subject with info
function deleteSubject(subjectName, subjectId) {
    showDeleteModal(
        '🗑️ Hapus Mata Pelajaran',
        `<div class="text-left">
            <p class="mb-2"><strong>Mata Pelajaran:</strong> ${subjectName}</p>
            <p class="text-red-600 mt-4">⚠️ Tindakan ini tidak dapat dibatalkan.</p>
        </div>`,
        () => {
            document.getElementById(`deleteSubjectForm${subjectId}`).submit();
        }
    );
    return false;
}

// Delete all items
function confirmDeleteAll(type = 'items', confirmCallback = null) {
    Swal.fire({
        title: '⚠️ Hapus Semua ' + type,
        html: `<div class="text-left">
            <p class="mb-4">Anda akan menghapus SEMUA ${type}.</p>
            <p class="text-red-600 font-semibold">⚠️ Tindakan ini TIDAK DAPAT DIBATALKAN!</p>
            <p class="text-gray-600 text-sm mt-3">Pastikan Anda benar-benar ingin melanjutkan.</p>
        </div>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#991b1b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Semua',
        cancelButtonText: 'Batal',
        didOpen: (modal) => {
            modal.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (confirmCallback && typeof confirmCallback === 'function') {
                confirmCallback();
            }
        }
    });
}

// Delete all students
function confirmDeleteAllStudents() {
    confirmDeleteAll('Siswa', () => {
        document.getElementById('deleteAllStudentsForm').submit();
    });
}

// Delete all questions
function confirmDeleteAllQuestions() {
    confirmDeleteAll('Soal', () => {
        document.getElementById('deleteAllQuestionsForm').submit();
    });
}

// Delete all exams
function confirmDeleteAllExams() {
    confirmDeleteAll('Ujian', () => {
        document.getElementById('deleteAllExamsForm').submit();
    });
}

// Delete all subjects
function confirmDeleteAllSubjects() {
    confirmDeleteAll('Mata Pelajaran', () => {
        document.getElementById('deleteAllSubjectsForm').submit();
    });
}
