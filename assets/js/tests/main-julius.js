// Cleaned and optimized JavaScript// Processed by Julius AI (https://julius.ai)// JavaScript source code
/**
 * Arthur Belanger
 * Maine Bread of Life - Augusta, Maine
 **/

(() => {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  mobileNavToggleBtn.addEventListener('click', mobileNavToogle);

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
    });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', (e) => {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
  scrollTop.addEventListener('click', (e) => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
  window.addEventListener('load', aosInit);

  /**
   * Auto generate the carousel indicators
   */
  document.querySelectorAll('.carousel-indicators').forEach((carouselIndicator) => {
    carouselIndicator.closest('.carousel').querySelectorAll('.carousel-item').forEach((carouselItem, index) => {
      if (index === 0) {
        carouselIndicator.innerHTML += `<li data-bs-target="#${carouselIndicator.closest('.carousel').id}" data-bs-slide-to="${index}" class="active"></li>`;
      } else {
        carouselIndicator.innerHTML += `<li data-bs-target="#${carouselIndicator.closest('.carousel').id}" data-bs-slide-to="${index}"></li>`;

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'

  /**
   * Initiate Pure Counter
   */
  new PureCounter();

  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach((swiperElement) => {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
        new Swiper(swiperElement, config);

  window.addEventListener("load", initSwiper);

  /**
   * Animate the skills items on reveal

  let skillsAnimation = document.querySelectorAll('.skills-animation');
  skillsAnimation.forEach((item) => {
  	new Waypoint({
  		element: item,
  		offset: '80%',
  		handler: (direction) => {
  			let progress = item.querySelectorAll('.progress .progress-bar');
  			progress.forEach(el => {
  				el.style.width = el.getAttribute('aria-valuenow') + '%';
  */
  /**
   * Init isotope layout and filters
   */
  document.querySelectorAll('.isotope-layout').forEach((isotopeItem) => {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), () => {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort

    isotopeItem.querySelectorAll('.isotope-filters li').forEach((filters) => {
      filters.addEventListener('click', () => {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        if (typeof aosInit === 'function') {
          aosInit();
      }, false);

  // Scroll Observer Utility
  function createScrollObserver(callback, options = {
    threshold: 0.1
  }) {
    return new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) callback(entry.target);
    }, options);

  // Image Loading Enhancement
  const MAX_RETRIES = 3;
  const RETRY_DELAY = 1000;

  function handleImageLoaded(img) {
    if (imageStatus.has(img)) {
      imageStatus.delete(img);

  function handleImageError(img) {
    if (!imageStatus.has(img)) return;
    const status = imageStatus.get(img);

    if (status.retries < MAX_RETRIES) {
      status.retries++;
      status.loading = false;
      setTimeout(() => {
          const cacheBuster = `?retry=${Date.now()}`;
          const originalSrc = status.src.split('?')[0];
          img.src = originalSrc + cacheBuster;
          status.loading = true;
      }, RETRY_DELAY * status.retries);
      console.warn(`Failed to load image after ${MAX_RETRIES} retries: ${img.src}`);

  // Initialize image tracking
  document.addEventListener('DOMContentLoaded', () => {
    const allImages = document.querySelectorAll('img');
    const imageStatus = new Map();

    allImages.forEach(img => {
      if (img.complete && img.naturalHeight !== 0) return;
      imageStatus.set(img, {
        src: img.src,
        retries: 0,
        loading: false

      img.addEventListener('load', () => handleImageLoaded(img));
      img.addEventListener('error', () => handleImageError(img));

      if (!img.hasAttribute('loading')) {
        img.setAttribute('loading', 'lazy');

  /**
   * Form Handler for Bread of Life Website
   * Handles CSRF token loading, form validation, and feedback
   */

    loadCsrfTokens();
    setupFormHandlers();

  /**
   * Load CSRF tokens for all forms with retry logic
   */
  function loadCsrfTokens(retryCount = 3) {
    if (retryCount === 3) {
      const loadingIndicator = document.getElementById('csrf-loading');
      if (loadingIndicator) loadingIndicator.style.display = 'inline-block';

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
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success || !data.token) throw new Error('Invalid CSRF token');

        const contactToken = document.getElementById('csrf_token');
        if (contactToken) contactToken.value = data.token;

        document.querySelectorAll('.newsletter-csrf-token').forEach(input => {
          input.value = data.token;

        if (loadingIndicator) loadingIndicator.style.display = 'none';
      .catch(error => {
        console.error('CSRF Token Error:', error);

        if (retryCount <= 0) {
          const messageDiv = document.getElementById('form-messages') || document.querySelector('.newsletter-message');
          if (messageDiv) {
            messageDiv.innerHTML = `
            <div class="alert alert-danger">
              <strong>Security Error:</strong> Unable to load security token.
              <button onclick="window.location.reload()" class="btn btn-link p-0">Refresh and try again</button>
            </div>`;
          const delay = Math.min(1000 * Math.pow(2, 3 - retryCount), 5000);
          setTimeout(() => loadCsrfTokens(retryCount - 1), delay);

  /**
   * Validate contact form fields and show inline errors
   */
  function validateContactForm(form) {
    let isValid = true;

    const fields = [{
      id: 'name',
      message: 'Please enter your name.'
    }, {
      id: 'email',
      message: 'Please enter a valid email address.'
      id: 'topic',
      message: 'Please enter a topic.'
      id: 'message',
      message: 'Please enter your message.'
    }];

    fields.forEach(field => {
      const input = form.querySelector(`[name="${field.id}"]`);
      const errorDiv = form.querySelector(`#${field.id}-error`);

      if (!input.value.trim()) {
        errorDiv.textContent = field.message;
        errorDiv.classList.remove('visually-hidden');
        input.classList.add('is-invalid');
        isValid = false;
        errorDiv.textContent = '';
        errorDiv.classList.add('visually-hidden');
        input.classList.remove('is-invalid');

    return isValid;

  /**
   * Set up form submission handlers
   */
  function setupFormHandlers() {
    const contactForm = document.querySelector('form.php-email-form[action$="contact.php"]');
    if (contactForm) {
      contactForm.addEventListener('submit', (e) => {
        if (!validateContactForm(this)) {
          return false;

        const messageDiv = document.getElementById('form-messages');
          messageDiv.innerHTML = '<div class="alert alert-info">Sending message, please wait...</div>';

    const volunteerForm = document.getElementById('volunteerForm');
    if (volunteerForm) {
      volunteerForm.addEventListener('submit', (e) => {
        if (!this.checkValidity()) {
          e.stopPropagation();
          this.classList.add('was-validated');

        const recaptchaResponse = grecaptcha && grecaptcha.getResponse();
        if (!recaptchaResponse || recaptchaResponse.length === 0) {
          alert('Please complete the reCAPTCHA verification.');

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

    const newsletterForms = document.querySelectorAll('form.php-email-form[action="forms/newsletter.php"]');
    newsletterForms.forEach(form => {
      form.addEventListener('submit', () => {
        const messageDiv = form.querySelector('.newsletter-message');
          messageDiv.innerHTML = '<div class="alert alert-info">Processing subscription, please wait...</div>';

    window.addEventListener('load', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const message = urlParams.get('message');

      if (status && message) {
        const decodedMessage = decodeURIComponent(message);
        const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';

        let messageContainer = document.referrer.includes('contact-us.html') ?
          document.getElementById('form-messages') :
          document.querySelector('.newsletter-message');

        if (messageContainer) {
          messageContainer.innerHTML = `<div class="alert ${alertClass}">${decodedMessage}</div>`;
          messageContainer.scrollIntoView({
            behavior: 'smooth',
            block: 'center'

  /**
   * Custom scripts for Bread of Life website
   * Created to replace inline scripts and strengthen Content Security Policy
   */

  // Force scroll to top on page load
  window.onload = () => {
    window.scrollTo(0, 0);
  };

  // Initialize core components
  function initCoreComponents() {
    // AOS Animation
      duration: 1000,

    // Bootstrap Tooltips
    const tooltipList = initBootstrapComponents('[data-bs-toggle="tooltip"]', 'Tooltip');

    // Bootstrap Popovers
    const popoverList = initBootstrapComponents('[data-bs-toggle="popover"]', 'Popover');

    // Back to Top Button
    initBackToTop();

    // Swiper Sliders
    initSwipers();

  // Generic Bootstrap component initializer
  function initBootstrapComponents(selector, componentType) {
    return [].slice.call(document.querySelectorAll(selector))
      .map(el => new bootstrap[componentType](el));

  // Back to Top functionality
  function initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    if (!backToTop) return;

    const toggleVisibility = () => {
      backToTop.classList.toggle('active', window.scrollY > 100);

    window.addEventListener('load', toggleVisibility);
    document.addEventListener('scroll', toggleVisibility);
    backToTop.addEventListener('click', (e) => {

  // Initialize Swiper sliders
  function initSwipers() {
    document.querySelectorAll('.init-swiper').forEach(swiperEl => {
      try {
        const config = JSON.parse(swiperEl.querySelector('.swiper-config').innerHTML);
        new Swiper(swiperEl, config);
      } catch (e) {
        console.error('Swiper initialization error:', e);

    initCoreComponents();

    // Force scroll to top again after DOM is loaded
    // Swiper configuration handling
    const swiperConfigs = document.querySelectorAll('.swiper-config');
    swiperConfigs.forEach(config => {
      // This will be handled by the main.js initSwiper function
      // We're just ensuring the JSON is properly formatted and accessible
        JSON.parse(config.innerHTML.trim());
        console.error('Invalid Swiper configuration JSON:', e);

    // Contact form handling
    const contactForm = document.getElementById('contact-form');
      const formMessages = document.getElementById('form-messages');
      const fileUpload = document.getElementById('file-upload');
      const fileLabel = document.getElementById('file-label');

      // File upload handling
      if (fileUpload && fileLabel) {
        fileUpload.addEventListener('change', () => {
          if (this.files.length > 0) {
            const fileNames = Array.from(this.files).map(file => file.name).join(', ');
            fileLabel.textContent = fileNames.length > 30 ?
              fileNames.substring(0, 30) + '...' :
              fileNames;
            fileLabel.textContent = 'Attach Files';

    // Newsletter form handling
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', (e) => {
        // Form validation can be added here

    // Handle URL parameter messages

      const alertDiv = document.createElement('div');
      alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
      alertDiv.role = 'alert';
      alertDiv.innerHTML = `
            ${decodeURIComponent(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

      const container = document.querySelector('main') || document.body;
      container.insertAdjacentElement('afterbegin', alertDiv);

  // Google Maps initialization (if needed)
  function initMap() {
    // This function can be used if custom map initialization is needed
    // Currently using iframe embed which doesn't require this

  // Function to create a scroll observer
  function createScrollObserver(callback) {
    return new IntersectionObserver(callback, {
})();
