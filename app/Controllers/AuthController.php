<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->verifyPassword($username, $password);

        if ($user) {
            // Set session
            $sessionData = [
                'user_id'    => $user['id'],
                'username'   => $user['username'],
                'nama'       => $user['nama'],
                'role'       => $user['role'],
                'lantai'     => $user['lantai'],
                'isLoggedIn' => true
            ];

            session()->set($sessionData);

            return redirect()->to('/dashboard')->with('success', 'Login berhasil');
        }

        return redirect()->back()->withInput()->with('error', 'Username atau password salah');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Logout berhasil');
    }
}