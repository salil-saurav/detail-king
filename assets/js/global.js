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

	/* ----------------------------------------------------------
		Scroll-reveal ([data-animate]) lives in motion.js now.

		It used to be an IntersectionObserver here, then GSAP + ScrollTrigger,
		and is now motion.js's own observer + CSS transitions (global.css §7).
		Two engines writing opacity/transform on the same nodes fight, so this
		one stays removed rather than being restored alongside it. `aos-ready` is
		added by motion.js only once it knows it can animate.

		If you are here because a reveal is stuck: the observer uses
		`threshold: 0` deliberately. This file's old version wanted 12% of the
		target's own height, which a section several viewports tall can never
		reach — that bug is why the house rule puts `data-animate` on the
		repeating item and not on the grid that wraps it.
		---------------------------------------------------------- */

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

		/* 1900, not the earlier 1600, and the ease below is not a guess: the
		   reference recording's craft stats were sampled every 0.25s while they
		   ran (t 20.4-22.3) and read 1461, 2810, 3639, 4289, 4592, 4749, 4795,
		   4799 against a 4799 target. Inverting ease-out cubic on those gives an
		   even ~0.131 of progress per 0.25s — i.e. exactly this curve over 1.9s. */
		const duration = 1900;

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
		Category filter pills ([data-dk-filter-group]).
		Each pill inside carries data-dk-filter="<slug>"; the group names the
		card container to act on via data-dk-filter-target="<selector>". Cards
		carry data-dk-filter-cats="<space-separated slugs>" — "all" means
		everything.

		Two modes, chosen by the group's data-dk-filter-mode:

		  "hide"   (default) — show/hide the non-matching cards. What Gallery
		                       wants: a wall of items, narrowed to one category.
		  "scroll"           — leave every card visible and scroll to the
		                       matching one. What Our Services wants, per the
		                       client brief: "When someone clicks on a service
		                       name, it will take them to that service section on
		                       the same page." See TASK-BRIEF.md §1.5.

		The mode is a data attribute rather than two components because the pill
		row, its markup and its active-state bookkeeping are identical — only
		what a click does differs.
		---------------------------------------------------------- */
	const initFilterTabs = () => {
		document.querySelectorAll('[data-dk-filter-group]').forEach((group) => {
			const targetSelector = group.getAttribute('data-dk-filter-target');
			const target = targetSelector ? document.querySelector(targetSelector) : null;
			const pills = group.querySelectorAll('[data-dk-filter]');
			if (!target || !pills.length) {
				return;
			}

			const isScrollMode = group.getAttribute('data-dk-filter-mode') === 'scroll';
			const cards = target.querySelectorAll('[data-dk-filter-cats]');

			const matches = (card, slug) =>
				card.getAttribute('data-dk-filter-cats').split(/\s+/).includes(slug);

			const applyFilter = (slug) => {
				cards.forEach((card) => {
					card.classList.toggle('is-hidden', slug !== 'all' && !matches(card, slug));
				});
			};

			/* "all" goes back to the top of the grid; anything else jumps to the
			   first card carrying that category. Vertical offset for the fixed nav
			   comes from `scroll-padding-block-start` on <html> (global.css), so it
			   is not duplicated here. */
			const scrollToCategory = (slug) => {
				const destination = slug === 'all'
					? target
					: Array.from(cards).find((card) => matches(card, slug));

				if (!destination) {
					return;
				}

				destination.scrollIntoView({
					behavior: prefersReducedMotion ? 'auto' : 'smooth',
					block: 'start',
				});
			};

			pills.forEach((pill) => {
				pill.addEventListener('click', () => {
					const slug = pill.getAttribute('data-dk-filter');
					pills.forEach((p) => {
						p.classList.toggle('is-active', p === pill);
						p.setAttribute('aria-pressed', p === pill ? 'true' : 'false');
					});

					if (isScrollMode) {
						scrollToCategory(slug);
					} else {
						applyFilter(slug);
					}
				});
			});
		});
	};

	/* ----------------------------------------------------------
		Sticky pill nav.
		The comp only ever draws the nav over the hero, but the client's own
		animation reference (animation.mp4, analysed in TASK-BRIEF.md §3) keeps
		the pill docked at the top of the viewport for the whole scroll. `.dk-nav`
		is therefore `position: fixed` (it was already out of flow, so nothing
		reflows); this only adds the `.is-stuck` state once the hero's own 22px
		offset has been scrolled past, which tightens the offset and deepens the
		shadow so the pill reads as docked rather than floating.

		The `--solid` variant (templates with no dark hero) is `position: sticky`
		in CSS instead — it is in flow, so sticky needs no padding compensation.
		---------------------------------------------------------- */
	const initStickyNav = () => {
		const nav = document.querySelector('.dk-nav');
		if (!nav) {
			return;
		}

		/* Publish the nav's real height so `scroll-padding-block-start` (global.css)
		   can clear it at every width. A hard-coded offset does not survive the
		   ≤991px breakpoint, where the pill wraps to ~124px and an anchor target
		   lands *underneath* the nav — measured at 375px, the card sat 6px under it.

		   Recomputed on resize only. Not on scroll frames (it would be layout
		   thrash for a value that cannot change), and deliberately *not* when the
		   mobile drawer opens: an open drawer is several hundred px tall, and
		   feeding that into the scroll offset would throw every anchor jump far
		   past its target. */
		const syncHeight = () => {
			document.documentElement.style.setProperty(
				'--dk-nav-height',
				`${Math.round(nav.getBoundingClientRect().height)}px`
			);
		};

		let queued = false;

		const sync = () => {
			queued = false;
			nav.classList.toggle('is-stuck', window.scrollY > 40);
		};

		window.addEventListener('scroll', () => {
			if (!queued) {
				queued = true;
				window.requestAnimationFrame(sync);
			}
		}, { passive: true });

		window.addEventListener('resize', syncHeight, { passive: true });

		syncHeight();
		sync();
	};

	/* ----------------------------------------------------------
		Product card save/wishlist ([data-dk-wishlist]).
		Visual-only, localStorage-backed — there is no account-linked wishlist
		feature in the brief (see figma-data/shop-spec.md, "open items"). Lives
		here rather than in a Woo-conditional script because the same card
		(and button) also renders on the homepage shop rail, which loads no
		shop-specific JS.
		---------------------------------------------------------- */
	const WISHLIST_KEY = 'dk_wishlist';

	const readWishlist = () => {
		try {
			const raw = window.localStorage.getItem(WISHLIST_KEY);
			return raw ? JSON.parse(raw) : [];
		} catch (e) {
			return [];
		}
	};

	const initWishlist = () => {
		const buttons = document.querySelectorAll('[data-dk-wishlist]');
		if (!buttons.length) return;

		let saved = readWishlist();

		const paint = (btn) => {
			const id = btn.dataset.productId;
			btn.setAttribute('aria-pressed', saved.includes(id) ? 'true' : 'false');
		};

		buttons.forEach(paint);

		document.addEventListener('click', (e) => {
			const btn = e.target.closest('[data-dk-wishlist]');
			if (!btn) return;

			const id = btn.dataset.productId;
			saved = saved.includes(id) ? saved.filter((x) => x !== id) : [...saved, id];

			try {
				window.localStorage.setItem(WISHLIST_KEY, JSON.stringify(saved));
			} catch (e) { /* storage unavailable — state just won't persist */ }

			document.querySelectorAll(`[data-dk-wishlist][data-product-id="${id}"]`).forEach(paint);
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
		initStatCounters();
		initNavToggle();
		initStickyNav();
		initFilterTabs();
		initHeaderSearch();
		initWishlist();
	});
})();
