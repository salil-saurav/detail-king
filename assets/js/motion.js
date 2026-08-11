/* =====================================================================
   Detail King — motion.js

   Implements ~/DWS/projects/detail-king/animation-implementation-spec.md.
   Section numbers in comments refer to that document.

   This file is the *single* reveal engine. global.js used to run its own
   IntersectionObserver over [data-animate]; that was removed when this landed,
   because two systems writing opacity/transform on the same elements fight.
   The `[data-animate]` attribute contract in the templates is unchanged — this
   just drives it with GSAP instead, which is what buys scrub-linked parallax
   and a real timeline for the hero.

   Load order matters and is declared in AssetsService: gsap, ScrollTrigger,
   lenis, then this file. Everything is deferred.

   Accessibility (§33): under prefers-reduced-motion this file does almost
   nothing — no Lenis, no ScrollTrigger, no transforms. It never adds the
   `aos-ready` class, so the CSS that hides [data-animate] never applies and all
   content is visible and functional. That is also the no-JS / GSAP-failed-to-
   load path, which is why the hiding rule is class-gated rather than plain.
   ===================================================================== */
(() => {
	'use strict';

	const reduced = window.matchMedia
		&& window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Bail before touching anything if the libraries are missing or motion is
	// unwanted. Content stays visible because `aos-ready` is never added.
	if (reduced || !window.gsap || !window.ScrollTrigger) {
		return;
	}

	const gsap = window.gsap;
	const ScrollTrigger = window.ScrollTrigger;
	gsap.registerPlugin(ScrollTrigger);

	// Hide [data-animate] only now that we know we can animate it again.
	document.documentElement.classList.add('aos-ready');

	/* §23 Responsive motion. The spec asks for smaller travel on small screens
	   (y 15-25px instead of 50) — large translations on a narrow viewport read as
	   jitter, not elegance. Read once; ScrollTrigger.refresh() on resize handles
	   layout, and re-reading distances mid-session would fight in-flight tweens. */
	const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
	const D = {
		y: isMobile ? 20 : 40,        // §28 generic reveal distance
		x: isMobile ? 15 : 30,        // §12 directional reveal distance
		parallax: isMobile ? 4 : 8,   // §30 yPercent, "discovered rather than obvious"
	};

	/* ------------------------------------------------------------------
	   §4 Smooth inertial scrolling.
	   Lenis drives the scroll; ScrollTrigger must be told about every frame or
	   scrub-linked animations lag behind the content. Driving Lenis from GSAP's
	   own ticker (rather than its internal rAF) keeps both on one clock, which
	   is what stops parallax from jittering against the page.
	   ------------------------------------------------------------------ */
	let lenis = null;

	if (window.Lenis) {
		lenis = new window.Lenis({
			duration: 1.1,
			easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
			smoothWheel: true,
			// Native momentum on touch is better than anything we can emulate,
			// and hijacking it breaks the address-bar collapse on mobile Safari.
			smoothTouch: false,
		});

		lenis.on('scroll', ScrollTrigger.update);
		gsap.ticker.add((time) => lenis.raf(time * 1000));
		gsap.ticker.lagSmoothing(0);

		// The pill nav's sticky state and the filter jump links both read
		// window.scrollY / call scrollIntoView, which Lenis proxies natively, so
		// global.js needs no changes. Expose it anyway for later sections that
		// may want lenis.scrollTo with an offset.
		window.dkLenis = lenis;
	}

	/* ------------------------------------------------------------------
	   §28/§29 The reveal system.
	   One batch over [data-animate]. ScrollTrigger.batch groups the elements
	   that cross the trigger line together and hands them over as one array —
	   which is exactly the set that should stagger (§8, §14, §15: 100ms).
	   Per-element initial state comes from the variant; the target is always the
	   neutral state, so one tween can finish every variant at once using GSAP's
	   function-based values for the properties that differ.
	   ------------------------------------------------------------------ */
	const variantOf = (el) => el.getAttribute('data-animate') || '';

	const initialFor = (el) => {
		switch (variantOf(el)) {
			case 'fade':        return { opacity: 0 };                       // opacity only
			case 'fade-left':   return { opacity: 0, x: -D.x };              // §12 left column
			case 'fade-right':  return { opacity: 0, x: D.x };               // §12 right column
			case 'zoom':        return { opacity: 0, scale: 1.08 };          // §29 image reveal
			case 'zoom-in':     return { opacity: 0, scale: 0.8 };           // §12 centre image
			default:            return { opacity: 0, y: D.y };               // §28 default
		}
	};

	/* §3 durations: images are slower than UI (1.0-1.5s vs 0.6-0.8s).
	   Signature is (index, target) because GSAP invokes function-based values as
	   (i, target, targets) — taking the element as the first argument silently
	   hands you the index instead, and every reveal then throws inside the tween. */
	const durationFor = (i, el) => {
		const v = variantOf(el);
		if (v === 'zoom')    return 1.1;
		if (v === 'zoom-in') return 1.0;
		return 0.7;
	};

	const animated = gsap.utils.toArray('[data-animate]');
	const START_RATIO = 0.88;               // matches `start: 'top 88%'` below

	const revealTween = (targets) => gsap.to(targets, {
		opacity: 1,
		y: 0,
		x: 0,
		scale: 1,
		duration: durationFor,          // function-based: per-element
		ease: 'power3.out',             // §3 preferred easing
		stagger: 0.1,                   // §28 group stagger
		overwrite: 'auto',
	});

	/* Split by whether the element is ALREADY past the trigger line at load.

	   ScrollTrigger fires onEnter on the *transition* into the active state, not
	   for triggers that are already active when they are created. Everything
	   above the fold is already active at start (measured: the hero's trigger
	   reported start -950, isActive true) — so a plain batch leaves the entire
	   first screen stranded at opacity 0 forever. Ask me how I know.

	   Measured before the initial gsap.set, because setting `y: 40` would shift
	   getBoundingClientRect() and skew the very comparison being made. */
	const onscreen = [];
	const offscreen = [];
	const line = window.innerHeight * START_RATIO;

	animated.forEach((el) => {
		(el.getBoundingClientRect().top < line ? onscreen : offscreen).push(el);
	});

	animated.forEach((el) => gsap.set(el, initialFor(el)));

	// Above the fold: play immediately, still staggered.
	if (onscreen.length) {
		revealTween(onscreen);
	}

	// Everything else reveals as it scrolls in, batched so siblings stagger.
	if (offscreen.length) {
		ScrollTrigger.batch(offscreen, {
			start: `top ${START_RATIO * 100}%`,
			once: true,
			onEnter: revealTween,
		});
	}

	/* ------------------------------------------------------------------
	   §6 Hero. The one place the spec asks for a real sequence rather than a
	   single reveal: backdrop settles from 1.05, then heading, supporting text,
	   and the CTAs with a stagger between them. The section carries `data-hero`
	   instead of `data-animate` precisely so the generic batch above leaves it
	   alone and cannot double-animate these nodes.

	   Stats are not in this timeline — they are numbers, and global.js's
	   count-up observer (§11: once, 1.6s) already owns them. Chaining them here
	   would run the count before the section is on screen.
	   ------------------------------------------------------------------ */
	const hero = document.querySelector('[data-hero]');

	if (hero) {
		const q = (sel) => hero.querySelector(sel);
		const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

		const bg = q('[data-hero-bg]');
		if (bg) {
			// §6 scale 1.05 -> 1, slow. Long enough to read as a settle, not a zoom.
			tl.fromTo(bg, { scale: 1.05 }, { scale: 1, duration: 1.6, ease: 'power2.out' }, 0);
		}

		const seq = [
			['.home-hero__badge', 0.15],
			['.home-hero__title', 0.25],
			['.home-hero__text',  0.4],
		];

		seq.forEach(([sel, at]) => {
			const el = q(sel);
			if (el) {
				tl.fromTo(el, { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.8 }, at);
			}
		});

		// §6 CTAs reveal after the heading, with a small stagger between buttons.
		const actions = hero.querySelectorAll('.home-hero__actions > *');
		if (actions.length) {
			tl.fromTo(
				actions,
				{ opacity: 0, y: 20 },
				{ opacity: 1, y: 0, duration: 0.6, stagger: 0.1 },
				0.55
			);
		}
	}

	/* ------------------------------------------------------------------
	   §19 FAQ accordion. Built on native <details>, which cannot transition its
	   own height — the browser snaps it open. The spec explicitly forbids that
	   ("do not instantly toggle"), so the open/close is animated by hand and the
	   `open` attribute is removed only after the closing tween finishes,
	   otherwise the content vanishes before it can animate out.

	   <details> is kept rather than replaced with buttons because it already
	   gives the keyboard and screen-reader behaviour §19's accessibility note
	   asks for, for free.
	   ------------------------------------------------------------------ */
	document.querySelectorAll('.dk-accordion__item').forEach((item) => {
		const summary = item.querySelector('.dk-accordion__summary');
		const panel = summary && summary.nextElementSibling;
		if (!summary || !panel) {
			return;
		}

		let animating = false;

		summary.addEventListener('click', (event) => {
			event.preventDefault();
			if (animating) {
				return;
			}
			animating = true;

			if (item.hasAttribute('open')) {
				gsap.to(panel, {
					height: 0,
					opacity: 0,
					duration: 0.45,
					ease: 'power2.inOut',
					onComplete: () => {
						item.removeAttribute('open');
						gsap.set(panel, { height: 'auto', opacity: 1 });
						animating = false;
					},
				});
			} else {
				item.setAttribute('open', '');
				gsap.fromTo(
					panel,
					{ height: 0, opacity: 0 },
					{
						height: 'auto',
						opacity: 1,
						duration: 0.45,
						ease: 'power2.out',
						onComplete: () => {
							ScrollTrigger.refresh();
							animating = false;
						},
					}
				);
			}
		});
	});

	/* ------------------------------------------------------------------
	   §17 "Follow the Finish" — horizontal gallery driven by vertical scroll.
	   Only engages when the track actually overflows: on mobile the row wraps to
	   a 2-column grid where there is nothing to translate, and pinning a section
	   that fits on screen would just feel like the page had frozen.
	   ------------------------------------------------------------------ */
	const gallery = document.querySelector('[data-hscroll]');

	if (gallery && !isMobile) {
		const overflow = gallery.scrollWidth - gallery.clientWidth;

		if (overflow > 40) {
			gsap.to(gallery, {
				x: -overflow,
				ease: 'none',
				scrollTrigger: {
					trigger: gallery.closest('section') || gallery,
					start: 'top top',
					end: () => `+=${overflow}`,
					pin: true,
					scrub: 0.8,
					invalidateOnRefresh: true,
					anticipatePin: 1,
				},
			});
		}
	}

	/* ------------------------------------------------------------------
	   §30 Parallax. Deliberately small — the spec wants it "discovered rather
	   than obvious", yPercent -8..+8 with scrub 0.5-1. Opt-in per element via
	   [data-parallax] so it lands on chosen imagery instead of everything.
	   ------------------------------------------------------------------ */
	gsap.utils.toArray('[data-parallax]').forEach((el) => {
		const amount = parseFloat(el.getAttribute('data-parallax')) || D.parallax;

		gsap.fromTo(
			el,
			{ yPercent: -amount },
			{
				yPercent: amount,
				ease: 'none',
				scrollTrigger: {
					trigger: el.closest('[data-parallax-scope]') || el.parentElement,
					start: 'top bottom',
					end: 'bottom top',
					scrub: 0.8,
				},
			}
		);
	});

	/* ------------------------------------------------------------------
	   Keep ScrollTrigger honest about late layout shifts — lazy-loaded imagery
	   below the fold changes document height after the triggers were measured,
	   which otherwise leaves every trigger past that point firing at the wrong
	   scroll position. This build lazy-loads nearly every photo, so it matters.
	   ------------------------------------------------------------------ */
	window.addEventListener('load', () => ScrollTrigger.refresh());
})();
