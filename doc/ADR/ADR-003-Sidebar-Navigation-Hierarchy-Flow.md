# ADR-003: Sidebar Navigation & Data Hierarchy Flow

Status: Accepted

Date: 2026-07-17

---

# Context

Sidebar admin saat ini memiliki 12 item navigasi secara flat (rata), termasuk Component, Sub Component, Indicator, dan Question sebagai item terpisah. Struktur ini tidak mencerminkan hierarki data yang sebenarnya (Instrument → Component → Sub Component → Indicator → Pertanyaan) dan menimbulkan kebingungan navigasi.

Selain itu, Component, Sub Component, Indicator, dan Pertanyaan memiliki relasi parent-child yang kuat. Menampilkan mereka sebagai item sidebar terpisah kehilangan konteks hierarki.

---

# Decision

## 1. Sidebar Structure

Sidebar direduksi menjadi item-item utama saja:

```
Dashboard
─── Master Data ───
  Period
  Instrument Penelitian
─── Pelaksanaan ───
  Responden
  Monitoring
─── Lainnya ───
  Reports
  Settings
```

Component, Sub Component, Indicator, dan Pertanyaan **dihapus dari sidebar** dan diakses melalui mekanisme **drill-down table** dari halaman Instrument Penelitian.

## 2. Navigation Flow

```
Sidebar: Instrument Penelitian
  → Tabel Instrument
    → Klik "Lihat Component"
      → Tabel Component (context: instrument yang dipilih)
        → Klik "Lihat Sub"
          → Tabel Sub Component
            → Klik "Lihat Indikator"
              → Tabel Indikator
                → Klik "Lihat Pertanyaan"
                  → Tabel Pertanyaan (leaf level)
```

**Navigasi antar instrument:** Klik "Instrument Penelitian" di breadcrumb → balik ke tabel Instrument → pilih instrument lain.

## 3. Navigation Elements

- **Breadcrumbs:** Klikable, warna hijau + hover underline. Update mengikuti level saat ini. Berfungsi sebagai navigasi utama antar instrument.
- **Tombol "← Kembali":** Ada di header setiap halaman drill-down (Sub Component s.d. Pertanyaan).
- **Info Bar:** Di halaman Component, menampilkan informasi instrument yang sedang dilihat (contoh: "Menampilkan component dari Instrument 2024. Klik breadcrumb untuk ganti instrument.").
- **More Menu (⋮):** Untuk semua aksi CRUD di setiap tabel. Hover menampilkan dropdown dengan opsi: View Detail, Edit, Toggle Status, Hapus.

## 4. Wording Changes

- "Questionnaire" → **"Instrument Penelitian"**
- "Component / Aspek" → **"Component"**
- "Recommendation" →belum diimplementasi (nanti dulu)

---

# Status Field

| Level | Status Options |
|-------|---------------|
| Instrument Penelitian | Draft / Published / Closed |
| Component | Active / Inactive |
| Sub Component | Active / Inactive |
| Indicator | Active / Inactive |
| Pertanyaan | Active / Inactive |

### Aturan Status:

1. **Tidak ada cascade:** Mengubah status Component ke Inactive tidak mengubah status Sub Component, Indicator, atau Pertanyaan di dalamnya.
2. **Tampilan Inactive:** Item inactive tetap muncul di tabel, tapi di-grey-out dengan badge "Inactive".
3. **Soft delete:** Semua operasi hapus menggunakan soft delete (deleted_at timestamp).

---

# Constraints Based on Instrument Status

| Operasi | Draft | Published | Closed |
|---------|-------|-----------|--------|
| Tambah Component | ✅ | ❌ | ❌ |
| Edit Component | ✅ | ❌ | ❌ |
| Hapus Component (soft delete) | ✅ | ❌ | ❌ |
| Toggle Active/Inactive Component | ✅ | ❌ | ❌ |
| Tambah/Edit/Hapus Sub Component | ✅ | ❌ | ❌ |
| Tambah/Edit/Hapus Indikator | ✅ | ❌ | ❌ |
| Tambah/Edit/Hapus Pertanyaan | ✅ | ❌ | ❌ |

### Kesimpulan:

- **Draft:** Admin bebas menambah, mengedit, menghapus, dan mengubah status semua data.
- **Published:** Semua perubahan struktur **terkunci**. Admin tidak dapat menambah, mengedit, menghapus, atau mengubah status Component s.d. Pertanyaan.
- **Closed:** Semua perubahan struktur **terkunci**. Status data tidak berubah.

Untuk melakukan perubahan struktur pada instrument yang sudah Published, admin harus:
1. Close instrument terlebih dahulu
2. Lakukan perubahan
3. Publish ulang instrument

---

# Actions Per Table

Setiap tabel memiliki **more menu (⋮)** dengan opsi aksi:

| Level | Aksi di More Menu | Aksi Tambahan |
|-------|-------------------|---------------|
| Instrument Penelitian | View, Edit, Delete | "Lihat Component" (link) |
| Component | View, Edit, Delete | "Lihat Sub" (link) |
| Sub Component | View, Edit, Delete | "Lihat Indikator" (link) |
| Indicator | View, Edit, Delete | "Lihat Pertanyaan" (link) |
| Pertanyaan | Edit, Delete | — (leaf level, tidak ada drill-down) |

---

# Consequences

## Advantages

- Sidebar bersih dan tidak membingungkan (5-6 item vs 12 item).
- Hierarki data terwakili dengan baik melalui drill-down table.
- Breadcrumbs memberikan konteks posisi yang jelas dan berfungsi sebagai navigasi antar instrument.
- Navigasi konsisten di semua level.
- Context-aware: Component table selalu menampilkan data dari instrument yang dipilih, tidak ada misleading.
- Status Published/Closed melindungi data integrity instrument yang sudah dipakai responden.
- Soft delete memungkinkan restore data jika diperlukan.
- More menu (⋮) dengan hover dropdown memberikan akses cepat ke semua aksi CRUD.

## Disadvantages

- Butuh 3-4 klik untuk mencapai level Pertanyaan (dari sidebar).
- Admin harus memahami konsep drill-down dan breadcrumbs.
- Perlu state management untuk track posisi saat ini di hierarki.
- Untuk melihat component dari instrument lain, harus navigasi balik ke tabel Instrument (1-2 klik tambahan).

---

# Notes

- Dokumen ini menjadi acuan untuk implementasi navigasi admin panel.
- Halaman konsep UI tersimpan di `doc/sidebar-flow-concept.html`.
- Recommendation belum diimplementasi — akan dibahas terpisah di ADR mendatang.
- Semua mockup UI menggunakan Poppins font (sudah dikonfigurasi di `resources/css/app.css`).
