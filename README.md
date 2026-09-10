# Maliki Katalog

Aplikasi **E-Katalog** adalah platform katalog produk digital dengan tampilan minimalis **putih/hitam** (tergantung mode) dan aksen **biru**, di mana perusahaan dapat menampilkan produknya yang terhubung langsung ke berbagai marketplace (Siplah, Tokoladang, GratisOngkir, dll). Member dapat menjelajah, memberi rating, menyimpan produk favorit, serta menerima promo dan rekomendasi produk melalui WhatsApp.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Proyek](#struktur-proyek)
- [Skema Database](#skema-database)
- [Data Flow Diagram (DFD)](#data-flow-diagram-dfd)
- [Alur Pengguna (Member/Pengunjung)](#alur-pengguna-memberpengunjung)
- [Alur Admin](#alur-admin)
- [Entity Relationship Diagram (ERD)](#entity-relationship-diagram-erd)
- [Cara Install](#cara-install)
- [Akun Awal (Seeder)](#akun-awal-seeder)
- [Konfigurasi Eksternal](#konfigurasi-eksternal)
- [Alur Sortir Produk](#alur-sortir-produk-prioritas)
- [Roadmap Pengembangan](#roadmap-pengembangan)
- [Keamanan](#keamanan)
- [Lisensi](#lisensi)

---

## Fitur Utama

1. **Tampilan UI/UX minimalis** — topbar putih dengan search bar besar, grid produk responsive, card produk dengan harga & rating, aksen biru.
2. **Tombol menuju marketplace lain** — setiap produk memiliki tombol di bawah gambar untuk redirect ke Siplah, Tokoladang, GratisOngkir, dan platform lainnya.
3. **Responsive untuk mobile** — grid 2 kolom di mobile, hingga 5 kolom di desktop.
4. **Login dengan Google Auth** (OAuth 2.0).
5. **2 Role pengguna** — `member` dan `admin`.
6. **Filter platform marketplace** — katalog dapat disaring berdasarkan marketplace tujuan.
7. **Gambar produk maksimal 4** per produk.
8. **Sistem rating** — member dapat memberi rating 1–5 bintang + komentar.
9. **Katalog adaptif** — daftar produk menyesuaikan kebiasaan user (sering dilihat/dicari/diklik) plus produk yang dipromosikan.
10. **Prioritas sorting produk** — promo > kebiasaan user > rating > produk terbaru.
11. **Fitur manajemen perusahaan** — setiap perusahaan memiliki produk sendiri.
12. **Kode KBLI** — setiap perusahaan dapat memiliki beberapa kode KBLI.
13. **Kategori produk berdasar KBLI** — produk dikategorikan sesuai klasifikasi baku lapangan usaha.
14. **Profil member dan perusahaan**.
15. **Akses member** — khusus memberi rating dan menyimpan daftar produk favorit.
16. **Profil member lengkap dengan nomor WhatsApp**.
17. **Kirim promo & rekomendasi produk via WA** menggunakan **API WAAJO**.
18. **Fitur lupa password**.
19. **Menu setting aplikasi** — dark mode, akun & profil, profil perusahaan.
20. **Banner promosi** di sela-sela daftar produk.
21. **Halaman Etalase (CV) Perusahaan** — profil perusahaan + baris rekomendasi, filter kategori & urutan, daftar produk, dan tombol **Hubungi** yang mengirim pesan ke nomor WhatsApp pemilik via **API WAAJO** (menampilkan alert bila WAAJO belum dikonfigurasi).

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | PHP dengan framework **CodeIgniter 3** |
| Database | MySQL (MariaDB) |
| Otentikasi | Google OAuth 2.0 + login/register manual |
| Integrasi WhatsApp | API **WAAJO** |
| Frontend | HTML, CSS, JavaScript (Vanilla + Bootstrap), jQuery |
| Penyimpanan Gambar | Folder server (`assets/uploads`) |

---

## Struktur Proyek

```
katalog/
├── application/
│   ├── config/            # database.php, routes.php, google.php, waajo.php
│   ├── controllers/       # Home, Auth, Catalog, Product, Company, Profile,
│   │                      # Favorite, Rating, Setting, Wa
│   │   └── admin/         # Dashboard, Users, Companies, Products, Kbli,
│   │                      # Platforms, Banners, Settings
│   ├── models/            # User_m, Company_m, Kbli_m, Product_m, Platform_m,
│   │                      # Rating_m, Favorite_m, Banner_m, Behavior_m, Wa_m
│   ├── views/             # templates, auth, catalog, product, company,
│   │                      # profile, settings, admin
│   ├── libraries/         # GoogleAuth.php, Waajo.php, Sorter.php
│   └── helpers/
├── assets/
│   ├── css/               # style (putih/hitam minimalis + dark mode)
│   ├── js/
│   ├── images/
│   └── uploads/           # gambar produk, banner, logo perusahaan
├── system/                # Core CodeIgniter 3
└── index.php
```

---

<details>
<summary><strong>Skema Database (Ringkasan)</strong></summary>

- **users** — google_id, name, email, password, avatar, role (admin/member), wa_number, is_active
- **companies** — user_id (pemilik), name, npwp, description, logo, address, city, is_active
- **kbli** — code, name
- **company_kbli** — company_id, kbli_id (perusahaan punya banyak KBLI)
- **categories** — name, slug, sort_order, is_active (kategori produk berbasis KBLI)
- **category_kbli** — category_id, kbli_id (relasi kategori ↔ KBLI; diatur sekali oleh admin, produk otomatis masuk kategori lewat KBLI-nya)
- **platforms** — name, slug, url, logo (Siplah, Tokoladang, GratisOngkir, dll)
- **products** — company_id, name, price, unit, description, avg_rating, rating_count, is_promo, promo_price, is_featured, total_views, is_active
- **product_kbli** — product_id, kbli_id (kategori berdasar KBLI)
- **product_images** — product_id, filename, position (maksimal 4)
- **product_platforms** — product_id, platform_id, product_url (tombol redirect)
- **ratings** — product_id, user_id, rating (1–5), comment
- **favorites** — user_id, product_id (daftar produk favorit)
- **banners** — title, image, url, position, sort_order, is_active
- **user_behaviors** — user_id, product_id, behavior_type (view/search/click), platform_id, created_at
- **search_logs** — user_id, keyword
- **password_resets** — email, token, expires_at
- **wa_logs** — user_id, phone, message_type (promo/rekomendasi), status, response
- **settings** — key, value (konfigurasi global aplikasi)

</details>

---

<details>
<summary><strong>Data Flow Diagram (DFD)</strong></summary>

DFD Level 1 menggambarkan alur data utama antara aktor (Member, Admin, Perusahaan/Marketplace) dengan proses-proses inti aplikasi.

```
┌───────────────────────────────────────────────────────────────────────────────┐
│                                    AKTOR                                      │
├───────────────┬───────────────┬───────────────┬───────────────────────────────┤
│    MEMBER     │     ADMIN     │  PERUSAHAAN   │         MARKETPLACE           │
└───────────────┴───────────────┴───────────────┴───────────────────────────────┘

PROSES 1.0 AUTENTIKASI
┌───────────────┐   ┌─────────────────────────────┐   ┌───────────────┐
│    MEMBER     │──▶│ 1.0 Autentikasi             │──▶│  D1 users     │
└───────────────┘   │ Login Google / Manual       │   └───────────────┘
                    │ Lupa Password               │
                    └─────────────────────────────┘

PROSES 2.0 KELOLA MASTER DATA
┌───────────────┐   ┌─────────────────────────────┐
│     ADMIN     │──▶│ 2.0 Kelola Master Data      │──▶ D2 companies
│  PERUSAHAAN   │──▶│ KBLI, Platform, Perusahaan, │──▶ D3 kbli
└───────────────┘   │ Produk, Banner,             │──▶ D4 platforms
                    │ Konfigurasi Aplikasi        │──▶ D5 products
                    └─────────────────────────────┘──▶ D10 banners
                                                      D12 settings

PROSES 3.0 KATALOG & SORTIR
┌───────────────┐   ┌─────────────────────────────┐   ┌───────────────┐
│    MEMBER     │──▶│ 3.0 Katalog & Sortir        │──▶│  D5 products  │
│  MARKETPLACE  │──▶│ Cari, Filter, Sortir        │──▶│  D4 platforms │
└───────────────┘   │ (Promo > Kebiasaan >        │──▶│  D10 banners  │
                    │  Rating > Baru)             │──▶│  D8 behavior  │
                    └─────────────────────────────┘

PROSES 4.0 RATING & FAVORIT
┌───────────────┐   ┌─────────────────────────────┐   ┌───────────────┐
│    MEMBER     │──▶│ 4.0 Rating & Favorit        │──▶│  D6 ratings   │
└───────────────┘   │ Beri rating & komentar      │──▶│  D7 favorites │
                    │ Simpan produk favorit       │──▶│  D5 products  │
                    └─────────────────────────────┘   (perbarui avg_rating)

PROSES 5.0 PERSONALISASI
┌───────────────┐   ┌─────────────────────────────┐   ┌─────────────────┐
│    MEMBER     │──▶│ 5.0 Personalisasi           │──▶│  D8 behavior    │
└───────────────┘   │ Tracking Perilaku,          │──▶│  D9 search_logs │
                    │ Rekomendasi Produk          │   └─────────────────┘
                    └─────────────────────────────┘
                    (kirim skor preferensi ke 3.0)

PROSES 6.0 PROMO VIA WHATSAPP
┌───────────────┐   ┌─────────────────────────────┐   ┌───────────────┐
│    MEMBER     │──▶│ 6.0 Promo via WhatsApp      │──▶│  D11 wa_logs  │
└───────────────┘   │ API WAAJO                   │   └───────────────┘
                    └─────────────────────────────┘
                    (kirim pesan WA ke Member)

PROSES 7.0 ETALASE & HUBUNGI PERUSAHAAN
┌───────────────┐   ┌─────────────────────────────┐   ┌───────────────┐
│  USER/ADMIN   │──▶│ 7.0 Etalase Perusahaan      │──▶│  D2 companies │
│  (pengunjung) │   │ Baris rekomendasi, filter,  │──▶│  D5 products  │
└───────────────┘   │ urutan, daftar produk       │   └───────────────┘
                    └─────────────────────────────┘
                    (tombol "Hubungi" → kirim pesan
                     ke WA pemilik via API WAAJO;
                     alert jika WAAJO belum aktif)
```

</details>

---

## Alur Pengguna (Member/Pengunjung)

1. Membuka katalog → mencari/memfilter/sortir produk.
2. Membuka **detail produk** → galeri, harga, tombol marketplace, rating/ulasan, produk terkait, info perusahaan.
3. Dari card perusahaan di halaman detail, menekan **"Lihat Etalase"** → masuk ke halaman **Etalase (CV) Perusahaan** (`/company/{id}/cv`):
   - **Profil perusahaan** (logo, nama, kota, NPWP, deskripsi, alamat).
   - **Baris Rekomendasi** — baris produk scrollable berisi produk dengan kategori yang sama dengan produk yang terakhir dikunjungi pengguna. Bila halaman diakses langsung (bukan dari detail), tampil kategori pertama yang punya produk.
   - **Filter kategori & urutan** — berada di bawah baris rekomendasi.
   - **Daftar semua produk** — mengikuti filter kategori & urutan yang dipilih.
4. Tombol **"Hubungi"** di card profil → form kecil (nama, nomor WA, pesan) → dikirim ke nomor WhatsApp pemilik perusahaan via API WAAJO. Bila WAAJO belum dikonfigurasi, muncul alert "Fitur WhatsApp belum dapat digunakan".
5. Member dapat memberi rating, menyimpan favorit, mengelola profil, dan menerima promo/rekomendasi via WhatsApp.

---

## Alur Admin

1. Login sebagai admin → dashboard.
2. Mengelola master data: KBLI, kategori, platform marketplace, perusahaan, produk + gambar (maks. 4), banner, pengguna, dan pengaturan aplikasi.
3. Mengirim **promo** ke seluruh member dan **rekomendasi** personal ke member tertentu via API WAAJO, serta memantau log pengiriman (`wa_logs`).
4. Menonaktifkan perusahaan/produk yang tidak layak tampil di katalog.

---

<details>
<summary><strong>Entity Relationship Diagram (ERD)</strong></summary>

```
 ENTITAS                 RELASI                                 ENTITAS
────────────────────────────────────────────────────────────────────────
 users  (1) ──────────────< memiliki >──────────────── (N) companies
 companies (1) ──────────< mempunyai >─────────────── (N) company_kbli
 kbli (1) ───────────────< dipetakan ke >──────────── (N) company_kbli
 companies (1) ──────────< memiliki >──────────────── (N) products
 products (1) ───────────< dikategorikan oleh >────── (N) product_kbli
 kbli (1) ───────────────< dipetakan ke >──────────── (N) product_kbli
 products (1) ───────────< memiliki >──────────────── (N) product_images
 products (1) ───────────< terhubung ke >──────────── (N) product_platforms
 platforms (1) ──────────< menampung >─────────────── (N) product_platforms
 products (1) ───────────< menerima >──────────────── (N) ratings
 users (1) ──────────────< memberi >───────────────── (N) ratings
 users (1) ──────────────< menyimpan >─────────────── (N) favorites
 products (1) ───────────< disimpan sebagai >──────── (N) favorites
 users (1) ──────────────< menghasilkan >──────────── (N) user_behaviors
 products (1) ───────────< direkam >───────────────── (N) user_behaviors
 users (1) ──────────────< membuat >───────────────── (N) search_logs
 users (1) ──────────────< meminta >───────────────── (N) password_resets
 users (1) ──────────────< menerima >──────────────── (N) wa_logs
```

**Struktur Tabel:**

| Tabel | Kolom |
|---|---|
| **users** | `id` PK, google_id, name, email, password, avatar, role (admin/member), wa_number, is_active |
| **companies** | `id` PK, user_id FK → users.id, name, npwp, description, logo, address, city, is_active |
| **kbli** | `id` PK, code, name |
| **company_kbli** | company_id FK → companies.id, kbli_id FK → kbli.id |
| **categories** | `id` PK, name, slug, sort_order, is_active |
| **category_kbli** | category_id FK → categories.id, kbli_id FK → kbli.id |
| **platforms** | `id` PK, name, slug, url, logo |
| **products** | `id` PK, company_id FK → companies.id, name, price, unit, description, avg_rating, rating_count, is_promo, promo_price, is_featured, total_views, is_active |
| **product_kbli** | product_id FK → products.id, kbli_id FK → kbli.id |
| **product_images** | `id` PK, product_id FK → products.id, filename, position |
| **product_platforms** | `id` PK, product_id FK → products.id, platform_id FK → platforms.id, product_url |
| **ratings** | `id` PK, product_id FK → products.id, user_id FK → users.id, rating (1–5), comment |
| **favorites** | `id` PK, user_id FK → users.id, product_id FK → products.id |
| **banners** | `id` PK, title, image, url, position, sort_order, is_active |
| **user_behaviors** | `id` PK, user_id FK → users.id, product_id FK → products.id, behavior_type (view/search/click), platform_id FK → platforms.id, created_at |
| **search_logs** | `id` PK, user_id FK → users.id, keyword |
| **password_resets** | `id` PK, email, token, expires_at |
| **wa_logs** | `id` PK, user_id FK → users.id, phone, message_type (promo/rekomendasi), status, response |
| **settings** | `id` PK, key, value |

</details>

---

## Cara Install

### Prasyarat

- XAMPP (PHP 5.6+ / 7.x, MySQL) — sudah tersedia di `D:\xampp`
- Composer (opsional, untuk dependency Google Client)
- Client ID/Secret Google OAuth (placeholder di `application/config/google.php`)
- API key WAAJO (placeholder di `application/config/waajo.php`)

### Langkah Instalasi

1. **Letakkan proyek** di folder htdocs:
   ```
   D:\xampp\htdocs\katalog
   ```

2. **Buat database** `ekatalog` di phpMyAdmin / MySQL, lalu import file `database.sql`.

3. **Konfigurasi database** — edit `application/config/database.php`:
   ```php
   $db['default']['hostname'] = 'localhost';
   $db['default']['username'] = 'root';
   $db['default']['password'] = '';
   $db['default']['database'] = 'ekatalog';
   ```

4. **Konfigurasi Google OAuth** — isi `application/config/google.php`:
   ```php
   $config['google_client_id']     = 'YOUR_GOOGLE_CLIENT_ID';
   $config['google_client_secret'] = 'YOUR_GOOGLE_CLIENT_SECRET';
   $config['google_redirect_uri']  = 'http://localhost/katalog/auth/google_callback';
   ```

5. **Konfigurasi WAAJO** — isi `application/config/waajo.php`:
   ```php
   $config['waajo_api_key']  = 'YOUR_WAAJO_API_KEY';
   $config['waajo_base_url'] = 'https://app.waajo.com/api/v1';
   $config['waajo_device']   = 'YOUR_DEVICE_ID';
   ```

6. **Atur base_url** — edit `application/config/config.php`:
   ```php
   $config['base_url'] = 'http://localhost/katalog/';
   ```

7. **Hak akses folder upload** — pastikan `assets/uploads/` dapat ditulis.

8. **Akses aplikasi** di browser:
   ```
   http://localhost/katalog/
   ```

---

## Akun Awal (Seeder)

| Role | Email | Password |
|---|---|---|
| Admin | `admin@ekatalog.test` | `admin123` |
| Member | `member@ekatalog.test` | `member123` |

---

## Konfigurasi Eksternal (Placeholder)

| Kredensial | Lokasi Config | Keterangan |
|---|---|---|
| Google Client ID / Secret | `application/config/google.php` | Wajib untuk fitur login Google |
| WAAJO API Key | `application/config/waajo.php` | Wajib untuk kirim promo/rekomendasi dan tombol **Hubungi** perusahaan |

---

## Alur Sortir Produk (Prioritas)

1. **Promo** — produk dengan `is_promo = 1` dan harga promo aktif.
2. **Kebiasaan user** — skor preferensi dari riwayat `user_behaviors` (dilihat/dicari/diklik) per kategori KBLI.
3. **Rating** — nilai `avg_rating` dan jumlah rating.
4. **Produk terbaru** — berdasarkan `created_at`.

---

## Roadmap Pengembangan

- [ ] **Fase 1 — Setup & Auth**: Instalasi CI3, database, Google Auth, register/login, lupa password
- [ ] **Fase 2 — Master Data**: CRUD KBLI, platform, perusahaan, produk + gambar, banner
- [ ] **Fase 3 — Katalog & UI**: Tampilan minimalis putih/hitam + dark mode, responsive, filter, sort, search
- [ ] **Fase 4 — Fitur Member**: Rating, favorit, profil + nomor WA, settings dark mode
- [ ] **Fase 5 — Personalization**: Tracking perilaku user, katalog adaptif, bobot sorting
- [ ] **Fase 6 — Integrasi WAAJO**: Kirim promo/rekomendasi via WhatsApp
- [ ] **Fase 7 — Finalisasi**: Polish mobile, testing, siap deploy

---

## Keamanan

- Session & CSRF protection aktif
- Password di-hash dengan `password_hash()`
- Query Builder (anti SQL injection)
- XSS filter pada input
- Validasi upload gambar (tipe, ukuran, rename)
- Pembatasan akses berbasis role (admin/member)

---
