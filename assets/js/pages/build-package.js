/**
 * Detail King — build-package.js
 * Handles Section 1, 2, 3 selection, live estimate calculations,
 * sidebar updates, and AJAX form submission.
 */
(() => {
	'use strict';

	const cfg = window.DetailKingPackageBuilder;
	if (!cfg || !cfg.restUrl) return;

	const form = document.getElementById('byop-form');
	if (!form) return;

	// Elements
	const vehicleTiles = Array.from(form.querySelectorAll('[data-vehicle-tile]'));
	const serviceTiles = Array.from(form.querySelectorAll('[data-service-tile]'));
	const serviceBlocks = Array.from(form.querySelectorAll('[data-service-block]'));
	const statusNode = form.querySelector('.dk-form__status');
	const submitBtn = form.querySelector('[data-byop-submit-btn]');

	// Sidebar elements
	const summaryImg = document.querySelector('[data-byop-summary-img]');
	const summaryVehicle = document.querySelector('[data-byop-summary-vehicle]');
	const summaryService = document.querySelector('[data-byop-summary-service]');
	const summaryPackage = document.querySelector('[data-byop-summary-package]');
	const summaryAddonsWrapper = document.querySelector('[data-byop-summary-addons-wrapper]');
	const summaryAddonsList = document.querySelector('[data-byop-summary-addons-list]');
	const summaryPrice = document.querySelector('[data-byop-summary-price]');

	// File upload support
	const fileInput = form.querySelector('#byop-vehicle-photos');
	const fileListText = form.querySelector('[data-byop-filelist]');

	if (fileInput && fileListText) {
		fileInput.addEventListener('change', () => {
			const files = Array.from(fileInput.files);
			if (files.length === 0) {
				fileListText.textContent = '';
			} else {
				fileListText.textContent = files.map(f => f.name).join(', ');
			}
		});
	}

	/**
	 * Get current selection states
	 */
	const getSelections = () => {
		const vehicle = vehicleTiles.find(t => t.classList.contains('is-selected')) || vehicleTiles[0];
		const service = serviceTiles.find(t => t.classList.contains('is-selected')) || serviceTiles[0];

		const serviceSlug = service ? service.dataset.serviceSlug : 'vinyl-wraps';
		const activeBlock = serviceBlocks.find(b => b.dataset.serviceBlock === serviceSlug);

		return {
			vehicle,
			service,
			serviceSlug,
			activeBlock
		};
	};

	/**
	 * Update the Package Summary sidebar
	 */
	const updateSummary = () => {
		const { vehicle, service, serviceSlug, activeBlock } = getSelections();

		if (!vehicle || !service) return;

		// 1. Update Vehicle label
		if (summaryVehicle) {
			summaryVehicle.textContent = vehicle.dataset.vehicleLabel || vehicle.textContent.trim();
		}

		// 2. Update Service label & photo
		if (summaryService) {
			summaryService.textContent = service.dataset.serviceTitle || service.textContent.trim();
		}
		if (summaryImg && service.dataset.serviceThumb) {
			summaryImg.src = service.dataset.serviceThumb;
			summaryImg.alt = service.dataset.serviceTitle || '';
		}

		// 3. Update Package and Add-ons/Requirements list
		let packageTitle = 'Custom Build';
		let isQuoteOnly = (serviceSlug === 'vinyl-wraps');
		let basePrice = 0;
		let addons = [];

		if (activeBlock) {
			if (serviceSlug === 'vinyl-wraps') {
				packageTitle = 'Custom Build';
				// Get wrap requirements
				const checkedReqs = Array.from(activeBlock.querySelectorAll('[data-byop-requirement]:checked'));
				addons = checkedReqs.map(el => el.dataset.title);
			} else {
				// Get selected package
				const checkedPkg = activeBlock.querySelector('[data-byop-package]:checked');
				if (checkedPkg) {
					packageTitle = checkedPkg.dataset.title;
					basePrice += parseFloat(checkedPkg.dataset.basePrice || '0');
					if (checkedPkg.dataset.quoteOnly === 'true') {
						isQuoteOnly = true;
					}
				} else {
					packageTitle = 'Choose Package';
				}

				// Get selected add-ons
				const checkedAddons = Array.from(activeBlock.querySelectorAll('[data-byop-addon]:checked'));
				checkedAddons.forEach(addon => {
					basePrice += parseFloat(addon.dataset.basePrice || '0');
					addons.push(addon.dataset.title);
				});
			}
		}

		if (summaryPackage) {
			summaryPackage.textContent = packageTitle;
		}

		// 4. Update Additional Services list in sidebar
		if (summaryAddonsList && summaryAddonsWrapper) {
			summaryAddonsList.innerHTML = '';
			if (addons.length > 0) {
				addons.forEach(name => {
					const li = document.createElement('li');
					li.textContent = name;
					summaryAddonsList.appendChild(li);
				});
				summaryAddonsWrapper.style.display = 'block';
			} else {
				summaryAddonsWrapper.style.display = 'none';
			}
		}

		// 5. Calculate and display Estimated Cost
		if (summaryPrice) {
			if (isQuoteOnly) {
				summaryPrice.textContent = 'Request Quote';
				summaryPrice.classList.add('byop-price-quote');
			} else {
				const multiplier = parseFloat(vehicle.dataset.multiplier || '1');
				const total = Math.round(basePrice * multiplier);
				summaryPrice.textContent = '$' + total;
				summaryPrice.classList.remove('byop-price-quote');
			}
		}

		// 6. Update CTA button text
		if (submitBtn) {
			if (isQuoteOnly) {
				submitBtn.innerHTML = 'Get My Custom Quote';
			} else {
				submitBtn.innerHTML = 'Send Enquiry';
			}
		}
	};

	/**
	 * Setup event listeners
	 */
	// Vehicle selection
	vehicleTiles.forEach(tile => {
		tile.addEventListener('click', () => {
			vehicleTiles.forEach(t => {
				t.classList.remove('is-selected');
				t.setAttribute('aria-checked', 'false');
			});
			tile.classList.add('is-selected');
			tile.setAttribute('aria-checked', 'true');
			updateSummary();
		});
	});

	// Service selection
	serviceTiles.forEach(tile => {
		tile.addEventListener('click', () => {
			serviceTiles.forEach(t => {
				t.classList.remove('is-selected');
				t.setAttribute('aria-checked', 'false');
			});
			tile.classList.add('is-selected');
			tile.setAttribute('aria-checked', 'true');

			const selectedSlug = tile.dataset.serviceSlug;

			// Show/hide blocks
			serviceBlocks.forEach(block => {
				if (block.dataset.serviceBlock === selectedSlug) {
					block.classList.add('is-active');
					block.removeAttribute('hidden');
				} else {
					block.classList.remove('is-active');
					block.setAttribute('hidden', '');
				}
			});

			// Standard service auto-select first package if none selected
			if (selectedSlug !== 'vinyl-wraps') {
				const activeBlock = serviceBlocks.find(b => b.dataset.serviceBlock === selectedSlug);
				if (activeBlock) {
					const pkgRadios = activeBlock.querySelectorAll('[data-byop-package]');
					const checked = activeBlock.querySelector('[data-byop-package]:checked');
					if (pkgRadios.length > 0 && !checked) {
						pkgRadios[0].checked = true;
					}
				}
			}

			updateSummary();
		});
	});

	// Conditional inputs change
	form.addEventListener('change', (e) => {
		if (e.target.matches('[data-byop-package]') || e.target.matches('[data-byop-addon]') || e.target.matches('[data-byop-requirement]')) {
			updateSummary();
		}
	});

	/* ---- Submission ---- */

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

		const { vehicle, service, serviceSlug, activeBlock } = getSelections();

		if (!vehicle) {
			setStatus(cfg.i18n.noVehicle, 'error');
			return;
		}
		if (!service) {
			setStatus(cfg.i18n.noService, 'error');
			return;
		}

		clearFieldErrors();
		setStatus('', '');

		// Client side validation
		let hasErrors = false;
		const nameInput = form.querySelector('[name="full_name"]');
		const phoneInput = form.querySelector('[name="phone"]');
		const emailInput = form.querySelector('[name="email"]');

		if (!nameInput || nameInput.value.trim() === '') {
			if (nameInput) nameInput.setAttribute('aria-invalid', 'true');
			hasErrors = true;
		}
		if (!phoneInput || phoneInput.value.trim() === '') {
			if (phoneInput) phoneInput.setAttribute('aria-invalid', 'true');
			hasErrors = true;
		}
		if (!emailInput || emailInput.value.trim() === '' || !emailInput.value.includes('@')) {
			if (emailInput) emailInput.setAttribute('aria-invalid', 'true');
			hasErrors = true;
		}

		if (hasErrors) {
			setStatus('Please fill in all required fields.', 'error');
			return;
		}

		const btnText = submitBtn ? submitBtn.innerHTML : '';
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.textContent = cfg.i18n.sending;
		}

		// Gather checked packages and add-ons
		let packageId = 0;
		let requirements = [];
		let addons = [];

		if (activeBlock) {
			if (serviceSlug === 'vinyl-wraps') {
				const checkedReqs = Array.from(activeBlock.querySelectorAll('[data-byop-requirement]:checked'));
				requirements = checkedReqs.map(el => el.value);
			} else {
				const checkedPkg = activeBlock.querySelector('[data-byop-package]:checked');
				if (checkedPkg) {
					packageId = parseInt(checkedPkg.dataset.packageId || '0');
					requirements = checkedPkg.dataset.title;
				}
				const checkedAddons = Array.from(activeBlock.querySelectorAll('[data-byop-addon]:checked'));
				addons = checkedAddons.map(el => parseInt(el.dataset.addonId || '0'));
			}
		}

		const estimateVal = summaryPrice ? summaryPrice.textContent.trim() : 'Request Quote';

		// Prepare FormData for potential photo uploads
		const formData = new FormData();
		formData.append('vehicle_size', vehicle.dataset.vehicleRawLabel || vehicle.dataset.vehicleLabel);
		formData.append('service_slug', serviceSlug);
		formData.append('service_id', service.dataset.serviceId);
		formData.append('package_id', packageId);
		formData.append('estimated_cost', estimateVal);
		formData.append('full_name', fieldValue('full_name'));
		formData.append('phone', fieldValue('phone'));
		formData.append('email', fieldValue('email'));
		formData.append('drop_date', fieldValue('drop_date'));
		formData.append('notes', fieldValue('notes'));
		formData.append('dk_hp', fieldValue('dk_hp'));

		if (serviceSlug === 'vinyl-wraps') {
			formData.append('wrap_notes', fieldValue('wrap_notes'));
			// Append requirements
			requirements.forEach(req => formData.append('requirements[]', req));
			// Append files if selected
			if (fileInput && fileInput.files.length > 0) {
				for (let i = 0; i < fileInput.files.length; i++) {
					formData.append('vehicle_photos[]', fileInput.files[i]);
				}
			}
		} else {
			formData.append('requirements', requirements);
			addons.forEach(id => formData.append('addons[]', id));
		}

		try {
			const res = await fetch(cfg.restUrl, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': cfg.nonce,
				},
				body: formData
			});

			const data = await res.json().catch(() => ({}));

			if (res.ok && data && data.success) {
				setStatus(data.message || cfg.i18n.enquirySent, 'success');
				form.reset();
				if (fileListText) fileListText.textContent = '';
				
				// Reset tiles to defaults
				vehicleTiles.forEach((t, i) => {
					t.classList.toggle('is-selected', i === 0);
					t.setAttribute('aria-checked', i === 0 ? 'true' : 'false');
				});
				serviceTiles.forEach((t) => {
					const isDefault = t.dataset.serviceSlug === 'vinyl-wraps';
					t.classList.toggle('is-selected', isDefault);
					t.setAttribute('aria-checked', isDefault ? 'true' : 'false');
				});
				serviceBlocks.forEach((block) => {
					const isDefault = block.dataset.serviceBlock === 'vinyl-wraps';
					block.classList.toggle('is-active', isDefault);
					if (isDefault) {
						block.removeAttribute('hidden');
					} else {
						block.setAttribute('hidden', '');
					}
				});

				updateSummary();

				if (data.redirect) {
					setTimeout(() => {
						window.location.assign(data.redirect);
					}, 1500);
				}
				return;
			}

			if (data && data.errors) {
				Object.keys(data.errors).forEach(markError);
			}
			setStatus((data && data.message) || cfg.i18n.error, 'error');
		} catch (err) {
			setStatus(cfg.i18n.error, 'error');
		} finally {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = btnText;
			}
		}
	});

	// Initialize summary on load
	updateSummary();
})();
