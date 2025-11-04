import ShopaholicCartRemove from '@oc-shopaholic/shopaholic-cart/shopaholic-cart-remove';
import {OffCanvasContainer} from "/partials/common/off-canvas/off-canvas";
import {FlashMessage} from "/partials/message/flash-message";

class ProductCardCheckout {

  hideIcon() {
    const stateBasketNode = document.querySelector('#product-active');
    if (!stateBasketNode) {
      return;
    }

    stateBasketNode.style = "visibility: hidden";
  }

  init() {
    const obShopaholicCartRemove = new ShopaholicCartRemove();

    const obThis = this;
    obShopaholicCartRemove.setAjaxRequestCallback((obRequestData, buttonNode) => {
      if (OffCanvasContainer.instance().find('header_cart')) {
        obRequestData.update = {'cart/cart-list-ajax': '._cart-list'};
      } else {
        obRequestData.update = {'main/header/header-ajax': '._header-purchases'};
      }

      const checkoutNode = document.querySelector('._checkout-list-wrapper');
      if (checkoutNode) {
        obRequestData.update['checkout/checkout-list-total-price'] = '._checkout-list-total-price';
        obRequestData.update['checkout/shipping-type-list'] = '._shipping_type_wrapper';
        obRequestData.update['checkout/checkout-subtotal'] = '._checkout-subtotal';
      }

      obRequestData.complete = (data) => {
        obShopaholicCartRemove.completeCallback(data, buttonNode);

        const obFlashMessage = new FlashMessage(window.messages.purchase_cart_remove_success, 'success');
        obFlashMessage.show();
      };

      return obRequestData;
    });

    obShopaholicCartRemove.init();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obProductCardCheckout = new ProductCardCheckout();
  obProductCardCheckout.init();
});

document.addEventListener('shopaholic-cart:update', (event) => {
  const obCartData = event.detail.cart;
  if (obCartData && obCartData.total_quantity > 0) {
    return;
  }

  const obProductCardCheckout = new ProductCardCheckout();
  obProductCardCheckout.hideIcon();
})
