/*
 *  ======================================================================
 *  MAIN.JS | MAINE BREAD OF LIFE - FORM SUBMIT VERSION
 *  AUTHOR: ARTHUR DANIEL BELANGER JR.
 *  https://github.com/coding-with-arty/maineBreadOfLife
 *  ======================================================================
 */
(function () {
  "use strict";

  // Toggle 'scrolled' class on header based on scroll position
  function toggleScrolled() {
    const header = document.querySelector(".home-header");
    if (!header) return;
    header.classList.toggle("scrolled", window.scrollY > 50);
  }

  document.addEventListener("scroll", toggleScrolled);
  window.addEventListener("load", toggleScrolled);

  // Mobile nav toggle
  const mobileNavToggleBtn = document.querySelector(".mobile-nav-toggle");
  function mobileNavToggle() {
    document.body.classList.toggle("mobile-nav-active");
    if (mobileNavToggleBtn) {
      mobileNavToggleBtn.classList.toggle("bi-list");
      mobileNavToggleBtn.classList.toggle("bi-x");
    }
  }
  mobileNavToggleBtn?.addEventListener("click", mobileNavToggle);

  // Close mobile nav on link click
  document.querySelectorAll("#navmenu a").forEach((link) => {
    link.addEventListener("click", () => {
      if (document.body.classList.contains("mobile-nav-active")) {
        mobileNavToggle();
      }
    });
  });

  // Toggle dropdowns in mobile nav
  document.querySelectorAll(".navmenu .toggle-dropdown").forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      const parent = this.parentNode;
      const sibling = parent?.nextElementSibling;
      parent?.classList.toggle("active");
      sibling?.classList.toggle("dropdown-active");
      e.stopImmediatePropagation();
    });
  });

  // Remove preloader on load
  const preloader = document.querySelector("#preloader");
  if (preloader) {
    window.addEventListener("load", () => preloader.remove());
  }

  // Scroll-to-top button
  const scrollTop = document.querySelector(".scroll-top");
  function toggleScrollTop() {
    if (scrollTop) {
      scrollTop.classList.toggle("active", window.scrollY > 100);
    }
  }

  scrollTop?.addEventListener("click", (e) => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  window.addEventListener("load", toggleScrollTop);
  document.addEventListener("scroll", toggleScrollTop);

  // Initialize AOS (Animate On Scroll) if loaded
  if (window.AOS) {
    window.addEventListener("load", () => {
      AOS.init({
        duration: 600,
        easing: "ease-in-out",
        once: true,
        mirror: false,
      });
    });
  }

  // Initialize PureCounter if loaded
  if (window.PureCounter) {
    new PureCounter();
  }

  // FAQ toggle functionality
  document
    .querySelectorAll(".faq-item h3, .faq-item .faq-toggle")
    .forEach((faqItem) => {
      faqItem.addEventListener("click", () => {
        faqItem.parentNode?.classList.toggle("faq-active");
      });
    });

  // Initialize Swiper sliders if loaded
  if (window.Swiper) {
    function initSwiper() {
      document
        .querySelectorAll(".init-swiper")
        .forEach(function (swiperElement) {
          try {
            const configElement = swiperElement.querySelector(".swiper-config");
            if (!configElement) return;
            let config = JSON.parse(configElement.textContent.trim());
            new Swiper(swiperElement, config);
          } catch (err) {
            console.error("Swiper initialization failed:", err);
          }
        });
    }
    window.addEventListener("load", initSwiper);
  }

  // Auto-generate carousel indicators for Bootstrap carousels
  document
    .querySelectorAll(".carousel-indicators")
    .forEach((carouselIndicator) => {
      const carousel = carouselIndicator.closest(".carousel");
      if (!carousel) return;

      const items = carousel.querySelectorAll(".carousel-item");
      carouselIndicator.innerHTML = ""; // clear existing
      items.forEach((item, index) => {
        const activeClass = index === 0 ? 'class="active"' : "";
        carouselIndicator.innerHTML += `<li data-bs-target="#${carousel.id}" data-bs-slide-to="${index}" ${activeClass}></li>`;
      });
    });

  // Update file upload label when files are selected
  const fileUpload = document.getElementById("file-upload");
  const fileLabel = document.querySelector('label[for="file-upload"]');
  if (fileUpload && fileLabel) {
    fileUpload.addEventListener("change", function () {
      if (this.files.length > 0) {
        const fileNames = Array.from(this.files)
          .map((file) => file.name)
          .join(", ");
        fileLabel.textContent =
          fileNames.length > 30
            ? fileNames.substring(0, 30) + "..."
            : fileNames;
      } else {
        fileLabel.textContent = "Attach Files (PDF, DOC, JPG, etc.)";
      }
    });
  }

  // Handle form submissions
  document.querySelectorAll('form[action*="formsubmit.co"]').forEach((form) => {
    form.addEventListener("submit", function (event) {
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn?.innerHTML;

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
      }

      // Let the form submit naturally to FormSubmit
      // The form will be handled by the browser's default behavior
      // since we're not calling event.preventDefault()

      // Reset the button after a delay in case submission fails
      setTimeout(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        }
      }, 10000); // Reset after 10 seconds if still on the page
    });
  });
})();
