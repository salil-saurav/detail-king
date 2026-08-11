/* =====================================================================
   StackPress — global.js
   Lightweight, dependency-free front-end behaviour:
     - scroll-reveal animations ([data-animate])
     - mobile navigation toggle ([data-sp-toggle])
     - header live search overlay (AJAX, via SearchService)
   Bootstrap's bundle handles dropdowns, collapse, etc.
   ===================================================================== */
(() => {
	'use strict';

	const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (!prefersReducedMotion) {
		document.documentElement.classList.add('aos-ready');
	}

	/* ----------------------------------------------------------
		Scroll-reveal animations.
		Add `data-animate` to any element to reveal it as it enters
		the viewport. Variants: data-animate="fade-left|fade-right|zoom|fade".
		---------------------------------------------------------- */
	const initScrollAnimations = () => {
		if (prefersReducedMotion) {
			return;
		}

		/* Scan the whole document, not just #site-container.

		   The rule that hides these (`.aos-ready [data-animate]` in global.css)
		   is document-wide, so scoping the observer to <main> meant anything
		   outside it — the header, and the footer, which footer.php renders after
		   </main> — was hidden by the stylesheet and then never observed. It
		   stayed at opacity 0 permanently. Keeping the two selectors in agreement
		   is the fix; the narrower observer is a trap for the next person. */
		const animated = document.querySelectorAll('[data-animate]');
		if (!animated.length) {
			return;
		}

		const reveal = (el) => el.classList.add('is-visible');

		if (!('IntersectionObserver' in window)) {
			animated.forEach(reveal);
			return;
		}

		const observer = new IntersectionObserver((entries, obs) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					reveal(entry.target);
					obs.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

		animated.forEach((el) => observer.observe(el));
	};

	/* ----------------------------------------------------------
		Stat counters ([data-count-to]).
		Every `.dk-stats__value` carries this attribute (hero stats, the
		homepage's craft stats, About's stats) but nothing ever read it —
		count-up was named as an interaction in the comp's own layer name
		("Counters animate count-up when scrolled into view") and never wired
		up. Parses the server-rendered display string itself (e.g. "4,134+",
		"2HR", "4.0★") into a numeric target plus whatever non-numeric prefix/
		suffix it carries, animates 0 → target, and re-applies the exact same
		formatting so the final frame is pixel-identical to the static markup.
		---------------------------------------------------------- */
	const initStatCounters = () => {
		const items = document.querySelectorAll('[data-count-to]');
		if (!items.length || prefersReducedMotion || !('IntersectionObserver' in window)) {
			return; // leave the server-rendered static value in place
		}

		const parse = (raw) => {
			const match = raw.match(/^([\d,]*\.?\d+)(.*)$/);
			if (!match) {
				return null;
			}
			const numberPart = match[1];
			return {
				target: parseFloat(numberPart.replace(/,/g, '')),
				decimals: numberPart.includes('.') ? numberPart.split('.')[1].length : 0,
				useGrouping: numberPart.includes(','),
				suffix: match[2],
			};
		};

		const format = (value, { decimals, useGrouping, suffix }) => {
			const fixed = value.toFixed(decimals);
			const body = useGrouping
				? Number(fixed).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
				: fixed;
			return body + suffix;
		};

		const duration = 1600;

		const animate = (el, parsed) => {
			let start = null;

			const step = (timestamp) => {
				if (start === null) {
					start = timestamp;
				}
				const progress = Math.min((timestamp - start) / duration, 1);
				const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
				el.textContent = format(parsed.target * eased, parsed);
				if (progress < 1) {
					requestAnimationFrame(step);
				}
			};
			requestAnimationFrame(step);
		};

		const observer = new IntersectionObserver((entries, obs) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}
				obs.unobserve(entry.target);

				const parsed = parse(entry.target.getAttribute('data-count-to').trim());
				if (!parsed) {
					return; // not a countable value — leave the static text alone
				}
				entry.target.textContent = format(0, parsed);
				animate(entry.target, parsed);
			});
		}, { threshold: 0.4 });

		items.forEach((el) => observer.observe(el));
	};

	/* ----------------------------------------------------------
		Mobile navigation toggle.
		Any [data-sp-toggle="<id>"] toggles `.is-open` on #<id>.
		---------------------------------------------------------- */
	const initNavToggle = () => {
		document.querySelectorAll('[data-sp-toggle]').forEach((toggler) => {
			const target = document.getElementById(toggler.getAttribute('data-sp-toggle'));
			if (!target) {
				return;
			}
			toggler.addEventListener('click', () => {
				const isOpen = target.classList.toggle('is-open');
				toggler.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		});
	};

	/* ----------------------------------------------------------
		Header live search overlay.
		Queries the `detailking_live_search` AJAX endpoint (debounced)
		and renders results live. Config is localized as window.detailkingSearch.
		---------------------------------------------------------- */
	const debounce = (fn, wait) => {
		let timer;
		return (...args) => {
			clearTimeout(timer);
			timer = setTimeout(() => fn.apply(null, args), wait);
		};
	};

	const initHeaderSearch = () => {
		const toggle  = document.getElementById('header-search-toggle');
		const overlay = document.getElementById('header-search-overlay');
		if (!toggle || !overlay) {
			return;
		}

		const panel    = overlay.querySelector('#header-search-panel');
		const input    = overlay.querySelector('[data-search-input]');
		const results  = overlay.querySelector('[data-search-results]');
		const form     = overlay.querySelector('[data-search-form]');
		const closeBtn = overlay.querySelector('[data-search-close]');
		const cfg      = window.detailkingSearch || {};
		const i18n     = cfg.i18n || {};

		let isOpen = false;
		let activeController = null;

		const open = () => {
			if (isOpen) return;
			isOpen = true;
			overlay.hidden = false;
			void overlay.offsetWidth;
			overlay.classList.add('is-open');
			document.body.classList.add('search-open');
			toggle.setAttribute('aria-expanded', 'true');
			if (input) input.focus();
		};

		const close = () => {
			if (!isOpen) return;
			isOpen = false;
			overlay.classList.remove('is-open');
			document.body.classList.remove('search-open');
			toggle.setAttribute('aria-expanded', 'false');
			if (activeController) {
				activeController.abort();
				activeController = null;
			}
			overlay.hidden = true;
		};

		const escapeHtml = (str) => String(str).replace(/[&<>"']/g, (c) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
		}[c]));

		const renderState = (markup) => {
			results.innerHTML = markup;
			results.classList.toggle('is-active', markup !== '');
		};

		const renderResults = (data) => {
			const items = (data && data.results) || [];
			if (!items.length) {
				renderState(`<p class="search-results__empty">${escapeHtml(i18n.empty || 'No results found.')}</p>`);
				return;
			}

			const list = items.map((item) => {
				const type = item.type ? `<span class="search-result__type">${escapeHtml(item.type)}</span>` : '';
				return `<a class="search-result d-block" href="${escapeHtml(item.url)}">
						<span class="search-result__title">${escapeHtml(item.title)}</span>
						${type}
					</a>`;
			}).join('');

			const viewAll = data.viewAll
				? `<a class="search-results__all" href="${escapeHtml(data.viewAll)}">${escapeHtml(i18n.viewAll || 'View all results')} &rarr;</a>`
				: '';

			renderState(`<div class="search-results__list">${list}</div>${viewAll}`);
		};

		const runSearch = (term) => {
			if (!cfg.ajaxUrl) return;
			if (activeController) activeController.abort();
			activeController = new AbortController();

			const action = cfg.action || 'detailking_live_search';
			const url = `${cfg.ajaxUrl}?action=${encodeURIComponent(action)}&nonce=${encodeURIComponent(cfg.nonce || '')}&q=${encodeURIComponent(term)}`;

			fetch(url, { signal: activeController.signal, credentials: 'same-origin' })
				.then((res) => res.json())
				.then((json) => {
					if (json && json.success) {
						renderResults(json.data);
					}
				})
				.catch((err) => {
					if (err.name !== 'AbortError') {
						renderState('');
					}
				});
		};

		const debouncedSearch = debounce((term) => runSearch(term), 300);

		toggle.addEventListener('click', () => (isOpen ? close() : open()));
		if (closeBtn) closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', (e) => {
			if (panel && !panel.contains(e.target)) close();
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && isOpen) close();
		});

		if (input) {
			input.addEventListener('input', () => {
				const term = input.value.trim();
				if (term.length < 2) {
					if (activeController) {
						activeController.abort();
						activeController = null;
					}
					renderState(term.length === 0 ? '' : `<p class="search-results__hint">${escapeHtml(i18n.minChars || 'Type at least 2 characters…')}</p>`);
					return;
				}
				renderState(`<p class="search-results__hint">${escapeHtml(i18n.searching || 'Searching…')}</p>`);
				debouncedSearch(term);
			});
		}

		if (form) {
			form.addEventListener('submit', () => close());
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		initScrollAnimations();
		initStatCounters();
		initNavToggle();
		initHeaderSearch();
	});
})();
