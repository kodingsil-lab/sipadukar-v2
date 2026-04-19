<?php

namespace App\Controllers;

use App\Models\AppSettingModel;
use Config\Services;

class PengaturanAplikasiController extends BaseController
{
    private const UPLOAD_DIR = 'uploads/settings';

    public function index()
    {
        $model = new AppSettingModel();
        $settings = $model->getAllAsMap();

        return view('pengaturan/aplikasi', [
            'title'      => 'Pengaturan Aplikasi',
            'settings'   => $settings,
            'timezones'  => $this->getTimezoneOptions(),
            'currentTz'  => (string) ($settings['app_timezone'] ?? (config('App')->appTimezone ?? 'Asia/Jakarta')),
        ]);
    }

    public function update()
    {
        $timezones = $this->getTimezoneOptions();
        $selectedTz = trim((string) $this->request->getPost('app_timezone'));
        if (! in_array($selectedTz, $timezones, true)) {
            return redirect()->back()->withInput()->with('error', 'Zona waktu tidak valid.');
        }

        $rules = [
            'logo_pt'     => 'permit_empty|uploaded[logo_pt]|max_size[logo_pt,2048]|is_image[logo_pt]|mime_in[logo_pt,image/png,image/jpeg,image/webp]',
            'favicon'     => 'permit_empty|uploaded[favicon]|max_size[favicon,1024]|ext_in[favicon,png,ico,webp]|mime_in[favicon,image/png,image/webp,image/x-icon,image/vnd.microsoft.icon]',
        ];

        $logoPt = $this->request->getFile('logo_pt');
        $favicon = $this->request->getFile('favicon');

        $validateFiles = [];
        if ($logoPt && $logoPt->isValid() && ! $logoPt->hasMoved()) {
            $validateFiles['logo_pt'] = $rules['logo_pt'];
        }
        if ($favicon && $favicon->isValid() && ! $favicon->hasMoved()) {
            $validateFiles['favicon'] = $rules['favicon'];
        }

        if (! empty($validateFiles) && ! $this->validate($validateFiles)) {
            return redirect()->back()->withInput()->with('error', 'File logo/favicon tidak valid.');
        }

        $model = new AppSettingModel();
        $updatedBy = (int) (session()->get('user_id') ?? 0);
        $model->setValue('app_timezone', $selectedTz, $updatedBy);

        $storagePath = FCPATH . self::UPLOAD_DIR;
        if (! is_dir($storagePath)) {
            @mkdir($storagePath, 0775, true);
        }

        if ($logoPt && $logoPt->isValid() && ! $logoPt->hasMoved()) {
            $path = $this->storeAsset($logoPt, $storagePath, 'logo-pt');
            if ($path !== null) {
                $model->setValue('logo_header_path', $path, $updatedBy);
            }
        }

        if ($favicon && $favicon->isValid() && ! $favicon->hasMoved()) {
            $path = $this->storeFavicon($favicon, $storagePath);
            if ($path !== null) {
                $model->setValue('favicon_path', $path, $updatedBy);
            }
        }

        catat_audit(
            'update_pengaturan_aplikasi',
            'pengaturan_aplikasi',
            $updatedBy > 0 ? $updatedBy : null,
            'Memperbarui pengaturan aplikasi dan aset tampilan.'
        );

        return redirect()->to('/pengaturan/aplikasi')->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    private function storeAsset($file, string $storagePath, string $prefix): ?string
    {
        try {
            $ext = strtolower((string) $file->getExtension());
            $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move($storagePath, $filename, true);

            return self::UPLOAD_DIR . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function storeFavicon($file, string $storagePath): ?string
    {
        try {
            $ext = strtolower((string) $file->getExtension());

            // Keep .ico untouched for maximum browser compatibility.
            if ($ext === 'ico') {
                return $this->storeAsset($file, $storagePath, 'favicon');
            }

            $filename = 'favicon-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.png';
            $target = rtrim($storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

            if (! $this->saveFaviconOnCanvas($file->getTempName(), $target, 64, 0)) {
                Services::image('gd')
                    ->withFile($file->getTempName())
                    ->resize(64, 64, true, 'auto')
                    ->save($target, 80);
            }

            return self::UPLOAD_DIR . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function saveFaviconOnCanvas(string $sourcePath, string $targetPath, int $size = 64, int $padding = 0): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return false;
        }

        $imageData = @file_get_contents($sourcePath);
        if ($imageData === false) {
            return false;
        }

        $src = @imagecreatefromstring($imageData);
        if (! is_resource($src) && ! ($src instanceof \GdImage)) {
            return false;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($srcW < 1 || $srcH < 1) {
            imagedestroy($src);
            return false;
        }

        $canvas = imagecreatetruecolor($size, $size);
        if (! is_resource($canvas) && ! ($canvas instanceof \GdImage)) {
            imagedestroy($src);
            return false;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

        $bounds = $this->detectOpaqueBounds($src, $srcW, $srcH);
        $srcX = $bounds['x'] ?? 0;
        $srcY = $bounds['y'] ?? 0;
        $cropW = $bounds['w'] ?? $srcW;
        $cropH = $bounds['h'] ?? $srcH;

        $maxContent = max(1, $size - ($padding * 2));
        $scale = min($maxContent / $cropW, $maxContent / $cropH);
        $dstW = max(1, (int) round($cropW * $scale));
        $dstH = max(1, (int) round($cropH * $scale));
        $dstX = (int) floor(($size - $dstW) / 2);
        $dstY = (int) floor(($size - $dstH) / 2);

        imagecopyresampled($canvas, $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $cropW, $cropH);

        $ok = imagepng($canvas, $targetPath, 8);

        imagedestroy($src);
        imagedestroy($canvas);

        return $ok;
    }

    private function detectOpaqueBounds($img, int $width, int $height): ?array
    {
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                // Pixel is visible enough to count as icon content.
                if ($alpha < 120) {
                    if ($x < $minX) {
                        $minX = $x;
                    }
                    if ($y < $minY) {
                        $minY = $y;
                    }
                    if ($x > $maxX) {
                        $maxX = $x;
                    }
                    if ($y > $maxY) {
                        $maxY = $y;
                    }
                }
            }
        }

        if ($maxX < 0 || $maxY < 0) {
            return null;
        }

        return [
            'x' => $minX,
            'y' => $minY,
            'w' => max(1, ($maxX - $minX) + 1),
            'h' => max(1, ($maxY - $minY) + 1),
        ];
    }

    private function getTimezoneOptions(): array
    {
        return [
            'Asia/Jakarta',
            'Asia/Makassar',
            'Asia/Jayapura',
            'UTC',
        ];
    }
}
