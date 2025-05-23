/**
 * Custom scripts for Bread of Life website
 * Created to replace inline scripts and strengthen Content Security Policy
 */

// Force scroll to top on page load
window.onload = function() {
    window.scrollTo(0, 0);
};

// Initialize core components
function initCoreComponents() {
    // AOS Animation
    AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Bootstrap Tooltips
    const tooltipList = initBootstrapComponents('[data-bs-toggle="tooltip"]', 'Tooltip');
    
    // Bootstrap Popovers
    const popoverList = initBootstrapComponents('[data-bs-toggle="popover"]', 'Popover');

    // Back to Top Button
    initBackToTop();

    // Swiper Sliders
    initSwipers();
}

// Generic Bootstrap component initializer
function initBootstrapComponents(selector, componentType) {
    return [].slice.call(document.querySelectorAll(selector))
        .map(el => new bootstrap[componentType](el));
}

// Back to Top functionality
function initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    if (!backToTop) return;

    const toggleVisibility = () => {
        backToTop.classList.toggle('active', window.scrollY > 100);
    };

    window.addEventListener('load', toggleVisibility);
    document.addEventListener('scroll', toggleVisibility);
    backToTop.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// Initialize Swiper sliders
function initSwipers() {
    document.querySelectorAll('.init-swiper').forEach(swiperEl => {
        try {
            const config = JSON.parse(swiperEl.querySelector('.swiper-config').innerHTML);
            new Swiper(swiperEl, config);
        } catch (e) {
            console.error('Swiper initialization error:', e);
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCoreComponents();

    // Force scroll to top again after DOM is loaded
    window.scrollTo(0, 0);
    // Swiper configuration handling
    const swiperConfigs = document.querySelectorAll('.swiper-config');
    swiperConfigs.forEach(config => {
        // This will be handled by the main.js initSwiper function
        // We're just ensuring the JSON is properly formatted and accessible
        try {
            JSON.parse(config.innerHTML.trim());
        } catch (e) {
            console.error('Invalid Swiper configuration JSON:', e);
        }
    });

    // Contact form handling
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        const formMessages = document.getElementById('form-messages');
        const fileUpload = document.getElementById('file-upload');
        const fileLabel = document.getElementById('file-label');
        
        // File upload handling
        if (fileUpload && fileLabel) {
            fileUpload.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const fileNames = Array.from(this.files).map(file => file.name).join(', ');
                    fileLabel.textContent = fileNames.length > 30 ? 
                        fileNames.substring(0, 30) + '...' : 
                        fileNames;
                } else {
                    fileLabel.textContent = 'Attach Files';
                }
            });
        }
    }

    // Newsletter form handling
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            // Form validation can be added here
        });
    }

    // Service Page Statistics Animation
    function animateStats() {
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(stat => {
            const target = parseInt(stat.textContent);
            let current = 0;
            const increment = target / 50;

            const updateCount = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = current >= target ? target.toLocaleString() : Math.ceil(current).toLocaleString();
                    requestAnimationFrame(updateCount);
                }
            };

            const statsObserver = createScrollObserver(() => updateCount());
            statsObserver.observe(stat);
        });
    }

    // Initialize stats counter
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateStats();
                observer.unobserve(statsSection);
            }
        }, { threshold: 0.1 });
        observer.observe(statsSection);
    }

    // Handle URL parameter messages
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

// Google Maps initialization (if needed)
function initMap() {
    // This function can be used if custom map initialization is needed
    // Currently using iframe embed which doesn't require this
}

// Function to create a scroll observer
function createScrollObserver(callback) {
    return new IntersectionObserver(callback, { threshold: 0.1 });
}