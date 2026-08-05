<?php

namespace App\Controllers;

use App\Models\PendaftaranQueueModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class PendaftaranKioskController extends Controller
{
    protected PendaftaranQueueModel $model;

    public function __construct()
    {
        $this->model = new PendaftaranQueueModel();
    }

    /**
     * Halaman utama kiosk pendaftaran - pilih kategori
     */
    public function index()
    {
        $data = [
            'title'    => 'Kiosk Pendaftaran - Puskesmas Tebet',
            'kategori' => PendaftaranQueueModel::daftarKategori(),
        ];

        return view('pendaftaran_kiosk/index', $data);
    }

    /**
     * Endpoint AJAX: ambil nomor antrian baru
     */
    public function ambilAntrian(): ResponseInterface
    {
        $json     = $this->request->getJSON(true);
        $kategori = strtoupper($json['kategori'] ?? '');

        $valid = array_keys(PendaftaranQueueModel::daftarKategori());
        if (! in_array($kategori, $valid, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Kategori tidak valid.',
            ]);
        }

        try {
            $result = $this->model->generateNomor($kategori);

            return $this->response->setJSON([
                'success'       => true,
                'full_number'   => $result['full_number'],
                'kategori'      => $result['kategori'],
                'kategori_nama' => PendaftaranQueueModel::daftarKategori()[$kategori]['nama'],
                'sisa_antrian'  => $result['sisa_antrian'],
                'waktu'         => date('d-m-Y H:i:s'),
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Gagal generate antrian pendaftaran: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem, silakan coba lagi.',
            ]);
        }
    }
}