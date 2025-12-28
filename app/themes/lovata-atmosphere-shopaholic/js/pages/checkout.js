import '/partials/product/offer-quantity/offer-quantity';

/* * Checkout * */
import '/partials/checkout/checkout';
import '/partials/checkout/coupon/coupon';
import '/js/vendor/validation';

document.addEventListener('DOMContentLoaded', function () {
  const checkbox = document.getElementById('checkout_rules');
  const button = document.querySelector('._checkout-btn');

  function toggleButton() {
    button.disabled = !checkbox.checked;
  }

  checkbox.addEventListener('change', toggleButton);
});
