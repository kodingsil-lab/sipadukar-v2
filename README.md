# SIPADUKAR v2
Sistem Pengelolaan Dokumen Akreditasi Terpadu

## Seeder Dummy Data

Project ini menyediakan 2 seeder untuk kebutuhan data uji:

1. `DummyFullSeeder`  
2. `DummyCleanupSeeder`

---

## 1) DummyFullSeeder

Seeder ini mengisi data dummy secara cukup lengkap dan aman dijalankan berulang (idempotent).

### Data yang diisi

- role dasar (`admin`, `lpm`, `dekan`, `kaprodi`, `dosen`, `asesor`)
- user dummy:
  - `dummy-admin` (password: `dummy123`) - role Admin
  - `dummy-lpm` (password: `dummy123`) - role LPM  
  - `dummy-dosen` (password: `dummy123`) - role Dosen
- relasi `user_roles`
- data UPPS & Program Studi dummy (via `UppsProgramStudiDummySeeder`)
- kriteria aktif 1-9
- master `jenis_dokumen` dummy:
  - Surat Keputusan (SK)
  - Laporan
  - SOP
  - Formulir
  - Data
  - Dokumen Pendukung
  - Lainnya
- sub bagian dummy per kriteria
- dokumen dummy per sub bagian dengan status sesuai workflow terbaru (`draft`, `perlu_revisi`, `ditolak`, `tervalidasi`) yang terkait `program_studi_id` (kompatibel dengan filter Prodi)
- review dokumen dummy hanya untuk status finalisasi LPM (`perlu_revisi`, `ditolak`, `tervalidasi`)
- riwayat dokumen dummy sesuai perubahan status
- peraturan dummy
- instrumen dummy

### Cara menjalankan

```bash
php spark db:seed DummyFullSeeder
```

---

## 2) DummyCleanupSeeder

Seeder ini untuk membersihkan data dummy yang dibuat oleh `DummyFullSeeder` (versi lama maupun versi full terbaru).

### Data yang dibersihkan

- dokumen dummy
- review & riwayat dokumen terkait dummy
- sub bagian dummy
- peraturan dummy
- instrumen dummy
- user dummy (`username` diawali `dummy-`) dan relasi `user_roles`-nya

### Cara menjalankan

```bash
php spark db:seed DummyCleanupSeeder
```

---

## Rekomendasi Alur Pakai

Jika ingin reset total data dummy lalu generate ulang:

```bash
php spark db:seed DummyCleanupSeeder
php spark db:seed DummyFullSeeder
```

---

## Catatan

- Seeder ini ditujukan untuk **environment development/staging**, bukan production.
- `DummyFullSeeder` dirancang idempotent, jadi aman dipanggil lebih dari sekali.
- Agar data dokumen tampil saat filter Prodi aktif digunakan, pastikan migrasi terbaru sudah dijalankan.
- Status dokumen dummy mengikuti workflow terbaru: finalisasi terpusat di LPM (tanpa review internal Dekan/Kaprodi).

---
