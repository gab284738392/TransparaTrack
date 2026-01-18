// Toggle password visibility
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    const eyeClosedPaths = icon.querySelectorAll('.eye-closed');
    const eyeOpenPaths = icon.querySelectorAll('.eye-open');
    
    if (input.type === 'password') {
        input.type = 'text';
        // Show open eye, hide closed eye
        eyeClosedPaths.forEach(el => el.style.display = 'none');
        eyeOpenPaths.forEach(el => el.style.display = 'block');
    } else {
        input.type = 'password';
        // Show closed eye, hide open eye
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
}

// Password validation requirements
const requirements = {
    length: false,
    uppercase: false,
    lowercase: false,
    number: false,
    special: false
};

// Check password requirements
function checkPasswordRequirements(password) {
    // Even though we're checking all requirements, we'll only enforce the length requirement
    requirements.length = password.length >= 8; 
    requirements.uppercase = /[A-Z]/.test(password);
    requirements.lowercase = /[a-z]/.test(password);
    requirements.number = /[0-9]/.test(password);
    requirements.special = /[^A-Za-z0-9]/.test(password);
    
    // Update UI to show only missing requirements
    updateRequirementUI('req-length', requirements.length);
    updateRequirementUI('req-uppercase', requirements.uppercase);
    updateRequirementUI('req-lowercase', requirements.lowercase);
    updateRequirementUI('req-number', requirements.number);
    updateRequirementUI('req-special', requirements.special);
    
    // Only enforce the length requirement for validation
    return requirements.length;
}

function updateRequirementUI(elementId, isValid) {
    const element = document.getElementById(elementId);
    if (isValid) {
        element.style.display = 'none';
    } else {
        element.style.display = 'list-item';
        element.style.color = '#e53935';
    }
}

// Set initial toggle state for password fields
window.addEventListener('load', function() {
    // Set initial toggle state for new password field
    const eyeIconNew = document.getElementById('eyeIconResetNew');
    if (eyeIconNew) {
        const eyeClosedPaths = eyeIconNew.querySelectorAll('.eye-closed');
        const eyeOpenPaths = eyeIconNew.querySelectorAll('.eye-open');
        // Initially show closed eye, hide open eye (password is hidden)
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
    
    // Set initial toggle state for confirm password field
    const eyeIconConfirm = document.getElementById('eyeIconResetConfirm');
    if (eyeIconConfirm) {
        const eyeClosedPaths = eyeIconConfirm.querySelectorAll('.eye-closed');
        const eyeOpenPaths = eyeIconConfirm.querySelectorAll('.eye-open');
        // Initially show closed eye, hide open eye (password is hidden)
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
    
    // Initially hide password requirements
    const passwordRequirements = document.getElementById('passwordRequirements');
    if (passwordRequirements) {
        passwordRequirements.style.display = 'none';
    }
});

// Password input listener
document.getElementById('newPassword').addEventListener('input', function(e) {
    const password = e.target.value;
    
    // Show requirements only when user starts typing
    const passwordRequirements = document.getElementById('passwordRequirements');
    if (password.length > 0) {
        passwordRequirements.style.display = 'block';
        checkPasswordRequirements(password);
    } else {
        passwordRequirements.style.display = 'none';
    }
});

// Handle reset password form submission
document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate password requirements (only enforcing length)
    if (!checkPasswordRequirements(newPassword)) {
        e.preventDefault();
        alert('Password must be at least 8 characters.');
        return;
    }
    
    // Check if passwords match
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return;
    }
    
    // Allow form to submit to backend (reset-password.php will handle redirect)
});