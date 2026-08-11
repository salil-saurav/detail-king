/* =====================================================================
   home.js — homepage behaviour.

   Dependency-free, matching the rest of the theme (no jQuery on the front end;
   DebloaterService removes it outside shop pages).

     - before/after comparison slider, with the comp's one-time tease
     - animated stat counters
     - horizontal rails (arrows + autoplay)
     - accordion single-open behaviour

   The accordion uses <details>, so it already works with JS disabled; this only
   adds the "close the others" behaviour and the height transition.
   ===================================================================== */
(() => {
	'use strict';

	const reduceMotion = window.matchMedia
		&& window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ----------------------------------------------------------------
	   Before / after comparison.

	   The <input type="range"> IS the control — dragging, arrow keys and
	   screen-reader semantics all come free. This only mirrors its value onto a
	   CSS custom property and swaps the images when a preset is chosen.
	   ---------------------------------------------------------------- */
	const initCompare = () => {
		document.querySelectorAll('[data-dk-compare]').forEach((root) => {
			const range = root.querySelector('.dk-compare__range');
			if (!range) {
				return;
			}

			const apply = (value) => root.style.setProperty('--dk-compare-pos', `${value}%`);

			range.addEventListener('input', () => apply(range.value));
			apply(range.value);

			/* Comp note: "Auto-teases left-right once on view." Runs once, only
			   when the widget is actually seen, and never under reduced-motion. */
			if (!reduceMotion && 'IntersectionObserver' in window) {
				const tease = () => {
					const from = parseFloat(range.value);
					const seq = [from, from - 14, from + 10, from];
					seq.forEach((pos, i) => {
						setTimeout(() => {
							range.value = String(pos);
							apply(pos);
						}, i * 420);
					});
				};

				const obs = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							obs.unobserve(entry.target);
							setTimeout(tease, 350);
						}
					});
				}, { threshold: 0.45 });

				obs.observe(root);
			}

			/* Preset pills swap the pair. */
			const section = root.closest('section');
			if (!section) {
				return;
			}

			const before = root.querySelector('.dk-compare__img--before');
			const after = root.querySelector('.dk-compare__img--after');

			section.querySelectorAll('[data-ba-preset]').forEach((btn) => {
				btn.addEventListener('click', () => {
					section.querySelectorAll('[data-ba-preset]').forEach((other) => {
						other.classList.toggle('is-active', other === btn);
						other.setAttribute('aria-selected', other === btn ? 'true' : 'false');
					});

					if (before && btn.dataset.before) {
						before.src = btn.dataset.before;
					}
					if (after && btn.dataset.after) {
						after.src = btn.dataset.after;
					}
					root.classList.toggle('dk-compare--faux', btn.dataset.faux === '1');
				});
			});
		});
	};

	/* ----------------------------------------------------------------
	   Stat counters.

	   The values are strings like "4,800+", "5.0★", "98%" and "2016" — not plain
	   integers. So the numeric part is animated and the surrounding characters are
	   preserved verbatim, rather than parsing to a number and rebuilding a format
	   that would drop the suffix.
	   ---------------------------------------------------------------- */
	const initCounters = () => {
		const targets = document.querySelectorAll('[data-count-to]');
		if (!targets.length || !('IntersectionObserver' in window)) {
			return;
		}

		const animate = (el) => {
			const raw = el.getAttribute('data-count-to') || '';
			const match = raw.match(/^(\D*)([\d.,]+)(.*)$/);
			if (!match) {
				return;
			}

			const [, prefix, numStr, suffix] = match;
			const hasComma = numStr.includes(',');
			const decimals = (numStr.split('.')[1] || '').length;
			const target = parseFloat(numStr.replace(/,/g, ''));

			if (!isFinite(target)) {
				return;
			}

			if (reduceMotion) {
				el.textContent = raw;
				return;
			}

			const duration = 1400;
			const start = performance.now();

			const format = (value) => {
				let out = value.toFixed(decimals);
				if (hasComma) {
					const [int, dec] = out.split('.');
					out = int.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (dec ? `.${dec}` : '');
				}
				return prefix + out + suffix;
			};

			const step = (now) => {
				const t = Math.min(1, (now - start) / duration);
				// easeOutCubic — fast start, settles rather than stopping dead.
				const eased = 1 - Math.pow(1 - t, 3);
				el.textContent = format(target * eased);
				if (t < 1) {
					requestAnimationFrame(step);
				} else {
					el.textContent = raw;
				}
			};

			requestAnimationFrame(step);
		};

		const obs = new IntersectionObserver((entries, o) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					o.unobserve(entry.target);
					animate(entry.target);
				}
			});
		}, { threshold: 0.6 });

		targets.forEach((el) => obs.observe(el));
	};

	/* ----------------------------------------------------------------
	   Horizontal rails.

	   Scrolling itself is CSS (overflow + scroll-snap), so this only wires the
	   arrows, disables them at the ends, and runs the reviews autoplay.
	   ---------------------------------------------------------------- */
	const initRails = () => {
		document.querySelectorAll('[data-dk-rail]').forEach((rail) => {
			const track = rail.querySelector('.dk-rail__track');
			if (!track) {
				return;
			}

			const section = rail.closest('section') || rail.parentElement;
			const prev = section ? section.querySelector('[data-dk-rail-prev]') : null;
			const next = section ? section.querySelector('[data-dk-rail-next]') : null;

			const stride = () => {
				const item = track.querySelector('.dk-rail__item');
				if (!item) {
					return track.clientWidth;
				}
				const gap = parseFloat(getComputedStyle(track).columnGap || '24') || 24;
				return item.getBoundingClientRect().width + gap;
			};

			const atEnd = () => track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;

			const sync = () => {
				if (prev) {
					prev.disabled = track.scrollLeft <= 2;
				}
				if (next) {
					next.disabled = atEnd();
				}
			};

			if (prev) {
				prev.addEventListener('click', () => track.scrollBy({ left: -stride(), behavior: 'smooth' }));
			}
			if (next) {
				next.addEventListener('click', () => track.scrollBy({ left: stride(), behavior: 'smooth' }));
			}

			track.addEventListener('scroll', sync, { passive: true });
			window.addEventListener('resize', sync);
			sync();

			/* Autoplay, where asked for. Pauses on hover and focus, and stops for
			   good on any manual interaction — an auto-advancing carousel that
			   fights the reader is worse than none. */
			const interval = parseInt(rail.dataset.dkAutoplay || '0', 10);
			if (!interval || reduceMotion) {
				return;
			}

			let timer = null;
			let stopped = false;

			const tick = () => {
				if (stopped) {
					return;
				}
				if (atEnd()) {
					track.scrollTo({ left: 0, behavior: 'smooth' });
				} else {
					track.scrollBy({ left: stride(), behavior: 'smooth' });
				}
			};

			const start = () => {
				if (!stopped && timer === null) {
					timer = setInterval(tick, interval);
				}
			};
			const pause = () => {
				if (timer !== null) {
					clearInterval(timer);
					timer = null;
				}
			};
			const stop = () => {
				stopped = true;
				pause();
			};

			rail.addEventListener('mouseenter', pause);
			rail.addEventListener('mouseleave', start);
			rail.addEventListener('focusin', pause);
			rail.addEventListener('pointerdown', stop);
			[prev, next].forEach((btn) => btn && btn.addEventListener('click', stop));

			start();
		});
	};

	/* ----------------------------------------------------------------
	   Accordion: keep one item open.
	   ---------------------------------------------------------------- */
	const initAccordion = () => {
		document.querySelectorAll('[data-dk-accordion]').forEach((group) => {
			const items = [...group.querySelectorAll('details')];

			items.forEach((item) => {
				item.addEventListener('toggle', () => {
					if (!item.open) {
						return;
					}
					items.forEach((other) => {
						if (other !== item) {
							other.open = false;
						}
					});
				});
			});
		});
	};

	const init = () => {
		initCompare();
		initCounters();
		initRails();
		initAccordion();
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
