<?php
$updatedAt = '11 Februari 2026';
?>

<section class="legal-section">
    <div class="container">
        <article class="legal-card">
            <header class="legal-header">
                <h1>Syarat & Ketentuan</h1>
                <p>Berlaku mulai: <?= $updatedAt; ?></p>
            </header>

            <section>
                <h2>1. Penerimaan Ketentuan</h2>
                <p>Dengan menggunakan Resep Nusantara, Anda menyetujui seluruh syarat pada halaman ini. Jika Anda tidak setuju, mohon hentikan penggunaan layanan.</p>
            </section>

            <section>
                <h2>2. Akun Pengguna</h2>
                <p>Anda bertanggung jawab atas keamanan akun, termasuk menjaga kerahasiaan email dan password. Semua aktivitas pada akun dianggap dilakukan oleh pemilik akun.</p>
            </section>

            <section>
                <h2>3. Penggunaan Layanan</h2>
                <p>Anda setuju menggunakan layanan secara wajar, tidak menyalahgunakan fitur, dan tidak melakukan tindakan yang merugikan pengguna lain maupun sistem.</p>
            </section>

            <section>
                <h2>4. Konten dan Hak Cipta</h2>
                <p>Konten resep, gambar, dan elemen visual di platform ini dilindungi ketentuan hak cipta. Penggunaan ulang konten wajib mengikuti izin pemilik konten.</p>
            </section>

            <section>
                <h2>5. Batas Tanggung Jawab</h2>
                <p>Kami berupaya menjaga layanan tetap tersedia dan akurat, namun tidak menjamin bebas gangguan atau kesalahan setiap saat.</p>
            </section>

            <section>
                <h2>6. Perubahan Ketentuan</h2>
                <p>Kami dapat memperbarui syarat sewaktu-waktu. Versi terbaru akan ditampilkan pada halaman ini beserta tanggal pembaruan.</p>
            </section>

            <section>
                <h2>7. Kontak</h2>
                <p>Jika ada pertanyaan terkait ketentuan ini, hubungi kami melalui halaman profil atau kanal dukungan yang tersedia.</p>
            </section>
        </article>
    </div>
</section>

<style>
.legal-section {
    padding: 3rem 0 4rem;
}

.legal-card {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 2rem;
}

.legal-header {
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.legal-header h1 {
    font-size: 2.2rem;
    margin-bottom: 0.3rem;
}

.legal-header p {
    margin: 0;
    color: var(--gray);
}

.legal-card section {
    margin-bottom: 1.2rem;
}

.legal-card h2 {
    font-size: 1.2rem;
    margin-bottom: 0.4rem;
}

.legal-card p {
    color: #495057;
    margin: 0;
}

@media (max-width: 768px) {
    .legal-section {
        padding: 2rem 0 3rem;
    }

    .legal-card {
        padding: 1.2rem;
    }

    .legal-header h1 {
        font-size: 1.8rem;
    }
}
</style>
