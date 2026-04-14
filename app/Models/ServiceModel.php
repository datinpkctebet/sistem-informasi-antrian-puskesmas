<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table            = 'services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_antrian',
        'nama_pelayanan',
        'lantai',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'kode_antrian'   => 'required|max_length[5]|is_unique[services.kode_antrian,id,{id}]',
        'nama_pelayanan' => 'required|min_length[3]|max_length[100]',
        'lantai'         => 'required|in_list[1,2_kiri,2_kanan,3]',
    ];

    protected $validationMessages = [
        'kode_antrian' => [
            'required'   => 'Kode antrian harus diisi',
            'max_length' => 'Kode antrian maksimal 5 karakter',
            'is_unique'  => 'Kode antrian sudah digunakan',
        ],
        'nama_pelayanan' => [
            'required'   => 'Nama pelayanan harus diisi',
            'min_length' => 'Nama pelayanan minimal 3 karakter',
        ],
        'lantai' => [
            'required' => 'Lantai harus dipilih',
            'in_list'  => 'Lantai tidak valid',
        ],
    ];

    protected $skipValidation = false;

    public function getByLantai($lantai, $activeOnly = true)
    {
        $builder = $this->where('lantai', $lantai);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        return $builder->orderBy('kode_antrian', 'ASC')->findAll();
    }

    public function getByKode($kode)
    {
        return $this->where('kode_antrian', $kode)->first();
    }

    public function getActiveServices()
    {
        return $this->where('is_active', 1)
                    ->orderBy('lantai', 'ASC')
                    ->orderBy('kode_antrian', 'ASC')
                    ->findAll();
    }

    public function getGroupedByLantai()
    {
        $services = $this->where('is_active', 1)
                         ->orderBy('lantai', 'ASC')
                         ->orderBy('kode_antrian', 'ASC')
                         ->findAll();

        $grouped = [];
        foreach ($services as $service) {
            $lantai = $service['lantai'];
            if (!isset($grouped[$lantai])) {
                $grouped[$lantai] = [];
            }
            $grouped[$lantai][] = $service;
        }

        return $grouped;
    }
}