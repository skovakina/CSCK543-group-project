// Tells the CSS that JavaScript is running.
document.documentElement.classList.add('js');

// Mobile menu
const menuButton = document.querySelector('.menu-toggle');
const siteNav = document.querySelector('.site-nav');

if (menuButton && siteNav) {
    menuButton.hidden = false;

    menuButton.addEventListener('click', function () {
        const isOpen = siteNav.classList.toggle('open');
        menuButton.setAttribute('aria-expanded', isOpen);
    });
}

// Counts Unicode code points to match PHP mb_strlen(..., 'UTF-8').
function charLength(value) {
    return Array.from(value).length;
}

// Returns the error message for a field, or an empty string.
function getError(input, form) {
    const value = input.value.trim();

    if (input.name === 'name') {
        if (value === '') {
            return 'Enter your name.';
        }
        if (charLength(value) > 100) {
            return 'Name must be 100 characters or fewer.';
        }
    }

    if (input.name === 'email') {
        if (value === '') {
            return 'Enter your email address.';
        }
        if (charLength(value) > 150) {
            return 'Email address must be 150 characters or fewer.';
        }
        if (!/^[^\s@\u0080-\uFFFF]+@([^\s@.\u0080-\uFFFF]+\.)+[^\s@.\u0080-\uFFFF]+$/.test(value)) {
            return 'Enter a valid email address.';
        }
    }

    if (input.name === 'password') {
        if (input.value === '') {
            return form.id === 'login-form' ? 'Enter your password.' : 'Enter a password.';
        }
        if (form.id === 'register-form' && charLength(input.value) < 8) {
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

    // Search form
    if ((input.name === 'q' || input.name === 'exclude') && charLength(value) > 100) {
        return 'Keep this to 100 characters or fewer.';
    }

    if (input.type === 'number') {
        const number = Number(value);
        const outOfRange = !Number.isInteger(number) || number < Number(input.min) || number > Number(input.max);
        if (input.validity.badInput || (value !== '' && outOfRange)) {
            return 'Enter a whole number from ' + input.min + ' to ' + input.max + '.';
        }
    }

    return '';
}

// Shows or clears a field's error. Returns true if the field is valid.
function checkField(input, form) {
    const errorElement = document.getElementById(input.id + '-error');

    // Fields without an error message (such as checkboxes) are not checked.
    if (!errorElement) {
        return true;
    }

    const error = getError(input, form);
    errorElement.textContent = error;

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
            if (input.name === 'password' && form.elements.confirm_password && form.elements.confirm_password.hasAttribute('aria-invalid')) {
                checkField(form.elements.confirm_password, form);
            }
        });
    });
});
