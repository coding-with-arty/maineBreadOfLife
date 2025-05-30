(() => {
	"use strict";

	/** ===============================
	 * SCROLL BEHAVIOR & HEADER EFFECTS
	 * =============================== */
	const body = document.body;
	const header = document.querySelector('#header');

	const toggleScrolled = () => {
		if (header?.classList.contains('scroll-up-sticky') ||
			header?.classList.contains('sticky-top') ||
			header?.classList.contains('fixed-top')) {
			body.classList.toggle('scrolled', window.scrollY > 100);
		}
	};

	window.addEventListener('load', toggleScrolled);
	document.addEventListener('scroll', toggleScrolled);

	/** ===============================
	 * MOBILE NAVIGATION TOGGLE
	 * =============================== */
	const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

	const toggleMobileNav = () => {
		body.classList.toggle('mobile-nav-active');
		mobileNavToggleBtn?.classList.toggle('bi-list');
		mobileNavToggleBtn?.classList.toggle('bi-x');
	};

	mobileNavToggleBtn?.addEventListener('click', toggleMobileNav);

	document.querySelectorAll('#navmenu a').forEach(link => {
		link.addEventListener('click', () => {
			if (body.classList.contains('mobile-nav-active')) {
				toggleMobileNav();
			}
		});
	});

	document.querySelectorAll('.navmenu .toggle-dropdown').forEach(dropdown => {
		dropdown.addEventListener('click', e => {
			e.preventDefault();
			dropdown.parentNode.classList.toggle('active');
			dropdown.parentNode.nextElementSibling?.classList.toggle('dropdown-active');
			e.stopImmediatePropagation();
		});
	});

	/** ===============================
	 * PRELOADER
	 * =============================== */
	const preloader = document.querySelector('#preloader');
	window.addEventListener('load', () => preloader?.remove());

	/** ===============================
	 * SCROLL TO TOP BUTTON
	 * =============================== */
	const scrollTopBtn = document.querySelector('.scroll-top');

	const toggleScrollTop = () => {
		scrollTopBtn?.classList.toggle('active', window.scrollY > 100);
	};

	scrollTopBtn?.addEventListener('click', e => {
		e.preventDefault();
		window.scrollTo({ top: 0, behavior: 'smooth' });
	});

	window.addEventListener('load', toggleScrollTop);
	document.addEventListener('scroll', toggleScrollTop);

	/** ===============================
	 * AOS (ANIMATE ON SCROLL)
	 * =============================== */
	const initAOS = () => {
		AOS.init({
			duration: 800,
			easing: 'ease-in-out',
			once: true,
			mirror: false
		});
	};

	window.addEventListener('load', initAOS);

	/** ===============================
	 * CAROUSEL INDICATORS
	 * =============================== */
	document.querySelectorAll('.carousel-indicators').forEach(indicator => {
		const carousel = indicator.closest('.carousel');
		carousel?.querySelectorAll('.carousel-item').forEach((item, index) => {
			const activeClass = index === 0 ? ' class="active"' : '';
			indicator.innerHTML += `
        <li data-bs-target="#${carousel.id}" data-bs-slide-to="${index}"${activeClass}></li>
      `;
		});
	});

	/** ===============================
	 * GLIGHTBOX INITIALIZATION
	 * =============================== */
	const glightbox = GLightbox({ selector: '.glightbox' });

	/** ===============================
	 * PURE COUNTER INITIALIZATION
	 * =============================== */
	new PureCounter();

	/** ===============================
	 * FAQ TOGGLE
	 * =============================== */
	document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach(trigger => {
		trigger.addEventListener('click', () => {
			trigger.parentNode.classList.toggle('faq-active');
		});
	});

	/** ===============================
	 * SWIPER SLIDER INITIALIZATION
	 * =============================== */
	const initSwipers = () => {
		document.querySelectorAll('.init-swiper').forEach(swiperEl => {
			try {
				const config = JSON.parse(swiperEl.querySelector('.swiper-config')?.innerHTML.trim() || '{}');
				if (swiperEl.classList.contains('swiper-tab')) {
					initSwiperWithCustomPagination(swiperEl, config); // Assuming this function is defined elsewhere
				} else {
					new Swiper(swiperEl, config);
				}
			} catch (err) {
				console.error('Swiper config error:', err);
			}
		});
	};

	window.addEventListener('load', initSwipers);

	/** ===============================
	 * ISOTOPE FILTERING
	 * =============================== */
	document.querySelectorAll('.isotope-layout').forEach(layoutEl => {
		const layoutMode = layoutEl.getAttribute('data-layout') || 'masonry';
		const defaultFilter = layoutEl.getAttribute('data-default-filter') || '*';
		const sortBy = layoutEl.getAttribute('data-sort') || 'original-order';

		let isoInstance;

		imagesLoaded(layoutEl.querySelector('.isotope-container'), () => {
			isoInstance = new Isotope(layoutEl.querySelector('.isotope-container'), {
				itemSelector: '.isotope-item',
				layoutMode,
				filter: defaultFilter,
				sortBy
			});
		});

		layoutEl.querySelectorAll('.isotope-filters li').forEach(filterBtn => {
			filterBtn.addEventListener('click', () => {
				layoutEl.querySelector('.filter-active')?.classList.remove('filter-active');
				filterBtn.classList.add('filter-active');
				isoInstance?.arrange({ filter: filterBtn.getAttribute('data-filter') });
				if (typeof initAOS === 'function') initAOS();
			});
		});
	});

	/** ===============================
	 * IMAGE LOADING ENHANCEMENTS
	 * =============================== */
	const MAX_RETRIES = 3;
	const RETRY_DELAY = 1000;
	const imageStatus = new Map();

	const handleImageLoaded = img => imageStatus.delete(img);

	const handleImageError = img => {
		const status = imageStatus.get(img);
		if (!status) return;

		if (status.retries < MAX_RETRIES) {
			status.retries++;
			status.loading = false;
			setTimeout(() => {
				if (imageStatus.has(img)) {
					const cacheBuster = `?retry=${Date.now()}`;
					const originalSrc = status.src.split('?')[0];
					img.src = originalSrc + cacheBuster;
					status.loading = true;
				}
			}, RETRY_DELAY * status.retries);
		} else {
			console.warn(`Image failed after ${MAX_RETRIES} retries: ${img.src}`);
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		document.querySelectorAll('img').forEach(img => {
			if (img.complete && img.naturalHeight !== 0) return;

			imageStatus.set(img, {
				src: img.src,
				retries: 0,
				loading: false
			});

			img.addEventListener('load', () => handleImageLoaded(img));
			img.addEventListener('error', () => handleImageError(img));

			if (!img.hasAttribute('loading')) {
				img.setAttribute('loading', 'lazy');
			}
		});
	});

	/** ===============================
	 * CSRF TOKEN LOADING
	 * =============================== */
	const loadCsrfTokens = (retryCount = 3) => {
		const loadingIndicator = document.getElementById('csrf-loading');
		if (retryCount === 3 && loadingIndicator) {
			loadingIndicator.style.display = 'inline-block';
		}

		fetch(`${window.location.origin}/forms/get_csrf_token.php`, {
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
			.then(res => res.json())
			.then(data => {
				if (!data.success || !data.token) throw new Error('Invalid CSRF token');

				document.getElementById('csrf_token')?.setAttribute('value', data.token);
				document.querySelectorAll('.newsletter-csrf-token').forEach(input => {
					input.value = data.token;
				});

				if (loadingIndicator) loadingIndicator.style.display = 'none';
			})
			.catch(err => {
				console.error('CSRF Token Error:', err);
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
	};

	/** ===============================
	 * CONTACT FORM VALIDATION
	 * =============================== */
	const validateContactForm = form => {
		let isValid = true;
		const fields = [
			{ id: 'name', message: 'Please enter your name.' },
			{ id: 'email', message: 'Please enter a valid email address.' },
			{ id: 'topic', message: 'Please enter a topic.' },
			{ id: 'message', message: 'Please enter your message.' }
		];

		fields.forEach(({ id, message }) => {
			const input = form.querySelector(`[name="${id}"]`);
			const errorDiv = form.querySelector(`#${id}-error`);
			const value = input?.value.trim();

			if (!value) {
				errorDiv.textContent = message;
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
	};

	/** ===============================
	 * FORM SUBMISSION HANDLERS
	 * =============================== */
	const setupFormHandlers = () => {
		const contactForm = document.querySelector('form.php-email-form[action$="contact.php"]');
		contactForm?.addEventListener('submit', function (e) {
			if (!validateContactForm(this)) {
				e.preventDefault();
				return false;
			}
			const messageDiv = document.getElementById('form-messages');
			if (messageDiv) {
				messageDiv.innerHTML = '<div class="alert alert-info">Sending message, please wait...</div>';
			}
		});

		const volunteerForm = document.getElementById('volunteerForm');
		volunteerForm?.addEventListener('submit', function (e) {
			if (!this.checkValidity()) {
				e.preventDefault();
				e.stopPropagation();
				this.classList.add('was-validated');
				return false;
			}

			const recaptchaResponse = grecaptcha?.getResponse();
			if (!recaptchaResponse) {
				alert('Please complete the reCAPTCHA verification.');
				e.preventDefault();
				return false;
			}

			const submitBtn = this.querySelector('button[type="submit"]');
			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
			}
		});

		document.querySelectorAll('form.php-email-form[action="forms/newsletter.php"]').forEach(form => {
			form.addEventListener('submit', () => {
				const messageDiv = form.querySelector('.newsletter-message');
				if (messageDiv) {
					messageDiv.innerHTML = '<div class="alert alert-info">Processing subscription, please wait...</div>';
				}
			});
		});

		window.addEventListener('load', () => {
			const urlParams = new URLSearchParams(window.location.search);
			const status = urlParams.get('status');
			const message = urlParams.get('message');

			if (status && message) {
				const decodedMessage = decodeURIComponent(message);
				const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';
				const messageContainer = document.referrer.includes('contact-us.html')
					? document.getElementById('form-messages')
					: document.querySelector('.newsletter-message');

				if (messageContainer) {
					messageContainer.innerHTML = `<div class="alert ${alertClass}">${decodedMessage}</div>`;
					messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
			}
		});
	};

	document.addEventListener('DOMContentLoaded', () => {
		loadCsrfTokens();
		setupFormHandlers();
	});
	// JavaScript source code
	/** ===============================
	 * CORE COMPONENT INITIALIZATION
	 * =============================== */
	const initBootstrapComponents = (selector, componentType) => {
		return Array.from(document.querySelectorAll(selector)).map(el => new bootstrapcomponentType);
	};

	const initBackToTop = () => {
		const backToTop = document.querySelector('.back-to-top');
		if (!backToTop) return;

		const toggleVisibility = () => {
			backToTop.classList.toggle('active', window.scrollY > 100);
		};

		window.addEventListener('load', toggleVisibility);
		document.addEventListener('scroll', toggleVisibility);

		backToTop.addEventListener('click', e => {
			e.preventDefault();
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	};

	const initCoreComponents = () => {
		AOS.init({
			duration: 1000,
			easing: 'ease-in-out',
			once: true,
			mirror: false
		});

		initBootstrapComponents('[data-bs-toggle="tooltip"]', 'Tooltip');
		initBootstrapComponents('[data-bs-toggle="popover"]', 'Popover');
		initBackToTop();
		initSwipers();
	};

	document.addEventListener('DOMContentLoaded', () => {
		initCoreComponents();
		window.scrollTo(0, 0);

		// Validate Swiper configs
		document.querySelectorAll('.swiper-config').forEach(config => {
			try {
				JSON.parse(config.innerHTML.trim());
			} catch (e) {
				console.error('Invalid Swiper configuration JSON:', e);
			}
		});

		// File upload label update
		const fileUpload = document.getElementById('file-upload');
		const fileLabel = document.getElementById('file-label');
		fileUpload?.addEventListener('change', function () {
			if (this.files.length > 0) {
				const fileNames = Array.from(this.files).map(file => file.name).join(', ');
				fileLabel.textContent = fileNames.length > 30 ? fileNames.substring(0, 30) + '...' : fileNames;
			} else {
				fileLabel.textContent = 'Attach Files';
			}
		});

		// Newsletter form placeholder (extend as needed)
		const newsletterForm = document.getElementById('newsletter-form');
		newsletterForm?.addEventListener('submit', e => {
			// Add validation if needed
		});

		// Display alert from URL parameters
		const urlParams = new URLSearchParams(window.location.search);
		const status = urlParams.get('status');
		const message = urlParams.get('message');

		if (status && message) {
			const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';
			const alertDiv = document.createElement('div');
			alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
			alertDiv.role = 'alert';
			alertDiv.innerHTML = `
        ${decodeURIComponent(message)}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;

			const container = document.querySelector('main') || document.body;
			container.insertAdjacentElement('afterbegin', alertDiv);
		}
	});

	/** ===============================
	 * OPTIONAL: GOOGLE MAP INIT
	 * =============================== */
	const initMap = () => {
		// Placeholder for custom map logic if needed
	};
})();