/**
 * Zuschuss Piloten - Main JavaScript
 * Handles navigation, form validation, animations, and interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
  // Initialize all modules
  initNavigation();
  initMobileMenu();
  initFormValidation();
  initScrollAnimations();
  initSmoothScroll();
  // Slow down hero video (1.0 = normal, 0.5 = half speed)
  const heroVideo = document.querySelector('.hero-video');
  if (heroVideo) heroVideo.playbackRate = 0.9;
});

/**
 * Navigation - Sticky header with scroll effects
 */
function initNavigation() {
  const navbar = document.getElementById('navbar');

  if (!navbar) return;

  const handleScroll = () => {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  };

  // Initial check
  handleScroll();

  // Add scroll listener with throttle
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => {
        handleScroll();
        ticking = false;
      });
      ticking = true;
    }
  });
}

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
  const mobileToggle = document.getElementById('mobileToggle');
  const navMenu = document.getElementById('navMenu');
  const navOverlay = document.getElementById('navOverlay');
  const navLinks = document.querySelectorAll('.nav-link');

  if (!mobileToggle || !navMenu) return;

  const toggleMenu = () => {
    navMenu.classList.toggle('active');
    if (navOverlay) navOverlay.classList.toggle('active');
    document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
  };

  const closeMenu = () => {
    navMenu.classList.remove('active');
    if (navOverlay) navOverlay.classList.remove('active');
    document.body.style.overflow = '';
  };

  mobileToggle.addEventListener('click', toggleMenu);
  if (navOverlay) navOverlay.addEventListener('click', closeMenu);

  // Close menu when clicking nav links
  navLinks.forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  // Close menu on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navMenu.classList.contains('active')) {
      closeMenu();
    }
  });
}

/**
 * Form Validation and Submission
 */
function initFormValidation() {
  const form = document.getElementById('foerderForm');
  const formSuccess = document.getElementById('formSuccess');

  if (!form) return;

  // Real-time validation for better UX
  const inputs = form.querySelectorAll('input, select, textarea');

  inputs.forEach(input => {
    input.addEventListener('blur', () => {
      validateField(input);
    });

    input.addEventListener('input', () => {
      if (input.classList.contains('error')) {
        validateField(input);
      }
    });
  });

  // Form submission
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Validate all fields
    let isValid = true;
    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    if (!isValid) {
      // Focus first error field
      const firstError = form.querySelector('.error');
      if (firstError) firstError.focus();
      return;
    }

    // Collect form data
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Submit to API
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
      // Show loading state
      submitBtn.innerHTML = '<span class="loading">Wird gesendet...</span>';
      submitBtn.disabled = true;

      // Send to PHP backend
      const response = await fetch('/api/submit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        // Show success message
        form.style.display = 'none';
        if (formSuccess) {
          formSuccess.classList.add('active');
        }

        // Scroll to form section
        document.getElementById('foerderanalyse').scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      } else {
        // Show error message
        throw new Error(result.error || 'Ein Fehler ist aufgetreten');
      }

    } catch (error) {
      console.error('Form submission error:', error);
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;

      // Show error message
      alert(error.message || 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.');
    }
  });

}

/**
 * Validate individual form field
 */
function validateField(field) {
  const value = field.value.trim();
  const isRequired = field.hasAttribute('required');
  let isValid = true;

  // Remove existing error styles
  field.classList.remove('error');

  // Check required fields
  if (isRequired && !value) {
    isValid = false;
  }

  // Email validation
  if (field.type === 'email' && value) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      isValid = false;
    }
  }

  // Phone validation (optional, but if filled, should be valid)
  if (field.type === 'tel' && value) {
    const phoneRegex = /^[+]?[\d\s()-]{6,}$/;
    if (!phoneRegex.test(value)) {
      isValid = false;
    }
  }

  // Add error class if invalid
  if (!isValid) {
    field.classList.add('error');
    field.style.borderColor = '#e53e3e';
  } else {
    field.style.borderColor = '';
  }

  return isValid;
}

/**
 * Scroll-triggered animations
 */
function initScrollAnimations() {
  const fadeElements = document.querySelectorAll('.fade-in');

  if (!fadeElements.length) return;

  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  fadeElements.forEach(el => observer.observe(el));
}

/**
 * Smooth scrolling for anchor links
 */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');

      if (href === '#') return;

      e.preventDefault();

      const target = document.querySelector(href);

      if (target) {
        const headerOffset = 80;
        const elementPosition = target.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });
}

/**
 * Utility: Add CSS styles for form errors dynamically
 */
(function addFormErrorStyles() {
  const style = document.createElement('style');
  style.textContent = `
    input.error,
    select.error,
    textarea.error {
      border-color: #e53e3e !important;
      animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    button:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
    
    .loading {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .loading::after {
      content: '';
      width: 16px;
      height: 16px;
      border: 2px solid transparent;
      border-top-color: currentColor;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  `;
  document.head.appendChild(style);
})();

/**
 * Frame-Sequence Scroll Animation (Apple-Style)
 * Frames are preloaded, then swapped based on scroll position.
 * Pinned hero stays sticky while frames cycle through.
 */
function initScrollVideo() {
  const heroFrame = document.getElementById('heroFrame');
  const container = document.getElementById('videoScrollContainer');
  const heroSection = document.getElementById('hero');

  if (!heroFrame || !container || !heroSection) return;

  const TOTAL_FRAMES = 100;
  const SCROLL_SPACE = 1800; // px of scroll space for the animation

  // Set container height immediately — no metadata wait needed
  container.style.height = (window.innerHeight + SCROLL_SPACE) + 'px';

  // Preload all frames into Image objects (browser caches them)
  const frames = new Array(TOTAL_FRAMES);
  for (let i = 0; i < TOTAL_FRAMES; i++) {
    const img = new Image();
    img.src = 'assets/frames/frame' + String(i + 1).padStart(3, '0') + '.jpg';
    frames[i] = img;
  }

  // First frame is already set in HTML (no flash)
  let currentFrameIndex = 0;

  const handleScroll = () => {
    const containerRect = container.getBoundingClientRect();
    const scrolled = -containerRect.top;
    const progress = Math.max(0, Math.min(1, scrolled / SCROLL_SPACE));

    const targetIndex = Math.round(progress * (TOTAL_FRAMES - 1));

    if (targetIndex !== currentFrameIndex) {
      currentFrameIndex = targetIndex;
      heroFrame.src = frames[currentFrameIndex].src;
    }

    // Hide scroll indicator once animation is near the end
    if (progress >= 0.95) {
      heroSection.classList.add('video-complete');
    } else {
      heroSection.classList.remove('video-complete');
    }
  };

  // Throttled scroll listener via rAF
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => {
        handleScroll();
        ticking = false;
      });
      ticking = true;
    }
  }, { passive: true });

  // Initial call
  handleScroll();

  // Update container height on resize
  window.addEventListener('resize', () => {
    container.style.height = (window.innerHeight + SCROLL_SPACE) + 'px';
  });
}
