/**
 * Form Handler for Bread of Life Website
 * Handles CSRF token loading, form validation, and feedback
 */

document.addEventListener('DOMContentLoaded', function () {
	loadCsrfTokens();
	setupFormHandlers();
});

/**
 * Load CSRF tokens for all forms with retry logic
 */
function loadCsrfTokens(retryCount = 3) {
	if (retryCount === 3) {
		const loadingIndicator = document.getElementById('csrf-loading');
		if (loadingIndicator) loadingIndicator.style.display = 'inline-block';
	}

	const baseUrl = window.location.origin;

	fetch(`${baseUrl}/forms/get_csrf_token.php`, {
		method: 'GET',
		cache: 'no-store',
		credentials: 'same-origin',
		headers: {
			'Accept': 'application/json',
			'X-Requested-With': 'XMLHttpRequest',
			'X-Requested-By': 'BreadOfLifeForm',
			'X-CSRF-Request': 'true'
		}
	})
		.then(response => response.json())
		.then(data => {
			if (!data.success || !data.token) throw new Error('Invalid CSRF token');

			const contactToken = document.getElementById('csrf_token');
			if (contactToken) contactToken.value = data.token;

			document.querySelectorAll('.newsletter-csrf-token').forEach(input => {
				input.value = data.token;
			});

			const loadingIndicator = document.getElementById('csrf-loading');
			if (loadingIndicator) loadingIndicator.style.display = 'none';
		})
		.catch(error => {
			console.error('CSRF Token Error:', error);
			const loadingIndicator = document.getElementById('csrf-loading');
			if (loadingIndicator) loadingIndicator.style.display = 'none';

			if (retryCount <= 0) {
				const messageDiv = document.getElementById('form-messages') || document.querySelector('.newsletter-message');
				if (messageDiv) {
					messageDiv.innerHTML = `
            <div class="alert alert-danger">
              <strong>Security Error:</strong> Unable to load security token.
              <button onclick="window.location.reload()" class="btn btn-link p-0">Refresh and try again</button>
            </div>`;
				}
			} else {
				const delay = Math.min(1000 * Math.pow(2, 3 - retryCount), 5000);
				setTimeout(() => loadCsrfTokens(retryCount - 1), delay);
			}
		});
}

/**
 * Validate contact form fields and show inline errors
 */
function validateContactForm(form) {
	let isValid = true;

	const fields = [
		{ id: 'name', message: 'Please enter your name.' },
		{ id: 'email', message: 'Please enter a valid email address.' },
		{ id: 'topic', message: 'Please enter a topic.' },
		{ id: 'message', message: 'Please enter your message.' }
	];

	fields.forEach(field => {
		const input = form.querySelector(`[name="${field.id}"]`);
		const errorDiv = form.querySelector(`#${field.id}-error`);

		if (!input.value.trim()) {
			errorDiv.textContent = field.message;
			errorDiv.classList.remove('visually-hidden');
			input.classList.add('is-invalid');
			isValid = false;
		} else {
			errorDiv.textContent = '';
			errorDiv.classList.add('visually-hidden');
			input.classList.remove('is-invalid');
		}
	});

	return isValid;
}

/**
 * Set up form submission handlers
 */
function setupFormHandlers() {
	const contactForm = document.querySelector('form.php-email-form[action$="contact.php"]');
	if (contactForm) {
		contactForm.addEventListener('submit', function (e) {
			if (!validateContactForm(this)) {
				e.preventDefault();
				return false;
			}

			const messageDiv = document.getElementById('form-messages');
			if (messageDiv) {
				messageDiv.innerHTML = '<div class="alert alert-info">Sending message, please wait...</div>';
			}
		});
	}

	const volunteerForm = document.getElementById('volunteerForm');
	if (volunteerForm) {
		volunteerForm.addEventListener('submit', function (e) {
			if (!this.checkValidity()) {
				e.preventDefault();
				e.stopPropagation();
				this.classList.add('was-validated');
				return false;
			}

			const recaptchaResponse = grecaptcha && grecaptcha.getResponse();
			if (!recaptchaResponse || recaptchaResponse.length === 0) {
				alert('Please complete the reCAPTCHA verification.');
				e.preventDefault();
				return false;
			}

			const submitBtn = this.querySelector('button[type="submit"]');
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
		});
	}

	const newsletterForms = document.querySelectorAll('form.php-email-form[action="forms/newsletter.php"]');
	newsletterForms.forEach(form => {
		form.addEventListener('submit', function () {
			const messageDiv = form.querySelector('.newsletter-message');
			if (messageDiv) {
				messageDiv.innerHTML = '<div class="alert alert-info">Processing subscription, please wait...</div>';
			}
		});
	});

	window.addEventListener('load', function () {
		const urlParams = new URLSearchParams(window.location.search);
		const status = urlParams.get('status');
		const message = urlParams.get('message');

		if (status && message) {
			const decodedMessage = decodeURIComponent(message);
			const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';

			let messageContainer = document.referrer.includes('contact-us.html')
				? document.getElementById('form-messages')
				: document.querySelector('.newsletter-message');

			if (messageContainer) {
				messageContainer.innerHTML = `<div class="alert ${alertClass}">${decodedMessage}</div>`;
				messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}
	});
}