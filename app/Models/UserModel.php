<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'password',
        'nama',
        'role',
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
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'nama'     => 'required|min_length[3]|max_length[100]',
        'role'     => 'required|in_list[admin,perawat,dokter]',
    ];

    protected $validationMessages = [
        'username' => [
            'required'    => 'Username harus diisi',
            'min_length'  => 'Username minimal 3 karakter',
            'is_unique'   => 'Username sudah digunakan',
        ],
        'nama' => [
            'required'    => 'Nama harus diisi',
            'min_length'  => 'Nama minimal 3 karakter',
        ],
        'role' => [
            'required' => 'Role harus dipilih',
            'in_list'  => 'Role tidak valid',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        
        return $data;
    }

    public function verifyPassword($username, $password)
    {
        $user = $this->where('username', $username)
                     ->where('is_active', 1)
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getUsersByLantai($lantai)
    {
        return $this->where('lantai', $lantai)
                    ->where('is_active', 1)
                    ->findAll();
    }
}