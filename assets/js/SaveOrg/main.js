/*
 *  ======================================================================
 *  MAIN.JS | MAINE BREAD OF LIFE
 *  AUTHOR: ARTHUR DANIEL BELANGER JR.
 *  https://github.com/MusicalViking/maineBreadOfLife
 *  ======================================================================
*/
(function () {
	"use strict";

	// Toggle 'scrolled' class on header based on scroll position
	function toggleScrolled() {
		const header = document.querySelector('.home-header');
		if (!header) return;
		if (window.scrollY > 50) {
			header.classList.add('scrolled');
		} else {
			header.classList.remove('scrolled');
		}
	}

	document.addEventListener('scroll', toggleScrolled);
	window.addEventListener('load', toggleScrolled);

	// Mobile nav toggle
	const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');
	function mobileNavToggle() {
		document.body.classList.toggle('mobile-nav-active');
		mobileNavToggleBtn.classList.toggle('bi-list');
		mobileNavToggleBtn.classList.toggle('bi-x');
	}
	mobileNavToggleBtn?.addEventListener('click', mobileNavToggle);

	// Close mobile nav on link click
	document.querySelectorAll('#navmenu a').forEach(link => {
		link.addEventListener('click', () => {
			if (document.body.classList.contains('mobile-nav-active')) {
				mobileNavToggle();
			}
		});
	});

	// Toggle dropdowns in mobile nav
	document.querySelectorAll('.navmenu .toggle-dropdown').forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			this.parentNode.classList.toggle('active');
			this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
			e.stopImmediatePropagation();
		});
	});

	// Remove preloader on load
	const preloader = document.querySelector('#preloader');
	if (preloader) {
		window.addEventListener('load', () => preloader.remove());
	}

	// Scroll-to-top button
	const scrollTop = document.querySelector('.scroll-top');
	function toggleScrollTop() {
		if (scrollTop) {
			scrollTop.classList.toggle('active', window.scrollY > 100);
		}
	}

	scrollTop?.addEventListener('click', (e) => {
		e.preventDefault();
		window.scrollTo({ top: 0, behavior: 'smooth' });
	});

	window.addEventListener('load', toggleScrollTop);
	document.addEventListener('scroll', toggleScrollTop);

	// Initialize AOS (Animate On Scroll)
	function aosInit() {
		AOS.init({
			duration: 600,
			easing: 'ease-in-out',
			once: true,
			mirror: false
		});
	}
	window.addEventListener('load', aosInit);

	// Initialize GLightbox
	const glightbox = GLightbox({
		selector: '.glightbox'
	});

	// Initialize PureCounter
	new PureCounter();

	// FAQ toggle functionality
	document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach((faqItem) => {
		faqItem.addEventListener('click', () => {
			faqItem.parentNode.classList.toggle('faq-active');
		});
	});

	// Initialize Swiper sliders
	function initSwiper() {
		document.querySelectorAll(".init-swiper").forEach(function (swiperElement) {
			let config = JSON.parse(swiperElement.querySelector(".swiper-config").innerHTML.trim());
			if (swiperElement.classList.contains("swiper-tab")) {
				initSwiperWithCustomPagination(swiperElement, config); // Custom pagination logic if needed
			} else {
				new Swiper(swiperElement, config);
			}
		});
	}
	window.addEventListener("load", initSwiper);

	// Auto-generate carousel indicators
	document.querySelectorAll('.carousel-indicators').forEach((carouselIndicator) => {
		carouselIndicator.closest('.carousel').querySelectorAll('.carousel-item').forEach((item, index) => {
			const activeClass = index === 0 ? 'class="active"' : '';
			carouselIndicator.innerHTML += `<li data-bs-target="#${carouselIndicator.closest('.carousel').id}" data-bs-slide-to="${index}" ${activeClass}></li>`;
		});
	});

	// Load CSRF tokens with retry logic
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
				document.getElementById('csrf_token')?.setAttribute('value', data.token);
				document.querySelectorAll('.newsletter-csrf-token').forEach(input => input.value = data.token);
				document.getElementById('csrf-loading')?.style.setProperty('display', 'none');
			})
			.catch(error => {
				console.error('CSRF Token Error:', error);
				document.getElementById('csrf-loading')?.style.setProperty('display', 'none');
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

	// Validate contact form fields
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

	// Set up form submission handlers
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
	}

	document.addEventListener('DOMContentLoaded', function () {
		loadCsrfTokens();
		setupFormHandlers();
	});

	// Show alert messages from URL parameters
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

	// Update file upload label
	const fileUpload = document.getElementById('file-upload');
	const fileLabel = document.getElementById('file-label');
	if (fileUpload && fileLabel) {
		fileUpload.addEventListener('change', function () {
			if (this.files.length > 0) {
				const fileNames = Array.from(this.files).map(file => file.name).join(', ');
				fileLabel.textContent = fileNames.length > 30 ? fileNames.substring(0, 30) + '...' : fileNames;
			} else {
				fileLabel.textContent = 'Attach Files';
			}
		});
	};
})(); // End of consolidated script