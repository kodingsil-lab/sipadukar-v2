# Standar Event Audit SIPADUKAR

Tanggal: 2026-04-16
Status: Aktif (operasional kampus)

## Tujuan
Dokumen ini menjadi acuan nama event audit trail agar:
- konsisten antar modul,
- mudah dibaca oleh tim non-programmer,
- mudah difilter saat investigasi keamanan.

## Prinsip Penamaan
- Format: kata_kunci_snake_case.
- Gunakan kata kerja yang jelas: tambah, edit, hapus, submit, review, login, logout.
- Hindari data sensitif di deskripsi (password, token, cookie, isi rahasia).
- Untuk login gagal, simpan identity fingerprint (hash pendek), bukan username/email mentah.

## Event Inti (Wajib)

### 1) Autentikasi dan Sesi
- login
- logout
- login_gagal
- login_lockout
- login_reset_attempt

### 2) Manajemen User dan Role
- tambah_user
- edit_user
- hapus_user
- ubah_role_user
- masuk_sebagai
- kembali_admin

### 3) Dokumen dan Review
- tambah_dokumen
- edit_dokumen
- hapus_dokumen
- finalisasi_dokumen
- submit_document_legacy
- resubmit_document_legacy
- review_document_legacy

### 4) Master Data Penting
- tambah_program_studi
- edit_program_studi
- hapus_program_studi
- tambah_upps
- edit_upps
- hapus_upps
- tambah_peraturan
- edit_peraturan
- hapus_peraturan
- tambah_instrumen
- edit_instrumen
- hapus_instrumen
- tambah_lembaga_akreditasi
- edit_lembaga_akreditasi
- hapus_lembaga_akreditasi
- tambah_profil_pt
- edit_profil_pt
- update_pengaturan_aplikasi

### 5) Publish/Unpublish
- publish_prodi_akreditasi
- unpublish_prodi_akreditasi

### 6) Integritas Audit Trail
- hapus_audit_trail
- hapus_audit_trail_gagal

## Struktur Deskripsi yang Disarankan
Setiap event sebaiknya memuat ringkas:
- siapa melakukan,
- objek apa (nama modul/data),
- hasil apa,
- sebelum/sesudah untuk perubahan penting (contoh role user).

Contoh:
- "Perubahan role user dari [dosen] menjadi [kaprodi]"
- "Percobaan login gagal. identity_fp=abc123..."

## Event Sensitif (Prioritas Monitoring)
Event berikut harus dipantau rutin:
- login_gagal
- login_lockout
- ubah_role_user
- masuk_sebagai
- hapus_user
- hapus_dokumen
- unpublish_prodi_akreditasi
- hapus_audit_trail

## Klasifikasi Prioritas Monitoring
- High: login_lockout, ubah_role_user, masuk_sebagai, hapus_audit_trail
- Medium: login_gagal, hapus_user, hapus_dokumen, unpublish_prodi_akreditasi
- Low: edit data non-kritis

## Catatan Operasional
- Untuk tim kampus: fokus dashboard audit pada event High dan Medium.
- Untuk tim teknis: pertahankan nama event di atas agar laporan historis tetap konsisten.
- Jika menambah modul baru, ikuti pola nama event yang sama.
