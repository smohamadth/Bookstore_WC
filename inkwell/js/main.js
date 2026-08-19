/**
 * Inkwell — main front-end behaviors (vanilla JS, no dependencies).
 */
(function () {
	'use strict';

	// Repeat the early header marker so external scripts still enhance the UI
	// when a strict Content Security Policy blocks the tiny inline marker.
	document.documentElement.classList.add('inkwell-js');

	document.addEventListener('DOMContentLoaded', function () {
		initMenu();
		initLanguageSelectors();
		initShopFilters();
		initSearch();
		initMiniCart();
		initStickyHeader();
		initQuantitySteppers();
		initNewsletter();
		initBackToTop();
		initReveal();
	});

	/* ------------------------------------------------------------------ *
	 * Mini-cart dropdown
	 * ------------------------------------------------------------------ */
	function initMiniCart() {
		var wrap = document.querySelector('[data-cart-wrap]');
		if (!wrap) {
			return;
		}
		var toggle = wrap.querySelector('.header-action');

		toggle.addEventListener('click', function (e) {
			if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
				return;
			}
			e.preventDefault();
			wrap.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', wrap.classList.contains('is-open') ? 'true' : 'false');
		});

		document.addEventListener('click', function (e) {
			if (wrap.classList.contains('is-open') && !wrap.contains(e.target)) {
				wrap.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && wrap.classList.contains('is-open')) {
				wrap.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});

		// WooCommerce dispatches this as a jQuery event on document.body.
		// Keep a native listener too for integrations using CustomEvent.
		function showUpdatedCart() {
			wrap.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
		}
		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', showUpdatedCart);
		}
		document.body.addEventListener('added_to_cart', showUpdatedCart);
	}

	/* ------------------------------------------------------------------ *
	 * Mobile menu
	 * ------------------------------------------------------------------ */
	function initMenu() {
		var toggle = document.querySelector('[data-menu-toggle]');
		var menu = document.getElementById('mobile-menu');
		if (!toggle || !menu) {
			return;
		}

		var iconOpen = toggle.querySelector('[data-icon-open]');
		var iconClose = toggle.querySelector('[data-icon-close]');

		var menuLabel = toggle.querySelector('[data-menu-label]');

		function setOpen(open) {
			menu.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (iconOpen) {
				iconOpen.style.display = open ? 'none' : '';
			}
			if (iconClose) {
				iconClose.style.display = open ? '' : 'none';
			}
			if (menuLabel && typeof inkwellVars !== 'undefined') {
				menuLabel.textContent = open ? inkwellVars.i18n.menuClose : inkwellVars.i18n.menuOpen;
			}
			document.body.classList.toggle('menu-open', open);
		}

		toggle.addEventListener('click', function () {
			setOpen(!menu.classList.contains('is-open'));
		});

		// Close on Escape.
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && menu.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});

		// Close on outside click.
		document.addEventListener('click', function (e) {
			if (menu.classList.contains('is-open') && !menu.contains(e.target) && !toggle.contains(e.target)) {
				setOpen(false);
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * Language selectors
	 * ------------------------------------------------------------------ */
	function initLanguageSelectors() {
		var selectors = Array.prototype.slice.call(document.querySelectorAll('[data-language-selector]'));
		if (!selectors.length) {
			return;
		}

		function setOpen(selector, open, restoreFocus) {
			var toggle = selector.querySelector('[data-language-toggle]');
			selector.classList.toggle('is-open', open);
			if (toggle) {
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (open) {
					var current = selector.querySelector('.language-selector__option.is-current');
					var first = selector.querySelector('.language-selector__option');
					setTimeout(function () {
						(current || first || toggle).focus();
					}, 20);
				} else if (restoreFocus) {
					toggle.focus();
				}
			}
		}

		function closeOthers(active) {
			selectors.forEach(function (selector) {
				if (selector !== active) {
					setOpen(selector, false, false);
				}
			});
		}

		selectors.forEach(function (selector) {
			var toggle = selector.querySelector('[data-language-toggle]');
			if (!toggle) {
				return;
			}
			toggle.addEventListener('click', function (event) {
				event.stopPropagation();
				var open = !selector.classList.contains('is-open');
				closeOthers(selector);
				setOpen(selector, open, false);
			});
		});

		document.addEventListener('click', function (event) {
			selectors.forEach(function (selector) {
				if (selector.classList.contains('is-open') && !selector.contains(event.target)) {
					setOpen(selector, false, false);
				}
			});
		});

		document.addEventListener('keydown', function (event) {
			var activeSelector = event.target.closest ? event.target.closest('[data-language-selector]') : null;
			if (activeSelector && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
				var options = Array.prototype.slice.call(activeSelector.querySelectorAll('.language-selector__option'));
				var index = options.indexOf(document.activeElement);
				if (options.length) {
					event.preventDefault();
					index =
						event.key === 'ArrowDown' ? (index + 1) % options.length : (index - 1 + options.length) % options.length;
					options[index].focus();
				}
				return;
			}
			if (event.key === 'Escape') {
				selectors.forEach(function (selector) {
					if (selector.classList.contains('is-open')) {
						setOpen(selector, false, true);
					}
				});
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * Mobile shop filter drawer
	 * ------------------------------------------------------------------ */
	function initShopFilters() {
		var toggle = document.querySelector('[data-shop-filter-toggle]');
		var sidebar = document.querySelector('.shop-layout #secondary');
		var closeButtons = document.querySelectorAll('[data-shop-filter-close]');
		if (!toggle || !sidebar) {
			return;
		}

		function setOpen(open, restoreFocus) {
			document.body.classList.toggle('shop-filters-open', open);
			sidebar.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				var close = sidebar.querySelector('[data-shop-filter-close]');
				if (close) {
					setTimeout(function () {
						close.focus();
					}, 30);
				}
			} else if (restoreFocus) {
				toggle.focus();
			}
		}

		toggle.addEventListener('click', function () {
			setOpen(true, false);
		});
		Array.prototype.forEach.call(closeButtons, function (button) {
			button.addEventListener('click', function () {
				setOpen(false, true);
			});
		});
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
				setOpen(false, true);
				return;
			}
			if (event.key === 'Tab' && sidebar.classList.contains('is-open')) {
				var focusable = Array.prototype.slice.call(
					sidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled])'),
				);
				if (!focusable.length) {
					return;
				}
				var first = focusable[0];
				var last = focusable[focusable.length - 1];
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});
		window.addEventListener('resize', function () {
			if (window.innerWidth > 900 && sidebar.classList.contains('is-open')) {
				setOpen(false, false);
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * Header search panel
	 * ------------------------------------------------------------------ */
	function initSearch() {
		var btn = document.querySelector('[data-search-toggle]');
		var panel = document.getElementById('search-panel');
		if (!btn || !panel) {
			return;
		}

		btn.addEventListener('click', function () {
			var open = !panel.classList.contains('is-open');
			panel.classList.toggle('is-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (open) {
				var input = panel.querySelector('input[type="search"]');
				if (input) {
					setTimeout(function () {
						input.focus();
					}, 60);
				}
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && panel.classList.contains('is-open')) {
				panel.classList.remove('is-open');
				btn.setAttribute('aria-expanded', 'false');
				btn.focus();
			}
		});
		document.addEventListener('click', function (e) {
			if (panel.classList.contains('is-open') && !panel.contains(e.target) && !btn.contains(e.target)) {
				panel.classList.remove('is-open');
				btn.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* ------------------------------------------------------------------ *
	 * Sticky header shadow
	 * ------------------------------------------------------------------ */
	function initStickyHeader() {
		var header = document.querySelector('[data-header]');
		if (!header) {
			return;
		}
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ------------------------------------------------------------------ *
	 * Quantity steppers (single product, cart, mini-cart)
	 * ------------------------------------------------------------------ */
	function initQuantitySteppers() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-qty-step]');
			if (!btn) {
				return;
			}
			var wrap = btn.closest('[data-inkwell-qty]');
			var input = wrap ? wrap.querySelector('input.qty, input[type="number"], input[name*="quantity"]') : null;
			if (!input || input.readOnly || input.disabled || btn.disabled) {
				return;
			}

			var step = parseFloat(btn.getAttribute('data-qty-step')) || 1;
			var min = input.hasAttribute('min') ? parseFloat(input.min) : 1;
			var max = input.hasAttribute('max') ? parseFloat(input.max) : Infinity;
			if (isNaN(min)) {
				min = 1;
			}
			if (isNaN(max)) {
				max = Infinity;
			}

			var current = parseFloat(input.value);
			if (isNaN(current)) {
				current = isFinite(min) ? min : 1;
			}
			var next = Math.min(Math.max(current + step, min), max);
			input.value = next;
			input.dispatchEvent(new Event('change', { bubbles: true }));
			input.dispatchEvent(new Event('input', { bubbles: true }));
		});
	}

	/* ------------------------------------------------------------------ *
	 * Newsletter (AJAX; falls back to admin-post)
	 * ------------------------------------------------------------------ */
	function initNewsletter() {
		var forms = document.querySelectorAll('form[data-inkwell-newsletter]');
		if (!forms.length || typeof inkwellVars === 'undefined') {
			return;
		}

		Array.prototype.forEach.call(forms, function (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();

				var email = form.querySelector('input[name="email"]');
				var note = form.querySelector('.newsletter-note');
				var btn = form.querySelector('button[type="submit"]');
				var honeypot = form.querySelector('input[name="website"]');
				var consent = form.querySelector('input[name="consent"]');

				// Honeypot: pretend success.
				if (honeypot && honeypot.value) {
					showNote(note, inkwellVars.i18n.subscribed, 'is-ok');
					form.reset();
					return;
				}

				if (!email || !email.value || email.value.indexOf('@') === -1) {
					showNote(note, inkwellVars.i18n.invalidEmail, 'is-error');
					return;
				}
				if (!consent || !consent.checked) {
					showNote(note, inkwellVars.i18n.consent, 'is-error');
					return;
				}

				var data = new FormData();
				data.append('action', 'inkwell_newsletter');
				data.append('nonce', inkwellVars.newsletter);
				data.append('email', email.value.trim());
				data.append('consent', '1');
				data.append('website', honeypot ? honeypot.value : '');

				if (btn) {
					btn.disabled = true;
				}

				fetch(inkwellVars.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: data,
				})
					.then(function (res) {
						return res.json();
					})
					.then(function (json) {
						if (json && json.success) {
							showNote(note, json.data.message || inkwellVars.i18n.subscribed, 'is-ok');
							form.reset();
						} else {
							showNote(note, (json && json.data && json.data.message) || inkwellVars.i18n.error, 'is-error');
						}
					})
					.catch(function () {
						showNote(note, inkwellVars.i18n.error, 'is-error');
					})
					.finally(function () {
						if (btn) {
							btn.disabled = false;
						}
					});
			});
		});

		function showNote(note, message, cls) {
			if (!note) {
				return;
			}
			note.textContent = message;
			note.classList.remove('is-ok', 'is-error');
			note.classList.add(cls);
		}
	}

	/* ------------------------------------------------------------------ *
	 * Back to top
	 * ------------------------------------------------------------------ */
	function initBackToTop() {
		var btn = document.querySelector('[data-back-to-top]');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function () {
			var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
		});
		var onScroll = function () {
			btn.classList.toggle('is-visible', window.scrollY > 600);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* ------------------------------------------------------------------ *
	 * Reveal on scroll
	 * ------------------------------------------------------------------ */
	function initReveal() {
		var items = document.querySelectorAll('[data-reveal]');
		if (!items.length) {
			return;
		}
		if (!('IntersectionObserver' in window)) {
			Array.prototype.forEach.call(items, function (el) {
				el.classList.add('in-view');
			});
			return;
		}
		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('in-view');
						observer.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
		);
		Array.prototype.forEach.call(items, function (el) {
			observer.observe(el);
		});
	}
})();
