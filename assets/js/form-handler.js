/**
 * Form Handler for Bread of Life Website
 * Handles CSRF token loading and form submission feedback
 */

document.addEventListener('DOMContentLoaded', function() {
  // Load CSRF tokens for all forms
  loadCsrfTokens();
  
  // Set up form submission handlers
  setupFormHandlers();
});

/**
 * Load CSRF tokens for contact and newsletter forms
 */
/**
 * Load CSRF tokens for all forms with retry logic
 * @param {number} retryCount - Number of retry attempts remaining
 */
function loadCsrfTokens(retryCount = 3) {
  // Only show loading indicator if this is the first attempt
  if (retryCount === 3) {
    const loadingIndicator = document.getElementById('csrf-loading');
    if (loadingIndicator) {
      loadingIndicator.style.display = 'inline-block';
    }
  }
  
  // Get the base URL from the current page
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
  .then(async response => {
    const data = await response.json();
    
    if (!response.ok || !data.success) {
      const error = new Error(data.message || 'Failed to load security token');
      error.response = response;
      error.data = data;
      throw error;
    }
    
    if (!data.token) {
      throw new Error('Invalid token received from server');
    }
    
    return data;
  })
  .then(data => {
    // Set token for contact form
    const contactCsrfInput = document.getElementById('csrf_token');
    if (contactCsrfInput) {
      contactCsrfInput.value = data.token;
    }
    
    // Set tokens for all newsletter forms
    const newsletterCsrfInputs = document.querySelectorAll('.newsletter-csrf-token');
    newsletterCsrfInputs.forEach(input => {
      input.value = data.token;
    });
    
    // Hide loading indicator
    const loadingIndicator = document.getElementById('csrf-loading');
    if (loadingIndicator) {
      loadingIndicator.style.display = 'none';
    }
  })
  .catch(error => {
    console.error('CSRF Token Error:', error);
    
    // Hide loading indicator
    const loadingIndicator = document.getElementById('csrf-loading');
    if (loadingIndicator) {
      loadingIndicator.style.display = 'none';
    }
    
    // Only show error to user on last attempt
    if (retryCount <= 0) {
      const messageDiv = document.getElementById('form-messages') || 
                        document.querySelector('.newsletter-message');
      if (messageDiv) {
        messageDiv.innerHTML = `
          <div class="alert alert-danger">
            <strong>Security Error:</strong> Unable to load security token. 
            <button onclick="window.location.reload()" class="btn btn-link p-0">
              Please refresh the page and try again.
            </button>
          </div>`;
      }
    } else {
      // Retry with exponential backoff
      const delay = Math.min(1000 * Math.pow(2, 3 - retryCount), 5000);
      console.log(`Retrying CSRF token load in ${delay}ms...`);
      setTimeout(() => loadCsrfTokens(retryCount - 1), delay);
    }
  });
}

/**
 * Set up form submission handlers for all forms
 */
function setupFormHandlers() {
  // Contact form handler
  const contactForm = document.querySelector('form.php-email-form[action$="contact.php"]');
  if (contactForm) {
    console.log('Contact form found, setting up submit handler');
    contactForm.addEventListener('submit', function(e) {
      console.log('Contact form submitted');
      const messageDiv = document.getElementById('form-messages');
      
      // Log form data for debugging
      const formData = new FormData(this);
      for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
      }
      
      // Show loading indicator
      if (messageDiv) {
        console.log('Showing loading message');
        messageDiv.innerHTML = '<div class="alert alert-info">Sending message, please wait...</div>';
      }
      
      // Let the form submit normally - PHP will handle it
      // We're not preventing default because we want the traditional form submission
      // This is more reliable on GoDaddy hosting than AJAX
    });
  }
  
  // Newsletter form handlers
  const newsletterForms = document.querySelectorAll('form.php-email-form[action="forms/newsletter.php"]');
  newsletterForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const messageDiv = form.querySelector('.newsletter-message');
      
      // Show loading indicator
      if (messageDiv) {
        messageDiv.innerHTML = '<div class="alert alert-info">Processing subscription, please wait...</div>';
      }
      
      // Let the form submit normally - PHP will handle it
    });
  });
  
  // Check for success/error parameters in URL
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    
    if (status && message) {
      // Display message based on status
      const decodedMessage = decodeURIComponent(message);
      const alertClass = status === 'success' ? 'alert-success' : 'alert-danger';
      
      // Find the appropriate message container based on the referring form
      let messageContainer;
      if (document.referrer.includes('contact-us.html')) {
        messageContainer = document.getElementById('form-messages');
      } else {
        messageContainer = document.querySelector('.newsletter-message');
      }
      
      if (messageContainer) {
        messageContainer.innerHTML = `<div class="alert ${alertClass}">${decodedMessage}</div>`;
        
        // Scroll to the message
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
}
