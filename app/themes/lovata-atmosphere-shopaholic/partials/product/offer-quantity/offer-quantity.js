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
});
