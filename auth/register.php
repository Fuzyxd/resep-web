<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user'])): ?>
    <script>window.location.href = '?page=home';</script>
    <?php exit(); ?>
<?php endif; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk menyimpan resep favorit dan fitur lainnya</p>
            </div>
            
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="displayName">
                        <i class="fas fa-user"></i> Nama Lengkap
                    </label>
                    <input type="text" id="displayName" name="displayName" required 
                           placeholder="Nama lengkap Anda">
                    <div class="form-error" id="nameError"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="nama@email.com">
                    <div class="form-error" id="emailError"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required 
                               placeholder="Minimal 6 karakter" minlength="6">
                        <button type="button" class="toggle-password" data-toggle-bound="true" onclick="togglePasswordField(this)" aria-label="Tampilkan atau sembunyikan password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-text" id="strengthText">Kekuatan password</span>
                    </div>
                    <div class="form-error" id="passwordError"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">
                        <i class="fas fa-lock"></i> Konfirmasi Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" name="confirmPassword" required 
                               placeholder="Ulangi password">
                        <button type="button" class="toggle-password" data-toggle-bound="true" onclick="togglePasswordField(this)" aria-label="Tampilkan atau sembunyikan konfirmasi password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-error" id="confirmError"></div>
                </div>
                
                <div class="form-group terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="terms" name="terms" required>
                        <span class="checkmark"></span>
                        Saya setuju dengan <a href="?page=terms" class="terms-link" target="_blank" rel="noopener noreferrer">Syarat & Ketentuan</a> dan <a href="?page=privacy" class="terms-link" target="_blank" rel="noopener noreferrer">Kebijakan Privasi</a>
                    </label>
                    <div class="form-error" id="termsError"></div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </div>
                
                <div class="auth-footer">
                    <p>Sudah punya akun? <a href="?page=login">Login di sini</a></p>
                </div>
            </form>
            
            <div class="auth-message" id="authMessage"></div>
        </div>
    </div>
</section>

<style>
/* Register specific styles */
.auth-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 4rem 0;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.05), rgba(78, 205, 196, 0.05));
}

.auth-container {
    max-width: 450px;
    margin: 0 auto;
    background: white;
    padding: 2.5rem;
    border-radius: var(--radius);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h2 {
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 2.2rem;
}

.auth-header p {
    color: var(--gray);
    font-size: 1rem;
}

.auth-form .form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.auth-form label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark);
    font-size: 0.95rem;
}

.auth-form input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--light-gray);
    border-radius: var(--radius);
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    transition: var(--transition);
    background: #f9f9f9;
}

.auth-form input:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.auth-form input.error {
    border-color: #ff6b6b;
}

.auth-form input.success {
    border-color: #4ecdc4;
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
    font-size: 1rem;
    z-index: 2;
    pointer-events: auto;
}

.toggle-password:hover {
    color: var(--primary);
}

.password-strength {
    margin-top: 8px;
    display: none;
}

.password-strength.visible {
    display: block;
}

.strength-bar {
    height: 4px;
    background: var(--light-gray);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 4px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    background: #ff6b6b;
    border-radius: 2px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.strength-text {
    font-size: 0.8rem;
    color: var(--gray);
}

.terms-group {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: var(--radius);
    margin-top: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    position: relative;
    padding-left: 35px;
    user-select: none;
    font-size: 0.9rem;
    line-height: 1.4;
    color: var(--gray);
    font-weight: 400;
}

.checkbox-label input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    left: 0;
    top: 2px;
    height: 20px;
    width: 20px;
    background-color: white;
    border: 2px solid var(--light-gray);
    border-radius: 4px;
    transition: var(--transition);
}

.checkbox-label:hover .checkmark {
    border-color: var(--primary);
}

.checkbox-label input:checked ~ .checkmark {
    background-color: var(--primary);
    border-color: var(--primary);
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 6px;
    top: 2px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label input:checked ~ .checkmark:after {
    display: block;
}

.terms-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.terms-link:hover {
    text-decoration: underline;
}

.form-error {
    color: #ff6b6b;
    font-size: 0.85rem;
    margin-top: 5px;
    min-height: 20px;
    display: none;
}

.form-error.show {
    display: block;
}

.btn-block {
    width: 100%;
    padding: 16px;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1rem;
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray);
}

.auth-footer p {
    color: var(--gray);
    font-size: 0.95rem;
}

.auth-footer a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.auth-message {
    margin-top: 1.5rem;
    padding: 16px;
    border-radius: var(--radius);
    text-align: center;
    display: none;
    font-weight: 500;
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

/* Responsive */
@media (max-width: 768px) {
    .auth-section {
        padding: 2rem 0;
    }
    
    .auth-container {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    
    .auth-header h2 {
        font-size: 1.8rem;
    }
    
    .terms-group {
        padding: 0.8rem;
    }
}
</style>

<script>
window.togglePasswordField = function(button) {
    const input = button.closest('.password-input')?.querySelector('input');
    if (!input) return;

    const isHidden = input.getAttribute('type') === 'password';
    input.setAttribute('type', isHidden ? 'text' : 'password');

    const icon = button.querySelector('i');
    if (icon) {
        icon.classList.toggle('fa-eye', !isHidden);
        icon.classList.toggle('fa-eye-slash', isHidden);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const authMessage = document.getElementById('authMessage');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const passwordStrength = document.querySelector('.password-strength');
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length > 0) {
            passwordStrength.classList.add('visible');
        } else {
            passwordStrength.classList.remove('visible');
            return;
        }
        
        // Length check
        if (password.length >= 8) strength += 25;
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength += 25;
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength += 25;
        
        // Number/Special char check
        if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) strength += 25;
        
        // Update UI
        strengthFill.style.width = `${strength}%`;
        
        if (strength < 50) {
            strengthFill.style.backgroundColor = '#ff6b6b';
            strengthText.textContent = 'Lemah';
            strengthText.style.color = '#ff6b6b';
        } else if (strength < 75) {
            strengthFill.style.backgroundColor = '#ffa502';
            strengthText.textContent = 'Sedang';
            strengthText.style.color = '#ffa502';
        } else {
            strengthFill.style.backgroundColor = '#2ed573';
            strengthText.textContent = 'Kuat';
            strengthText.style.color = '#2ed573';
        }
    });
    
    // Form validation
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.form-error').forEach(el => {
            el.classList.remove('show');
        });
        document.querySelectorAll('input').forEach(el => {
            el.classList.remove('error', 'success');
        });
        
        // Name validation
        const name = document.getElementById('displayName').value.trim();
        if (name.length < 2) {
            showError('nameError', 'Nama minimal 2 karakter');
            document.getElementById('displayName').classList.add('error');
            isValid = false;
        } else {
            document.getElementById('displayName').classList.add('success');
        }
        
        // Email validation
        const email = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('emailError', 'Email tidak valid');
            document.getElementById('email').classList.add('error');
            isValid = false;
        } else {
            document.getElementById('email').classList.add('success');
        }
        
        // Password validation
        const password = passwordInput.value;
        if (password.length < 6) {
            showError('passwordError', 'Password minimal 6 karakter');
            passwordInput.classList.add('error');
            isValid = false;
        } else {
            passwordInput.classList.add('success');
        }
        
        // Confirm password validation
        const confirmPassword = confirmPasswordInput.value;
        if (confirmPassword !== password) {
            showError('confirmError', 'Password tidak cocok');
            confirmPasswordInput.classList.add('error');
            isValid = false;
        } else if (confirmPassword.length > 0) {
            confirmPasswordInput.classList.add('success');
        }
        
        // Terms validation
        const terms = document.getElementById('terms').checked;
        if (!terms) {
            showError('termsError', 'Anda harus menyetujui syarat & ketentuan');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Show error message
    function showError(elementId, message) {
        const element = document.getElementById(elementId);
        element.textContent = message;
        element.classList.add('show');
    }
    
    // Show message
    function showMessage(text, type) {
        authMessage.textContent = text;
        authMessage.className = `auth-message ${type}`;
        authMessage.style.display = 'block';
        
        setTimeout(() => {
            authMessage.style.display = 'none';
        }, 5000);
    }
    
    // Form submission
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const displayName = document.getElementById('displayName').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = passwordInput.value;
        
        // Show loading
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftarkan...';
        submitBtn.disabled = true;
        
        try {
            const result = await registerWithEmail(email, password, displayName);
            
            if (result.success) {
                showMessage('Pendaftaran berhasil! Mengalihkan...', 'success');
                setTimeout(() => {
                    window.location.href = '?page=home';
                }, 1500);
            } else {
                let errorMessage = result.error || 'Pendaftaran gagal. Silakan coba lagi.';
                
                // Translate Firebase error messages
                if (errorMessage.includes('email-already-in-use')) {
                    errorMessage = 'Email sudah terdaftar. Silakan gunakan email lain.';
                } else if (errorMessage.includes('weak-password')) {
                    errorMessage = 'Password terlalu lemah. Gunakan password yang lebih kuat.';
                } else if (errorMessage.includes('invalid-email')) {
                    errorMessage = 'Format email tidak valid.';
                }
                
                showMessage(errorMessage, 'error');
            }
        } catch (error) {
            showMessage('Terjadi kesalahan. Silakan coba lagi.', 'error');
            console.error('Registration error:', error);
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    
    // Real-time validation
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('blur', function() {
            validateForm();
        });
    });
    
    // Confirm password real-time check
    confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirmPassword = this.value;
        
        if (confirmPassword.length > 0) {
            if (confirmPassword !== password) {
                showError('confirmError', 'Password tidak cocok');
                this.classList.remove('success');
                this.classList.add('error');
            } else {
                document.getElementById('confirmError').classList.remove('show');
                this.classList.remove('error');
                this.classList.add('success');
            }
        }
    });
    
});
</script>
