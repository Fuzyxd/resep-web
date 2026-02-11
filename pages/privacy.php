<?php
$updatedAt = '11 Februari 2026';
?>

<section class="legal-section">
    <div class="container">
        <article class="legal-card">
            <header class="legal-header">
                <h1>Kebijakan Privasi</h1>
                <p>Berlaku mulai: <?= $updatedAt; ?></p>
            </header>

            <section>
                <h2>1. Informasi yang Kami Kumpulkan</h2>
                <p>Kami mengumpulkan data akun seperti nama, email, serta data penggunaan fitur favorit untuk kebutuhan operasional aplikasi.</p>
            </section>

            <section>
                <h2>2. Cara Penggunaan Data</h2>
                <p>Data digunakan untuk autentikasi, personalisasi pengalaman pengguna, dan peningkatan kualitas layanan Resep Nusantara.</p>
            </section>

            <section>
                <h2>3. Penyimpanan dan Keamanan</h2>
                <p>Kami menerapkan langkah keamanan yang wajar untuk melindungi data pengguna dari akses tidak sah, perubahan, atau penghapusan tanpa izin.</p>
            </section>

            <section>
                <h2>4. Pembagian Informasi</h2>
                <p>Kami tidak menjual data pribadi pengguna. Informasi hanya dapat dibagikan jika diwajibkan oleh hukum atau untuk keperluan operasional layanan.</p>
            </section>

            <section>
                <h2>5. Hak Pengguna</h2>
                <p>Anda dapat meminta pembaruan data profil, serta dapat berhenti menggunakan layanan kapan saja sesuai ketentuan yang berlaku.</p>
            </section>

            <section>
                <h2>6. Cookie dan Teknologi Serupa</h2>
                <p>Layanan dapat menggunakan penyimpanan lokal atau teknologi serupa untuk menjaga sesi login dan preferensi pengguna.</p>
            </section>

            <section>
                <h2>7. Perubahan Kebijakan</h2>
                <p>Kebijakan ini dapat diperbarui dari waktu ke waktu. Perubahan terbaru akan selalu ditampilkan pada halaman ini.</p>
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
