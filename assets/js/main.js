/**
 * Main JavaScript for the site.
 *
 * Vanilla JS. No dependencies and no build tooling required.
 */
initPageLoader();

function initSite() {
  initMobileMenu();
  initLightbox();
  initTagSearch();
  initManifestoStrip();
  initMarquee();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initSite, { once: true });
} else {
  initSite();
}

function initPageLoader() {
  const loader = document.querySelector('[data-page-loader]');
  if (!loader) return;

  let hidden = false;
  const hide = () => {
    if (hidden) return;
    hidden = true;

    loader.classList.add('is-hiding');
    loader.setAttribute('aria-hidden', 'true');
  };

  const show = () => {
    hidden = false;
    loader.removeAttribute('aria-hidden');
    loader.classList.remove('is-hiding');
  };

  const shouldShowForLink = (link, event) => {
    if (
      event.defaultPrevented ||
      event.button !== 0 ||
      event.metaKey ||
      event.ctrlKey ||
      event.shiftKey ||
      event.altKey ||
      link.target ||
      link.hasAttribute('download')
    ) {
      return false;
    }

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
      return false;
    }

    const nextUrl = new URL(link.href, window.location.href);
    const currentUrl = new URL(window.location.href);

    if (nextUrl.origin !== currentUrl.origin) return false;

    return nextUrl.pathname !== currentUrl.pathname ||
      nextUrl.search !== currentUrl.search ||
      nextUrl.hash === '';
  };

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || shouldShowForLink(link, event) === false) return;

    show();
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form || form.target) return;

    show();
  });

  window.addEventListener('pageshow', hide);

  if (document.readyState === 'complete') {
    window.requestAnimationFrame(hide);
  } else {
    window.addEventListener('load', hide, { once: true });
    window.setTimeout(hide, 6000);
  }
}


function initMobileMenu() {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.site-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    const isActive = nav.classList.toggle('active');
    toggle.classList.toggle('active');
    toggle.setAttribute('aria-expanded', String(isActive));
    setScrollLock(isActive);
  });

  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('active');
      toggle.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
      setScrollLock(false);
    });
  });
}

function initLightbox() {
  const lightbox = document.getElementById('lightbox');
  const allTriggers = Array.from(document.querySelectorAll('.js-lightbox-trigger'));
  if (!lightbox || allTriggers.length === 0) return;

  const lbImage = lightbox.querySelector('.lightbox-image');
  const lbTitle = lightbox.querySelector('.lightbox-title');
  const lbDesc = lightbox.querySelector('.lightbox-desc');
  const lbExif = lightbox.querySelector('.lightbox-exif');
  const lbClose = lightbox.querySelector('.lightbox-close');
  const lbPrev = lightbox.querySelector('.lightbox-prev');
  const lbNext = lightbox.querySelector('.lightbox-next');

  if (!lbImage || !lbTitle || !lbDesc || !lbExif || !lbClose) return;

  // Deduplicate triggers that share the same data-lightbox-id (e.g. marquee duplicates).
  // Only navigate through unique images; all duplicates still open the correct one.
  const seenIds = new Set();
  const triggers = [];
  const triggerIndexMap = new Map(); // Maps all trigger elements to their unique index

  allTriggers.forEach(el => {
    const lid = el.dataset.lightboxId;
    if (lid && seenIds.has(lid)) {
      // Duplicate — map it to the same unique index
      triggerIndexMap.set(el, triggers.length - 1);
    } else {
      if (lid) seenIds.add(lid);
      triggerIndexMap.set(el, triggers.length);
      triggers.push(el);
    }
  });

  let currentIndex = 0;

  const exifFields = [
    { key: 'camera', label: lightbox.dataset.labelCamera || 'Camera' },
    { key: 'lens', label: lightbox.dataset.labelLens || 'Lens' },
    { key: 'focalLength', label: lightbox.dataset.labelFocalLength || 'Focal Length' },
    { key: 'iso', label: lightbox.dataset.labelIso || 'ISO' },
    { key: 'shutter', label: lightbox.dataset.labelShutter || 'Shutter' },
    { key: 'aperture', label: lightbox.dataset.labelAperture || 'F-Stop' },
    { key: 'date', label: lightbox.dataset.labelDate || 'Date' },
    { key: 'imageRole', label: lightbox.dataset.labelRole || 'Role' },
    { key: 'technicalNote', label: lightbox.dataset.labelTechnicalNote || 'Technical' },
    { key: 'materialNote', label: lightbox.dataset.labelMaterialNote || 'Material' },
    { key: 'imageDate', label: lightbox.dataset.labelImageDate || 'Image Date' },
    { key: 'imageNote', label: lightbox.dataset.labelImageNote || 'Note' },
  ];

  function focusableControls() {
    return Array.from(lightbox.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'))
      .filter(el => !el.disabled && el.offsetParent !== null);
  }

  function openLightbox(index) {
    const trigger = triggers[index];
    if (!trigger) return;

    currentIndex = index;
    const img = trigger.querySelector('img');
    const src = trigger.dataset.src || (img ? img.currentSrc || img.src : '');
    if (!src) return;

    lbImage.src = src;
    lbImage.alt = trigger.dataset.altText || trigger.dataset.title || (img ? img.alt : '');

    lbTitle.textContent = trigger.dataset.title || '';
    lbDesc.textContent = trigger.dataset.caption || '';
    lbTitle.style.display = trigger.dataset.title ? '' : 'none';
    lbDesc.style.display = trigger.dataset.caption ? '' : 'none';

    lbExif.replaceChildren();
    let hasExif = false;
    exifFields.forEach(field => {
      const val = trigger.dataset[field.key];
      if (val && val.trim() && val !== 'null' && val !== 'undefined') {
        const term = document.createElement('dt');
        const desc = document.createElement('dd');
        term.textContent = field.label;
        desc.textContent = val;
        lbExif.append(term, desc);
        hasExif = true;
      }
    });
    lbExif.style.display = hasExif ? 'grid' : 'none';

    lightbox.hidden = false;
    lightbox.setAttribute('aria-hidden', 'false');
    lightbox.classList.add('active');
    setScrollLock(true);
    lbClose.focus();
  }

  function closeLightbox() {
    lightbox.classList.remove('active');
    lightbox.setAttribute('aria-hidden', 'true');
    lightbox.hidden = true;
    setScrollLock(false);
    lbImage.src = '';

    if (triggers[currentIndex]) {
      triggers[currentIndex].focus();
    }
  }

  function navigate(dir) {
    currentIndex = (currentIndex + dir + triggers.length) % triggers.length;
    openLightbox(currentIndex);
  }

  // Bind click/keyboard to ALL trigger elements (including duplicates),
  // but resolve to the unique index for lightbox navigation.
  allTriggers.forEach(el => {
    el.setAttribute('tabindex', '0');
    el.setAttribute('role', 'button');
    el.setAttribute('aria-label', el.dataset.title || 'Fotoğrafı büyüt');

    el.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      const uniqueIndex = triggerIndexMap.get(el) ?? 0;
      openLightbox(uniqueIndex);
    });

    el.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        const uniqueIndex = triggerIndexMap.get(el) ?? 0;
        openLightbox(uniqueIndex);
      }
    });
  });

  lbClose.addEventListener('click', closeLightbox);
  if (lbPrev) lbPrev.addEventListener('click', event => { event.stopPropagation(); navigate(-1); });
  if (lbNext) lbNext.addEventListener('click', event => { event.stopPropagation(); navigate(1); });

  lightbox.addEventListener('click', event => {
    if (event.target === lightbox) closeLightbox();
  });

  // Swipe support
  let touchStartX = 0;
  let touchEndX = 0;
  lightbox.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
  lightbox.addEventListener('touchend', e => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      navigate(diff > 0 ? 1 : -1);
    }
  }, { passive: true });

  document.addEventListener('keydown', event => {
    if (!lightbox.classList.contains('active')) return;

    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') navigate(-1);
    if (event.key === 'ArrowRight') navigate(1);

    if (event.key === 'Tab') {
      const controls = focusableControls();
      if (controls.length === 0) return;

      const first = controls[0];
      const last = controls[controls.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });
}

function initTagSearch() {
  const form = document.querySelector('[data-tags-search]');
  const input = document.querySelector('[data-tags-search-input]');
  const grid = document.querySelector('[data-tags-grid]');
  if (!form || !input || !grid) return;

  const cards = Array.from(grid.querySelectorAll('[data-tag-name]')).map(card => ({
    el: card,
    name: (card.dataset.tagName || '').trim().toLocaleLowerCase('tr-TR')
  }));

  if (cards.length === 0) return;

  let debounceTimer;
  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = input.value.trim().toLocaleLowerCase('tr-TR');
      cards.forEach(card => {
        card.el.hidden = query !== '' && !card.name.includes(query);
      });
    }, 150);
  });
}

function initManifestoStrip() {
  const strip = document.querySelector('[data-manifesto-strip]');
  if (!strip) return;

  const items = Array.from(strip.querySelectorAll('[data-manifesto-item]'));
  if (items.length <= 1) return;

  const dots = Array.from(strip.querySelectorAll('[data-manifesto-dot]'));
  const itemsWrap = strip.querySelector('.home-manifesto-strip__items');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const interval = Number.parseInt(strip.dataset.interval || '6500', 10);
  let index = Math.max(0, items.findIndex(item => item.classList.contains('is-active')));
  const initialIndex = nextManifestoIndex(items.length, index);
  let autoTimer;
  let pointerStartX = null;

  function show(nextIndex, persist = true) {
    items[index].classList.remove('is-active');
    if (dots[index]) {
      dots[index].classList.remove('is-active');
      dots[index].setAttribute('aria-current', 'false');
    }

    index = ((nextIndex % items.length) + items.length) % items.length;
    items[index].classList.add('is-active');

    if (dots[index]) {
      dots[index].classList.add('is-active');
      dots[index].setAttribute('aria-current', 'true');
    }

    if (persist) {
      rememberManifestoIndex(index);
    }
  }

  function nextManifestoIndex(count, fallbackIndex) {
    try {
      const storageKey = 'fa_manifesto_strip_index';
      const storedIndex = Number.parseInt(window.sessionStorage.getItem(storageKey) || '', 10);
      const nextIndex = Number.isFinite(storedIndex)
        ? (storedIndex + 1) % count
        : (fallbackIndex + 1) % count;

      window.sessionStorage.setItem(storageKey, String(nextIndex));
      return nextIndex;
    } catch (error) {
      return fallbackIndex;
    }
  }

  function rememberManifestoIndex(nextIndex) {
    try {
      window.sessionStorage.setItem('fa_manifesto_strip_index', String(nextIndex));
    } catch (error) {
      // Session storage can be unavailable; the strip still works without it.
    }
  }

  function startAuto() {
    if (prefersReducedMotion) return;

    window.clearInterval(autoTimer);
    autoTimer = window.setInterval(() => show(index + 1), Number.isFinite(interval) ? interval : 6500);
  }

  function stopAuto() {
    window.clearInterval(autoTimer);
  }

  if (initialIndex !== index) {
    show(initialIndex, false);
  }

  dots.forEach((dot, dotIndex) => {
    dot.addEventListener('click', () => {
      show(dotIndex);
      startAuto();
    });
  });

  if (itemsWrap) {
    itemsWrap.addEventListener('pointerdown', event => {
      if (event.pointerType === 'mouse') return; // Ignore desktop mouse selection drags
      pointerStartX = event.clientX;
    });

    itemsWrap.addEventListener('pointerup', event => {
      if (pointerStartX === null) return;

      const deltaX = event.clientX - pointerStartX;
      pointerStartX = null;

      if (Math.abs(deltaX) < 45) return;

      show(deltaX < 0 ? index + 1 : index - 1);
      startAuto();
    });

    itemsWrap.addEventListener('pointercancel', () => {
      pointerStartX = null;
    });
  }

  strip.addEventListener('mouseenter', stopAuto);
  strip.addEventListener('mouseleave', startAuto);

  startAuto();
}

function initMarquee() {
  const track = document.querySelector('.home-marquee-gallery__track');
  if (!track) return;

  const section = track.closest('.home-marquee-gallery');

  // Pause when not in viewport (pure perf optimization)
  if ('IntersectionObserver' in window && section) {
    const observer = new IntersectionObserver(entries => {
      track.style.animationPlayState = entries[0].isIntersecting ? 'running' : 'paused';
    }, { threshold: 0 });
    observer.observe(section);
  }
}

function setScrollLock(locked) {
  if (locked) {
    const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.paddingRight = `${scrollBarWidth}px`;
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';
  }
}
