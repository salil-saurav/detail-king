/* =====================================================================
	StackPress — forms.js
	Progressive AJAX handling for every custom (non-CF7) form.

	Any <form data-sp-form="<id>"> is intercepted, validated server-side
	via the detailking/v1 REST endpoint, and on success the browser is
	redirected to the configured thank-you page. Security tokens (nonce,
	signed time-trap) are injected from the localized DetailKingForms
	config; the honeypot field lives in the markup.
	===================================================================== */
(() => {
	'use strict';

	const cfg = window.DetailKingForms;
	if (!cfg || !cfg.restUrl) return;

	const forms = document.querySelectorAll('form[data-sp-form]');
	if (!forms.length) return;

	/* Find (or lazily create) the status/alert node for a form. */
	const statusNode = (form) => {
		let node = form.querySelector('.sp-form-status');
		if (!node) {
			node = document.createElement('div');
			node.className = 'sp-form-status';
			node.setAttribute('role', 'alert');
			node.setAttribute('aria-live', 'polite');
			form.appendChild(node);
		}
		return node;
	};

	const setStatus = (form, message, type) => {
		const node = statusNode(form);
		node.textContent = message || '';
		node.dataset.state = type || '';
	};

	/* Clear per-field error styling. */
	const clearErrors = (form) => {
		form.querySelectorAll('[aria-invalid="true"]').forEach((el) => {
			el.removeAttribute('aria-invalid');
		});
	};

	const markError = (form, field) => {
		const el = form.querySelector('[name="' + field + '"]');
		if (el) el.setAttribute('aria-invalid', 'true');
	};

	const submit = async (form) => {
		const formId = form.getAttribute('data-sp-form');
		const button = form.querySelector('[type="submit"]');
		const btnText = button ? button.innerHTML : '';

		clearErrors(form);
		setStatus(form, '', '');

		// Build the payload from the visible fields + injected security tokens.
		const payload = {};
		new FormData(form).forEach((value, key) => { payload[key] = value; });
		payload[cfg.tsField] = cfg.ts;
		payload[cfg.tokField] = cfg.token;

		if (button) {
			button.disabled = true;
			button.dataset.spBusy = '1';
			button.textContent = cfg.i18n.sending;
		}

		try {
			const res = await fetch(cfg.restUrl + encodeURIComponent(formId), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce,
				},
				body: JSON.stringify(payload),
			});

			const data = await res.json().catch(() => ({}));

			if (res.ok && data && data.success) {
				// Hard redirect to the thank-you page; no further UI needed.
				window.location.assign(data.redirect);
				return;
			}

			// Validation / throttle / security failure.
			if (data && data.errors) {
				Object.keys(data.errors).forEach((field) => markError(form, field));
			}
			setStatus(form, (data && data.message) || cfg.i18n.error, 'error');
		} catch (err) {
			setStatus(form, cfg.i18n.error, 'error');
		} finally {
			if (button && button.dataset.spBusy) {
				button.disabled = false;
				delete button.dataset.spBusy;
				button.innerHTML = btnText;
			}
		}
	};

	forms.forEach((form) => {
		// Native validation (required/email) runs first; the submit event only
		// fires once the browser is satisfied, then we take over for AJAX.
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			submit(form);
		});
	});
})();
