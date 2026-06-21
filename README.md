# Sistem Tracking Alumni Berbasis Web dengan Visualisasi Karir

## Spesifikasi Teknis
- Backend: PHP Native (PDO)
- Database: MySQL (XAMPP / phpMyAdmin)
- Frontend: Tailwind CSS (CDN) + Chart.js (CDN)
- Struktur: Modular include/require PHP

## Struktur Folder
```
tracking-alumni/
├── koneksi.php
├── auth_check.php
├── login.php
├── register.php
├── logout.php
├── index.php
├── dashboard.php
├── alumni_input.php
├── db_alumni.sql
├── components/
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
└── js/
    └── charts.js
```

## Fitur Tambahan

- **Halaman Utama Publik (`index.php`)**: menampilkan tabel "Alumni yang Sudah Bekerja" (nama, jurusan, tahun lulus, sektor, perusahaan, dan estimasi gaji pertama) tanpa perlu login, agar mahasiswa bisa melihat gambaran peluang karir secara langsung. Gaji ditampilkan dalam format estimasi (contoh: "Rp 6,5 Juta") untuk menjaga privasi alumni.
- **Registrasi Mandiri (`register.php`)**: alumni baru dapat mendaftarkan akun sendiri dengan mengisi data akun (username, password) sekaligus biodata (NIM, nama, jurusan, tahun lulus, email) dalam satu form. Sistem otomatis menolak pendaftaran jika username, NIM, atau email sudah terdaftar. Proses penyimpanan menggunakan database transaction agar data akun dan biodata selalu konsisten.

## Cara Instalasi (XAMPP)

1. **Salin folder project**
   Copy seluruh folder `tracking-alumni` ke dalam direktori `htdocs` pada instalasi XAMPP Anda.
   Contoh path: `C:\xampp\htdocs\tracking-alumni`

2. **Jalankan Apache dan MySQL**
   Buka XAMPP Control Panel, lalu start service **Apache** dan **MySQL**.

3. **Import Database**
   - Buka browser, akses `http://localhost/phpmyadmin`
   - Klik tab **Import**
   - Pilih file `db_alumni.sql` dari folder project
   - Klik **Go** / **Kirim**
   - Database `db_alumni` beserta tabel dan data sampel akan otomatis dibuat

4. **Sesuaikan Konfigurasi Koneksi (Opsional)**
   Jika kredensial MySQL Anda berbeda dari default XAMPP, edit file `koneksi.php`:
   ```php
   $db_host = 'localhost';
   $db_name = 'db_alumni';
   $db_user = 'root';
   $db_pass = '';
   ```

5. **Akses Aplikasi**
   Buka browser dan akses:
   ```
   http://localhost/tracking-alumni/index.php
   ```

## Akun Demo (Password sama: `123`)

| Role   | Username   | Password |
|--------|------------|----------|
| Admin  | admin      | 123      |
| Alumni | budianto   | 123      |

## Cara Mengompres Project Menjadi .zip atau .rar

### A. Menggunakan Windows (Tanpa software tambahan, untuk .zip)
1. Buka File Explorer, masuk ke folder tempat folder `tracking-alumni` berada (misalnya `htdocs`).
2. Klik kanan pada folder **tracking-alumni**.
3. Pilih **Send to** > **Compressed (zipped) folder**.
4. Windows akan otomatis membuat file `tracking-alumni.zip` di lokasi yang sama.

### B. Menggunakan WinRAR (untuk .zip atau .rar)
1. Install WinRAR jika belum ada (https://www.win-rar.com/).
2. Klik kanan pada folder **tracking-alumni**.
3. Pilih **Add to archive...**
4. Pada jendela yang muncul, pilih format **ZIP** atau **RAR** sesuai kebutuhan.
5. Klik **OK**, file hasil kompresi akan muncul di lokasi yang sama.

### C. Menggunakan 7-Zip
1. Install 7-Zip (https://www.7-zip.org/).
2. Klik kanan folder **tracking-alumni** > **7-Zip** > **Add to archive...**
3. Pilih format Zip atau pilih 7z lalu convert ke rar bila diperlukan (7-Zip native tidak membuat .rar, hanya .zip/.7z).
4. Klik **OK**.

### D. Menggunakan Command Line (Linux/Mac/Git Bash di Windows)
Untuk membuat file **.zip**:
```bash
cd /path/menuju/folder/parent
zip -r tracking-alumni.zip tracking-alumni/
```

Untuk membuat file **.rar** (memerlukan tool `rar` terinstall):
```bash
cd /path/menuju/folder/parent
rar a tracking-alumni.rar tracking-alumni/
```

### E. Menggunakan PowerShell (Windows, untuk .zip)
```powershell
Compress-Archive -Path "C:\xampp\htdocs\tracking-alumni" -DestinationPath "C:\xampp\htdocs\tracking-alumni.zip"
```

Setelah dikompres, file `.zip` atau `.rar` tersebut sudah siap dibagikan, diunggah, atau dijadikan backup project.

## Catatan Keamanan
- Seluruh query menggunakan **PDO Prepared Statement**, sehingga aman dari SQL Injection.
- Password disimpan menggunakan **bcrypt** melalui fungsi `password_hash()` dan diverifikasi dengan `password_verify()`.
- Setiap halaman yang membutuhkan login dilindungi oleh `auth_check.php`.
- Halaman `alumni_input.php` memiliki validasi role agar hanya bisa diakses oleh user dengan role `alumni`.
