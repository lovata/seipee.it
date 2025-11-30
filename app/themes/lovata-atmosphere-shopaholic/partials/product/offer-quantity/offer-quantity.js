import ShopaholicCartUpdate from '@oc-shopaholic/shopaholic-cart/shopaholic-cart-update';
import {OffCanvasContainer} from "/partials/common/off-canvas/off-canvas";

document.addEventListener('DOMContentLoaded', () => {
  const obShopaholicCartUpdate = new ShopaholicCartUpdate();
  obShopaholicCartUpdate.setAjaxRequestCallback((obRequestData, inputNode) => {
    obRequestData.update = {};

    // If cart popup is opened, then update cart partials
    if (OffCanvasContainer.instance().find('header_cart')) {
      obRequestData.update['cart/cart-list-total-price'] = '._cart_list_total_price';
    }

    // If current page is "checkout", then update checkout partials
    const checkoutNode = document.querySelector('._checkout-list-wrapper');
    if (checkoutNode) {
      obRequestData.update['checkout/checkout-list-total-price'] = '._checkout-list-total-price';
      obRequestData.update['checkout/shipping-type-list'] = '._shipping_type_wrapper';
      obRequestData.update['checkout/checkout-subtotal'] = '._checkout-subtotal';
    }

    return obRequestData;
  });

  obShopaholicCartUpdate.init();

  const availableQuantityEl = document.querySelector('#available-quantity');

  if (availableQuantityEl) {
    const maxQuantity = parseInt(availableQuantityEl.dataset.quantity, 10);

    const quantityInput = document.querySelector('[name="quantity"]');

    if (quantityInput) {
      const checkQuantity = () => {
        const value = parseInt(quantityInput.value, 10);
        availableQuantityEl.classList.remove('bg-green-500', 'bg-orange-500');

        if (!isNaN(value)) {
          if (value > maxQuantity) {
            availableQuantityEl.classList.add('bg-orange-500');
          } else {
            availableQuantityEl.classList.add('bg-green-500');
          }
        }
      };

      quantityInput.addEventListener('input', checkQuantity);

      const decreaseBtn = document.querySelector('._shopaholic-cart-decrease-quantity');
      const increaseBtn = document.querySelector('._shopaholic-cart-increase-quantity');

      if (decreaseBtn) {
        decreaseBtn.addEventListener('click', () => {
          setTimeout(checkQuantity, 0);
        });
      }

      if (increaseBtn) {
        increaseBtn.addEventListener('click', () => {
          setTimeout(checkQuantity, 0);
        });
      }
    }
  }

  const checkboxes = document.querySelectorAll('input[name="variants[]"]');
  const defaultProducts = document.querySelectorAll('._default-product');
  const customProducts = document.querySelectorAll('._custom-product');

  checkboxes.forEach(cb => {
    cb.addEventListener("change", () => {
      const anyChecked = [...checkboxes].some(c => c.checked);

      if (anyChecked) {
        defaultProducts.forEach(el => {
          if (!el.classList.contains('hidden')) {
            el.classList.add('hidden');
          }
        });

        customProducts.forEach(el => {
          if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
          }
        });
      } else {
        defaultProducts.forEach(el => el.classList.remove('hidden'));
        customProducts.forEach(el => el.classList.add('hidden'));
      }
    });
  });

});
