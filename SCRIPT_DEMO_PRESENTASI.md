# 🎤 SCRIPT DEMO PRESENTASI CATTLEPRO
**Presenter: [Nama Kamu]**  
**Tim: [Nama Kamu], Darma, Fia, Mika**  
**Durasi: 10-15 menit**

---

## 🎯 STRUKTUR PRESENTASI

| Bagian | Durasi |
|--------|--------|
| **Pembukaan + Dashboard** | 3 menit |
| **Manajemen Data Sapi** | 3 menit |
| **Smart Cycle Reproduksi** | 4 menit |
| **Monitoring & Penutup** | 3-4 menit |

---

## 🌟 KEY HIGHLIGHTS (Poin Penting untuk Ditekankan)

### Keunggulan Teknis:
1. ✅ **Production-Grade Infrastructure** - Bukan hanya prototype, sudah deployed dengan Docker + Dokploy
2. ✅ **Modern Database** - PostgreSQL untuk reliability dan scalability
3. ✅ **API-First Architecture** - GraphQL untuk flexible dan efficient data fetching
4. ✅ **Cloud Integration** - Firebase Google Cloud untuk realtime sync
5. ✅ **Multi-Channel Notification** - WhatsApp, Telegram, Email terintegrasi
6. ✅ **Containerized Deployment** - Docker untuk consistency across environments
7. ✅ **Auto SSL & Monitoring** - Dokploy handle security dan uptime monitoring

### Keunggulan Fungsional:
1. ✅ **Smart Cycle Automation** - Sistem otomatis guide user step-by-step
2. ✅ **Intelligent Notifications** - Alert tepat waktu untuk tindakan kritis
3. ✅ **Realtime Monitoring** - Live tracker untuk semua sapi
4. ✅ **Predictive Scoring** - Algoritma prediksi kesiapan reproduksi
5. ✅ **Complete Audit Trail** - Log aktivitas dan timeline riwayat lengkap
6. ✅ **Mobile-Friendly** - Responsive design untuk akses di lapangan
7. ✅ **Role-Based Access** - Admin dan petugas dengan hak akses berbeda

---

## 📝 SCRIPT LENGKAP

### 1️⃣ PEMBUKAAN - [Nama Kamu] (3 menit)

**[Slide: Judul Project]**

> "Selamat pagi Bapak/Ibu. Kami dari kelompok 2 alur nalar dari golongan B akan mempresentasikan project kami yang berjudul **CattlePro - Sistem Informasi Manajemen Reproduksi Sapi Berbasis Website**."

**[Slide: Latar Belakang]**

> "Latar belakang project ini adalah masih banyak peternak yang mencatat siklus reproduksi sapi secara manual menggunakan buku atau Excel. Hal ini sering menyebabkan:
> - Terlewatnya waktu optimal untuk inseminasi
> - Tidak ada pengingat jadwal pemeriksaan kebuntingan
> - Sulit melacak riwayat reproduksi setiap sapi
> 
> Oleh karena itu, kami membuat CattlePro untuk mendigitalisasi proses ini dengan teknologi modern dan infrastruktur yang scalable."

**[Slide: Fitur Utama]**

> "CattlePro memiliki 4 fitur utama:
> 1. Dashboard dengan notifikasi cerdas
> 2. Manajemen data sapi lengkap
> 3. Smart Cycle reproduksi otomatis
> 4. Monitoring dan prediksi realtime
> 
> Baik, langsung saya demo aplikasinya."

**[DEMO: Login]**

> "Pertama, kita login menggunakan akun admin."
> 
> *[Ketik email: admin@cattlepro.com, password: admin123]*
> 
> "Setelah login, kita masuk ke halaman Dashboard."

**[DEMO: Dashboard]**

> "Baik, ini adalah halaman Dashboard. Sebelum saya jelaskan fitur-fiturnya, perlu kami sampaikan bahwa CattlePro ini Kami sudah deploy aplikasi ini ke VPS-Hosting menggunakan Docker dan Dokploy sebagai platform deployment-nya. Untuk database, kami pakai PostgreSQL sebagai database engine nya. karena lebih powerful dan reliable untuk handle data relasional yang kompleks, plus kami integrasikan juga dengan Firebase Google Cloud untuk authentication dan realtime synchronization. API-nya sendiri kami bangun dengan GraphQL, jadi lebih flexible dan efficient dalam fetching data. Intinya, ini adalah aplikasi production-ready yang bisa diakses dari mana saja dan siap digunakan di peternakan real dengan skala besar."
> 
> "Nah, di Dashboard ini kita bisa lihat:
> - **Statistik populasi** - Total sapi, berapa yang birahi, bunting, dan gagal hamil
> - **Grafik distribusi** - Visualisasi status reproduksi dalam bentuk bar chart
> - **Notifikasi cerdas** - Ini yang paling penting. Sistem otomatis memberikan alert untuk:
>   * Sapi yang perlu IB segera karena masih dalam masa subur
>   * Jadwal PKB yang mendekati
>   * Estimasi kelahiran yang tinggal 30 hari lagi
> - **Log aktivitas** - Semua pencatatan yang dilakukan tim tercatat di sini
> 

---

### 2️⃣ MANAJEMEN DATA SAPI - Darma (3 menit)

**[DEMO: Halaman Sapi]**

 
> *[Klik menu 'Kelola Sapi']*
> 
> "Di halaman ini kita bisa lihat semua data sapi yang terdaftar. Ada informasi:
> - Kode sapi
> - Jenis sapi - seperti Limousin, Simental, PO, Bali
> - Tanggal lahir dan berat badan
> - Status reproduksi dengan badge berwarna untuk memudahkan identifikasi
> 
> Kita juga bisa **search** berdasarkan kode atau jenis sapi."

**[DEMO: Tambah Sapi Baru]**

> "Sekarang saya coba tambah sapi baru."
> 
> *[Klik tombol 'Tambah Sapi Baru']*
> 
> "Kita isi form:
> - Kode sapi: misalnya **LIM-025**
> - Jenis: **Limousin**
> - Tanggal lahir: **[pilih tanggal]**
> - Berat badan: **350 kg**
> - Status awal: **Kosong**
> 
> *[Klik Simpan]*
> 
> Dan data sapi berhasil ditambahkan. Kita bisa lihat sapi baru muncul di tabel."

**[DEMO: Lihat Detail]**

> "Untuk melihat detail dan mengelola siklus reproduksi, kita klik tombol 'Detail' pada salah satu sapi. "

### 3️⃣ SMART CYCLE REPRODUKSI - Fia (4 menit)

**[DEMO: Detail Sapi - Status Kosong]**
> 
> "Sistem ini mengikuti alur reproduksi sapi secara bertahap. Sekarang sapi ini statusnya **Kosong**, artinya sedang dalam pengawasan normal."

**[DEMO: Lapor Birahi]**

> "Misalnya hari ini kita deteksi sapi ini birahi. Kita klik **'Simpan Birahi'** dan isi tanggal serta jam deteksi birahi."
> 
> *[Isi form birahi, klik Simpan]*
> 
> "Setelah disimpan, status otomatis berubah jadi **Sudah Birahi**. Dan yang penting, sistem langsung memberikan **instruksi jadwal IB optimal**, yaitu 12 jam setelah deteksi birahi."

Jelasin API whatsapp dan telegram di sini

**[DEMO: Input IB]**

> "Setelah kita lakukan inseminasi buatan, kita input data IB-nya."
> 
> *[Klik 'Simpan Data IB', isi tanggal IB]*
> 
> "Status berubah jadi **Sudah IB**. Sistem sekarang otomatis menjadwalkan:
> - **Pantau birahi ulang** di hari ke-21
> - **Cek kebuntingan (PKB)** di hari ke-60
> 
> Ini membantu petugas tidak lupa jadwal pemeriksaan."

**[DEMO: Input PKB]**

> "Sekarang kita simulasikan sudah 60 hari, dan kita lakukan PKB. Kita input hasil pemeriksaan."
> 
> *[Klik 'Pemeriksaan Kebuntingan', pilih 'Bunting', klik Simpan]*
> 
> "Jika hasil PKB positif bunting, status berubah jadi **Bunting** dan sistem otomatis menghitung **estimasi kelahiran (HPL)** yaitu 283 hari sejak Inseminasi Buatan."

**[DEMO: Integrasi Notifikasi Eksternal]**

> "Yang menarik dari sistem ini adalah **integrasi notifikasi multi-channel**. Setiap kali kita melakukan pencatatan seperti tadi, sistem otomatis mengirimkan notifikasi melalui:
> 
> 1. **WhatsApp API** - Menggunakan Fonnte API untuk mengirim pesan konfirmasi dan pengingat ke nomor petugas
> 2. **Telegram Bot** - Notifikasi realtime ke grup Telegram tim peternakan
> 3. **Email** - untuk lupa password
> 
> Jadi petugas di lapangan langsung dapat notifikasi di HP mereka tanpa harus buka aplikasi web."

**[DEMO: Timeline Riwayat]**

> "Di bagian bawah halaman detail ini, ada **timeline riwayat reproduksi dan arsip birahi** yang mencatat semua kejadian dari awal birahi sampai sekarang. Ini sangat membantu untuk tracking dan evaluasi."
> 
> "Untuk monitoring keseluruhan, saya serahkan ke Mika."

---

### 4️⃣ MONITORING & PENUTUP - Mika (3 menit)

**[DEMO: Halaman Monitoring]**

> *[Klik menu 'Live Tracker']*
> 
> "Di halaman ini kita bisa monitor **semua sapi secara realtime** dalam satu tabel. Kita bisa lihat:
> - Status reproduksi terkini
> - Jadwal tindakan selanjutnya - kapan harus IB, kapan harus PKB
> - Update terakhir - kapan terakhir kali data sapi ini diubah, dan siapa yang mengubah
> 
> Ini sangat membantu koordinasi tim, karena semua orang bisa lihat jadwal yang sama."

**[DEMO: Prediksi Kesiapan]**

> "Selain itu, ada fitur **prediksi kesiapan reproduksi** yang menggunakan algoritma scoring berdasarkan 3 parameter:
> - **Umur** - optimal 15-18 bulan
> - **Berat badan** - optimal 300-400 kg
> - **Siklus birahi** - minimal 3 data dengan interval 18-24 hari
> 
> Sistem akan memberikan skor dan rekomendasi apakah sapi sudah siap reproduksi atau belum."



**[DEMO: Notifikasi Multi-Channel]**

> "Sekarang saya tunjukkan salah satu fitur unggulan kami, yaitu **notifikasi multi-channel**."
> 
> *[Buka tab WhatsApp Web atau tunjukkan screenshot notifikasi]*
> 
> "Setiap kali ada pencatatan, sistem otomatis mengirim notifikasi ke:
> 
> 1. **WhatsApp** - Menggunakan Fonnte API, pesan langsung ke HP petugas berisi konfirmasi dan jadwal tindakan selanjutnya
dan untuk notifikasi wa sendiri, akan mengirim 2x pesan. saat pencatatan dan saat telah sampai pada waktu jadwal tindakan selanjutnya sebagai pengingat.
> 2. **Telegram** - Notifikasi ke tim peternakan untuk koordinasi realtime
> Ini sangat membantu karena petugas di lapangan tidak perlu selalu buka aplikasi web. Mereka langsung dapat pengingat di HP."

**[Slide: Tech Stack & Arsitektur]**

> "Dari sisi teknis, seperti yang sudah disampaikan tadi, CattlePro dibangun dengan arsitektur modern:
> 
> **Backend:** PHP 8 dengan GraphQL API untuk flexible data fetching
> **Database:** PostgreSQL yang reliable dan Firebase untuk realtime sync
> **Infrastructure:** Docker containerization, deployed via Dokploy di VPS
> **Integration:** WhatsApp API, Telegram Bot API official
> 
> Aplikasi ini juga **mobile-friendly**, jadi bisa diakses dari smartphone di lapangan."

**[Slide: Kesimpulan]**

> "Jadi kesimpulannya, CattlePro adalah solusi digital yang:
> ✅ Mencatat siklus reproduksi secara terstruktur dengan Smart Cycle automation
> ✅ Memberikan notifikasi multi-channel - WhatsApp, dan Telegram
> ✅ Monitoring realtime seluruh populasi sapi dalam satu dashboard
> ✅ Dibangun dengan teknologi modern - PostgreSQL, GraphQL, Firebase, Docker
> ✅ Production-ready dengan deployment profesional di VPS hosting
> 
> Dengan CattlePro, peternak bisa mengelola reproduksi sapi lebih efisien dan meningkatkan tingkat keberhasilan breeding."

**[Slide: Terima Kasih]**

> "Sekian presentasi dari kami. Terima kasih atas perhatiannya. Kami mengundang bapak/ibu sekalian untuk lanjut ke sesi tanya jawab"

---

## 🏗️ ARSITEKTUR TEKNIS (Untuk Pertanyaan Teknis)

### Database Architecture
```
PostgreSQL (Primary Database)
├── Relational Tables (sapi, users, birahi, log_aktivitas)
├── JSON Support (untuk flexible data structure)
├── Full-text Search (untuk pencarian cepat)
└── Concurrent Access (multiple users realtime)

Firebase Realtime Database
├── User Authentication & Authorization
├── Realtime Sync antar device
└── Push Notifications
```

### API Layer
```
GraphQL API
├── Single Endpoint (/graphql)
├── Type-safe queries
├── Flexible data fetching (no over-fetching)
└── Real-time subscriptions untuk live updates
```

### External API Integrations
```
1. WhatsApp (Fonnte API)
   - Endpoint: https://api.fonnte.com/send
   - Method: POST dengan token authentication
   - Trigger: Setiap pencatatan reproduksi
   - Response time: < 2 detik

2. Telegram Bot API
   - Bot Token dari @BotFather
   - Send message ke grup via chat_id
   - Support markdown formatting
   - Instant notification

3. Email (Resend.com)
   - Service: Resend API untuk transactional email
   - Use case: Reset password & forgot password flow
   - Template HTML untuk email reset password
   - Reliable delivery dengan tracking status
   - Template HTML untuk laporan detail
   - Attachment support untuk export data
```

### Deployment Architecture
```
VPS Server (Cloud Provider)
├── Dokploy (Deployment Platform)
│   ├── Auto SSL (Let's Encrypt)
│   ├── Environment Management
│   ├── One-click Rollback
│   └── Health Monitoring
│
├── Docker Containers
│   ├── App Container (PHP-FPM + Nginx)
│   ├── PostgreSQL Container
│   └── Redis Container (untuk caching)
│
└── Reverse Proxy (Nginx)
    ├── Load Balancing
    ├── SSL Termination
    └── Static File Serving
```

### Data Flow
```
User Action → Frontend → GraphQL API → Business Logic
                                            ↓
                                    PostgreSQL (Write)
                                            ↓
                                    Firebase (Sync)
                                            ↓
                        ┌───────────────────┴───────────────────┐
                        ↓                   ↓                   ↓
                WhatsApp API        Telegram API          Email (Resend)
                        ↓                   ↓                   ↓
                  Petugas HP          Grup Telegram      Email Inbox
```

### Contoh Template Notifikasi

**WhatsApp Message (via Fonnte):**
```
🐄 CattlePro Alert

Sapi: LIM-025 (Limousin)
Status: SUDAH BIRAHI ✅

📅 Jadwal IB Optimal:
   18 Januari 2026, 14:00 WIB
   (12 jam dari deteksi birahi)

⚠️ Segera lakukan inseminasi buatan!

Dicatat oleh: Admin Peternakan
Waktu: 18 Jan 2026, 02:00 WIB
```

**Telegram Message:**
```
🔔 Notifikasi CattlePro

📊 Pemeriksaan Kebuntingan (PKB)
Sapi: SIM-012 (Simental)
Hasil: BUNTING 🎉

📅 Estimasi Kelahiran (HPL):
   28 Oktober 2026 (283 hari dari IB)

✅ Status diupdate ke: BUNTING
👤 Oleh: Petugas Fia
🕐 18 Jan 2026, 10:30 WIB
```

**Email Subject & Body:**
```
Subject: [CattlePro] Laporan Kelahiran - Sapi PO-008

Yth. Tim Peternakan,

Laporan kelahiran sapi telah dicatat dalam sistem:

Kode Sapi: PO-008
Jenis: Peranakan Ongole (PO)
Tanggal Kelahiran: 18 Januari 2026
Status Anak: Sehat
Jenis Kelamin: Betina

Sapi induk telah direset ke status KOSONG dan siap untuk 
siklus reproduksi berikutnya.

Timeline lengkap dapat dilihat di:
https://cattlepro.yourdomain.com/detail_sapi.php?id=8

---
Sistem CattlePro
Automated Notification System
```

---

## 💡 TIPS PRESENTASI

### Persiapan Sebelum Demo:
1. ✅ **Pastikan aplikasi sudah running** di localhost
2. ✅ **Siapkan data dummy** yang cukup (minimal 5-7 sapi dengan berbagai status)
3. ✅ **Test semua fitur** sebelum presentasi
4. ✅ **Buka tab browser** yang diperlukan sebelumnya
5. ✅ **Zoom tampilan browser** agar audience bisa lihat jelas (Ctrl + Plus)

### Saat Demo:
- 🎯 **Jangan terburu-buru** - beri jeda agar audience bisa lihat
- 🎯 **Jelaskan sambil klik** - "Sekarang saya klik tombol ini..."
- 🎯 **Tunjuk dengan cursor** - gerakkan mouse ke elemen yang dijelaskan
- 🎯 **Jika ada error** - tetap tenang, jelaskan bahwa ini masih prototype
- 🎯 **Highlight tech stack** - Tekankan penggunaan PostgreSQL, GraphQL, Docker, Dokploy
- 🎯 **Tunjukkan notifikasi** - Jika bisa, buka WhatsApp/Telegram untuk show real notification

### Pembagian Peran:
- **[Nama Kamu]**: Kontrol laptop, navigasi antar halaman, jelaskan arsitektur
- **Darma**: Siap bantu jika ada technical issue, backup untuk demo
- **Fia**: Fokus ke demo Smart Cycle dan API integration
- **Mika**: Demo monitoring, deployment, dan handle Q&A teknis

### Tips Menjawab Pertanyaan Teknis:
- Jika ditanya detail PostgreSQL: "Kami pilih PostgreSQL karena support JSON native, better concurrency, dan lebih robust untuk production"
- Jika ditanya GraphQL: "GraphQL memungkinkan client request hanya data yang dibutuhkan, mengurangi bandwidth usage"
- Jika ditanya Docker: "Docker memastikan aplikasi berjalan konsisten di development, staging, dan production"
- Jika ditanya Dokploy: "Dokploy adalah modern deployment platform yang simplify deployment process dengan auto SSL dan monitoring"

---

## ❓ ANTISIPASI PERTANYAAN

**Q: Apakah ada notifikasi via WhatsApp atau email?**
> A: "Ya, sudah terintegrasi. Kami menggunakan Fonnte API untuk WhatsApp, Telegram Bot API untuk notifikasi grup, dan SMTP service untuk email. Setiap pencatatan otomatis trigger notifikasi ke semua channel."

**Q: Bagaimana cara kerja integrasi WhatsApp-nya?**
> A: "Kami menggunakan Fonnte sebagai WhatsApp Gateway. Setiap kali ada event penting (birahi, IB, PKB), sistem mengirim HTTP request ke Fonnte API dengan template pesan yang sudah kami buat. Fonnte kemudian forward pesan tersebut ke nomor WhatsApp petugas."

**Q: Bagaimana jika sapi gagal hamil?**
> A: "Ada status 'Gagal Hamil' yang bisa dipilih saat PKB. Status ini memungkinkan evaluasi sebelum sapi di-reset kembali ke status Kosong untuk memulai siklus baru."

**Q: Apakah bisa export data atau cetak laporan?**
> A: "Fitur cetak laporan PDF sedang dalam tahap pengembangan. Saat ini data bisa dilihat dan dimonitor secara realtime di aplikasi."

**Q: Apakah ada backup data?**
> A: "Data tersimpan di database MySQL yang bisa di-backup secara berkala. Kami juga mencatat log aktivitas untuk audit trail."

**Q: Kenapa pakai PostgreSQL bukan MySQL?**
> A: "PostgreSQL kami pilih karena lebih stabil dan powerful untuk aplikasi production. Database ini bisa handle banyak user akses bersamaan tanpa masalah, dan punya fitur pencarian yang lebih cepat. Ini penting karena aplikasi ini dirancang untuk bisa dipakai oleh banyak peternakan sekaligus kedepannya."

**Q: Apa itu Dokploy?**
> A: "Dokploy adalah platform deployment modern yang memudahkan kita untuk upload aplikasi ke server. Mirip seperti kita upload file ke Google Drive, tapi ini khusus untuk aplikasi web. Dokploy otomatis handle SSL certificate untuk keamanan, monitoring untuk cek aplikasi jalan atau tidak, dan kalau ada masalah bisa rollback ke versi sebelumnya dengan mudah."

**Q: Kenapa pakai GraphQL?**
> A: "GraphQL kami pakai karena lebih flexible dan efficient. Jadi aplikasi bisa minta data yang spesifik sesuai kebutuhan, tidak perlu download semua data. Ini bikin aplikasi lebih cepat, terutama kalau diakses dari HP dengan koneksi internet yang terbatas."

**Q: Bagaimana dengan keamanan data?**
> A: "Untuk keamanan, kami implement beberapa layer proteksi: Firebase Authentication untuk login yang aman, HTTPS untuk enkripsi data saat transfer, dan prepared statements untuk mencegah SQL injection. Data juga di-backup rutin ke cloud storage, jadi kalau ada masalah data tidak hilang."

**Q: Berapa lama waktu pengembangannya?**
> A: "Project ini dikembangkan selama [sesuaikan dengan timeline kalian] dengan pembagian tugas: [Nama Kamu] fokus di backend dan database, Darma di frontend, Fia di fitur reproduksi dan API integration, dan Mika di deployment dan monitoring."

---

## 🎬 CHECKLIST HARI H

**1 Jam Sebelum Presentasi:**
- [ ] Laptop fully charged + bawa charger
- [ ] Test koneksi internet (jika perlu)
- [ ] Buka aplikasi dan test semua fitur
- [ ] Siapkan data dummy yang bervariasi
- [ ] **Siapkan screenshot notifikasi WA/Telegram** (jika tidak bisa demo live)
- [ ] **Test GraphQL endpoint** (jika akan demo API)
- [ ] Buka slide presentasi
- [ ] Atur zoom browser ke 125-150%
- [ ] **Siapkan tab untuk show VPS/Dokploy dashboard** (optional tapi impressive)

**15 Menit Sebelum:**
- [ ] Tutup aplikasi yang tidak perlu
- [ ] Matikan notifikasi (Focus Mode)
- [ ] Test proyektor/screen sharing
- [ ] Koordinasi pembagian tugas dengan tim
- [ ] **Pastikan semua tab demo sudah terbuka** (localhost, WhatsApp Web, Telegram Web)

**Saat Presentasi:**
- [ ] Bicara dengan jelas dan tidak terlalu cepat
- [ ] Eye contact dengan audience
- [ ] Tunjukkan antusiasme terhadap project
- [ ] Koordinasi smooth antar pembicara
- [ ] **Highlight tech stack yang modern** (PostgreSQL, GraphQL, Docker)
- [ ] **Tunjukkan notifikasi real** (jika memungkinkan)

---

## 🎯 "WOW FACTOR" MOMENTS

Ini adalah momen-momen yang akan membuat presentasi kalian stand out:

### 1. Live Notification Demo (Jika Memungkinkan)
> Saat Fia demo input data IB, langsung show WhatsApp/Telegram yang menerima notifikasi realtime. Ini akan sangat impressive!

**Setup:**
- Buka WhatsApp Web di tab terpisah
- Saat input data, switch ke tab WhatsApp
- Show pesan yang baru masuk
- Audience akan kagum dengan integrasi realtime

### 2. Show Production URL
> Saat Mika bagian deployment, buka URL production di browser baru dan show bahwa aplikasi bisa diakses dari internet.

**Script:**
> "Aplikasi ini bukan hanya jalan di localhost. Ini URL production-nya: [sebutkan URL]. Siapapun bisa akses dari mana saja."

### 3. Docker Container Demo (Optional - Jika Waktu Ada)
> Buka terminal dan show `docker ps` untuk tunjukkan container yang running.

**Script:**
> "Ini container Docker yang sedang running. Kita punya app container, PostgreSQL, dan Redis untuk caching."

### 4. GraphQL Playground (Optional - Untuk Audience Teknis)
> Jika audience-nya teknis (dosen IT), buka GraphQL playground dan show query example.

**Script:**
> "Ini GraphQL endpoint kami. Client bisa request hanya field yang dibutuhkan, jadi lebih efficient daripada REST API tradisional."

### 5. Database Schema Visualization
> Show diagram ERD atau screenshot dari database tool (pgAdmin/DBeaver) untuk tunjukkan struktur data yang well-designed.

---

## 🚀 GOOD LUCK!

**Ingat:** Kalian sudah kerja keras bikin project ini. Presentasi adalah kesempatan untuk showcase hasil kerja kalian. Be confident! 💪

**"The best demo is a working demo."** - Pastikan semua fitur jalan dengan baik.

---

## 📊 VISUAL AIDS (Untuk Slide Presentasi)

### Slide 1: Arsitektur Sistem
```
┌─────────────────────────────────────────────────────────┐
│                    USER INTERFACE                        │
│              (Web App - Responsive Design)               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────┐
│                   GRAPHQL API LAYER                      │
│         (Flexible Queries, Type-Safe, Realtime)          │
└────────────────────┬────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        ↓            ↓            ↓
┌──────────┐  ┌──────────┐  ┌──────────┐
│PostgreSQL│  │ Firebase │  │  Redis   │
│ (Primary)│  │  (Auth)  │  │ (Cache)  │
└──────────┘  └──────────┘  └──────────┘
        │
        └─────────────┬─────────────────────────┐
                      ↓                         ↓
        ┌─────────────────────┐    ┌─────────────────────┐
        │  NOTIFICATION APIs   │    │   CLOUD SERVICES    │
        ├─────────────────────┤    ├─────────────────────┤
        │ • WhatsApp (Fonnte) │    │ • Google Cloud      │
        │ • Telegram Bot      │    │ • VPS Hosting       │
        │ • Email (Resend)    │    │ • Docker/Dokploy    │
        └─────────────────────┘    └─────────────────────┘
```

### Slide 2: Smart Cycle Flow
```
    KOSONG ──[Deteksi Birahi]──► SUDAH BIRAHI
       ▲                              │
       │                              │
       │                         [Input IB]
       │                              │
       │                              ↓
       │                         SUDAH IB
       │                              │
       │                         [PKB H+60]
       │                              │
       │                    ┌─────────┴─────────┐
       │                    │                   │
       │                 Positif             Negatif
       │                    │                   │
       │                    ↓                   ↓
       │                 BUNTING          GAGAL HAMIL
       │                    │                   │
       │              [HPL H+283]          [Evaluasi]
       │                    │                   │
       │              [Kelahiran]               │
       │                    │                   │
       └────────────────────┴───────────────────┘
```

### Slide 3: Notification Flow
```
User Input → System Processing → Multi-Channel Notification
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    ↓                     ↓                     ↓
            ┌──────────────┐      ┌──────────────┐    ┌──────────────┐
            │  WhatsApp    │      │  Telegram    │    │    Email     │
            │              │      │              │    │              │
            │ • Instant    │      │ • Group      │    │ • Detailed   │
            │ • Personal   │      │ • Realtime   │    │ • Archive    │
            │ • Reminder   │      │ • Team Sync  │    │ • Report     │
            └──────────────┘      └──────────────┘    └──────────────┘
```

### Slide 4: Tech Stack Overview
```
┌─────────────────────────────────────────────────────────┐
│                      FRONTEND                            │
│  Tailwind CSS • Chart.js • Responsive Design             │
└─────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────┐
│                      BACKEND                             │
│  PHP 8 • GraphQL • RESTful API                           │
└─────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────┐
│                      DATABASE                            │
│  PostgreSQL • Firebase • Redis Cache                     │
└─────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────┐
│                   INFRASTRUCTURE                         │
│  Docker • Dokploy • VPS • Google Cloud                   │
└─────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────┐
│                    INTEGRATIONS                          │
│  WhatsApp API • Telegram Bot • Email (Resend.com)       │
└─────────────────────────────────────────────────────────┘
```

---

*Script ini dibuat untuk presentasi demo CattlePro. Sesuaikan dengan durasi dan format presentasi yang diminta dosen.*
