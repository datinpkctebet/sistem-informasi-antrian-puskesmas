<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftaranQueueModel extends Model
{
    protected $table            = 'queue_kiosk';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'kategori', 'nomor_antrian', 'full_number', 'nama_pasien',
        'status', 'loket', 'petugas_id', 'tanggal',
        'waktu_masuk', 'waktu_panggil', 'waktu_selesai',
        'is_warning', 'last_called_at', 'call_count',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'kategori'      => 'required|in_list[A,B,C,D,E]',
        'nomor_antrian' => 'required|integer',
        'tanggal'       => 'required|valid_date',
    ];

    /**
     * Daftar kategori pelayanan kiosk pendaftaran
     */
    public static function daftarKategori(): array
    {
        return [
            'A' => [
                'nama' => 'KLASTER IBU DAN ANAK',
                'sub'  => ['Ibu Hamil', 'Ibu Bersalin', 'Ibu Nifas', 'Kontrol Bayi', 'MTBS', 'Usia Sekolah & Remaja'],
            ],
            'B' => [
                'nama' => 'KLASTER USIA DEWASA & LANSIA',
                'sub'  => ['Usia Dewasa 18 s.d < 60 th', 'Lansia ≥ 60 th'],
            ],
            'C' => [
                'nama' => 'PRIORITAS (DENGAN KEBUTUHAN KHUSUS)',
                'sub'  => ['Disabilitas', 'Dengan Kendala Bahasa', 'Check-in Online'],
            ],
            'D' => [
                'nama' => 'INFORMASI / PPIP',
                'sub'  => ['Petugas Pemberi Informasi Layanan'],
            ],
            'E' => [
                'nama' => 'KASIR',
                'sub'  => [],
            ],
        ];
    }

    /**
     * Generate & simpan nomor antrian baru untuk kategori tertentu.
     * Reset otomatis tiap hari berdasarkan kolom `tanggal`.
     */
    public function generateNomor(string $kategori): array
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        $db->transStart();

        // Lock baris kategori+tanggal yang sama untuk hindari nomor ganda
        $last = $db->table($this->table)
                   ->where('kategori', $kategori)
                   ->where('tanggal', $today)
                   ->orderBy('nomor_antrian', 'DESC')
                   ->limit(1)
                   ->get(0, 0, false)
                   ->getRowArray();

        $nomorBaru = $last ? ((int) $last['nomor_antrian'] + 1) : 1;
        $fullNumber = $kategori . str_pad((string) $nomorBaru, 4, '0', STR_PAD_LEFT); // contoh: B0184

        $this->insert([
            'kategori'      => $kategori,
            'nomor_antrian' => $nomorBaru,
            'full_number'   => $fullNumber,
            'status'        => 'waiting',
            'tanggal'       => $today,
            'waktu_masuk'   => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Gagal generate nomor antrian, silakan coba lagi.');
        }

        $sisaSebelum = $nomorBaru - 1;

        return [
            'kategori'     => $kategori,
            'nomor_antrian'=> $nomorBaru,
            'full_number'  => $fullNumber,
            'sisa_antrian' => $sisaSebelum,
        ];
    }
}