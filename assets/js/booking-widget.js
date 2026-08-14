/* =====================================================================
	StackPress — booking-widget.js
	Wires the single-service booking widget (template-parts/sections/service/
	booking.php): clickable package cards, live price recalculation on vehicle
	size, and real submission to BookingWidgetService's REST endpoint. Not
	routed through forms.js — that binds [data-sp-form] to FormService's Lead
	pipeline, and this widget's response shape (cart redirect vs. enquiry
	success) is different enough to warrant its own small handler.
	===================================================================== */
(() => {
	'use strict';

	const cfg = window.DetailKingBooking;
	if (!cfg || !cfg.restUrl) return;

	const root = document.querySelector('.service-booking');
	if (!root) return;

	const cards       = Array.from(root.querySelectorAll('[data-pkg-id]'));
	const vehicleSel  = root.querySelector('#dk-svc-vehicle');
	const priceOut    = root.querySelector('[data-dk-price-display]');
	const packageOut  = root.querySelector('#dk-svc-package');
	const form        = root.querySelector('[data-dk-booking-form]');

	if (!form) return;

	let selected = cards.find((c) => c.classList.contains('is-selected')) || cards[0] || null;

	const currentMultiplier = () => {
		const opt = vehicleSel ? vehicleSel.selectedOptions[0] : null;
		return opt ? parseFloat(opt.dataset.multiplier || '1') : 1;
	};

	const renderPrice = () => {
		if (!selected) {
			if (priceOut) priceOut.textContent = '—';
			return;
		}

		const base  = parseFloat(selected.dataset.pkgPrice || '0');
		const title = selected.dataset.pkgTitle || '';

		if (packageOut) {
			packageOut.innerHTML = '';
			const opt = document.createElement('option');
			opt.textContent = base > 0 ? title + ' — from $' + Math.round(base) : title;
			packageOut.appendChild(opt);
		}

		if (!priceOut) return;
		priceOut.textContent = base > 0 ? '$' + Math.round(base * currentMultiplier()) : '—';
	};

	const selectCard = (card) => {
		if (selected === card) return;
		if (selected) {
			selected.classList.remove('is-selected');
			selected.setAttribute('aria-pressed', 'false');
		}
		selected = card;
		selected.classList.add('is-selected');
		selected.setAttribute('aria-pressed', 'true');
		renderPrice();
	};

	cards.forEach((card) => {
		card.setAttribute('aria-pressed', card === selected ? 'true' : 'false');
		card.addEventListener('click', () => selectCard(card));
	});

	if (vehicleSel) {
		vehicleSel.addEventListener('change', renderPrice);
	}

	/* ---- submission ---- */

	const statusNode = form.querySelector('.dk-form__status');

	const setStatus = (message, type) => {
		if (!statusNode) return;
		statusNode.textContent = message || '';
		statusNode.dataset.state = type || '';
	};

	const clearFieldErrors = () => {
		form.querySelectorAll('[aria-invalid="true"]').forEach((el) => el.removeAttribute('aria-invalid'));
	};

	const markError = (field) => {
		const el = form.querySelector('[name="' + field + '"]');
		if (el) el.setAttribute('aria-invalid', 'true');
	};

	const fieldValue = (name) => {
		const el = form.querySelector('[name="' + name + '"]');
		return el ? el.value : '';
	};

	form.addEventListener('submit', async (e) => {
		e.preventDefault();

		if (!selected) {
			setStatus(cfg.i18n.noPackage, 'error');
			return;
		}

		clearFieldErrors();
		setStatus('', '');

		const vehicleOpt = vehicleSel ? vehicleSel.selectedOptions[0] : null;
		const button     = form.querySelector('[type="submit"]');
		const btnText    = button ? button.innerHTML : '';

		const payload = {
			package_id:      selected.dataset.pkgId,
			vehicle_size:    vehicleOpt ? (vehicleOpt.dataset.slug || vehicleOpt.value) : '',
			full_name:       fieldValue('full_name'),
			phone:           fieldValue('phone'),
			email:           fieldValue('email'),
			drop_date:       fieldValue('drop_date'),
			drop_time:       fieldValue('drop_time'),
			location:        fieldValue('location'),
			notes:           fieldValue('notes'),
			[cfg.hpField]:   fieldValue('dk_hp'),
		};

		if (button) {
			button.disabled = true;
			button.textContent = cfg.i18n.sending;
		}

		try {
			const res = await fetch(cfg.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce,
				},
				body: JSON.stringify(payload),
			});

			const data = await res.json().catch(() => ({}));

			if (res.ok && data && data.success) {
				if (data.mode === 'cart' && data.redirect) {
					const crossSell = window.DetailKingCrossSell;

					// Open the recommendation modal in place of the hard
					// redirect when a cross-sell script loaded and this
					// booking actually has related products to show.
					// Falls back to the redirect otherwise — no related
					// products, or cross-sell.js failed to load — so the
					// customer is never stranded on a stuck button.
					if (crossSell && typeof crossSell.open === 'function' && data.crossSellHtml) {
						if (typeof data.cartCount === 'number') {
							document.querySelectorAll('[data-dk-cart-count]').forEach((el) => {
								el.textContent = String(data.cartCount);
							});
						}
						crossSell.open(data.crossSellHtml);
						setStatus(data.message || cfg.i18n.enquirySent, 'success');
						form.reset();
						renderPrice();
						return;
					}

					window.location.assign(data.redirect);
					return;
				}

				setStatus(data.message || cfg.i18n.enquirySent, 'success');
				form.reset();
				renderPrice();
				return;
			}

			if (data && data.errors) {
				Object.keys(data.errors).forEach(markError);
			}
			setStatus((data && data.message) || cfg.i18n.error, 'error');
		} catch (err) {
			setStatus(cfg.i18n.error, 'error');
		} finally {
			if (button) {
				button.disabled = false;
				button.innerHTML = btnText;
			}
		}
	});

	renderPrice();
})();
