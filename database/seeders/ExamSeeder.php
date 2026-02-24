<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create Pemrograman Web Dasar subject
        $webSubject = Subject::firstOrCreate([
            'name' => 'Pemrograman Web Dasar'
        ]);

        $this->command->info("Using subject: {$webSubject->name}");

        // Create 15 questions for Pemrograman Web Dasar
        $questions = [
            [
                'topic' => 'HTML & CSS Basics',
                'question_text' => 'Apa fungsi utama dari tag &lt;html&gt; dalam dokumen HTML?',
                'option_a' => 'Mendefinisikan style CSS',
                'option_b' => 'Mendefinisikan struktur root dokumen HTML',
                'option_c' => 'Mendefinisikan metada dokumen',
                'option_d' => 'Mendefinisikan bagian body dokumen',
                'option_e' => 'Mendefinisikan heading',
                'correct_answer' => 'b',
                'difficulty_level' => 'easy',
                'explanation' => 'Tag &lt;html&gt; adalah elemen root yang mendefinisikan struktur root dari dokumen HTML.'
            ],
            [
                'topic' => 'HTML & CSS Basics',
                'question_text' => 'Properti CSS mana yang digunakan untuk mengubah warna teks?',
                'option_a' => 'background-color',
                'option_b' => 'text-color',
                'option_c' => 'color',
                'option_d' => 'font-color',
                'option_e' => 'foreground-color',
                'correct_answer' => 'c',
                'difficulty_level' => 'easy',
                'explanation' => 'Properti color digunakan untuk mengubah warna teks dalam CSS.'
            ],
            [
                'topic' => 'HTML & CSS Basics',
                'question_text' => 'Apa perbedaan antara margin dan padding dalam CSS?',
                'option_a' => 'Tidak ada perbedaan',
                'option_b' => 'Margin adalah ruang di dalam elemen, padding adalah ruang di luar',
                'option_c' => 'Padding adalah ruang di dalam elemen, margin adalah ruang di luar',
                'option_d' => 'Margin untuk lebar, padding untuk tinggi',
                'option_e' => 'Keduanya sama-sama untuk ruang horizontal',
                'correct_answer' => 'c',
                'difficulty_level' => 'medium',
                'explanation' => 'Padding adalah ruang di dalam elemen (antara konten dan border), sedangkan margin adalah ruang di luar elemen.'
            ],
            [
                'topic' => 'JavaScript Basics',
                'question_text' => 'Apa fungsi dari method getElementById() dalam JavaScript?',
                'option_a' => 'Mengambil elemen berdasarkan class name',
                'option_b' => 'Mengambil elemen berdasarkan tag name',
                'option_c' => 'Mengambil elemen berdasarkan id',
                'option_d' => 'Mengambil semua elemen di halaman',
                'option_e' => 'Menghapus elemen dari DOM',
                'correct_answer' => 'c',
                'difficulty_level' => 'easy',
                'explanation' => 'getElementById() digunakan untuk memilih elemen HTML berdasarkan atribut id-nya.'
            ],
            [
                'topic' => 'JavaScript Basics',
                'question_text' => 'Variabel mana yang memiliki scope terbatas hanya dalam blok kode yang membuatnya?',
                'option_a' => 'var',
                'option_b' => 'let',
                'option_c' => 'global',
                'option_d' => 'static',
                'option_e' => 'const dan var',
                'correct_answer' => 'b',
                'difficulty_level' => 'medium',
                'explanation' => 'Keyword let memiliki block scope, sedangkan var memiliki function scope.'
            ],
            [
                'topic' => 'HTTP & Web Basics',
                'question_text' => 'Apa kepanjangan dari HTTP?',
                'option_a' => 'Hyper Text Transfer Protocol',
                'option_b' => 'Home Text Transfer Protocol',
                'option_c' => 'Hyper Transfer Text Protocol',
                'option_d' => 'High Technology Transfer Protocol',
                'option_e' => 'Host Text Transfer Protocol',
                'correct_answer' => 'a',
                'difficulty_level' => 'easy',
                'explanation' => 'HTTP singkatan dari HyperText Transfer Protocol, protokol untuk transfer data di web.'
            ],
            [
                'topic' => 'HTTP & Web Basics',
                'question_text' => 'Status code HTTP mana yang menunjukkan bahwa halaman ditemukan dengan sukses?',
                'option_a' => '301',
                'option_b' => '404',
                'option_c' => '200',
                'option_d' => '500',
                'option_e' => '403',
                'correct_answer' => 'c',
                'difficulty_level' => 'easy',
                'explanation' => 'Kode status 200 OK menunjukkan bahwa halaman atau resource ditemukan dan dikembalikan dengan sukses.'
            ],
            [
                'topic' => 'Form & Input',
                'question_text' => 'Apa fungsi atribut method pada tag &lt;form&gt;?',
                'option_a' => 'Menentukan style form',
                'option_b' => 'Menentukan tipe HTTP request (GET atau POST)',
                'option_c' => 'Menentukan destinasi email',
                'option_d' => 'Menentukan validasi input',
                'option_e' => 'Menentukan warna form',
                'correct_answer' => 'b',
                'difficulty_level' => 'medium',
                'explanation' => 'Atribut method menentukan metode HTTP yang digunakan untuk mengirim data form, yaitu GET atau POST.'
            ],
            [
                'topic' => 'Form & Input',
                'question_text' => 'Tipe input HTML mana yang digunakan untuk password?',
                'option_a' => '&lt;input type="hidden"&gt;',
                'option_b' => '&lt;input type="password"&gt;',
                'option_c' => '&lt;input type="secure"&gt;',
                'option_d' => '&lt;input type="encrypted"&gt;',
                'option_e' => '&lt;input type="secret"&gt;',
                'correct_answer' => 'b',
                'difficulty_level' => 'easy',
                'explanation' => 'Tipe input password menyembunyikan karakter yang diketik dengan asterisk atau titik.'
            ],
            [
                'topic' => 'DOM & Events',
                'question_text' => 'Apa yang dimaksud dengan DOM?',
                'option_a' => 'Dynamic Object Model',
                'option_b' => 'Document Object Model',
                'option_c' => 'Data Object Management',
                'option_d' => 'Digital Object Model',
                'option_e' => 'Domain Object Model',
                'correct_answer' => 'b',
                'difficulty_level' => 'easy',
                'explanation' => 'DOM singkatan dari Document Object Model, representasi struktur dokumen dalam bentuk objek yang dapat dimanipulasi.'
            ],
            [
                'topic' => 'DOM & Events',
                'question_text' => 'Event mana yang dipicu ketika user mengklik elemen?',
                'option_a' => 'onhover',
                'option_b' => 'onmouse',
                'option_c' => 'onclick',
                'option_d' => 'onpress',
                'option_e' => 'onevent',
                'correct_answer' => 'c',
                'difficulty_level' => 'easy',
                'explanation' => 'Event onclick dipicu ketika user mengklik elemen dengan mouse.'
            ],
            [
                'topic' => 'Responsive Design',
                'question_text' => 'Apa kepanjangan dari CSS?',
                'option_a' => 'Cascading Structure Sheets',
                'option_b' => 'Cascading Style Sheets',
                'option_c' => 'Computer Style Sheets',
                'option_d' => 'Colored Style Sheets',
                'option_e' => 'Central Style Sheets',
                'correct_answer' => 'b',
                'difficulty_level' => 'easy',
                'explanation' => 'CSS singkatan dari Cascading Style Sheets, digunakan untuk styling dan layout dokumen HTML.'
            ],
            [
                'topic' => 'Responsive Design',
                'question_text' => 'Apa itu media query dalam CSS?',
                'option_a' => 'Query untuk mencari elemen',
                'option_b' => 'Rule untuk mengatur tampilan berdasarkan ukuran layar dan device',
                'option_c' => 'Query untuk database',
                'option_d' => 'Method untuk validasi form',
                'option_e' => 'Teknik untuk kompresi gambar',
                'correct_answer' => 'b',
                'difficulty_level' => 'medium',
                'explanation' => 'Media query memungkinkan kita untuk menerapkan styling yang berbeda berdasarkan karakteristik device seperti ukuran layar.'
            ],
            [
                'topic' => 'Common Errors',
                'question_text' => 'Apa penyebab umum error "404 Not Found" di web?',
                'option_a' => 'Server tidak dapat memproses request',
                'option_b' => 'Database tidak ditemukan',
                'option_c' => 'Resource atau halaman yang diminta tidak ditemukan',
                'option_d' => 'Koneksi internet terputus',
                'option_e' => 'Browser tidak kompatibel',
                'correct_answer' => 'c',
                'difficulty_level' => 'easy',
                'explanation' => 'Error 404 berarti resource atau halaman yang diminta tidak ditemukan di server.'
            ],
            [
                'topic' => 'Web Development Concepts',
                'question_text' => 'Apa perbedaan antara client-side dan server-side?',
                'option_a' => 'Tidak ada perbedaan',
                'option_b' => 'Client-side dijalankan di browser, server-side dijalankan di server',
                'option_c' => 'Client-side lebih cepat dari server-side',
                'option_d' => 'Server-side tidak perlu browser',
                'option_e' => 'Keduanya dijalankan di browser',
                'correct_answer' => 'b',
                'difficulty_level' => 'medium',
                'explanation' => 'Client-side code (HTML, CSS, JS) dijalankan di browser client, sedangkan server-side code dijalankan di server (PHP, Python, etc).'
            ],
            [
                'topic' => 'Web Development Concepts',
                'question_text' => 'Framework web mana yang menggunakan bahasa PHP?',
                'option_a' => 'React',
                'option_b' => 'Vue.js',
                'option_c' => 'Laravel',
                'option_d' => 'Angular',
                'option_e' => 'Next.js',
                'correct_answer' => 'c',
                'difficulty_level' => 'medium',
                'explanation' => 'Laravel adalah framework PHP yang digunakan untuk membangun aplikasi web server-side.'
            ]
        ];

        // Add essay questions
        $essayQuestions = [
            [
                'topic' => 'Web Development',
                'question_type' => 'essay',
                'question_text' => 'Jelaskan perbedaan antara HTML, CSS, dan JavaScript serta peran masing-masing dalam pengembangan web modern.',
                'difficulty_level' => 'medium',
                'explanation' => 'Jawaban harus menjelaskan bahwa HTML adalah struktur, CSS adalah styling, dan JavaScript adalah interaktivitas.'
            ],
            [
                'topic' => 'Web Development',
                'question_type' => 'essay',
                'question_text' => 'Bagaimana cara mengoptimalkan performa website? Jelaskan minimal 5 teknik optimasi.',
                'difficulty_level' => 'hard',
                'explanation' => 'Jawaban harus mencakup teknik seperti minifikasi, caching, lazy loading, CDN, dan image optimization.'
            ],
            [
                'topic' => 'Web Development',
                'question_type' => 'essay',
                'question_text' => 'Apa itu responsive design dan bagaimana cara mengimplementasikannya dengan CSS?',
                'difficulty_level' => 'medium',
                'explanation' => 'Jawaban harus menjelaskan media queries, flexible grids, dan flexible images.'
            ],
            [
                'topic' => 'Web Development',
                'question_type' => 'essay',
                'question_text' => 'Jelaskan konsep MVC (Model-View-Controller) dalam pengembangan web dan berikan contoh implementasinya.',
                'difficulty_level' => 'hard',
                'explanation' => 'Jawaban harus menjelaskan separasi concerns dan bagaimana setiap komponen bekerja.'
            ],
            [
                'topic' => 'Web Development',
                'question_type' => 'essay',
                'question_text' => 'Apa itu API REST dan bagaimana cara membuat endpoint REST yang baik?',
                'difficulty_level' => 'hard',
                'explanation' => 'Jawaban harus mencakup HTTP methods, status codes, dan best practices REST API design.'
            ]
        ];

        // Insert essay questions
        foreach ($essayQuestions as $qData) {
            Question::firstOrCreate(
                [
                    'subject_id' => $webSubject->id,
                    'question_text' => $qData['question_text'],
                ],
                [
                    'topic' => $qData['topic'],
                    'difficulty_level' => $qData['difficulty_level'],
                    'question_type' => 'essay',
                    'explanation' => $qData['explanation'],
                ]
            );
        }

        $this->command->info("Created " . count($essayQuestions) . " essay questions");

        // Combine all arrays to get total
        $allQuestionsData = array_merge($questions, $essayQuestions);

        // Insert multiple choice questions
        foreach ($questions as $qData) {
            Question::firstOrCreate(
                [
                    'subject_id' => $webSubject->id,
                    'question_text' => $qData['question_text'],
                ],
                [
                    'topic' => $qData['topic'],
                    'difficulty_level' => $qData['difficulty_level'],
                    'question_type' => 'multiple_choice',
                    'option_a' => $qData['option_a'],
                    'option_b' => $qData['option_b'],
                    'option_c' => $qData['option_c'],
                    'option_d' => $qData['option_d'],
                    'option_e' => $qData['option_e'],
                    'correct_answer' => $qData['correct_answer'],
                    'explanation' => $qData['explanation'],
                ]
            );
        }

        $this->command->info("Created " . count($questions) . " multiple choice questions");

        // Create exam for Pemrograman Web Dasar
        $exam = Exam::firstOrCreate(
            [
                'title' => 'Ujian Pemrograman Web Dasar'
            ],
            [
                'subject_id' => $webSubject->id,
                'duration_minutes' => 90,
                'total_questions' => count($allQuestionsData),
                'start_time' => Carbon::now()->subDay(),
                'end_time' => Carbon::now()->addDays(30),
                'randomize_questions' => true,
                'randomize_options' => true,
                'show_score_after_submit' => true,
                'status' => 'published',
            ]
        );

        // Get all questions for this subject
        $allQuestions = Question::where('subject_id', $webSubject->id)->get();

        if ($allQuestions->count() > 0) {
            // Attach questions to exam
            $examQuestionIds = $allQuestions->pluck('id')->toArray();

            // Sync to remove old relations and attach new ones
            $exam->questions()->sync($examQuestionIds);

            $this->command->info("Created exam '{$exam->title}' with {$allQuestions->count()} questions");
        } else {
            $this->command->warn("No questions found for subject '{$webSubject->name}'");
        }
    }
}
