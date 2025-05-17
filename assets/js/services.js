/**
 * Services Page JavaScript
 * Handles animations and interactivity for the services page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll) if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Animate stats counter
    function animateStats() {
        const statNumbers = document.querySelectorAll('.stat-number');
        if (statNumbers.length > 0) {
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50; // Adjust speed of counting
                
                const updateCount = () => {
                    if (current < target) {
                        current += increment;
                        stat.textContent = current >= target ? target.toLocaleString() : Math.ceil(current).toLocaleString();
                        requestAnimationFrame(updateCount);
                    }
                };
                
                // Only start counting when in viewport
                const observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        updateCount();
                        observer.unobserve(stat);
                    }
                });
                
                observer.observe(stat);
            });
        }
    }

    // Initialize stats counter when in viewport
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
});

// Add class to body when JavaScript is enabled
document.documentElement.classList.add('js-enabled');
