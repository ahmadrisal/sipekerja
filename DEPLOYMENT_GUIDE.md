# Panduan Deployment SIPEKERJA

Ikuti langkah-langkah di bawah ini untuk meng-online-kan aplikasi SIPEKERJA secara gratis dan cepat.

---

## 1. Persiapan Database (Supabase)

1.  Buka [Supabase](https://supabase.com/) dan buat project baru.
2.  Tunggu hingga database siap, lalu buka menu **Settings > Database**.
3.  Cari bagian **Connection String** dan pilih tab **URI**.
4.  Copy URL tersebut. Formatnya:
    `postgresql://postgres:[PASSWORD]@db.[REF].supabase.co:5432/postgres`
    *(Ganti `[PASSWORD]` dengan password database yang Anda buat).*

---

## 2. Persiapan Backend (Koyeb)

1.  Pastikan kode backend Anda sudah dipush ke GitHub.
2.  Buka [Koyeb Dashboard](https://app.koyeb.com/).
3.  Klik **Create Service** > pilih **GitHub**.
4.  Pilih repository project Anda dan arahkan ke folder `backend`.
5.  Pada bagian **Environment Variables**, tambahkan:
    *   `DATABASE_URL`: Masukkan URI dari Supabase tadi.
    *   `JWT_SECRET`: Masukkan string acak (misal: `SipekerjaSuperSecret2024`).
    *   `NODE_ENV`: `production`
6.  Pastikan **Instance Type** pilih yang "Free" (Nano).
7.  Deploy. Tunggu hingga statusnya **Healthy**, lalu copy URL backend yang diberikan (misal: `https://sipekerja-api-abc.koyeb.app`).

> [!IMPORTANT]
> **Migrasi Data**: Setelah statusnya Healthy, buka menu **Console** di Koyeb untuk service tersebut dan jalankan perintah:
> `npx prisma db push && npm run seed`
> Ini akan membuat tabel di Supabase dan mengisi data awal (Admin/Bos).

---

## 3. Persiapan Frontend (Vercel)

1.  Buka [Vercel](https://vercel.com/) dan buat project baru.
2.  Hubungkan repository GitHub Anda.
3.  Arahkan ke folder `frontend`.
4.  Pada bagian **Environment Variables**, tambahkan:
    *   `NEXT_PUBLIC_API_URL`: Masukkan URL Backend dari Koyeb (tambahkan `/api` di ujungnya).
        Contoh: `https://sipekerja-api-abc.koyeb.app/api`
5.  Klik **Deploy**.

---

## Ringkasan Konfigurasi yang Telah Dibuat
- **`backend/prisma/schema.prisma`**: Diubah ke PostgreSQL.
- **`backend/Dockerfile`**: Konfigurasi Docker siap pakai untuk Koyeb.
- **`backend/package.json`**: Ditambahkan script `build` & `start` untuk produksi.
- **`frontend/.env.example`**: Panduan variabel lingkungan untuk Vercel.

---

## Verifikasi
Setelah semua selesai:
1. Akses URL Vercel Anda.
2. Coba login dengan user Admin atau Bos.
3. Cek apakah chart dan data muncul dengan benar.

---
© 2024 SIPEKERJA Deployment Config
