<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = (int) (session()->get('user_id') ?? 0);

        if (! session()->get('isLoggedIn') || $userId <= 0) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Validasi: user masih ada di database dan masih aktif
        $user = (new UserModel())->select('id, is_aktif')->find($userId);

        if (! $user || (int) $user['is_aktif'] !== 1) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Akun Anda tidak aktif atau telah dihapus.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}