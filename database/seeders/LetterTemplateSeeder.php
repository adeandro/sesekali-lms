<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LetterTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Surat Keterangan Siswa Aktif',
                'code' => 'SKS-A',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT KETERANGAN SISWA AKTIF</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Yang bertanda tangan di bawah ini, Kepala [nama_sekolah] menerangkan bahwa:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_siswa]</td></tr>
    <tr><td>NIS / NISN</td><td>: [nis] / [nisn]</td></tr>
    <tr><td>Tempat, Tgl Lahir</td><td>: [tempat_lahir], [tanggal_lahir]</td></tr>
    <tr><td>Jenis Kelamin</td><td>: [jenis_kelamin]</td></tr>
    <tr><td>Kelas</td><td>: [kelas]</td></tr>
</table>
<p>Adalah benar-benar siswa aktif di [nama_sekolah] pada Tahun Ajaran [tahun_ajaran].</p>
<p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Surat Keterangan Siswa Lulus',
                'code' => 'SKS-L',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT KETERANGAN KELULUSAN</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Kepala [nama_sekolah] dengan ini menerangkan bahwa:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_siswa]</td></tr>
    <tr><td>NIS / NISN</td><td>: [nis] / [nisn]</td></tr>
    <tr><td>Tempat, Tgl Lahir</td><td>: [tempat_lahir], [tanggal_lahir]</td></tr>
    <tr><td>Kelas</td><td>: [kelas]</td></tr>
</table>
<p>Berdasarkan hasil Rapat Pleno Dewan Guru pada tanggal [tanggal_rapat], siswa tersebut di atas dinyatakan:</p>
<div style="text-align: center; margin: 30px 0; font-size: 24px; font-weight: bold; border: 2px solid #000; padding: 10px;">
    LULUS
</div>
<p>Dari satuan pendidikan [nama_sekolah] Tahun Ajaran [tahun_ajaran].</p>
<p>Demikian surat keterangan ini diberikan untuk dapat dipergunakan sebagai pengganti ijazah sementara.</p>',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Surat Keterangan Pindah Sekolah',
                'code' => 'SKPS',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT KETERANGAN PINDAH SEKOLAH</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Yang bertanda tangan di bawah ini, Kepala [nama_sekolah] menerangkan bahwa:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_siswa]</td></tr>
    <tr><td>NIS / NISN</td><td>: [nis] / [nisn]</td></tr>
    <tr><td>Kelas</td><td>: [kelas]</td></tr>
</table>
<p>Sesuai surat permohonan pindah sekolah oleh orang tua/wali murid tanggal [tanggal_mohon], maka siswa tersebut di atas terhitung mulai tanggal [tanggal_pindah] telah pindah dari [nama_sekolah] ke [sekolah_tujuan] dengan alasan [alasan_pindah].</p>
<p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Surat Izin Siswa',
                'code' => 'SIS',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT IZIN SISWA</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Diberikan izin kepada siswa berikut:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_siswa]</td></tr>
    <tr><td>Kelas</td><td>: [kelas]</td></tr>
</table>
<p>Untuk tidak mengikuti kegiatan belajar mengajar pada tanggal [tanggal_mulai] s/d [tanggal_selesai] dikarenakan [alasan_izin].</p>
<p>Demikian surat izin ini dibuat untuk diketahui dan menjadi maklum bagi Bapak/Ibu Guru pengampu mata pelajaran.</p>',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Surat Panggilan Orang Tua',
                'code' => 'SPOT',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT PANGGILAN ORANG TUA / WALI</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Kepada Yth.<br>Orang Tua/Wali dari [nama_siswa]<br>di Tempat</p>
<p>Dengan hormat, mengharap kehadiran Bapak/Ibu pada:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Hari, Tanggal</td><td>: [hari_panggilan], [tanggal_panggilan]</td></tr>
    <tr><td>Waktu</td><td>: [waktu_panggilan]</td></tr>
    <tr><td>Tempat</td><td>: [ruangan_panggilan]</td></tr>
    <tr><td>Keperluan</td><td>: [keperluan_panggilan]</td></tr>
</table>
<p>Mengingat pentingnya hal tersebut, dimohon Bapak/Ibu hadir tepat pada waktunya. Demikian undangan ini, atas perhatiannya kami ucapkan terima kasih.</p>',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Surat Rekomendasi',
                'code' => 'SR',
                'category' => 'siswa',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT REKOMENDASI</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Yang bertanda tangan di bawah ini, Kepala [nama_sekolah] memberikan rekomendasi kepada:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_siswa]</td></tr>
    <tr><td>NIS / NISN</td><td>: [nis] / [nisn]</td></tr>
    <tr><td>Kelas</td><td>: [kelas]</td></tr>
</table>
<p>Berdasarkan pengamatan kami, siswa tersebut memiliki kualifikasi yang baik dalam bidang [bidang_unggulan]. Oleh karena itu, kami merekomendasikan yang bersangkutan untuk mengikuti [nama_kegiatan].</p>
<p>Demikian surat rekomendasi ini dibuat untuk dipergunakan sebagaimana mestinya.</p>',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Surat Keterangan Guru/Pegawai',
                'code' => 'SKG',
                'category' => 'guru',
                'body' => '<div style="text-align: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">SURAT KETERANGAN GURU / PEGAWAI</h2>
    <p style="margin: 0;">Nomor: [nomor_surat]</p>
</div>
<p>Yang bertanda tangan di bawah ini, Kepala [nama_sekolah] menerangkan bahwa:</p>
<table style="width: 100%; margin: 20px 0;">
    <tr><td style="width: 30%;">Nama Lengkap</td><td>: [nama_guru]</td></tr>
    <tr><td>NIP / NIY</td><td>: [nip_guru]</td></tr>
    <tr><td>Jabatan</td><td>: [jabatan_guru]</td></tr>
</table>
<p>Adalah benar-benar Tenaga Pendidik/Kependidikan di [nama_sekolah] terhitung sejak [tanggal_mulai_tugas] sampai dengan saat ini.</p>
<p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($templates as $template) {
            \App\Models\LetterTemplate::updateOrCreate(
                ['code' => $template['code']],
                $template
            );
        }
    }
}
