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
        element.style.display = 'none'; // Hide when requirement is met
    } else {
        element.style.display = 'list-item'; // Show when requirement is not met
        element.style.color = '#e53935';
    }
}

// Password input listener
// Set initial toggle state for password fields
window.addEventListener('load', function() {
    // Set initial toggle state for password field
    const eyeIconSignup = document.getElementById('eyeIconSignup');
    if (eyeIconSignup) {
        const eyeClosedPaths = eyeIconSignup.querySelectorAll('.eye-closed');
        const eyeOpenPaths = eyeIconSignup.querySelectorAll('.eye-open');
        // Initially show closed eye, hide open eye (password is hidden)
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
    
    // Set initial toggle state for confirm password field
    const eyeIconConfirm = document.getElementById('eyeIconConfirmSignup');
    if (eyeIconConfirm) {
        const eyeClosedPaths = eyeIconConfirm.querySelectorAll('.eye-closed');
        const eyeOpenPaths = eyeIconConfirm.querySelectorAll('.eye-open');
        // Initially show closed eye, hide open eye (password is hidden)
        eyeClosedPaths.forEach(el => el.style.display = 'block');
        eyeOpenPaths.forEach(el => el.style.display = 'none');
    }
});

document.getElementById('password').addEventListener('input', function(e) {
    const passwordRequirements = document.getElementById('passwordRequirements');
    if (e.target.value.length > 0) {
        passwordRequirements.style.display = 'block';
    } else {
        passwordRequirements.style.display = 'none';
    }
    checkPasswordRequirements(e.target.value);
});

// Handle signup form submission
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;

    if (!checkPasswordRequirements(password)) {
        alert('Password must be at least 8 characters.');
        e.preventDefault();
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        e.preventDefault();
        return;
    }

    if (!agreeTerms) {
        alert('Please agree to the Terms and Conditions and Privacy Policy.');
        e.preventDefault();
        return;
    }
});