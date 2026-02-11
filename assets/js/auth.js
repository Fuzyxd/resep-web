import { auth, onAuthStateChanged } from './firebase-config.js';
import { 
    signInWithEmailAndPassword, 
    createUserWithEmailAndPassword,
    signOut,
    updateProfile
} from "https://www.gstatic.com/firebasejs/9.6.10/firebase-auth.js";

// Track auth state
onAuthStateChanged(auth, async (user) => {
    if (user) {
        // User is signed in
        console.log('User signed in:', user.email);
        
        // Store user info in session
        const userData = {
            uid: user.uid,
            email: user.email,
            displayName: user.displayName,
            photoURL: user.photoURL
        };
        
        // Send to server to store in session
        let sessionUser = null;
        try {
            const response = await fetch('api/auth_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });
            const result = await response.json();
            if (result && result.user) {
                sessionUser = result.user;
            }
        } catch (err) {
            console.warn('Failed to sync session user', err);
        }

        const uiUser = sessionUser ? {
            displayName: sessionUser.displayName || user.displayName,
            photoURL: sessionUser.photoURL || user.photoURL,
            email: sessionUser.email || user.email
        } : user;

        // Update UI
        updateAuthUI(uiUser);
    } else {
        // User is signed out
        console.log('User signed out');
        
        // Clear session
        fetch('api/auth_session.php?action=logout');
        
        // Update UI
        updateAuthUI(null);
    }
});

function getFirstName(displayName, email) {
    const name = (displayName || '').trim();
    if (name) {
        return name.split(/\s+/)[0];
    }
    if (email && email.includes('@')) {
        return email.split('@')[0];
    }
    return 'User';
}

// Update UI based on auth state
function updateAuthUI(user) {
    const authElements = document.querySelectorAll('[data-auth]');
    
    authElements.forEach(element => {
        const authState = element.getAttribute('data-auth');
        
        if (authState === 'authenticated') {
            element.style.display = user ? 'block' : 'none';
        } else if (authState === 'unauthenticated') {
            element.style.display = user ? 'none' : 'block';
        }
    });
    
    // Update user info in navbar
    if (user) {
        const userAvatar = document.querySelector('.user-avatar');
        const userName = document.querySelector('.user-btn span');
        const nameForAvatar = user.displayName || user.email || 'User';
        
        if (userAvatar) {
            userAvatar.src = user.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(nameForAvatar)}&background=ff6b6b&color=fff`;
        }
        
        if (userName) {
            userName.textContent = getFirstName(user.displayName, user.email);
        }
    }
}
// Login function
window.loginWithEmail = async function(email, password) {
    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        return { success: true, user: userCredential.user };
    } catch (error) {
        return { 
            success: false, 
            error: error.message 
        };
    }
};

// Register function
// Register function (update existing function in auth.js)
window.registerWithEmail = async function(email, password, displayName) {
    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        
        // Update profile with display name
        await updateProfile(userCredential.user, {
            displayName: displayName
        });
        
        // Send welcome email (optional Firebase Cloud Function can be added)
        console.log('User registered successfully:', userCredential.user.email);
        
        return { success: true, user: userCredential.user };
    } catch (error) {
        console.error('Registration error:', error);
        return { 
            success: false, 
            error: error.message 
        };
    }
};

// Logout function
window.logoutUser = async function() {
    try {
        await signOut(auth);
        return { success: true };
    } catch (error) {
        return { 
            success: false, 
            error: error.message 
        };
    }
};

// Initialize auth UI
document.addEventListener('DOMContentLoaded', function() {
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            await logoutUser();
            window.location.href = '?page=home';
        });
    }
    
    // Toggle password visibility
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(btn => {
        if (btn.dataset.toggleBound === 'true') {
            return;
        }
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        btn.dataset.toggleBound = 'true';
    });
});
