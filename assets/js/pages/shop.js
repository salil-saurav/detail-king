/* =====================================================================
   shop.js — WooCommerce page behaviour (Shop landing, category archive,
   Product Detail). Loaded only in Woo contexts — see AssetsService's
   isWooContext(). jQuery is re-enabled there for Woo's own scripts, but this
   file stays dependency-free like the rest of the theme.

     - Category archive filters (category checkboxes, now real multi-select,
       + the price slider): re-query via ShopFilterService's admin-ajax
       endpoint and swap #shop-listing-grid in place, no navigation.
     - Quantity steppers (Product Detail, Cart — one per line item):
       progressive enhancement over Woo's own woocommerce_quantity_input()
       markup — the real <input class="qty"> Woo renders keeps working with
       JS disabled, this only adds the comp's −/+ buttons around it (classic
       themes get a bare number input; the built-in stepper UI is a
       block-theme-only feature in this Woo version).
   ===================================================================== */
(() => {
	'use strict';

	/* ----------------------------------------------------------
		Category archive filters — shop-sidebar.php's category checkboxes
		and price slider, re-querying #shop-listing-grid via
		ShopFilterService (admin-ajax) instead of navigating. JS-only: see
		shop-sidebar.php's doc comment for why there's no non-JS fallback
		path here.
		---------------------------------------------------------- */
	const initShopFilters = () => {
		const main = document.getElementById('shop-listing-main');
		const cfg  = window.DetailKingShopFilter;
		if (!main || !cfg || !cfg.ajaxUrl) return;

		const grid     = document.getElementById('shop-listing-grid');
		const empty    = document.getElementById('shop-listing-empty');
		const count    = document.getElementById('shop-listing-count');
		const more     = document.getElementById('shop-listing-more');
		const loadMore = document.getElementById('shop-listing-load-more');
		const catBoxes = () => Array.from(document.querySelectorAll('#shop-sidebar-cats [data-dk-cat-filter]'));
		const priceForm  = document.querySelector('[data-dk-price-filter]');
		const priceRange = priceForm ? priceForm.querySelector('input[type="range"]') : null;
		const sortSelect = document.querySelector('.woocommerce-ordering select[name="orderby"]');

		let activeController = null;

		const setCount = (n) => {
			if (!count) return;
			const template = n === 1 ? cfg.i18n.countSingular : cfg.i18n.countPlural;
			count.textContent = template.replace('%d', String(n));
		};

		const revealInjected = (scope) => {
			scope.querySelectorAll('[data-animate]').forEach((el) => el.classList.add('is-visible'));
		};

		const request = ({ paged, append }) => {
			if (activeController) activeController.abort();
			activeController = new AbortController();

			const body = new URLSearchParams();
			body.set('action', cfg.action);
			body.set('nonce', cfg.nonce);
			body.set('max_price', priceRange ? priceRange.value : '');
			body.set('paged', String(paged));
			if (sortSelect && sortSelect.value) body.set('orderby', sortSelect.value);
			catBoxes().filter((box) => box.checked).forEach((box) => body.append('cats[]', box.value));

			main.classList.add('is-loading');

			fetch(cfg.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				signal: activeController.signal,
				body,
			})
				.then((res) => res.json())
				.then((json) => {
					if (!json || !json.success) return;
					const data = json.data;

					if (append) {
						grid.insertAdjacentHTML('beforeend', data.html);
					} else {
						grid.innerHTML = data.html;
					}
					revealInjected(grid);
					if (window.dkRepaintWishlist) window.dkRepaintWishlist();

					const hasPosts = data.count > 0;
					grid.hidden = !hasPosts;
					if (empty) empty.hidden = hasPosts;
					setCount(data.count);

					main.dataset.page = String(data.page);
					main.dataset.maxPages = String(data.maxPages);
					if (more) more.hidden = data.page >= data.maxPages;
				})
				.catch((err) => {
					if (err.name !== 'AbortError') {
						// Network/parse failure: leave the current grid as-is rather
						// than blanking a working page over a transient error.
					}
				})
				.finally(() => {
					main.classList.remove('is-loading');
				});
		};

		const refetch = () => request({ paged: 1, append: false });

		catBoxes().forEach((box) => box.addEventListener('change', refetch));

		const clearLink = document.querySelector('[data-dk-cat-clear]');
		if (clearLink) {
			clearLink.addEventListener('click', (e) => {
				e.preventDefault();
				catBoxes().forEach((box) => { box.checked = false; });
				refetch();
			});
		}

		if (loadMore) {
			loadMore.addEventListener('click', (e) => {
				e.preventDefault();
				const next = (parseInt(main.dataset.page, 10) || 1) + 1;
				request({ paged: next, append: true });
			});
		}

		if (priceRange) {
			priceRange.addEventListener('change', refetch);
		}

		if (priceForm) {
			priceForm.addEventListener('submit', (e) => e.preventDefault());
		}
	};

	const initPriceFilter = () => {
		const form  = document.querySelector('[data-dk-price-filter]');
		if (!form) return;

		const range = form.querySelector('input[type="range"]');
		const label = form.querySelector('[data-dk-price-value]');

		range.addEventListener('input', () => {
			if (label) label.textContent = `$${range.value}`;
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
		initShopFilters();
		initPriceFilter();
		initQtySteppers();
	});
})();
