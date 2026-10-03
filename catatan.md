# Catatan Pengembangan E-Katalog

Catatan ini menampung setiap catatan selama proses pengembangan aplikasi E-Katalog.
Format: ditambahkan per fase, urutan terbaru di bawah.

---

## Fase 1 — Setup & Auth (selesai)

### Catatan Teknis
1. **Versi PHP XAMPP = 8.0.30**, bukan PHP 7.x seperti tertulis di README. CodeIgniter 3.1.13 sudah berjalan di PHP 8.0 dengan catatan:
   - `index.php` diset `error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING)` pada mode development agar deprecation warning PHP 8 tidak membanjiri layar.
   - Timezone diset `Asia/Jakarta` di `index.php`.

2. **google/apiclient** diinstal via Composer di `application/vendor`. **Penting**: Composer di laptop ini memakai PHP 8.4 (`C:\php8.4`), bukan PHP XAMPP. Karena `google/apiclient` versi terbaru butuh PHP >= 8.1, maka:
   - Di-pin ke `google/apiclient: 2.15` + `google/apiclient-services: 0.200.0`.
   - Ditambahkan `config.platform.php = "8.0.30"` di `application/composer.json` agar Composer otomatis memilih versi dependency yang kompatibel PHP 8.0 (google/auth 1.47, monolog 2.11, dll).
   - **Jika menjalankan `composer update` lagi, jangan hapus config `platform.php`**, atau aplikasi akan error "Composer detected issues in your platform" karena XAMPP cuma PHP 8.0.

3. **Database dual-environment** di `application/config/database.php`:
   - Group `xampp` (testing) dan `laragon` (server produksi).
   - Auto-detect via HTTP_HOST: mengandung `localhost`/`127.0.0.1`/berakhiran `.local` → `xampp`, selain itu → `laragon`.
   - Group `laragon` masih placeholder — isi hostname/kredensial saat deploy.
   - Satu file `database.sql` untuk kedua environment (struktur identik).

4. **CSRF aktif** (`config.php`). Semua form wajib menyertakan token `csrf_test_name` (otomatis oleh `form_open()`). POST tanpa token → HTTP 403. Ini sudah terverifikasi.

5. **Lupa password** belum terintegrasi mail server. Untuk testing, link reset ditampilkan langsung di flashdata (notice hijau) setelah submit email. Saat production, ganti dengan kirim email (CodeIgniter Email library / SMTP).

6. **Placeholder eksternal** (belum diisi):
   - Google OAuth: `application/config/google.php` → isi Client ID/Secret. Tombol "Login dengan Google" otomatis disembunyikan selama placeholder.
   - WAAJO: `application/config/waajo.php` → API key + device ID. Library `Waajo` masih stub (Fase 6).

7. **Akun seeder** (di `database.sql`):
   - Admin: `admin@ekatalog.test` / `admin123`
   - Member: `member@ekatalog.test` / `member123`
   - Password di-hash bcrypt `password_hash()`.

8. **Kebiasaan memulai server** (XAMPP tanpa service Windows):
   - MySQL: `C:\xampp\mysql\bin\mysqld.exe`
   - Apache: `C:\xampp\apache\bin\httpd.exe`
   - Atau gunakan XAMPP Control Panel.

9. **Catatan keamanan Composer**: ada 1 advisory vulnerability di dependency (`composer audit`). Bukan dari kode aplikasi; dipantau.

10. **Redirect login admin** saat ini menuju `admin/dashboard` yang baru ada di Fase 2 (sebelumnya 404).

---

## Fase 2 — Master Data (selesai)

### Yang Dibangun
- **Base controller admin**: `application/core/MY_Controller.php` → class `Admin_Controller` memastikan hanya role `admin` yang bisa mengakses halaman admin; selain itu redirect ke login. Semua controller admin (`application/controllers/admin/`) extend class ini.
- **CRUD Admin** (controller + views + model):
  - KBLI (`admin/kbli`) — kode & nama klasifikasi.
  - Platform (`admin/platforms`) — marketplace tujuan, mendukung upload logo.
  - Perusahaan (`admin/companies`) — pemilik (user), NPWP, logo, alamat, dan multi KBLI.
  - Produk (`admin/products`) — perusahaan pemilik, harga/promo, maksimal **4 gambar**, kategori KBLI (multi), dan URL produk per marketplace (tombol redirect).
  - Banner (`admin/banners`) — gambar wajib untuk banner baru, posisi top/middle/bottom, urutan.
- **Template admin**: sidebar (responsive) di `application/views/admin/templates/`.

### Catatan Teknis & Keputusan
1. **Controller admin di subfolder**: CI3 otomatis memetakan `admin/kbli` → `controllers/admin/Kbli.php`. Route eksplisit hanya untuk `admin` → `admin/dashboard`.
2. **Auto-load**: form_validation, session, database sudah autoload. Controller admin memuat model + `upload` helper (baru, di `application/helpers/upload_helper.php`) + `form_validation`.
3. **Upload gambar**: helper `ekatalog_upload()` (1 file) & `ekatalog_upload_many()` (banyak file, dengan batas maksimal). File disimpan di `assets/uploads/<folder>/` dengan nama acak (`encrypt_name`), ekstensi diizinkan jpg/jpeg/png/webp, maks 2MB.
4. **Hapus file saat delete**: model `Product_m::delete()`, `delete_image()`, `Banner_m::delete()`, dan controller platform/company ikut `unlink()` file dari disk — sudah terverifikasi.
5. **Maksimal 4 gambar**: dihitung dari jumlah gambar yang ada (`max_new = 4 - existing`); kelebihan file diabaikan. Posisi gambar dirapatkan saat satu dihapus (query `UPDATE position = position - 1`).
6. **Bug yang sempat muncul & diperbaiki**: `input->post('kbli')` bisa berupa string (bukan array) → dipaksa `(array)` di controller Companies & Products agar tidak TypeError di `set_kbli()`.
7. **Relasi multi (KBLI, platform)**: pola "hapus semua lalu insert ulang" (`set_kbli`, `set_platforms`).
8. **Status code redirect**: halaman CI di Windows mengembalikan 303/307 pada `redirect()` (bukan 302) — ini normal, tetap membawa header `Location` dan diproses browser.
9. **CSRF tetap aktif** — semua form memakai `form_open()`/`form_open_multipart()` sehingga token otomatis disertakan. Uji via curl harus menyertakan token.

### Catatan Pengujian (diverifikasi)
- Guard akses: anonim ke `/admin` → redirect login.
- Semua halaman index admin → 200.
- Create: KBLI, perusahaan (dengan KBLI), produk (3 gambar + promo + platform URL), banner (gambar wajib). Semua tersimpan di DB + file upload ada di disk.
- Edit: produk (tambah gambar sampai penuh 4, perbaikan nama), platform (upload logo).
- Batas gambar: dari 3 slot, upload 2 file → hanya 1 diterima (total tetap 4).
- Hapus gambar → posisi dirapatkan + file dihapus.
- Delete cascade: produk+banner+perusahaan → baris & file di disk bersih.

### Catatan untuk Fase 3 (Katalog & UI)
- Fitur marketplace "produk terkait"/pencarian belum tampil di frontend — data sudah tersimpan di `product_platforms` dan siap dipakai katalog.
- Filter platform (Fitur 6 README) tinggal membaca `platforms` + `product_platforms`.

---

## Fase 3 — Katalog & UI (selesai)

### Yang Dibangun
- **Halaman katalog publik** (`catalog` = default controller): grid produk responsive ala Tokopedia, search bar besar di topbar, filter platform marketplace, sorting, pagination, dan banner promosi (top di atas grid, middle di sela-sela produk, bottom di bawah).
- **Halaman detail produk** (`product/<id>`): galeri gambar (thumbnail ganti gambar utama), harga/promo, deskripsi, badge KBLI, tombol redirect ke tiap marketplace (`product_platforms`), info perusahaan, produk terkait (berdasar KBLI sama), penghitung views.
- **CSS Tokopedia-style** di `assets/css/style.css`: tema hijau `#03ac0e`, grid `row-cols-2 → row-cols-lg-5` (2 kolom mobile, 5 kolom desktop), card dengan badge promo, tombol marketplace hijau.
- **Partial kartu produk** `views/templates/product_card.php` — dipakai katalog & produk terkait, termasuk tombol "Beli di <platform>" (marketplace pertama) sebagai redirect langsung sesuai Fitur 2 README.
- **Redirect member**: setelah login member → `catalog` (bukan `home`). `Home` kini redirect ke `catalog`. Admin tetap → `admin/dashboard`.
- **Data contoh** `database_dev.sql` (hanya development): 3 perusahaan, 10 produk, 13 gambar, 2 banner + relasi KBLI/platform. Gambar contoh dibuat di `assets/uploads/`. **Jangan import di produksi.**

### Catatan Teknis & Keputusan
1. **Query katalog** di `Product_m` (`_public_base` + `public_query` + `count_public`): hanya menampilkan produk aktif dari perusahaan aktif. Gambar pertama diambil via subquery `(SELECT ... product_images ... LIMIT 1)` untuk menghindari duplikat baris dari JOIN.
2. **Bug yang diperbaiki**: pemanggilan `get('products p')` diikuti `from('products p')` → error `Not unique table/alias 'p'` (MySQL 1066). Solusi: gunakan `get()` tanpa argumen setelah `from()`.
3. **Sorting** (Fase 3): `recommended` (promo → featured → rating → jumlah rating → baru), `promo`, `rating`, `newest`, `price_asc`, `price_desc`. Sortir kebiasaan user (personalization) menyusul di Fase 5.
4. **Filter platform** memakai `EXISTS (SELECT 1 FROM product_platforms ...)` agar efisien.
5. **Galeri detail** memakai JS sederhana (ganti `src` gambar utama saat thumbnail diklik). Placeholder `assets/images/no-image.png` untuk produk tanpa gambar.
6. **Halaman auth** (`login/register/lupa/reset`) menyembunyikan search bar topbar via flag `$data['hide_search']`.
7. **Perlu diingat**: `database_dev.sql` dibuat terpisah agar skema inti (`database.sql`) tetap bersih. Jika re-import `database.sql`, data contoh hilang dan perlu import `database_dev.sql` lagi.
8. **Route baru**: `catalog` (default), `product/(:num)` → `product/index/$1`.

### Catatan Pengujian (diverifikasi)
- `/` → katalog: 10 produk, 10 tombol "Beli di", 4 badge PROMO, 2 banner tampil.
- Detail `product/1`: nama, 3 tombol marketplace (SIPLah/Tokoladang/GratisOngkir), thumbnail, produk terkait, views bertambah (1500→1501).
- Search `q=kopi` → 2 produk; filter `platform=1` (SIPLah) → 7 produk; sort `price_asc` dimulai Rp5.000, `price_desc` dimulai Rp250.000.
- `product/9999` → 404. Member login → katalog. Admin dashboard & daftar produk tetap OK.

---

## Fase 4 — Fitur Member (selesai)

### Yang Dibangun
- **Rating & ulasan** (`rating/submit`, member only): member memberi rating 1–5 bintang + komentar. Satu user hanya boleh 1 rating per produk (UNIQUE `user_id+product_id`) — submit lagi berarti update. Setiap submit menghitung ulang `avg_rating` & `rating_count` di tabel `products`.
- **Favorit** (`favorite`, member only): tombol heart di kartu produk & halaman detail, halaman daftar favorit. Status heart (aktif/tidak) ditandai per produk.
- **Profil member** (`profile`): edit nama, nomor WhatsApp, upload avatar, dan ganti password (validasi password lama). Ringkasan akun + info perusahaan milik user.
- **Settings** (`setting`): toggle **dark mode** (tersimpan per-user), navigasi akun & profil, info profil perusahaan.
- **Dark mode**: kolom baru `users.dark_mode` (TINYINT) + `assets/css/dark.css` + `data-bs-theme="dark"` + class `body.ek-dark`. Toggle tersedia di topbar (icon) dan halaman settings.
- **Guard role**: `Member_Controller` (fitur rating & favorit — hanya member), `User_Controller` (profile & settings — semua yang login). Ditambahkan di `core/MY_Controller.php`.

### Catatan Teknis & Keputusan
1. **Skema berubah**: README menetapkan kolom `wa_number` saja untuk users; saya tambahkan **`users.dark_mode TINYINT(1) DEFAULT 0`** untuk preferensi tampilan per user (bukan global). `database.sql` sudah diperbarui; untuk DB lama jalankan `ALTER TABLE users ADD COLUMN dark_mode TINYINT(1) NOT NULL DEFAULT 0 AFTER wa_number;`.
2. **Rekalkulasi rating**: `Rating_m::recalc_product()` menghitung dari tabel `ratings` saja. Konsekuensi: avg/rating_count yang di-seed manual di `database_dev.sql` akan **ditimpa** saat ada rating nyata pertama. Ini perilaku benar (avg mencerminkan rating riil), tapi perlu dicatat.
3. **CSRF pada form dinamis/JS**: form yang dibuat murni via JS akan kena 403. Semua form (termasuk toggle dark di settings) harus lewat `form_open()`/`form_close()` agar token `csrf_test_name` ikut terkirim.
4. **Redirect favorit**: form heart menyertakan `redirect` berisi URL saat ini (termasuk query string untuk menjaga filter/sort). `Favorite::toggle` hanya menerima redirect yang diawali `base_url()` (mencegah open redirect).
5. **Session dark_mode**: diset saat login (`Auth::_set_session`). Preferensi di DB tetap tersimpan lintas sesi.
6. **Placeholder avatar**: `ek_avatar` CSS (inisial) bila user belum punya avatar; URL avatar Google (http) tidak dihapus saat ganti avatar.
7. **Bug yang ditemukan saat uji**: pengujian awal ganti password gagal karena token CSRF diambil dari halaman redirect kosong → peringatan: token harus diambil dari halaman yang *benar-benar* me-render form (ikuti `-L` dulu atau ambil ulang).

### Catatan Pengujian (diverifikasi)
- Member login → katalog. Favorit: tambah → muncul di daftar + heart aktif; toggle off → terhapus.
- Rating: submit 5★ → avg produk 5.0/1; update 4★ → avg 4.0/1, komentar terganti; tampilan detail menampilkan form "Ubah rating" bila sudah memberi rating.
- Dark mode: toggle ON → DB `dark_mode=1`, body `ek-dark`, `data-bs-theme="dark"`.
- Profil: update nama/WA/avatar tersimpan (file avatar di disk); ganti password salah → error; benar → login dengan password baru berhasil (kemudian dikembalikan).
- Guard: anonim ke `/favorite` & `/setting` → redirect login; admin `rating/submit` → redirect katalog, tidak ada rating tersimpan.
- Katalog member: 10 heart form; anonim: 0 heart form + tombol login tampil.

---

## Fase 5 — Personalization (selesai)

### Yang Dibangun
- **Tracking perilaku user** (`Behavior_m` + controller `Behavior`):
  - `view` → setiap kali member membuka halaman detail produk (server-side).
  - `click` → klik tombol marketplace via endpoint `behavior/track` (AJAX, `sendBeacon`), menyimpan `platform_id` tujuan.
  - `search` → kata kunci pencarian disimpan di `search_logs`.
- **Skor preferensi per KBLI** (`Sorter` library + `Behavior_m::get_kbli_scores`):
  - `view` = bobot 1, `click` = bobot 3 (via `product_kbli`).
  - `search` yang cocok dengan nama/kode KBLI = +2.
- **Katalog adaptif** (`Product_m::public_query` + `user_id`): pada sortir **Rekomendasi**, urutan menjadi **Promo > Kebiasaan user (pref_score) > Rating > Terbaru** — sesuai prioritas README. Indikator hijau tampil di katalog saat adaptif aktif.
- **Sortir tetap** (promo/rating/terbaru/harga) tidak terpengaruh personalisasi.

### Catatan Teknis & Keputusan
1. **Integrasi skor via subquery**: `Sorter::score_subquery()` membangun tabel skor `UNION ALL` (kbli_id, score) lalu menghitung `pref_score` per produk = skor maksimum di antara KBLI produk tsb. Aman di MySQL/MariaDB XAMPP.
2. **Bug penting — urutan operasi query builder**: `Behavior_m::get_kbli_scores()` memanggil `$this->db->reset_query()` agar tidak terkontaminasi state builder lain. Karena itu **skor preferensi harus dihitung SEBELUM `_public_base()`** di `public_query` (kalau setelahnya, `reset_query()` menghapus FROM/JOIN/WHERE utama → error `Unknown column 'p.id'`).
3. **Bug kedua**: tanpa `reset_query()`, query `search_logs` di `get_kbli_scores` tercampur state `_public_base` → error `Column 'user_id' in where clause is ambiguous` (FROM berisi `products p, search_logs`).
4. **CSRF di endpoint tracking**: `behavior/track` dimasukkan ke `csrf_exclude_uris` karena dipanggil via `sendBeacon` tanpa token. Risiko rendah (hanya mencatat integer id, wajib login). Catat: `app.js` adalah file statis — **tidak bisa memuat `<?php site_url() ?>`**; endpoint tracking disuntikkan via atribut `data-track-url` di `<body>` (diproses PHP di header).
5. **Skor dicari dua kali** di `Catalog` (untuk flag adaptif) dan di `public_query` (untuk ordering) — duplikasi kecil; bisa dioptimalkan dengan menyimpan skor di session/satu pemanggilan bila perlu.
6. **Perilaku `sendBeacon`**: POST `Content-Type: multipart/form-data` (FormData) — di CI3 `$this->input->post()` tetap terbaca. Alternatif: `fetch(..., {keepalive:true})`.
7. **Sortir 'recommended' non-adaptif**: tanpa preferensi (user baru / anonim), fallback = promo > rating > rating_count > terbaru (tanpa `is_featured` sejak Fase 5, sesuai README).

### PENTING — Pelajaran PowerShell & hash password
Saat pengujian Fase 4, kredensial member tidak sengaja rusak karena menjalankan SQL inline via PowerShell:
- PowerShell **menginterpretasi `$` di string double-quote** → `\$2y$10$Vk1a...` bagian `$Vk1a...` dianggap variabel (undefined → kosong), sehingga hash bcrypt yang tersimpan jadi korup (login member gagal).
- **Solusi**: jangan menulis hash/string ber-`$` lewat `mysql -e "..."` di PowerShell. Gunakan **file `.sql`** atau **script PHP** (`password_hash()`) untuk update. Sudah diperbaiki: `password` member dikembalikan ke `member123` via script PHP (`temp/fix_member_pw.php`).

### Catatan Pengujian (diverifikasi)
- Login member → katalog; view produk 1 & 5 tercatat `view`; `behavior/track` mencatat `click` + `platform_id=3`; `?q=kopi` tercatat di `search_logs`.
- Skor: kbli 2 (kopi) > kbli 3 (pakaian) → urutan katalog adaptif benar: promo dulu (Kopi Robusta, Kaos), lalu pref_score, rating, terbaru. Indikator adaptif tampil.
- Anonim: katalog normal tanpa adaptif; detail tanpa `data-track-url`. Member: tombol marketplace punya `data-product-id`/`data-platform-id`.

---

## Fase 6 — Integrasi WAAJO (selesai)

### Yang Dibangun
- **Library `Waajo`** kini berisi implementasi HTTP sungguhan (bukan stub):
  - `send_message($phone, $message)` memakai cURL → `POST {base_url}/send-message` dengan header `apikey` & `device` + body JSON `{to, text}`.
  - Jika kredensial masih placeholder → `FALSE` (tanpa panggil API).
  - Header/endpoint adalah **asumsi umum** — harus diverifikasi terhadap akun WAAJO yang sebenarnya.
- **Model `Wa_m`**: catat `wa_logs` (user_id, phone, message_type promo/rekomendasi, status pending/sent/failed, response).
- **Halaman admin "Kirim WhatsApp"** (`admin/wa`):
  - **Kirim Promo**: pilih produk promo (checkbox) → pesan dikirim ke semua member yang punya nomor WA.
  - **Kirim Rekomendasi**: per-member, memakai hasil `public_query(..., user_id)` (urutan adaptif Fase 5) → 3 produk teratas dikirim personal.
  - Riwayat pengiriman (wa_logs) ditampilkan di halaman.
- Menu sidebar admin + route `admin/wa/*`.

### Catatan Teknis & Keputusan
1. **API WAAJO sudah diverifikasi** (Postman collection *"WhatsappV2Public"*): base `https://api.waajo.id`, kirim teks `POST /go-omni-v2/public/whatsapp/send-text`, header `apikey`, body `{recipient_number, text, check_session}`. Respons sukses = HTTP 200 + `{"data":{"id":"..."},"msg":"OK","status":200}`. Kredensial sebelumnya menunjuk `https://app.waajo.com/api/v1` yang tidak ada di DNS (NXDOMAIN) → semua pengiriman gagal (`failed`) — sudah diperbaiki ke `api.waajo.id` (terverifikasi: apikey diterima, `is_online` saat ini mengembalikan `status 406 / msg "Offline"`). **Syarat operasional**: nomor device harus terhubung (`Online`) di dashboard Waajo (scan QR) sebelum pesan bisa terkirim. Juga ditambahkan tombol **Uji Koneksi** di `admin/wa` (`is_online`) dan fallback TLS bila CA bundle XAMPP hilang.
2. **Bug saat uji**: `$this->Product_m->where(...)->get('products')` memanggil method `where` di model (tidak ada) → `Call to undefined method Product_m::where()`. Fix: pakai `$this->db` untuk query chaining (properti `db`), bukan method model. Catatan umum CI3: model hanya memproksi **properti** `db` via `__get`, bukan method.
3. **Target pengiriman**: hanya member dengan `wa_number` terisi (`User_m::get_members_with_wa()`). Member 2 diberi nomor saat pengujian lalu dikembalikan `NULL`.
4. **Format nomor WA**: `send_message()` memfilter non-digit, sehingga format "62xxxx" / "08xxxx" sama-sama berubah jadi digit-only. Konversi "08"→"62" belum otomatis — perlu dipastikan user mengisi format internasional.
5. **Rekomendasi personal** mengandalkan data perilaku user; bila user belum punya riwayat, `public_query` tetap menghasilkan produk (fallback rating/terbaru).

### Catatan Pengujian (diverifikasi)
- Halaman `admin/wa`: warning "belum dikonfigurasi", 4 checkbox produk promo, member dengan WA terdaftar.
- `send_promo` (2 produk): flash "0 dari 1 member sukses" + baris wa_logs `promo/failed`.
- `send_recommendation/2`: baris wa_logs `rekomendasi/failed`.
- Riwayat wa_logs tampil di tabel admin dengan badge `failed`.

---

## Fase 7 — Finalisasi (selesai)

### Yang Dikerjakan
- **Data demo diperbanyak** (`database_dev_extra.sql`): produk 11–24 (total 24) → katalog kini punya 2 halaman untuk menguji pagination.
- **Polish mobile**:
  - Sidebar admin di layar <768px menjadi menu horizontal scroll (bukan tumpukan vertikal), logo & `hr` disesuaikan.
  - Topbar frontend sudah responsif (logo → search bar → nav di bawahnya).
- **base_url otomatis** (`config.php`): dihitung dari skema + `HTTP_HOST` + `dirname(SCRIPT_NAME)`. Tidak lagi hardcoded `localhost/katalog` → sekali-deploy di subfolder/domain mana pun langsung jalan.
- **.htaccess generik** (tidak lagi bergantung prefix `/katalog/`):
  - Blokir akses langsung ke `application`, `system`, `vendor` di posisi path apa pun (`^(.*/)?(application|system|vendor)`).
  - Blokir file sensitif: `.htaccess`, `.gitignore`, `database*.sql`, `composer.*`, `.env`.
  - `Options -Indexes` (nonaktifkan listing direktori).
  - `RewriteBase` dihapus → aturan rewrite relatif (subfolder maupun root).

### Checklist Deploy (Laragon / server produksi)
1. **Salin proyek** ke server (mis. `D:\laragon\www\katalog` atau root domain). Sertakan `application/vendor` (dari Composer) atau jalankan `composer install --no-dev` di `application/` — **jangan ubah `config.platform.php = 8.0.30`**.
2. **Apache**: pastikan `mod_rewrite` aktif dan `AllowOverride All` (agar `.htaccess` berfungsi).
3. **Environment**: set variabel server `CI_ENV=production` (di Apache/Laragon). Efek: `display_errors` off, `db_debug` off.
4. **Database**: buat DB `ekatalog` (Laragon atau server), import `database.sql`. **Jangan** import `database_dev*.sql` (data contoh).
5. **Koneksi DB**: isi group `laragon` di `application/config/database.php` (hostname/port/kredensial). Auto-detect memilih `laragon` karena host bukan `localhost`/`.local`. (Pada server yang memakai `localhost` untuk MySQL, sesuaikan deteksi.)
6. **Kredensial eksternal**: isi `google.php` (Client ID/Secret + redirect URI sesuai domain) dan `waajo.php` (API key + device; verifikasi endpoint/header WAAJO).
7. **Hak akses tulis**: `assets/uploads/`, `application/cache/`, `application/logs/` writable.
8. **base_url**: otomatis terdeteksi; pastikan `RewriteRule` bekerja (cek halaman tanpa `/index.php`).

### Catatan Pengujian (diverifikasi)
- Pagination: `?page=1` → 20 produk, `?page=2` → 4 produk, no page 3, halaman aktif benar.
- Mobile admin: sidebar jadi baris scroll di viewport <768px (CSS media query).
- base_url dinamis: asset CSS/JS mengarah benar; login POST, filter platform+sort, search "sepatu" (3 hasil) tetap jalan.
- Blokir akses: `/application/...`, `/system/...`, `/vendor/...`, `/database.sql` semua 403; halaman publik 200.
- Smoke test semua fase: anonim/member/admin page status 200, produk tak ada 404; tidak ada error baru di log CI.







---

## Penambahan — Kunjungan (pengganti rating)

### Yang Dikerjakan
- **Kunjungan produk menggantikan rating**: kolom baru `products.visit_count`.
  - `Behavior_m::register_visit()` menaikkan `visit_count` untuk **anonim maupun member**; baris `user_behaviors` hanya ditulis bila user login (kolomnya `NOT NULL`), sehingga skor kebiasaan KBLI tetap utuh.
  - Throttle session `ek_visit_seen`: satu kunjungan per produk+platform+user per 30 menit (dibatasi 200 entri).
  - `header.php` kini selalu menyertakan `data-track-url` (dulu hanya untuk user login), jadi `app.js` (`sendBeacon`) bekerja untuk anonim juga.
- **Sorting**: `rating` → `visit` ("Kunjungan Tertinggi") di `Catalog`, `Company::cv`, dan `Product_m` (`public_query`, `get_by_company`, `get_related`). Prioritas `recommended` menjadi **promo > kebiasaan user > kunjungan > terbaru > views**. `Wa::send_recommendation()` ikut karena memakai `public_query(..., 'recommended')`.
- **Rating dihapus** dari kode: `Rating.php`, `Rating_m.php`, route `rating/submit`, blok ulasan di `product/detail.php`, kolom Rating di `admin/products`, serta CSS `ek-stars`/`ek-star`. Kolom `avg_rating`/`rating_count` dan tabel `ratings` **dibiarkan** (tidak destruktif) tetapi tidak lagi dipakai.
- **Dokumentasi**: README diperbarui (fitur, DFD, struktur folder, prioritas sortir) + blok SQL upgrade untuk DB lama.

### Catatan Teknis & Keputusan
1. **Reset query builder** (`catatan.md` Fase 5): `Behavior_m::get_kbli_scores()` memanggil `$this->db->reset_query()`. Karena itu `Product_m` menghitung skor preferensi lewat `_preference()` **sebelum** `_public_base()` menyusun query, lalu skor itu diteruskan ke `_apply_public_sort()`. Menghitungnya di dalam `_apply_public_sort()` akan menghapus FROM/JOIN/WHERE utama.
2. **`product_card.php` pakai `$product->visit_count`** — kolom ini otomatis terbawa karena query memakai `p.*`/`products.*`. Kalau suatu query memilih kolom eksplisit, `visit_count` harus ditambahkan manual.
3. **Bug pre-existing yang ikut diperbaiki**: `Company.php` punya deklarasi `protected $allowed_sorts` menempel di baris yang sama dengan penutup `__construct()`.

### Catatan Pengujian (diverifikasi)
- `php -l` bersih untuk seluruh file yang diubah.

---

## Penambahan — Riwayat (produk + pencarian)

### Yang Dikerjakan
- **Halaman Riwayat dipecah dua tab** (`history?tab=produk` / `history?tab=pencarian`), server-side lewat query string, tanpa JS:
  - `History::index()` memvalidasi `?tab=` terhadap whitelist `produk`/`pencarian` (default `produk`).
  - **Hanya tab aktif yang di-query** — tab `pencarian` tidak menyentuh `get_recent_products()` dan sebaliknya.
  - `is_empty` dihitung per tab, jadi empty state tiap tab jujur menggambarkan isinya sendiri.
  - Tabs memakai `nav nav-tabs` Bootstrap 5.3 → otomatis responsif, URL bisa di-share, tahan refresh.
- **Perbaikan inkonsistensi filter**: `Behavior_m::get_recent_products()` kini `WHERE p.is_active = 1 AND c.is_active = 1`.
- **Saran riwayat di form search** (`templates/header.php`): 8 kata kunci terakhir milik user, diambil server-side lalu difilter live di client. Muncul saat input di-fokus, pilih dengan klik atau `↑/↓` + `Enter`, tutup dengan `Esc` atau klik luar.
- **`/history` dibuka untuk admin**: `History` kini extends `User_Controller` (login cukup), bukan `Member_Controller`, supaya mudah dites. Menu "Riwayat" di `header.php` sudah tampil untuk semua user login, jadi kini konsisten.

### Catatan Teknis & Keputusan
1. **Skema tidak berubah sama sekali.** Tabel `users`, `user_behaviors`, `search_logs`, dan `products` tetap apa adanya. Untuk DB lama yang sempat menjalankan kolom `show_recent_row`, jalankan `ALTER TABLE users DROP COLUMN show_recent_row;`.
2. **`c.is_active = 1` memang belum ada sebelumnya** — ini bukan soal produk nonaktif. `WHERE p.is_active = 1` sudah ada, jadi produk yang dinonaktifkan admin **tidak pernah** muncul di riwayat. Yang bocor cuma **perusahaan** nonaktif: produknya lolos karena `products.is_active` masih 1 per-item, padahal sudah hilang dari grid katalog (`Product_m::_public_base`), pencarian, dan etalase (`get_by_company`).
3. **Badge kata kunci** diganti dari `text-bg-light border text-dark` ke `text-bg-secondary border`. Kombinasi lama memaksa teks gelap; di dark mode `text-bg-light` berubah jadi latar gelap sehingga kontrasnya rusak. `text-bg-secondary` aman di kedua tema. Ditambah `hover` memakai `var(--ek-green-dark)`.
4. **Saran di form search diambil inline di `header.php`**, bukan AJAX, mengikuti pola yang sudah dipakai view itu sendiri (`header.php` memang memanggil `$this->load->model('User_m')` untuk avatar). `get_recent_keywords()` adalah `GROUP BY` kecil dengan indeks `fk_search_logs_user`, dan `Behavior_m` sudah mengembalikan `array()` kalau `$user_id` kosong, jadi pemanggilnya tidak perlu penjaga tambahan. Kalau nanti volume `search_logs` tumbuh besar dan query ini mulai terasa, dibangun ulang jadi endpoint yang dipanggil saat fokus.
5. **Enter tidak pernah dialihkan selama tidak ada opsi aktif** — jadi perilaku submit biasa tetap utuh. `mousedown` di daftar di-`preventDefault()` supaya input tidak kehilangan fokus sebelum `click` mendarat.
6. **Cache-buster `app.js` di-`footer.php` di-bump `?v=2` → `?v=3`.** Wajib: CSS pakai `@filemtime` sehingga otomatis bust, tapi JS tidak.
7. **Saran hanya untuk user login.** `search_logs.user_id` bisa NULL untuk pencarian anonim, jadi riwayat anonim tidak bisa diambil ulang per-pengunjung tanpa skema tambahan. Admin sengaja ikut dapat karena halaman ini dipakai untuk tes.
8. **Baris "Baru Dilihat" di katalog sudah dibatalkan** atas permintaan pemilik: blok view, query, switch `setting`, route, setter model, session, dan kolom `show_recent_row` semuanya ikut dibuang. Yang **tidak** ikut dibuang: CSS dasar `.ek-scroll-row` (`style.css`) + aturan scrollbar di `dark.css` — keduanya masih dipakai baris rekomendasi etalase di `company/cv.php`. `get_recent_products()` juga tetap, masih dipakai tab Produk di `/history`.
9. **Penting — model tidak bisa dipanggil `$this->...` dari dalam view.** `CI_Loader::_ci_load()` menyalin seluruh properti controller ke loader (`system/core/Loader.php:927-930`), jadi `$this` di dalam view adalah `CI_Loader`, bukan controller. Tapi `CI_Loader::model()` menaruh objek model di `get_instance()` (`:273`). Akibatnya `$this->load->model('X_m')` berhasil, lalu `$this->X_m` bernilai `null` → fatal `Call to a member function ...() on null`. Yang benar: `$this->load->model('X_m');` lalu pakai `get_instance()->X_m->...`. Pola ini sudah ada di `templates/header.php` untuk `User_m` (avatar), hanya `Behavior_m` sempat ditulis dengan `$this->`. `$this->session` dan `$this->router` tetap aman karena ikut tersalin sebagai objek.
10. **Bug di atas tidak cuma di `/setting`.** Semua halaman frontend yang punya kolom search ikut kena karena view yang sama.
11. **Menu "Riwayat" di dropdown user tadinya ter-gate `$ek_role === 'member'`.** Guard itu membungkus `Wishlist` dan `Riwayat` sekaligus, jadi admin — yang `/history`-nya sudah dibuka — tetap tidak melihat tombolnya di mana pun. `Riwayat` sekarang dipindah keluar dari guard itu, `Wishlist` tetap di dalamnya karena `Favorite` masih `Member_Controller`. Ini penyebab sebenarnya "tombol riwayat tidak muncul di admin", bukan ketiadaan kotak pencarian di panel admin.
12. **Panel admin sengaja tidak diberi kotak pencarian atau dropdown.** Percobaan pertama salah mendiagnosis dan menambahkan search form ke navbar admin; semuanya sudah dicabut lagi. Layout admin memang tidak punya form search, dan tidak ada fitur `app.js` (`#ekCatNav`, `.ek-market-link`, `data-track-url`) yang terpakai di sana, jadi `app.js` dan `bootstrap-icons` juga tidak perlu dimuat di layout admin. Admin menguji lewat sidebar **Lihat Situs** → dropdown user → **Riwayat**.

### Catatan Pengujian
- `php -l` bersih untuk 12 file PHP yang diubah, dan `node --check` bersih untuk `assets/js/app.js`. `git diff --check` tanpa error whitespace. Pengujian fungsional di browser belum dilakukan.

---

## Penambahan — Riwayat: hapus terpilih & ikon trash

### Yang Dikerjakan
- **Tiga cara menghapus riwayat**, semuanya hanya menyentuh `user_behaviors` + `search_logs` milik user tersebut:
  1. **Ikon tong sampah** (merah) di pojok kanan atas tiap card produk — hapus 1 produk (`history/delete_product/{id}`). Di tab Pencarian ada ikon yang sama di sebelah tiap badge kata kunci (hapus 1 kata kunci).
  2. **Pilih Beberapa** → mode centang: `?select=1`, ikon trash digantikan checkbox di slot yang sama, lalu **Hapus Terpilih (n)** menghapus semua item yang dicentang (`history/delete_items`). Di mode centang tombolnya berganti jadi **Batal** di slot yang sama persis.
  3. **Hapus Semua** (dulu "Hapus Riwayat") — `history/clear`, tidak berubah.
- **Responsif** sesuai README butir 3: grid riwayat diselaraskan ke `row-cols-2 row-cols-md-3 row-cols-lg-5` (sama katalog & etalase), trash/checkbox membesar mengikuti aturan `.ek-fav-btn` yang sudah ada, badge kata kunci boleh terpotong (ellipsis) di layar sempit, dan tombol toolbar **tidak dipecah/direntangkan** di mobile — hanya font + padding-nya dikecilkan.
- `product_card.php` dapat **varian kartu**: `$card_variant = 'history'` mengganti tombol wishlist (heart) dengan trash/checkbox. Tanpa varian, kartu dipakai persis seperti sebelumnya (katalog, produk terkait, favorit) — heart tidak hilang dari mana pun kecuali `/history`.

### Catatan Teknis & Keputusan
1. **Mode centang diputuskan di server, bukan di JS.** `History::index()` membaca `?select=1` ke `$data['select_mode']`, jadi checkbox, tombol, dan form-nya sudah ada di HTML. Alasannya: hapus terpilih harus tetap bisa dipakai kalau `app.js` gagal dimuat. JS hanya menambah "pilih semua" + penghitung `(n)` + disabling tombol saat kosong. Tombol masuk/keluar mode karena itu **link biasa** (`?select=1` ↔ tanpa `select`), bukan toggle JS — sama seperti tab riwayat yang juga server-side.
2. **Checkbox di luar form, ditautkan lewat atribut `form="..."`.** Form hapus-terpilih ada di toolbar, sementara tiap kartu punya form trash sendiri, dan HTML melarang form bersarang. Jadi checkbox diletakkan di dalam `.ek-product-img` dengan `form="ekHistoryBulk"`. Konsekuensi untuk JS: `querySelector('[form=...]')` tidak dipakai, checkbox dicari lewat **`form.elements`** — elemen yang terasosiasi lewat atribut `form` memang ikut masuk `form.elements`.
3. **Checkbox & trash saling menggantikan di server, bukan lewat CSS.** Server hanya merender salah satu (`$card_select ? checkbox : trash`), jadi tidak ada elemen `display:none` yang memakan slot dan tidak perlu status kelas CSS. Varian yang sama dipakai di badge kata kunci.
4. **Pola "saling ganti" yang sama diterapkan ke tombol `Pilih Beberapa` ↔ `Batal`.** Sebelumnya itu satu elemen yang teksnya berubah (`aria-pressed` ikut berubah) — dari luar terlihat tetap satu tombol yang sama. Sekarang jadi dua cabang `if/else` terpisah, persis seperti trash/checkbox: `Pilih Beberapa` = `btn-outline-secondary`, `Batal` = `btn-secondary` (solid, supaya jelas itu jalan keluar). `aria-pressed` dihapus karena bukan lagi tombol toggle, melainkan dua link biasa. Konsekuensi sampingnya bagus: form bulk + tombol **Hapus Terpilih** sekarang hanya dirender di mode pilih, jadi tidak ada lagi tombol `disabled` yang hanya jadi tempat di mode normal — dan urutan slot di toolbar tidak bergeser.
5. **Toolbar mobile tetap satu baris, bukan di-stack full-width.** Versi pertama memakai `flex-direction: column` + `.ek-history-form { width: 100% }` + `.btn { flex: 1 1 auto }` — hasilnya tombol jadi besar dan berbaris vertikal, jadi posisinya berubah. Permintaan pemilik: posisi tombol tetap, ukuran mengecil. Sekarang yang dikecilkan hanya `font-size`/padding (`.72rem`/`.25rem .5rem` di ≤768px, `.68rem`/`.2rem .4rem` di ≤360px), `flex-wrap: wrap` di `.ek-history-head` dipakai sebagai cadangan saja, dan di ≤576px teks "semua" pada label **Pilih Semua** disembunyikan (`.ek-checkall-txt`) sehingga yang tersisa `☑ (24)` — jumlah item justru lebih penting daripada kata "semua", dan baris tidak perlu pecah karena teks. `.ek-history-head h5` juga diturunkan ke `1.05rem` supaya judul tidak memakan tempat.
6. **Satu endpoint untuk dua keperluan.** `History::delete_items()` melayani hapus terpilih (banyak `items[]`) maupun hapus satu kata kunci (satu `items[]`) — tidak perlu endpoint terpisah. `$scope` divalidasi terhadap `$allowed_tabs` yang sama dan sekaligus menentukan tab tujuan redirect, jadi tidak ada input mentah yang dipakai untuk redirect.
7. **Nilai POST tidak pernah dipakai mentah.** `Behavior_m::delete_items()` mengubah produk ke `int`, memotong kata kunci ke 150 char (sesuai `search_logs.keyword VARCHAR(150)`), membuang duplikat lewat array key, dan membatasi 100 item per request (`MAX_BULK_ITEMS`) supaya satu POST tidak bisa mengosongkan seluruh riwayat tanpa sengaja. Jumlah yang dilaporkan di flash adalah jumlah **item** yang dicentang, bukan jumlah baris yang terhapus (satu kata kunci bisa punya beberapa baris `search_logs`).
8. **`is_favorited` + `Favorite_m` dibuang dari `History`.** Heart tidak dirender di `/history` lagi, jadi query `get_favorited_ids()` cuma jadi satu query sia-sia per halaman. Halaman **favorit** tetap mengisi `is_favorited` sendiri.
9. **Cache-buster `app.js` di-bump `?v=3` → `?v=4`** (CSS tetap otomatis lewat `@filemtime`).
10. **Ikon trash memakai Bootstrap Icons** (`bi-trash`) agar sama dengan `bi-clock-history` di saran pencarian, bukan entity HTML seperti heart `&#9829;`. Latar merahnya diambil dari `var(--ek-red)` yang sudah ada, jadi warna konsisten dengan badge PROMO.

### Catatan Pengujian (belum diverifikasi di browser)
- `php -l` bersih untuk 6 file PHP yang diubah; `node --check` bersih untuk `assets/js/app.js`; `git diff --check` tanpa error whitespace.
- Perlu diuji manual di XAMPP:
  - Member & admin → `/history`: ikon trash di card produk, hapus 1 produk (flash muncul, kartu hilang, `user_behaviors` untuk produk itu habis).
  - "Pilih Beberapa" → checkbox muncul menggantikan trash, tombol berganti jadi "Batal" (solid) di slot yang sama, "Pilih Semua" + "Hapus Terpilih" muncul; "Pilih Semua (24)" mencentang semua; counter ikut berubah; "Hapus Terpilih" menghapus yang dicentang saja; "Batal" kembali ke trash **dan** tombol "Hapus Terpilih" hilang lagi (tidak ada tombol mati).
  - Tab Pencarian: trash per badge + hapus terpilih kata kunci.
  - **Matikan JavaScript**: mode centang + Hapus Terpilih tetap bisa dipakai.
  - Guard: GET ke `history/delete_items` / `history/delete_product/1` → redirect; POST tanpa token CSRF → 403; submit tanpa centang → flash "Centang minimal satu item…".
  - `favorites` dan `products.visit_count` tetap utuh setelah semua jenis hapus.
  - Tampilan di 360px / 375px / 768px / desktop, plus dark mode: tombol toolbar tetap urut & sebaris (tidak vertikal, tidak full-width), label "Pilih Semua" jadi `☑ (24)` di ≤576px.

