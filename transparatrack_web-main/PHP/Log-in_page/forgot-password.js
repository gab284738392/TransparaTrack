// Handle forgot password form submission
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // In a real application, send reset request to backend
    // For demo, just redirect to check email page
    console.log('Password reset requested for:', email);
    
    // Simulate sending email
    setTimeout(() => {
        window.location.href = 'check-email.php';
    }, 500);
});
