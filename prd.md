# Product Requirements Document (PRD): SIPEKERJA (Sistem Penilaian Kinerja)

**Version:** 1.0.0  
**Status:** Draft / In-Review  
**Target Platform:** Web-Based Dashboard  

---

## 1. Overview
SIPEKERJA adalah platform manajemen kinerja internal untuk BPS Provinsi yang bertujuan untuk memfasilitasi penilaian pegawai secara bulanan. Sistem ini dirancang untuk mengatasi kompleksitas hierarki organisasi di mana satu individu dapat memiliki peran ganda (multiple roles), serta memberikan transparansi bagi pimpinan dalam memonitor kedisiplinan evaluasi tim.

### Objective
* Digitalisasi proses penilaian kinerja yang sebelumnya manual.
* Menyediakan dashboard rekapitulasi nilai (rata-rata dan total) untuk pimpinan.
* Memberikan sistem peringatan (monitoring) bagi ketua tim yang belum mengisi nilai.

---

## 2. Requirements

### 2.1 Functional Requirements
* **Multi-Role Switching:** User dapat berganti peran tanpa harus logout jika memiliki lebih dari satu jabatan (misal: Kabag Umum sekaligus Anggota Tim).
* **Monthly Evaluation Cycle:** Penilaian dibuka setiap tanggal 25 hingga akhir bulan.
* **Dynamic Team Assignment:** Admin dapat memindahkan pegawai ke dalam tim yang berbeda-beda.
* **Hierarchical Visibility:** Atasan hanya bisa melihat data bawahan sesuai strukturnya, sedangkan Admin bisa melihat semuanya.
* **Export Engine:** Export rekapitulasi penilaian ke format PDF dan Excel.

### 2.2 Non-Functional Requirements
* **Security:** Autentikasi berbasis JWT atau Session dengan enkripsi password Argon2/Bcrypt.
* **Performance:** Laporan rekapitulasi harus dimuat dalam waktu < 2 detik.
* **Mobile Responsive:** Dashboard dapat diakses dengan baik melalui perangkat tablet atau smartphone.

---

## 3. Core Features

### A. User & Role Management
* **Super Admin:** Manajemen User (Create/Update/Delete), penetapan Role, dan manajemen master data (Jabatan/Unit Kerja).
* **Role Switcher:** UI element untuk berpindah konteks peran bagi user dengan multiple roles.

### B. Team Management
* Pembuatan struktur tim.
* Penunjukan **Ketua Tim** dan **Anggota Tim**.

### C. Performance Rating (Monthly)
* **Form Penilaian:** Input nilai numerik (1-100) berdasarkan indikator yang ditentukan.
* **Feedback Column:** Catatan kualitatif dari Ketua Tim untuk Anggota.
* **Status Tracker:** Indikator "Draft" (tersimpan lokal) dan "Submitted" (final).

### D. Monitoring & Analytics (Pimpinan)
* **Progress Bar:** Persentase penyelesaian penilaian oleh para Ketua Tim.
* **Unrated List:** Daftar Ketua Tim yang belum melakukan penilaian hingga tenggat waktu.
* **Performance Ranking:** Rekapitulasi nilai tertinggi dan terendah dalam satu provinsi.

---

## 4. User Flow

1.  **Authentication:** User login menggunakan NIP & Password.
2.  **Context Selection:** Jika user memiliki >1 role, sistem meminta user memilih "Masuk sebagai [Role]".
3.  **Process (Ketua Tim):** * Pilih Menu "Penilaian Tim".
    * Pilih daftar anggota yang tersedia.
    * Isi nilai & Simpan.
4.  **Process (Pimpinan):**
    * Lihat "Dashboard Overview".
    * Filter berdasarkan "Bulan/Tahun" atau "Tim".
    * Unduh laporan jika diperlukan.

---

## 5. Architecture
Sistem menggunakan pola **Client-Server Architecture**.

* **Frontend:** Single Page Application (SPA).
* **Backend:** RESTful API dengan struktur Modular Monolith.
* **Authorization:** Middleware pengecekan Role untuk setiap endpoint.

---

## 7. Database Schema

Sistem ini menggunakan pendekatan relasional untuk mendukung struktur organisasi yang dinamis dan fitur *multi-role*.

### Entity Relationship Logic

* **`users`**: Menyimpan data autentikasi dan profil dasar.
    * `id`, `nip`, `name`, `email`, `password`
* **`roles`**: Daftar hak akses yang tersedia.
    * `id`, `role_name` (Contoh: Admin, Kepala BPS, Kabag Umum, Ketua Tim, Pegawai)
* **`user_roles`**: Tabel penghubung untuk mendukung satu user memiliki banyak role (*Many-to-Many*).
    * `user_id`, `role_id`
* **`teams`**: Mendefinisikan kelompok kerja.
    * `id`, `team_name`, `leader_id` (FK ke `users.id`)
* **`team_members`**: Daftar anggota di dalam setiap tim.
    * `team_id`, `user_id`
* **`ratings`**: Data inti penilaian kinerja bulanan.
    * `id`, `evaluator_id` (Ketua Tim), `target_user_id` (Pegawai), `team_id`, `score`, `notes`, `period_month`, `period_year`, `created_at`

---

## 8. Tech Stack

Pemilihan stack teknologi difokuskan pada stabilitas, kecepatan pengembangan, dan kemudahan pengolahan data laporan.

| Layer | Technology |
| :--- | :--- |
| **Frontend** | React.js / Next.js (App Router) |
| **Styling** | Tailwind CSS |
| **UI Components** | Shadcn UI atau Ant Design |
| **Backend** | Node.js (NestJS/Express) |
| **Database** | PostgreSQL |
| **State Management** | Zustand (Handling global state & role switching) |
| **Reporting** | ExcelJS (Excel) & PDFKit (PDF) |

---

## 9. Design Guidelines

Desain harus mencerminkan identitas profesional instansi dengan navigasi yang intuitif.

* **Color Palette**:
    * **Primary**: `#003366` (BPS Blue) - Digunakan untuk elemen utama dan branding.
    * **Secondary**: `#FFC107` (Amber) - Digunakan untuk status pending, peringatan, atau *call-to-action* sekunder.
* **Typography**:
    * Menggunakan font Sans-serif (**Inter** atau **Roboto**) untuk keterbacaan data angka yang lebih baik.
* **Layout Structure**:
    * **Desktop**: Sidebar navigation untuk akses cepat ke berbagai modul manajemen.
    * **Mobile**: Bottom navigation untuk memudahkan pegawai melihat nilai atau ketua tim mengisi evaluasi saat mobile.

---

## 10. Development Process Flow

Pengembangan akan dibagi menjadi 5 fase utama (Sprints):

1.  **Phase 1 (Sprint 1-2) - Foundation**:
    * Setup Database, implementasi Autentikasi, dan sistem RBAC (Role Based Access Control).
2.  **Phase 2 (Sprint 3) - Organization & Switching**:
    * CRUD Manajemen Tim, alokasi Anggota, dan pembuatan UI untuk fitur *Role Switcher*.
3.  **Phase 4 (Sprint 4) - Core Engine**:
    * Pengembangan formulir penilaian dan logika validasi periode bulanan.
4.  **Phase 4 (Sprint 5) - Reporting & Analytics**:
    * Pembuatan dashboard ringkasan, query agregasi nilai, dan engine untuk export laporan.
5.  **Phase 5 (Sprint 6) - Finalization**:
    * User Acceptance Testing (UAT), perbaikan bug, dan deployment ke server lingkungan BPS.