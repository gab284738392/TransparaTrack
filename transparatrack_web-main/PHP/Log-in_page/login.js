// Track login attempts
const maxAttempts = 5;
const lockoutTime = 1 * 60 * 1000; // 1 minute lockout
let loginAttempts = parseInt(localStorage.getItem('loginAttempts')) || 0;

// Check if account is locked
function checkLockout() {
    const lockoutEnd = localStorage.getItem('lockoutEnd');
    if (lockoutEnd) {
        const lockoutEndTime = parseInt(lockoutEnd);
        if (Date.now() < lockoutEndTime) {
            // Still locked
            window.location.href = 'account-locked.php';
            return true;
        } else {
            // Lockout expired — reset attempts
            localStorage.removeItem('lockoutEnd');
            localStorage.removeItem('loginAttempts');
            loginAttempts = 0;
        }
    }
    return false;
}

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeClosedPaths = eyeIcon.querySelectorAll('.eye-closed');
    const eyeOpenPaths = eyeIcon.querySelectorAll('.eye-open');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Show open eye, hide closed eye
        eyeClosedPaths.forEach(el => el.style.display = 'none');
        eyeOpenPaths.forEach(el => el.style.display = 'block');
    } else {
        passwordInput.type = 'password';
        // Show closed eye, hide open eye
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
}

// Handle form submit
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (checkLockout()) return;

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    try {
        const response = await fetch('login-process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ email, password })
        });

        const data = await response.json();

        if (data.status === 'success') {
            // ✅ Login success: reset attempts
            localStorage.removeItem('loginAttempts');
            localStorage.removeItem('lockoutEnd');
            // Redirect to success page
            window.location.href = 'login-success.php';
        } else {
            // ❌ Failed login attempt
            loginAttempts = parseInt(localStorage.getItem('loginAttempts')) || 0;
            loginAttempts++;
            localStorage.setItem('loginAttempts', loginAttempts);

            const remainingAttempts = maxAttempts - loginAttempts;
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const attemptsWarning = document.getElementById('attemptsWarning');
            const attemptsCount = document.getElementById('attemptsCount');

            errorText.textContent = data.message || 'Incorrect Email or Password';
            errorMessage.style.display = 'block';

            // Show remaining attempts
            if (remainingAttempts > 0) {
                attemptsCount.textContent = remainingAttempts;
                attemptsWarning.style.display = 'block';
            }

            // 🚫 Lock out after too many attempts
            if (loginAttempts >= maxAttempts) {
                const lockoutEnd = Date.now() + lockoutTime;
                localStorage.setItem('lockoutEnd', lockoutEnd);
                window.location.href = 'account-locked.php';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    }
});

// On page load: show remaining attempts
window.addEventListener('load', function() {
    if (checkLockout()) return;

    loginAttempts = parseInt(localStorage.getItem('loginAttempts')) || 0;
    if (loginAttempts > 0 && loginAttempts < maxAttempts) {
        const attemptsWarning = document.getElementById('attemptsWarning');
        const attemptsCount = document.getElementById('attemptsCount');
        const remainingAttempts = maxAttempts - loginAttempts;
        attemptsCount.textContent = remainingAttempts;
        attemptsWarning.style.display = 'block';
    }
});
