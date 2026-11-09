// Tells the CSS that JavaScript is running.
document.documentElement.classList.add('js');

// Mobile menu
const menuButton = document.querySelector('.menu-toggle');
const siteNav = document.querySelector('.site-nav');

menuButton.hidden = false;

menuButton.addEventListener('click', function () {
    const isOpen = siteNav.classList.toggle('open');
    menuButton.setAttribute('aria-expanded', isOpen);
});

// Returns the error message for a field, or an empty string.
function getError(input, form) {
    const value = input.value.trim();

    if (input.name === 'name' && value === '') {
        return 'Enter your name.';
    }

    if (input.name === 'email') {
        if (value === '') {
            return 'Enter your email address.';
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            return 'Enter a valid email address.';
        }
    }

    if (input.name === 'password') {
        if (input.value === '') {
            return form.id === 'login-form' ? 'Enter your password.' : 'Enter a password.';
        }
        if (form.id === 'register-form' && input.value.length < 8) {
            return 'Password must be at least 8 characters.';
        }
    }

    if (input.name === 'confirm_password') {
        if (input.value === '') {
            return 'Confirm your password.';
        }
        if (input.value !== form.elements.password.value) {
            return 'Passwords do not match.';
        }
    }

    return '';
}

// Shows or clears a field's error. Returns true if the field is valid.
function checkField(input, form) {
    const error = getError(input, form);

    document.getElementById(input.id + '-error').textContent = error;

    if (error) {
        input.setAttribute('aria-invalid', 'true');
    } else {
        input.removeAttribute('aria-invalid');
    }

    return error === '';
}

// Form validation
document.querySelectorAll('form[data-validate]').forEach(function (form) {
    const inputs = Array.from(form.querySelectorAll('input'));

    // Use our own messages instead of the browser's.
    form.noValidate = true;

    form.addEventListener('submit', function (event) {
        const invalid = inputs.filter(function (input) {
            return !checkField(input, form);
        });

        if (invalid.length > 0) {
            event.preventDefault();
            invalid[0].focus();
        }
    });

    // Once a field shows an error, update it as the user types.
    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            if (input.hasAttribute('aria-invalid')) {
                checkField(input, form);
            }
        });
    });
});
