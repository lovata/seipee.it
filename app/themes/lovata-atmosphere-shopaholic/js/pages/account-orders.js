import '/partials/account/order-list/order-list';
import '/partials/common/accordion/accordion'

document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('._toggle_button');
    if (!toggle) return;

    const id = toggle.dataset.toggleId;
    const target = document.querySelector(`[data-toggle-target="${id}"]`);
    if (!target) return;

    target.classList.toggle('hidden');

    const icon = toggle.querySelector('._toggle_icon');
    if (icon) {
      icon.classList.toggle('bi-chevron-down');
      icon.classList.toggle('bi-chevron-up');
    }

    toggle.classList.toggle('is-open');
  });
});

