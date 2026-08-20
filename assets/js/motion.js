/* =====================================================================
	Detail King — motion.js

	The in-house scroll-animation engine. No dependencies, ~4KB unminified.
	Implements ~/DWS/clients/detail-king/build/animation-implementation-spec.md;
	section numbers in comments refer to that document.

	Replaces GSAP 3.13 + ScrollTrigger (17 Aug 2026), which were 116KB of
	vendored JS (~42KB gzipped) and two extra deferred requests to do four
	things this file now does in a page of code. The division of labour changed
	with them:

	  - The *animation* is CSS now. Every reveal is a transition declared in
		 global.css §7 (from-state per variant, duration per variant, one easing
		 curve). This file only adds a class. That means a reveal costs zero
		 main-thread work per frame — the compositor interpolates opacity and
		 transform off-thread — where GSAP ticked every active tween on the main
		 thread. It also means `.is-visible` is once again the single, inspectable
		 switch for "revealed", which is what build/scripts/shoot.py has always
		 assumed when it force-reveals a page before screenshotting (that had been
		 quietly inert for as long as GSAP owned the transforms).
	  - The *scroll-linked* bits (§30 parallax, §17 pinned h-scroll) are the only
		 things that genuinely need per-frame work. They share one rAF-throttled
		 scroll listener, and each element is gated by an IntersectionObserver, so
		 an off-screen parallax image costs nothing at all rather than being
		 recalculated for the life of the page.

	Accessibility (§33): under prefers-reduced-motion this file returns before
	touching anything. It never adds the `aos-ready` class, so the CSS that hides
	[data-animate] never applies and all content is visible and functional. That
	is also the no-JS and script-failed-to-parse path, which is why the hiding
	rule is class-gated rather than plain.

	§4's Lenis-driven inertial scroll was here and was removed earlier for
	measured frame-throughput reasons — see the AssetsService motion-layer
	comment. Scrolling is native; everything below reads it directly.
	===================================================================== */
(() => {
	'use strict';

	const mq = (query) => window.matchMedia && window.matchMedia(query).matches;

	// Bail before touching anything if motion is unwanted, or if the browser is
	// old enough to lack IntersectionObserver. Content stays visible because
	// `aos-ready` is never added.
	if (mq('(prefers-reduced-motion: reduce)') || !('IntersectionObserver' in window)) {
		return;
	}

	/* `aos-ready` is NOT added here. It is added synchronously in <head> by
		AssetsService::printMotionGate(), because this file is deferred and cannot
		run before the browser's first paint — adding the class from here meant the
		first screen was painted visible and then snapped hidden, a flash before
		every above-the-fold reveal. All this file owes the gate is the handshake
		below: clearing its watchdog says "motion.js is alive, the from-states are
		safe to keep". If this file never runs, the watchdog fires and un-hides
		everything, which is why the class must not be re-added here either — that
		would hide content again after the safety net had already caught it. */
	clearTimeout(window.dkMotionFallback);

	const isMobile = mq('(max-width: 767.98px)');

	/* ------------------------------------------------------------------
		§28/§29 The reveal system.

		One observer over every [data-animate]. Elements that cross the line
		together arrive in the same callback, which is exactly the set that should
		stagger (§8, §14, §15: 100ms) — so the batch is the callback's own entry
		list and needs no grouping logic.

		Two traps this shape avoids, both of which this build has been bitten by:

		1. `threshold: 0` with a bottom rootMargin, never a fractional threshold.
			The old pre-GSAP observer wanted 12% of the *target's own height*
			visible, so a section several viewports tall (a 7-card CPT grid, the
			booking widget) could never reach it and stayed invisible forever.
			Height-independent by construction now.
		2. Above-the-fold elements are not a special case. IntersectionObserver
			reports already-intersecting targets in its first callback, so the first
			screen reveals on load, staggered, with no separate code path — where
			ScrollTrigger only fired on the *transition* into view and needed the
			whole first screen split out by hand.

		`-12%` bottom margin = GSAP's `start: 'top 88%'`: reveal once the element's
		top has passed 88% of the viewport height. rootMargin percentages resolve
		against the root, i.e. the viewport, so this is the same line.

		The huge *top* margin is not cosmetic — without it, elements can be
		stranded for good, which this build has been bitten by twice. An observer
		only fires when the intersection ratio changes, and a fast scroll can move
		an element from "below the line" (ratio 0) to "entirely above the viewport"
		(still ratio 0) between two frames, in which case there is no crossing to
		report and no callback ever runs. The window is narrower than it sounds:
		viewport*0.88 + element height, so a 318px card at a 900px viewport has
		1110px of it, and verify.sh's own 1200px wheel steps jumped clean over one
		card on /gallery/. Extending the root upward means "above the viewport"
		also counts as intersecting, so an element that was skipped reveals on the
		next frame instead of never. It also covers landing mid-page from an anchor
		or a restored scroll position. ScrollTrigger did not need this because it
		compared scroll positions frame to frame rather than observing ratios.
		------------------------------------------------------------------ */
	const STAGGER_MS = 100;

	const revealObserver = new IntersectionObserver((entries, obs) => {
		let i = 0;

		entries.forEach((entry) => {
			if (!entry.isIntersecting) {
				return;
			}

			const el = entry.target;
			obs.unobserve(el);                       // reveal is once, per spec

			if (i > 0) {
				el.style.setProperty('--dk-rv-delay', `${i * STAGGER_MS}ms`);
			}
			i += 1;

			/* Clear the stagger delay once it has been consumed. Left in place it
				would also delay any *later* transition on the same element — the
				card hover states share the transform property. */
			el.addEventListener('transitionend', function drop(event) {
				if (event.target === el) {
					el.style.removeProperty('--dk-rv-delay');
					el.removeEventListener('transitionend', drop);
				}
			});

			el.classList.add('is-visible');
		});
	}, { threshold: 0, rootMargin: '100000px 0px -12% 0px' });

	document.querySelectorAll('[data-animate]').forEach((el) => revealObserver.observe(el));

	/* ------------------------------------------------------------------
		Bottom-of-document safety net. The rootMargin above only rescues an
		element that a fast scroll jumped clean over — it does nothing for an
		element that sits so close to the true end of the page that its top
		never crosses the -12% line, because there is no scroll distance left
		to produce that crossing at all. Measured 20 Aug 2026 (verify.sh's
		stranded= check, identical count on every page): the footer's
		legal-menu items — last thing in the DOM everywhere — sat at opacity 0
		forever on any page short enough that max scroll left them a few px
		short of the line (e.g. /cart/: scrollY pinned at 742/742, item top at
		840 against an 792px line on a 900px viewport). A second observer on
		the footer, with no negative margin, fires exactly when the page has
		been scrolled as far as it goes; at that point everything above the
		footer must already be revealed by definition, so anything still
		hidden is force-shown rather than left stranded for good. */
	const footer = document.querySelector('.dk-footer');

	if (footer) {
		const bottomObserver = new IntersectionObserver((entries, obs) => {
			if (!entries[0].isIntersecting) {
				return;
			}
			document.querySelectorAll('[data-animate]:not(.is-visible)').forEach((el) => {
				revealObserver.unobserve(el);
				el.classList.add('is-visible');
			});
			obs.disconnect();
		}, { threshold: 0 });

		bottomObserver.observe(footer);
	}

	/* ------------------------------------------------------------------
		§6 Hero. A real sequence rather than a single reveal: the backdrop settles
		from 1.05 while the heading, supporting text and CTAs come up behind it on
		staggered delays. All of that is declared in global.css §7b; the only thing
		needed here is the switch, one frame after paint so the from-state is the
		browser's starting point and the transition actually runs.

		Stats are not part of this — they are numbers, and global.js's count-up
		observer (§11: once, 1.6s) owns them. Sequencing them here would run the
		count before the section is on screen.
		------------------------------------------------------------------ */
	const hero = document.querySelector('[data-hero]');

	if (hero) {
		requestAnimationFrame(() => hero.classList.add('is-hero-in'));

		/* The CTA "settle" rule in global.css §7b keys off `.is-hero-settled`
			rather than `.is-hero-in` — `.is-hero-in` is added on the very first
			frame and never removed, so a rule scoped to it alone is already
			active *during* the entrance transition and clobbers the CTAs'
			.6s/staggered-delay timing from frame one instead of only after
			reveal. 1.4s clears the latest entrance leg (§6: .75s delay + .6s
			duration on the third CTA) with a small buffer. */
		setTimeout(() => hero.classList.add('is-hero-settled'), 1400);
	}

	/* ------------------------------------------------------------------
		§19 FAQ accordion. Built on native <details>, which cannot transition its
		own height — the browser snaps it open. The spec explicitly forbids that
		("do not instantly toggle"), so the open/close is animated with the Web
		Animations API and the `open` attribute is removed only after the closing
		animation finishes, otherwise the content vanishes before it can animate
		out.

		<details> is kept rather than replaced with buttons because it already
		gives the keyboard and screen-reader behaviour §19's accessibility note
		asks for, for free.

		`height: auto` is not animatable here (interpolate-size is not broadly
		supported yet), so the target is the panel's measured scrollHeight and the
		inline height is dropped on finish so the panel can reflow with its
		content afterwards.
		------------------------------------------------------------------ */
	const EASE_OUT = 'cubic-bezier(.25, .46, .45, .94)';   // = GSAP power2.out
	const EASE_IN_OUT = 'cubic-bezier(.455, .03, .515, .955)';

	document.querySelectorAll('.dk-accordion__item').forEach((item) => {
		const summary = item.querySelector('.dk-accordion__summary');
		const panel = summary && summary.nextElementSibling;

		if (!summary || !panel || typeof panel.animate !== 'function') {
			return;   // no WAAPI: leave <details> to its native snap-open
		}

		let running = null;

		const play = (frames, easing, onDone) => {
			panel.style.overflow = 'hidden';
			running = panel.animate(frames, { duration: 450, easing, fill: 'none' });
			running.onfinish = () => {
				running = null;
				/* onDone before the style cleanup: on close it is what removes the
					`open` attribute, and doing it second would show one frame of
					full-height panel between the animation ending and the element
					collapsing. */
				onDone();
				panel.style.removeProperty('overflow');
				panel.style.removeProperty('height');
			};
		};

		summary.addEventListener('click', (event) => {
			event.preventDefault();

			if (running) {
				return;
			}

			const height = `${panel.scrollHeight}px`;

			if (item.hasAttribute('open')) {
				play(
					[{ height, opacity: 1 }, { height: '0px', opacity: 0 }],
					EASE_IN_OUT,
					() => item.removeAttribute('open')
				);
			} else {
				item.setAttribute('open', '');
				play(
					[{ height: '0px', opacity: 0 }, { height: `${panel.scrollHeight}px`, opacity: 1 }],
					EASE_OUT,
					() => { }
				);
			}
		});
	});

	/* ------------------------------------------------------------------
		The scroll loop. One passive listener, one rAF per frame at most, and a
		set of "readers" that is empty whenever nothing scroll-linked is on
		screen — so on the great majority of pages (no parallax, no h-scroll)
		this costs a single listener that returns immediately.

		Each reader is registered with the element that decides whether it is
		worth running; a shared observer adds it to `active` on entry and removes
		it on exit.
		------------------------------------------------------------------ */
	const active = new Set();
	let queued = false;

	const frame = () => {
		queued = false;
		active.forEach((read) => read());
	};

	const schedule = () => {
		if (!queued) {
			queued = true;
			requestAnimationFrame(frame);
		}
	};

	const readers = new WeakMap();

	const gate = new IntersectionObserver((entries) => {
		entries.forEach((entry) => {
			const read = readers.get(entry.target);
			if (!read) {
				return;
			}
			if (entry.isIntersecting) {
				active.add(read);
			} else {
				active.delete(read);
			}
		});
		schedule();
	}, { threshold: 0 });

	const track = (el, read) => {
		readers.set(el, read);
		gate.observe(el);
	};

	let listening = false;

	const listen = () => {
		if (listening) {
			return;
		}
		listening = true;
		window.addEventListener('scroll', schedule, { passive: true });
		window.addEventListener('resize', schedule, { passive: true });
	};

	const clamp = (n) => (n < 0 ? 0 : (n > 1 ? 1 : n));

	/* ------------------------------------------------------------------
		§30 Parallax. Deliberately small — the spec wants it "discovered rather
		than obvious", yPercent -8..+8, opt-in per element via [data-parallax] so
		it lands on chosen imagery instead of everything.

		Progress runs 0 at "scope top hits the viewport bottom" to 1 at "scope
		bottom hits the viewport top", which is the window ScrollTrigger's
		`start: 'top bottom'` / `end: 'bottom top'` described. The scope's rect is
		read fresh each frame rather than cached: it is one getBoundingClientRect
		on one element, cheap enough that it is not worth the cache-invalidation
		bugs — and it is inherently correct across lazy-loaded images, resizes and
		accordion opens, all of which used to need an explicit
		`ScrollTrigger.refresh()`.

		Note the CSS that pairs with this: `.home-hero__bg img` and
		`.home-seam__figure img` are given ~110-116% height and a negative offset
		so the travel never reveals an edge.
		------------------------------------------------------------------ */
	const DEFAULT_PARALLAX = isMobile ? 4 : 8;

	document.querySelectorAll('[data-parallax]').forEach((el) => {
		const amount = parseFloat(el.getAttribute('data-parallax')) || DEFAULT_PARALLAX;
		const invert = el.hasAttribute('data-parallax-invert');
		const scope = el.closest('[data-parallax-scope]') || el.parentElement || el;

		track(scope, () => {
			const rect = scope.getBoundingClientRect();
			const span = window.innerHeight + rect.height;

			if (span <= 0) {
				return;
			}

			const progress = clamp((window.innerHeight - rect.top) / span);
			let y;

			if (invert) {
				/* Biased toward the rise: a small settle-down on entry, most of the
				   travel spent climbing, so scrolling down reads as the image moving
				   up (and scrolling back up reverses it). Opt-in via
				   data-parallax-invert — leaves every other [data-parallax] element
				   (the hero bg's tight ±4 buffer) on the original symmetric curve. */
				const down = amount * 0.3;
				const up = amount * 1.7;
				y = down - ((down + up) * progress);
			} else {
				y = -amount + (2 * amount * progress);
			}

			el.style.transform = `translate3d(0, ${y.toFixed(3)}%, 0)`;
		});

		listen();
	});

	/* ------------------------------------------------------------------
		§17 "Follow the Finish" — horizontal gallery driven by vertical scroll.

		Only engages when the track actually overflows its own box. That is the
		same guard the GSAP version carried, and it is worth knowing that it has
		never once passed on this build: `.home-instagram__row` is a
		`repeat(N, 1fr)` grid, so the row is exactly as wide as its container at
		every breakpoint and there is nothing to translate. The path stays here
		for the real Instagram feed integration (TASK-BRIEF §1.3), which is the
		thing likely to produce a row wider than the viewport. Verified by forcing
		fixed-width columns; see HISTORY.md.

		The pin is `position: sticky` on a wrapper, plus a scope tall enough to
		give the sticky something to travel inside — which is all ScrollTrigger's
		`pin: true` amounts to when the pinned element is in normal flow. The two
		wrappers are created here rather than in the template so that a layout
		where the row fits carries no extra DOM at all.
		------------------------------------------------------------------ */
	const hscroll = document.querySelector('[data-hscroll]');

	if (hscroll && !isMobile) {
		const overflow = hscroll.scrollWidth - hscroll.clientWidth;

		if (overflow > 40) {
			const scope = document.createElement('div');
			const pin = document.createElement('div');

			pin.className = 'dk-pin';
			scope.className = 'dk-pin-scope';
			scope.style.height = `${hscroll.offsetHeight + overflow}px`;

			hscroll.parentNode.insertBefore(scope, hscroll);
			scope.appendChild(pin);
			pin.appendChild(hscroll);

			track(scope, () => {
				const rect = scope.getBoundingClientRect();
				const progress = clamp(-rect.top / overflow);

				hscroll.style.transform = `translate3d(${(-progress * overflow).toFixed(2)}px, 0, 0)`;
			});

			listen();
		}
	}
})();
