<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $userModel = new UserModel();
        $user = $userModel->getDetailWithRoles($userId);

        if (! $user) {
            return redirect()->to('/dashboard')->with('error', 'Data profil tidak ditemukan.');
        }

        return view('profile/index', [
            'title' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    public function edit()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $userModel = new UserModel();
        $user = $userModel->getDetailWithRoles($userId);

        if (! $user) {
            return redirect()->to('/dashboard')->with('error', 'Data profil tidak ditemukan.');
        }

        return view('profile/edit', [
            'title' => 'Edit Profil Saya',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/dashboard')->with('error', 'Data profil tidak ditemukan.');
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[150]',
            'username'     => 'required|min_length[3]|max_length[100]',
            'email'        => 'required|valid_email|max_length[150]',
            'nip'          => 'permit_empty|max_length[50]',
            'jabatan'      => 'permit_empty|max_length[100]',
            'password'     => 'permit_empty|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Data profil belum valid.');
        }

        $usernameBaru = trim((string) $this->request->getPost('username'));
        $emailBaru = trim((string) $this->request->getPost('email'));

        $cekUsername = $userModel
            ->where('username', $usernameBaru)
            ->where('id !=', $userId)
            ->first();
        if ($cekUsername) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $cekEmail = $userModel
            ->where('email', $emailBaru)
            ->where('id !=', $userId)
            ->first();
        if ($cekEmail) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
        }

        $dataUpdate = [
            'nama_lengkap' => trim((string) $this->request->getPost('nama_lengkap')),
            'username'     => $usernameBaru,
            'email'        => $emailBaru,
            'nip'          => trim((string) $this->request->getPost('nip')),
            'jabatan'      => trim((string) $this->request->getPost('jabatan')),
        ];

        $passwordBaru = (string) ($this->request->getPost('password') ?? '');
        if ($passwordBaru !== '') {
            $dataUpdate['password_hash'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $userModel->update($userId, $dataUpdate);

        session()->set([
            'nama_lengkap' => $dataUpdate['nama_lengkap'],
            'username'     => $dataUpdate['username'],
            'email'        => $dataUpdate['email'],
        ]);

        catat_audit(
            'edit_profil',
            'profil',
            $userId,
            'User memperbarui profil sendiri.'
        );

        return redirect()->to('/profil')->with('success', 'Profil berhasil diperbarui.');
    }
}
