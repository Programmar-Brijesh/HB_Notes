/**
 * HB Notes - Authentication UI Interactivity
 * Handles password visibility toggle, real-time password strength, and submission feedback.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggles
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');
    toggleButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = button.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const eyeIcon = button.querySelector('.icon-eye');
            const eyeOffIcon = button.querySelector('.icon-eye-off');

            if (eyeIcon && eyeOffIcon) {
                eyeIcon.style.display = isPassword ? 'none' : 'block';
                eyeOffIcon.style.display = isPassword ? 'block' : 'none';
            }

            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });

    // 2. Password Strength Meter
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    const critLength = document.getElementById('crit-length');
    const critLetter = document.getElementById('crit-letter');
    const critNumber = document.getElementById('crit-number');
    const critSpecial = document.getElementById('crit-special');

    if (passwordInput && strengthMeter && strengthText) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let score = 0;

            const hasLength = val.length >= 8;
            const hasLetter = /[a-zA-Z]/.test(val);
            const hasNumber = /[0-9]/.test(val);
            const hasSpecial = /[^a-zA-Z0-9]/.test(val);

            // Update criteria items if present
            if (critLength) updateCriterion(critLength, hasLength);
            if (critLetter) updateCriterion(critLetter, hasLetter);
            if (critNumber) updateCriterion(critNumber, hasNumber);
            if (critSpecial) updateCriterion(critSpecial, hasSpecial);

            if (hasLength) score++;
            if (hasLetter) score++;
            if (hasNumber) score++;
            if (hasSpecial) score++;

            if (val.length === 0) {
                strengthMeter.style.width = '0%';
                strengthMeter.style.backgroundColor = '#E2E8F0';
                strengthText.textContent = 'Enter password';
                strengthText.style.color = 'var(--color-text-muted)';
            } else if (score <= 1) {
                strengthMeter.style.width = '25%';
                strengthMeter.style.backgroundColor = '#EF4444';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#EF4444';
            } else if (score === 2) {
                strengthMeter.style.width = '50%';
                strengthMeter.style.backgroundColor = '#F59E0B';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#F59E0B';
            } else if (score === 3) {
                strengthMeter.style.width = '75%';
                strengthMeter.style.backgroundColor = '#3B82F6';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#3B82F6';
            } else {
                strengthMeter.style.width = '100%';
                strengthMeter.style.backgroundColor = '#10B981';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#10B981';
            }
        });
    }

    function updateCriterion(el, isValid) {
        if (isValid) {
            el.classList.add('valid');
        } else {
            el.classList.remove('valid');
        }
    }

    // 3. Form Submission Feedback & Double-Submit Prevention
    const authForms = document.querySelectorAll('form[data-auth-form]');
    authForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                return; // Let standard HTML5 validation run
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-submitting');
                const btnText = submitBtn.querySelector('.btn-text');
                if (btnText) {
                    btnText.setAttribute('data-original-text', btnText.textContent);
                    btnText.textContent = 'Processing...';
                }
            }
        });
    });
});
