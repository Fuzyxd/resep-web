<?php if (isset($_SESSION['user'])): ?>
    <script>window.location.href = '?page=home';</script>
<?php endif; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Login ke Akun Anda</h2>
                <p>Masuk untuk menyimpan resep favorit Anda</p>
            </div>
            
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="nama@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required 
                               placeholder="Masukkan password">
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
                
                <div class="auth-footer">
                    <p>Belum punya akun? <a href="?page=register">Daftar di sini</a></p>
                </div>
            </form>
            
            <div class="auth-message" id="authMessage"></div>
        </div>
    </div>
</section>

<style>
.auth-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 4rem 0;
}

.auth-container {
    max-width: 400px;
    margin: 0 auto;
    background: white;
    padding: 2.5rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h2 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: var(--gray);
}

.auth-form .form-group {
    margin-bottom: 1.5rem;
}

.auth-form label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark);
}

.auth-form input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--light-gray);
    border-radius: var(--radius);
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    transition: var(--transition);
}

.auth-form input:focus {
    outline: none;
    border-color: var(--primary);
}

.password-input {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray);
    cursor: pointer;
    padding: 5px;
}

.btn-block {
    width: 100%;
    padding: 14px;
    font-size: 1.1rem;
}

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray);
}

.auth-footer a {
    color: var(--primary);
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.auth-message {
    margin-top: 1rem;
    padding: 12px;
    border-radius: var(--radius);
    text-align: center;
    display: none;
}

.auth-message.success {
    background-color: rgba(78, 205, 196, 0.1);
    color: #2a7c76;
    border: 1px solid #4ecdc4;
}

.auth-message.error {
    background-color: rgba(255, 107, 107, 0.1);
    color: #d63031;
    border: 1px solid #ff6b6b;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const authMessage = document.getElementById('authMessage');
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Show loading
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        submitBtn.disabled = true;
        
        try {
            const result = await loginWithEmail(email, password);
            
            if (result.success) {
                showMessage('Login berhasil! Mengalihkan...', 'success');
                setTimeout(() => {
                    window.location.href = '?page=home';
                }, 1500);
            } else {
                showMessage(result.error || 'Login gagal. Silakan coba lagi.', 'error');
            }
        } catch (error) {
            showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    function showMessage(text, type) {
        authMessage.textContent = text;
        authMessage.className = `auth-message ${type}`;
        authMessage.style.display = 'block';
        
        setTimeout(() => {
            authMessage.style.display = 'none';
        }, 5000);
    }
});
</script>