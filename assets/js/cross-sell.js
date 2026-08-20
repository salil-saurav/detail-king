/* =====================================================================
	StackPress — cross-sell.js
	Recommendation popup + cart cross-sell (BUILD-PLAN §7 Phase 1 step 8).
	Self-disables if window.DetailKingCrossSell (localized by AssetsService)
	is absent. Three jobs:
	  1. Intercept Woo's own single-product form.cart submit (no fragments API
	     exists in this theme — see CLAUDE.md — so this is genuinely new
	     capability, not an extension of one).
	  2. Delegated click handler for [data-dk-add-to-cart] (primary adds —
	     e.g. membership-card.php's CTA) and [data-dk-cross-sell-add] (the
	     discounted recommendation cards themselves).
	  3. Modal open/close, mirroring the header search overlay's own pattern
	     exactly (assets/js/global.js's initHeaderSearch) rather than the
	     plain data-sp-toggle class-toggle, which has no focus/escape handling.
	===================================================================== */
(() => {
	'use strict';

	const cfg = window.DetailKingCrossSell;
	if (!cfg || !cfg.restUrl) return;

	const overlay   = document.getElementById('cross-sell-overlay');
	const grid      = overlay ? overlay.querySelector('[data-cross-sell-grid]') : null;
	const closeBtn  = overlay ? overlay.querySelector('[data-cross-sell-close]') : null;
	const isCartPage = !!document.querySelector('.dk-cart-page');

	let isOpen = false;

	const open = (html) => {
		if (!overlay || !grid || !html) return;
		grid.innerHTML = html;
		isOpen = true;
		overlay.hidden = false;
		void overlay.offsetWidth; // force reflow before adding the class, so the transition runs
		overlay.classList.add('is-open');
		if (closeBtn) closeBtn.focus();
	};

	const close = () => {
		if (!overlay || !isOpen) return;
		isOpen = false;
		overlay.classList.remove('is-open');
		overlay.hidden = true;
	};

	if (overlay) {
		if (closeBtn) closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', (e) => {
			if (e.target === overlay) close();
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') close();
		});
	}

	// Exposed so BookingWidgetService's own booking-widget.js can open this
	// same modal directly from its /booking response's crossSellHtml — no
	// second REST round-trip needed for that path.
	cfg.open  = open;
	cfg.close = close;

	const updateCartCount = (count) => {
		if (typeof count !== 'number') return;
		document.querySelectorAll('[data-dk-cart-count]').forEach((el) => {
			el.textContent = String(count);
		});
	};

	const postAdd = async (payload) => {
		const res = await fetch(cfg.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce,
			},
			body: JSON.stringify(payload),
		});
		return res.json().catch(() => ({}));
	};

	/* ---- Woo's own single-product add-to-cart form ---- */
	document.querySelectorAll('form.cart').forEach((form) => {
		const submitBtn = form.querySelector('[name="add-to-cart"]');

		// Variable/grouped products don't carry the product id on this
		// button (Woo's own variation form intercepts separately) — let
		// those submit normally rather than guessing at a payload.
		if (!submitBtn || !submitBtn.value) return;

		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			const productId = parseInt(submitBtn.value, 10);
			const qtyInput  = form.querySelector('[name="quantity"]');
			const quantity  = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

			const original = submitBtn.textContent;
			submitBtn.disabled = true;
			submitBtn.textContent = cfg.i18n.adding;

			try {
				const data = await postAdd({ product_id: productId, quantity, cross_sell: false });

				if (data && data.success) {
					updateCartCount(data.cartCount);
					submitBtn.textContent = cfg.i18n.added;
					if (window.dkSnackbar) window.dkSnackbar(cfg.i18n.added, 'success');
					setTimeout(() => {
						submitBtn.textContent = original;
						submitBtn.disabled = false;
					}, 1500);

					if (data.crossSellHtml) {
						open(data.crossSellHtml);
					}
					return;
				}

				// Graceful degrade: never strand the customer on a silently
				// failed AJAX add — fall back to the real form submission
				// (same pattern this project already uses for GSAP/reduced-
				// motion fallbacks in motion.js).
				submitBtn.disabled = false;
				submitBtn.textContent = original;
				form.submit();
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = original;
				form.submit();
			}
		});
	});

	/* ---- delegated clicks: primary adds + cross-sell adds ---- */
	document.addEventListener('click', async (e) => {
		const crossSellBtn = e.target.closest('[data-dk-cross-sell-add]');
		const primaryBtn   = e.target.closest('[data-dk-add-to-cart]');
		const btn = crossSellBtn || primaryBtn;
		if (!btn || btn.disabled) return;

		e.preventDefault();

		const productId = parseInt(btn.getAttribute('data-product-id') || '0', 10);
		if (!productId) return;

		const original = btn.innerHTML;
		btn.disabled = true;

		try {
			const data = await postAdd({ product_id: productId, quantity: 1, cross_sell: !!crossSellBtn });

			if (!data || !data.success) {
				btn.innerHTML = original;
				btn.disabled = false;
				if (window.dkSnackbar) window.dkSnackbar((data && data.message) || cfg.i18n.error, 'error');
				return;
			}

			updateCartCount(data.cartCount);
			if (window.dkSnackbar) window.dkSnackbar(cfg.i18n.added, 'success');

			if (crossSellBtn) {
				// A cross-sell add never reopens/reuses the modal for a second
				// recommendation round — just confirm in place. On the Cart
				// page specifically, reload so the order-summary sidebar (no
				// fragments API exists here to patch it live) reflects the
				// new line item and its 10%-off price.
				btn.innerHTML = '<span aria-hidden="true">&#10003;</span>';
				if (isCartPage) {
					window.location.reload();
				}
				return;
			}

			// Primary add (e.g. membership-card.php's CTA, product-card.php's
			// own add-to-cart button): behaves exactly like the form.cart path
			// above.
			if (data.crossSellHtml) {
				open(data.crossSellHtml);
			}
			btn.innerHTML = original;
			btn.disabled = false;
		} catch (err) {
			btn.innerHTML = original;
			btn.disabled = false;
			if (window.dkSnackbar) window.dkSnackbar(cfg.i18n.error, 'error');
		}
	});
})();
