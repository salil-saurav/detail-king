/**
 * Detail King — auth.js
 * Password visibility toggling and interactive enhancements for auth pages.
 */

document.addEventListener('DOMContentLoaded', () => {
   const toggleButtons = document.querySelectorAll('[data-auth-toggle]');

   toggleButtons.forEach((btn) => {
      btn.addEventListener('click', (e) => {
         e.preventDefault();
         const targetSelector = btn.getAttribute('data-auth-toggle');
         if (!targetSelector) return;

         const input = document.querySelector(targetSelector);
         if (!input) return;

         const isPassword = input.type === 'password';
         input.type = isPassword ? 'text' : 'password';

         const eyeShow = btn.querySelector('.auth-eye-show');
         const eyeHide = btn.querySelector('.auth-eye-hide');

         if (eyeShow && eyeHide) {
            if (isPassword) {
               eyeShow.style.display = 'none';
               eyeHide.style.display = 'block';
            } else {
               eyeShow.style.display = 'block';
               eyeHide.style.display = 'none';
            }
         }
      });
   });
});
