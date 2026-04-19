<?php
/**
 * PUBLIC PORTAL CONFIGURATION REFERENCE
 * 
 * File ini berisi konstanta dan variable yang bisa dikustomisasi
 * Jika ingin mengubah behavior portal, edit di sini
 */

// =====================================================
// CONFIGURATION: JENIS DOKUMEN PENTING
// =====================================================
// Dokumentasi di: beranda(), dokumenPenting()
// 
// Ubah array ini jika ingin dokumen jenis lain dianggap "penting"
// Default: LED, LKPS, Renstra, Statuta, Kurikulum, SK Penting

const JENIS_DOKUMEN_PENTING = [
    'LED',           // Laporan Evaluasi Diri
    'LKPS',          // Laporan Kinerja Perguruan Tinggi
    'Renstra',       // Rencana Strategis
    'Statuta',       // Statuta Institusi
    'Kurikulum',     // Kurikulum Program Studi
    'SK Penting',    // Surat Keputusan Penting
];

// =====================================================
// CONFIGURATION: STATUS DOKUMEN PUBLIC
// =====================================================
// Hanya status ini yang ditampilkan di portal public
// Ubah jika ada status baru yang ingin di-public

const STATUS_DOKUMEN_PUBLIC = 'tervalidasi';
const FLAG_AKTIF = 1;

// =====================================================
// CONFIGURATION: LIMIT DOKUMEN
// =====================================================
// Jumlah dokumen penting yang ditampilkan di beranda
define('LIMIT_DOKUMEN_PENTING_BERANDA', 5);

// Jumlah kategori yang ditampilkan di form pencarian
define('LIMIT_KATEGORI_SEARCH', 50);

// Limit tahun search dropdown
define('LIMIT_TAHUN_SEARCH', 20);

// =====================================================
// CONFIGURATION: STYLING
// =====================================================
// Warna-warna yang digunakan (bisa customize di layouts/public.php)

const THEME_COLORS = [
    'primary'       => '#0d47a1',      // Warna biru utama
    'secondary'     => '#1565c0',      // Gradient secondary
    'success'       => '#4CAF50',      // Warna hijau (valid)
    'warning'       => '#FF9800',      // Warna orange
    'danger'        => '#F44336',      // Warna merah
    'light_bg'      => '#f8f9fa',      // Background light
    'light_border'  => '#e0e0e0',      // Border light
];

// Badge status colors (lihat CSS di layouts/public.php)
const BADGE_COLORS = [
    'tervalidasi' => ['bg' => '#d4edda', 'text' => '#155724'],  // Hijau valid
    'draft'       => ['bg' => '#fff3cd', 'text' => '#856404'],  // Kuning draft
    'revisi'      => ['bg' => '#f8d7da', 'text' => '#721c24'],  // Merah revisi
    'ditolak'     => ['bg' => '#f8d7da', 'text' => '#721c24'],  // Merah tolak
];

// =====================================================
// CONFIGURATION: MENU & NAVIGATION
// =====================================================
// Daftar menu yang tampil di topbar (urutan penting!)

const PUBLIC_MENU = [
    [
        'label'  => 'Beranda',
        'url'    => '/public',
        'icon'   => 'bi-house-door',
        'active' => 'beranda',
    ],
    [
        'label'  => 'Profil',
        'url'    => '/public/profil',
        'icon'   => 'bi-building',
        'active' => 'profil',
    ],
    [
        'label'  => 'Dokumen Kriteria',
        'url'    => '/public/kriteria',
        'icon'   => 'bi-list-check',
        'active' => 'kriteria',
    ],
    [
        'label'  => 'Dokumen Penting',
        'url'    => '/public/dokumen-penting',
        'icon'   => 'bi-star',
        'active' => 'penting',
    ],
    [
        'label'  => 'Pencarian',
        'url'    => '/public/pencarian',
        'icon'   => 'bi-search',
        'active' => 'pencarian',
    ],
];

// =====================================================
// CONFIGURATION: FIELD MAPPING
// =====================================================
// Mapping antara field database dan tampilan di UI
// Jika nama field di DB berubah, update di sini

const FIELD_MAPPING = [
    // Dokumen
    'dokumen_title'        => 'judul_dokumen',
    'dokumen_code'         => 'kode_dokumen',
    'dokumen_type'         => 'jenis_dokumen',
    'dokumen_year'         => 'tahun_dokumen',
    'dokumen_status'       => 'status_dokumen',
    'dokumen_description'  => 'deskripsi',
    'dokumen_source'       => 'sumber_dokumen',
    'dokumen_file_path'    => 'path_file',
    'dokumen_validated_at' => 'tanggal_validasi',
    
    // Kriteria
    'criteria_number'      => 'nomor_kriteria',
    'criteria_name'        => 'nama_kriteria',
    'criteria_code'        => 'kode',
    
    // PT
    'pt_name'              => 'nama_pt',
    'pt_short_name'        => 'nama_singkatan',
    'pt_accreditation'     => 'status_akreditasi_pt',
    'pt_address'           => 'alamat_lengkap',
    'pt_website'           => 'website_resmi',
    'pt_email'             => 'email_resmi_pt',
    'pt_phone'             => 'nomor_telepon',
];

// =====================================================
// CONFIGURATION: TEXT & MESSAGES
// =====================================================
// Pesan-pesan yang tampil di UI (untuk kemudahan terjemahan)

const MESSAGES = [
    'no_data'              => 'Tidak ada data tersedia',
    'no_documents'         => 'Tidak ada dokumen untuk kriteria ini',
    'no_search_results'    => 'Tidak ada dokumen yang cocok dengan kriteria pencarian Anda',
    'start_search'         => 'Mulai Pencarian',
    'use_form_to_search'   => 'Gunakan formulir di atas untuk mencari dokumen akreditasi',
    'total_documents'      => 'Total Dokumen Public',
    'total_criteria'       => 'Total Kriteria',
    'valid_documents'      => 'Dokumen Valid',
    'main_documents'       => 'Dokumen Utama',
    'criteria_progress'    => 'Progres Dokumen Per Kriteria',
    'important_documents'  => 'Dokumen Penting',
    'criteria_list'        => 'Daftar Kriteria',
    'search_documents'     => 'Pencarian Dokumen',
    'institution_profile'  => 'Profil Institusi',
    'accredited_programs'  => 'Program Studi Terakreditasi',
    'accreditation_bodies' => 'Lembaga Akreditasi',
];

// =====================================================
// CONFIGURATION: SECURITY
// =====================================================
// Setting keamanan untuk public portal

const SECURITY_CONFIG = [
    // Ubah true jika ingin logging akses public
    'enable_access_logging'  => false,
    
    // Ubah false jika ingin public bisa download dokumen
    'allow_downloads'        => true,
    
    // Ubah false jika ingin preview di window baru (tidak recommended)
    'preview_target_blank'   => true,
    
    // Jumlah karakter minimum untuk pencarian
    'minimum_search_length'  => 1,
    
    // Rate limiting (opsional, gunakan middleware jika diperlukan)
    'enable_rate_limiting'   => false,
    'rate_limit_per_minute'  => 100,
];

// =====================================================
// CONFIGURATION: PAGINATION
// =====================================================
// Setting untuk pagination (future enhancement)

const PAGINATION_CONFIG = [
    'documents_per_page'      => 20,
    'search_results_per_page' => 25,
    'page_links_count'        => 5,
];

// =====================================================
// HELPER: Get Config Value
// =====================================================
// Fungsi helper untuk akses config di controller/view
// Contoh: getPublicConfig('enable_downloads')

if (!function_exists('getPublicConfig')) {
    function getPublicConfig($key = null)
    {
        $config = [
            'status'              => SECURITY_CONFIG,
            'colors'              => THEME_COLORS,
            'menu'                => PUBLIC_MENU,
            'messages'            => MESSAGES,
            'fields'              => FIELD_MAPPING,
            'dokumen_penting'     => JENIS_DOKUMEN_PENTING,
            'limit_penting'       => LIMIT_DOKUMEN_PENTING_BERANDA,
        ];
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? null;
    }
}

// =====================================================
// CONTOH PENGGUNAAN DI CONTROLLER
// =====================================================
/*

// Di PublicController.php:

// 1. Gunakan konstanta dokumen penting
public function dokumenPenting()
{
    $dokumen = $this->dokumenModel
        ->whereIn('jenis_dokumen', JENIS_DOKUMEN_PENTING)
        ->findAll();
    // ...
}

// 2. Set limit dinamis
public function beranda()
{
    $dokumenPenting = $this->dokumenModel
        ->limit(LIMIT_DOKUMEN_PENTING_BERANDA)
        ->findAll();
    // ...
}

// 3. Gunakan message
public function pencarian()
{
    $data['message'] = MESSAGES['start_search'];
    // ...
}

*/

// =====================================================
// CONTOH PENGGUNAAN DI VIEW
// =====================================================
/*

// Di view file, gunakan HTML Escape:

<h3><?= esc(MESSAGES['criteria_progress']) ?></h3>

// Akses menu dari config:
<?php foreach (PUBLIC_MENU as $menu): ?>
    <a href="<?= $menu['url'] ?>" class="nav-link">
        <i class="bi <?= $menu['icon'] ?>"></i> <?= $menu['label'] ?>
    </a>
<?php endforeach; ?>

// Warna dinamis:
<span style="background-color: <?= THEME_COLORS['primary'] ?>">...</span>

*/

?>
