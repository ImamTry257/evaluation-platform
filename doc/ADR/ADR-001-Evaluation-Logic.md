# ADR-001: Evaluation Logic

Status: Accepted

Date: 2026-07-13

---

# Context

Platform Evaluasi Kebijakan Lingkungan Sekolah dibangun berdasarkan dua dokumen penelitian:

1. Matriks Instrumen Evaluasi Kebijakan Lingkungan Satuan Pendidikan (SMA)
2. Instrumen Kebijakan Lingkungan Sekolah (SMA)

Pada awal perancangan sistem terdapat asumsi bahwa hasil evaluasi dihitung pada level Component (5 aspek utama).

Namun setelah mendapatkan klarifikasi dari pembimbing penelitian, diketahui bahwa pengukuran dilakukan pada 12 aspek dengan 5 kategori penilaian.

---

# Decision

Struktur instrumen tetap mengikuti Matriks Instrumen.

Hierarchy:

Evaluation Period

↓

Questionnaire

↓

Component

↓

Sub Component

↓

Indicator

↓

Question

Sub Component tetap dipertahankan walaupun saat ini memiliki relasi 1:1 dengan Indicator.

Hal ini dilakukan agar struktur database tetap mengikuti metodologi penelitian dan mudah dikembangkan apabila di masa depan satu Sub Component memiliki lebih dari satu Indicator.

---

# Evaluation Logic

Perhitungan dilakukan dengan tahapan berikut:

Question Answer

↓

Question Score

↓

Indicator Score

↓

Category

↓

Recommendation

↓

Overall Evaluation Result

Kategori penilaian terdiri dari lima level:

- A - Kesesuaian Sangat Tinggi
- B - Kesesuaian Tinggi
- C - Kesesuaian Sedang
- D - Kesesuaian Rendah
- E - Kesesuaian Sangat Rendah

Metode perhitungan kategori akan mengikuti rumus penelitian yang diberikan oleh pembimbing (Standar Baku Ideal dan Rerata Ideal).

---

# Recommendation Logic

Rekomendasi diberikan berdasarkan hasil evaluasi.

Mapping dilakukan berdasarkan:

Indicator

+

Evaluation Category

↓

Recommendation Template

Sehingga rekomendasi dapat berbeda untuk setiap Indicator walaupun kategorinya sama.

---

# Consequences

## Advantages

- Struktur database mengikuti metodologi penelitian.
- Mudah dikembangkan apabila struktur instrumen berubah.
- Evaluation Engine lebih fleksibel.
- Recommendation Engine dapat dikembangkan tanpa mengubah struktur database.

## Disadvantages

- Jumlah tabel lebih banyak dibanding desain sederhana.
- Evaluation Engine sedikit lebih kompleks karena menghitung kategori per Indicator.

---

# Notes

Dokumen ini menjadi acuan utama untuk seluruh implementasi Evaluation Engine.

Apabila terdapat perubahan metode penelitian dari pembimbing, dokumen ADR ini harus diperbarui sebelum implementasi kode dilakukan.