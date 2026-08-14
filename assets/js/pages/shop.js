/* =====================================================================
   shop.js — WooCommerce page behaviour (Shop landing, category archive,
   Product Detail). Loaded only in Woo contexts — see AssetsService's
   isWooContext(). jQuery is re-enabled there for Woo's own scripts, but this
   file stays dependency-free like the rest of the theme.

     - price filter: live label + navigate on release, preserving other
       query params (sort, paging) already on the URL
     - Quantity steppers (Product Detail, Cart — one per line item):
       progressive enhancement over Woo's own woocommerce_quantity_input()
       markup — the real <input class="qty"> Woo renders keeps working with
       JS disabled, this only adds the comp's −/+ buttons around it (classic
       themes get a bare number input; the built-in stepper UI is a
       block-theme-only feature in this Woo version).
   ===================================================================== */
(() => {
	'use strict';

	const initPriceFilter = () => {
		const form  = document.querySelector('[data-dk-price-filter]');
		if (!form) return;

		const range = form.querySelector('input[type="range"]');
		const label = form.querySelector('[data-dk-price-value]');

		range.addEventListener('input', () => {
			if (label) label.textContent = `$${range.value}`;
		});

		range.addEventListener('change', () => {
			const params = new URLSearchParams(window.location.search);
			params.set('max_price', range.value);
			window.location.search = params.toString();
		});
	};

	const enhanceQty = (qty) => {
		if (!qty || qty.closest('.dk-qty')) return;

		const step = () => document.createElement('button');

		const minus = step();
		minus.type = 'button';
		minus.className = 'dk-qty__btn';
		minus.setAttribute('aria-label', 'Decrease quantity');
		minus.textContent = '−';

		const plus = step();
		plus.type = 'button';
		plus.className = 'dk-qty__btn';
		plus.setAttribute('aria-label', 'Increase quantity');
		plus.textContent = '+';

		const wrap = document.createElement('div');
		wrap.className = 'dk-qty';
		qty.parentNode.insertBefore(wrap, qty);
		wrap.append(minus, qty, plus);

		const nudge = (delta) => {
			const step = parseFloat(qty.step) || 1;
			const min  = parseFloat(qty.min);
			const max  = parseFloat(qty.max);
			let value  = (parseFloat(qty.value) || 0) + delta * step;
			if (!Number.isNaN(min)) value = Math.max(min, value);
			if (!Number.isNaN(max)) value = Math.min(max, value);
			qty.value = value;
			qty.dispatchEvent(new Event('change', { bubbles: true }));
		};

		minus.addEventListener('click', () => nudge(-1));
		plus.addEventListener('click', () => nudge(1));
	};

	const initQtySteppers = () => {
		document.querySelectorAll('.quantity .qty').forEach(enhanceQty);
	};

	document.addEventListener('DOMContentLoaded', () => {
		initPriceFilter();
		initQtySteppers();
	});
})();
