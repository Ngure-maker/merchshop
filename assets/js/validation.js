// Form validation and utilities
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = '254' + value.substring(1);
            }
            if (value.length <= 12) {
                e.target.value = value;
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    showFieldError(field, 'This field is required');
                } else {
                    clearFieldError(field);
                }
            });

            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    valid = false;
                    showFieldError(field, 'Please enter a valid email address');
                }
            });

            // Password confirmation
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                valid = false;
                showFieldError(confirmPassword, 'Passwords do not match');
            }

            if (!valid) {
                e.preventDefault();
                showToast('Please fix the errors in the form', 'error');
            }
        });
    });

    // Cart quantity validation
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);

            if (value > max) {
                this.value = max;
                showToast(`Maximum available quantity is ${max}`, 'warning');
            } else if (value < min) {
                this.value = min;
            }
        });
    });

    // Auto-submit forms on quantity change
    const autoSubmitForms = document.querySelectorAll('form[data-auto-submit]');
    autoSubmitForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                form.submit();
            });
        });
    });
});

// Utility functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// M-Pesa payment simulation
function simulateMpesaPayment(phone, amount, callback) {
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
    submitBtn.disabled = true;

    // Simulate API call
    setTimeout(() => {
        // Random success/failure for simulation
        const success = Math.random() > 0.2;
        
        if (success) {
            callback(true, 'Payment initiated successfully. Check your phone for M-Pesa prompt.');
        } else {
            callback(false, 'Payment failed. Please try again.');
        }
        
        // Restore button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
}

// Stock validation
function validateStock(productId, size, quantity) {
    // This would typically make an AJAX call to check stock
    return new Promise((resolve) => {
        setTimeout(() => {
            // Mock validation - in real implementation, this would call your backend
            const inStock = Math.random() > 0.1; // 90% chance of being in stock
            resolve(inStock);
        }, 500);
    });
}