<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = (int) (session()->get('user_id') ?? 0);

        if (! session()->get('isLoggedIn') || $userId <= 0) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (empty($arguments)) {
            return;
        }

        // Cross-check roles langsung dari database (bukan dari session)
        $dbRoles = (new UserModel())->getRolesByUserId($userId);
        $dbRoleSlugs = array_column($dbRoles, 'slug_role');

        foreach ($arguments as $role) {
            if (in_array($role, $dbRoleSlugs, true)) {
                // Sinkronisasi session jika roles berubah
                if (session()->get('roles') !== $dbRoleSlugs) {
                    session()->set('roles', $dbRoleSlugs);
                }
                return;
            }
        }

        return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}