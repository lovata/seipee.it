import ShopaholicCartShippingType from '@oc-shopaholic/shopaholic-cart/shopaholic-cart-shipping-type';
import ShopaholicCartPaymentMethod from '@oc-shopaholic/shopaholic-cart/shopaholic-cart-payment-method';
import ShopaholicOrder from '@oc-shopaholic/shopaholic-cart/shopaholic-order';
import { initExpandable } from '/partials/common/expandable-text/expandable-text';
import Validation from '/js/vendor/validation';

class Checkout {
  constructor() {
    this.formSelector = '#make-order';
    this.formNode = document.querySelector(this.formSelector);
    this.buttonNode = this.formNode ? this.formNode.querySelector('button[type="submit"]') : null;
  }

  init() {
    this.initShippingTypeHandler();
    this.initPaymentMethodHandler();
    this.shippingTypeTermsHandler();
    this.initMakeOrderHandler();
  }

  initShippingTypeHandler() {
    const obShopaholicCartShippingType = new ShopaholicCartShippingType();
    obShopaholicCartShippingType.setAjaxRequestCallback((obRequestData, inputNode) => {
      obRequestData.update = {
        'checkout/shipping-type-list': '._shipping_type_wrapper',
        'checkout/payment-method-list': '._payment-method-wrapper',
        'checkout/checkout-subtotal': '._checkout-subtotal',
      }
      obRequestData.complete = (response) => {
        initExpandable();
        obShopaholicCartShippingType.completeCallback(response);
      };

      return obRequestData;
    });

    obShopaholicCartShippingType.init();
  }

  initPaymentMethodHandler() {
    const obShopaholicCartPaymentMethod = new ShopaholicCartPaymentMethod();
    obShopaholicCartPaymentMethod.setAjaxRequestCallback((obRequestData, inputNode) => {
      obRequestData.update = {
        'checkout/shipping-type-list': '._shipping_type_wrapper',
        'checkout/payment-method-list': '._payment-method-wrapper',
        'checkout/checkout-subtotal': '._checkout-subtotal',
      }
      obRequestData.complete = (response) => {
        initExpandable();
        obShopaholicCartPaymentMethod.completeCallback(response);
      };

      return obRequestData;
    });

    obShopaholicCartPaymentMethod.init();
  }

  shippingTypeTermsHandler() {
    document.addEventListener('click', (event) => {
      const eventNode = event.target;
      const buttonNode = eventNode.closest('._delivery-terms');
      if (!buttonNode) {
        return;
      }

      const infoNode = document.querySelector('._delivery-info');
      const toggleNode = document.querySelector('._delivery-toggle');
      if (!infoNode || !toggleNode) {
        return;
      }

      if (infoNode.classList.contains('hidden')) {
        infoNode.classList.remove('hidden');
        toggleNode.classList.remove('rotate-180');
        buttonNode.setAttribute('aria-expanded', true);
      } else {
        infoNode.classList.add('hidden');
        toggleNode.classList.add('rotate-180');
        buttonNode.setAttribute('aria-expanded', false);
      }
    });
  }

  initMakeOrderHandler() {
    if (!this.formNode) {
      return;
    }

    const obThis = this;
    this.validation = new Validation(this.formSelector);
    this.validation.init();

    this.formNode.addEventListener('submit', (event) => {
      event.preventDefault();
      obThis.sendRequest();
    });
  }

  async sendRequest() {
    const isValid = await this.validation.validate();
    if (!isValid) {
      return;
    }

    this.buttonNode.setAttribute('disabled', 'disabled');

    const obShopaholicOrder = new ShopaholicOrder();
    obShopaholicOrder.setAjaxRequestCallback((obRequestData) => {
      obRequestData.complete = () => {
        this.buttonNode.removeAttribute('disabled');
      };

      return obRequestData;
    });

    obShopaholicOrder.create();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obCheckout = new Checkout();
  obCheckout.init();
});
