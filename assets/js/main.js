document.addEventListener('DOMContentLoaded', function() {
    // Validación de formulario en tiempo real
    const form = document.getElementById('inscripcionForm');
    if (form) {
        initFormValidation(form);
    }
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-hide alerts después de 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 5000);
});

function initFormValidation(form) {
    const fields = {
        cedula: {
            element: form.querySelector('#cedula'),
            validator: validateCedula,
            message: 'Ingrese una cédula ecuatoriana válida'
        },
        email: {
            element: form.querySelector('#email'),
            validator: validateEmail,
            message: 'Ingrese un email válido'
        },
        telefono: {
            element: form.querySelector('#telefono'),
            validator: validatePhone,
            message: 'Ingrese un teléfono válido (10 dígitos)'
        },
        fecha_nacimiento: {
            element: form.querySelector('#fecha_nacimiento'),
            validator: validateAge,
            message: 'Debe ser mayor de 16 años'
        }
    };
    
    // Agregar event listeners para validación en tiempo real
    Object.keys(fields).forEach(fieldName => {
        const field = fields[fieldName];
        if (field.element) {
            field.element.addEventListener('blur', function() {
                validateField(field, this.value);
            });
            
            field.element.addEventListener('input', function() {
                clearFieldError(this);
            });
        }
    });
    
    // Validación al enviar formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        Object.keys(fields).forEach(fieldName => {
            const field = fields[fieldName];
            if (field.element && !validateField(field, field.element.value)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showAlert('error', 'Por favor, corrija los errores en el formulario');
        } else {
            // Mostrar loading en el botón de envío
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="loading"></span> Procesando...';
                submitBtn.disabled = true;
            }
        }
    });
}

function validateField(field, value) {
    const isValid = field.validator(value);
    
    if (!isValid) {
        showFieldError(field.element, field.message);
        return false;
    } else {
        clearFieldError(field.element);
        return true;
    }
}

function showFieldError(element, message) {
    element.classList.add('is-invalid');
    
    let errorDiv = element.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        element.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function clearFieldError(element) {
    element.classList.remove('is-invalid');
    const errorDiv = element.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Validadores específicos
function validateCedula(cedula) {
    if (!cedula || cedula.length !== 10) return false;
    
    const digits = cedula.split('').map(Number);
    const region = parseInt(cedula.substring(0, 2));
    
    if (region < 1 || region > 24) return false;
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        let num = digits[i];
        if (i % 2 === 0) {
            num *= 2;
            if (num > 9) num -= 9;
        }
        sum += num;
    }
    
    const verifier = (10 - (sum % 10)) % 10;
    return verifier === digits[9];
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

function validateAge(birthDate) {
    if (!birthDate) return false;
    
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age >= 16;
}

function showAlert(type, message) {
    const alertContainer = document.querySelector('.alert-container') || document.body;
    const alertDiv = document.createElement('div');
    
    const alertClasses = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    alertDiv.className = `alert ${alertClasses[type]} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.style.transition = 'opacity 0.5s';
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 500);
        }
    }, 5000);
}