// Find both forms and define shared validation rules.
const loginForm = document.querySelector('.log-in-form');
const registerForm = document.querySelector('.sign-up-form');
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const minimumPasswordLength = 8;

// Show one custom error message below a field and mark it invalid.
function setFieldError(input, message) {
	const form = input.closest('form');
	const errorId = `${input.id}-error`;
	const container = input.closest('.form-group, .terms');
	const visibleError = form?.querySelector('.field-error');

	if (visibleError && visibleError.id !== errorId) {
		return;
	}

	let error = document.getElementById(errorId) || container?.querySelector('.field-error');

	if (!error) {
		error = document.createElement('small');
		error.id = errorId;
		error.className = 'field-error';
		container?.append(error);
	}

	error.textContent = message;
	input.setAttribute('aria-invalid', 'true');
	input.setCustomValidity(message);
}

// Remove a field's custom error and make it valid again.
function clearFieldError(input) {
	const container = input.closest('.form-group, .terms');
	const form = input.closest('form');
	const visibleError = form?.querySelector('.field-error');

	if (visibleError && visibleError.id === `${input.id}-error`) {
		visibleError.remove();
	}

	container?.querySelector('.field-error')?.remove();
	document.getElementById(`${input.id}-error`)?.remove();
	input.removeAttribute('aria-invalid');
	input.setCustomValidity('');
}

// Run a field-specific validator and return whether it passed.
function validateInput(input, validator) {
	const message = validator(input.value);

	if (message) {
		setFieldError(input, message);
		return false;
	}

	clearFieldError(input);
	return true;
}

// Remove accidental spaces from text-based auth values.
function trimTextInput(input) {
	if (input) {
		input.value = input.value.trim();
	}
}

// Attach shared input and submit behavior to a form.
function attachValidation(form, validate) {
	if (!form) {
		return;
	}

	form.querySelectorAll('input').forEach((input) => {
		// Clear the old message as soon as the user edits the field.
		input.addEventListener('input', () => clearFieldError(input));
	});

	form.addEventListener('submit', (event) => {
		// Stop the request when any field fails validation.
		if (!validate()) {
			event.preventDefault();
			form.querySelector('[aria-invalid="true"]')?.focus();
			return;
		}

		// Prevent accidental double submissions while the request is processing.
		const submitButton = form.querySelector('button[type="submit"]');

		if (submitButton) {
			submitButton.disabled = true;
			submitButton.setAttribute('aria-busy', 'true');
		}
	});
}


const loginIdentifier = document.getElementById('login-identifier');
const loginPassword = document.getElementById('login-password');

// Login rules: accept a username or email and require a password.
attachValidation(loginForm, () => {
	trimTextInput(loginIdentifier);

	const identifierIsValid = validateInput(loginIdentifier, (value) => {
		return value ? '' : 'Username or email is required.';
	});
	const passwordIsValid = validateInput(loginPassword, (value) => (
		value ? '' : 'Password is required.'
	));

	return identifierIsValid && passwordIsValid;
});

loginForm?.addEventListener('submit', async (event) => {
	if (event.defaultPrevented) {
		return;
	}

	event.preventDefault();
	const submitButton = loginForm.querySelector('button[type="submit"]');

	try {
		const response = await fetch(loginForm.action, {
			method: loginForm.method,
			headers: {
				Accept: 'application/json',
				'X-Requested-With': 'XMLHttpRequest',
			},
			body: new FormData(loginForm),
		});
		const data = await response.json();

		if (!response.ok) {
			Object.entries(data.errors || {}).forEach(([field, messages]) => {
				const input = loginForm.elements.namedItem(field);
				if (input) {
					setFieldError(input, messages[0]);
				}
			});
			return;
		}

		localStorage.setItem('auth_data', JSON.stringify(data));
		window.location.assign('/dashboard');
	} catch {
		setFieldError(loginIdentifier, 'Unable to log in. Please try again.');
	} finally {
		if (submitButton) {
			submitButton.disabled = false;
			submitButton.removeAttribute('aria-busy');
		}
	}
});

const registerUsername = document.getElementById('new-username');
const registerEmail = document.getElementById('new-email');
const registerPassword = document.getElementById('new-password');
const registerPasswordConfirmation = document.getElementById('new-password-confirm');
const terms = document.getElementById('terms');

// Registration rules: validate identity, password strength, confirmation, and terms.
attachValidation(registerForm, () => {
	trimTextInput(registerUsername);
	trimTextInput(registerEmail);

	const usernameIsValid = validateInput(registerUsername, (value) => {
		if (!value) return 'Username is required.';
		return value.length >= 3 ? '' : 'Username must be at least 3 characters.';
	});
	const emailIsValid = validateInput(registerEmail, (value) => {
		if (!value) return 'Email is required.';
		return emailPattern.test(value) ? '' : 'Enter a valid email address.';
	});
	const passwordIsValid = validateInput(registerPassword, (value) => {
		if (!value) return 'Password is required.';
		return value.length >= minimumPasswordLength
			? ''
			: `Password must be at least ${minimumPasswordLength} characters.`;
	});
	const confirmationIsValid = validateInput(registerPasswordConfirmation, (value) => {
		if (!value) return 'Please confirm your password.';
		return value === registerPassword.value ? '' : 'Passwords do not match.';
	});

	if (!terms.checked) {
		setFieldError(terms, 'You must accept the terms and conditions.');
	} else {
		clearFieldError(terms);
	}

	return usernameIsValid
		&& emailIsValid
		&& passwordIsValid
		&& confirmationIsValid
		&& terms.checked;
});
document.querySelectorAll('input[type="password"]').forEach((input) => {
	const container = input.closest('.form-group');
	if (!container) return;

	let toggle = container.querySelector('.password-toggle');
	if (!toggle) {
		toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'password-toggle';
		toggle.setAttribute('aria-label', 'Show password');
		toggle.innerHTML = '<i class="fa-regular fa-eye"></i>';
		container.appendChild(toggle);
	}

	toggle.addEventListener('click', () => {
		const shouldShow = input.type === 'password';
		input.type = shouldShow ? 'text' : 'password';
		toggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
		toggle.innerHTML = shouldShow
			? '<i class="fa-regular fa-eye-slash"></i>'
			: '<i class="fa-regular fa-eye"></i>';
	});
});