// ========================================
// DOM Ready
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initForms();
    initAlerts();
    initDeleteConfirmation();
    initFileUpload();
});

// ========================================
// Navigation Toggle (Mobile)
// ========================================
function initNavigation() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            const icon = this.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                navToggle.querySelector('i').classList.remove('fa-times');
                navToggle.querySelector('i').classList.add('fa-bars');
            }
        });
    }
}

// ========================================
// Form Validation
// ========================================
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateInput(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');
    
    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();
    let isValid = true;
    let message = '';
    
    // Required check
    if (!value) {
        isValid = false;
        message = 'هذا الحقل مطلوب';
    }
    // Email validation
    else if (input.type === 'email' && !isValidEmail(value)) {
        isValid = false;
        message = 'البريد الإلكتروني غير صحيح';
    }
    // URL validation
    else if (input.type === 'url' && !isValidURL(value)) {
        isValid = false;
        message = 'الرابط غير صحيح';
    }
    // Min length
    else if (input.minLength && value.length < input.minLength) {
        isValid = false;
        message = `يجب أن يكون على الأقل ${input.minLength} أحرف`;
    }
    // Max length
    else if (input.maxLength && value.length > input.maxLength) {
        isValid = false;
        message = `يجب ألا يتجاوز ${input.maxLength} حرف`;
    }
    // Number validation
    else if (input.type === 'number') {
        const num = parseFloat(value);
        if (isNaN(num)) {
            isValid = false;
            message = 'يجب إدخال رقم صحيح';
        } else if (input.min && num < parseFloat(input.min)) {
            isValid = false;
            message = `القيمة يجب أن تكون ${input.min} على الأقل`;
        } else if (input.max && num > parseFloat(input.max)) {
            isValid = false;
            message = `القيمة يجب ألا تتجاوز ${input.max}`;
        }
    }
    
    if (isValid) {
        clearError(input);
    } else {
        showError(input, message);
    }
    
    return isValid;
}

function showError(input, message) {
    const formGroup = input.closest('.form-group');
    let errorElement = formGroup.querySelector('.error-message');
    
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    input.classList.add('error');
}

function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup.querySelector('.error-message');
    
    if (errorElement) {
        errorElement.remove();
    }
    input.classList.remove('error');
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidURL(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

// ========================================
// Alerts Auto Dismiss
// ========================================
function initAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.alert-close');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
}

// ========================================
// Delete Confirmation
// ========================================
function initDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-confirm]');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'هل أنت متأكد من الحذف؟';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ========================================
// File Upload Preview
// ========================================
function initFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Show file name
                const fileName = file.name;
                const label = this.closest('.form-group').querySelector('label');
                if (label) {
                    const fileInfo = document.createElement('span');
                    fileInfo.className = 'file-info';
                    fileInfo.textContent = ` - ${fileName}`;
                    label.querySelector('.file-info')?.remove();
                    label.appendChild(fileInfo);
                }
                
                // Image preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = document.getElementById('imagePreview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.id = 'imagePreview';
                            preview.style.maxWidth = '200px';
                            preview.style.marginTop = '1rem';
                            input.closest('.form-group').appendChild(preview);
                        }
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });
}

// ========================================
// AJAX Helper Functions
// ========================================
function fetchData(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Fetch error:', error);
            throw error;
        });
}

function postData(url, data) {
    return fetchData(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

// ========================================
// Utility Functions
// ========================================
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ========================================
// Export functions for global use
// ========================================
window.app = {
    fetchData,
    postData,
    showLoading,
    hideLoading,
    escapeHtml,
    validateEmail: isValidEmail,
    validateURL: isValidURL
};