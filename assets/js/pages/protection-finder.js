/**
 * Protection Finder wizard.
 *
 * Single-select per question, client-side navigation between question cards
 * (no reload), live "Your Build" rail, then a REST call to
 * detailking/v1/protection-finder on the 5th answer for the real scoring
 * result. Mirrors booking-widget.js's fetch/nonce/error-handling shape.
 */
(function () {
   'use strict';

   var config = window.DetailKingProtectionFinder || {};
   var card = document.getElementById('pf-card');
   if (!card) {
      return;
   }

   var total = parseInt(card.dataset.pfTotal, 10) || 5;
   var answers = {};
   var current = 0;

   var questions = card.querySelectorAll('[data-pf-question]');
   var progressSegs = card.querySelectorAll('[data-pf-progress-seg]');
   var railValues = card.parentElement.querySelectorAll('[data-pf-rail-value]');
   var railProgress = card.parentElement.querySelector('[data-pf-rail-progress]');
   var railProgressFill = card.parentElement.querySelector('[data-pf-rail-progress-fill]');
   var railProgressBar = card.parentElement.querySelector('[data-pf-rail-progressbar]');
   var railTitle = card.parentElement.querySelector('[data-pf-rail-title]');
   var railFootnote = card.parentElement.querySelector('[data-pf-rail-footnote]');
   var resultEl = card.querySelector('[data-pf-result]');
   var resultInner = card.querySelector('[data-pf-result-inner]');
   var startOverBtn = card.querySelector('[data-pf-start-over]');
   var resultTemplate = document.getElementById('pf-result-template');

   function updateProgress() {
      var answeredCount = Object.keys(answers).length;
      progressSegs.forEach(function (seg, i) {
         seg.classList.toggle('is-filled', i < answeredCount);
      });
      var progressPct = Math.round((answeredCount / total) * 100);
      if (railProgress) {
         railProgress.textContent = progressPct + '%';
      }
      if (railProgressFill) {
         railProgressFill.style.width = progressPct + '%';
      }
      if (railProgressBar) {
         railProgressBar.setAttribute('aria-valuenow', String(progressPct));
      }
      if (railFootnote) {
         var remaining = total - answeredCount;
         railFootnote.textContent = remaining > 0
            ? (answeredCount === 0
               ? "Answer the questions and we'll reveal your ideal protection."
               : 'Keep going — ' + remaining + (remaining === 1 ? ' question' : ' questions') + ' to go.')
            : '';
      }
   }

   function updateRailValue(key, label) {
      railValues.forEach(function (el) {
         if (el.getAttribute('data-pf-rail-value') === key) {
            el.textContent = label;
         }
      });
   }

   function showQuestion(index) {
      questions.forEach(function (q, i) {
         q.classList.toggle('is-active', i === index);
      });
      card.querySelector('[data-pf-progress]').setAttribute('aria-valuenow', String(index));
   }

   card.addEventListener('click', function (evt) {
      var optionInput = evt.target.closest('[data-pf-option]');
      if (optionInput) {
         var questionEl = optionInput.closest('[data-pf-question]');
         var key = questionEl.getAttribute('data-pf-key');
         var label = optionInput.closest('.pf-option').querySelector('strong').textContent;
         answers[key] = optionInput.value;
         updateRailValue(key, label);
         updateProgress();
         return;
      }

      var nextBtn = evt.target.closest('[data-pf-next]');
      if (nextBtn) {
         var qEl = nextBtn.closest('[data-pf-question]');
         var qKey = qEl.getAttribute('data-pf-key');
         if (!answers[qKey]) {
            qEl.classList.add('pf-question--nudge');
            window.setTimeout(function () { qEl.classList.remove('pf-question--nudge'); }, 400);
            return;
         }

         if (current < total - 1) {
            current++;
            showQuestion(current);
         } else {
            submitAnswers();
         }
         return;
      }

      if (evt.target.closest('[data-pf-start-over]')) {
         resetWizard();
      }
   });

   function resetWizard() {
      answers = {};
      current = 0;
      card.querySelectorAll('[data-pf-option]').forEach(function (input) { input.checked = false; });
      railValues.forEach(function (el) { el.textContent = '—'; });
      if (railTitle) {
         railTitle.textContent = 'BUILDING YOUR MATCH…';
      }
      updateProgress();
      showQuestion(0);
      card.classList.remove('is-result');
   }

   function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
   }

   function submitAnswers() {
      card.classList.add('is-loading');

      fetch(config.restUrl, {
         method: 'POST',
         headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': config.nonce
         },
         body: JSON.stringify(answers)
      })
         .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
         .then(function (result) {
            card.classList.remove('is-loading');
            if (!result.ok || !result.data.success) {
               window.alert((result.data && result.data.message) || 'Something went wrong. Please try again.');
               return;
            }
            renderResult(result.data);
         })
         .catch(function () {
            card.classList.remove('is-loading');
            window.alert('Something went wrong. Please try again.');
         });
   }

   function renderResult(data) {
      card.classList.add('is-result');
      questions.forEach(function (q) { q.classList.remove('is-active'); });

      if (railTitle) {
         railTitle.textContent = data.service.title;
      }
      if (railFootnote) {
         railFootnote.textContent = '';
      }

      var featuresHtml = (data.service.features || [])
         .map(function (f) {
            return '<span class="pf-result__feature"><span class="pf-result__feature-check" aria-hidden="true">&#10003;</span>' + escapeHtml(f) + '</span>';
         })
         .join('');

      var priceHtml = data.service.fromPrice
         ? '<span>From</span><strong>$' + data.service.fromPrice + '</strong>'
         : '';

      var runnerHtml = data.runnerUp
         ? 'Also worth considering: <a href="' + escapeHtml(data.runnerUp.permalink) + '">' + escapeHtml(data.runnerUp.title) + '</a>'
         : '';

      var html = resultTemplate.innerHTML
         .replace('{{matchPct}}', String(data.matchPct))
         .replace('{{title}}', escapeHtml(data.service.title))
         .replace('{{teaser}}', escapeHtml(data.service.teaser))
         .replace('{{features}}', featuresHtml)
         .replace('{{priceRow}}', priceHtml)
         .replace('{{permalink}}', escapeHtml(data.service.permalink))
         .replace('{{runnerUp}}', runnerHtml);

      resultInner.innerHTML = html;
   }

   updateProgress();
})();
