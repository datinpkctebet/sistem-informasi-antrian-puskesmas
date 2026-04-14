<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        
        // Get user role
        $userRole = $session->get('role');
        
        // If no arguments, allow all logged in users
        if (empty($arguments)) {
            return;
        }
        
        // Check if user has required role
        $allowedRoles = $arguments;
        
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}