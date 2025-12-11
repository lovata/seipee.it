import ShopaholicCartAdd from '@oc-shopaholic/shopaholic-cart/shopaholic-cart-add';
import {FlashMessage} from "/partials/message/flash-message";

class OfferChoose {
  loadPrice() {
    const offerInput = document.querySelector('[name="offer_id"]');
    if (!offerInput) {
      return;
    }

    const obThis = this;
    oc.ajax('onAjax', {
      data: {
        load_price: true,
        offer_id: offerInput.value,
      },
      update: {
        'product/offer-choose/offer-price': '._offer-price-wrapper',
      },
      complete: () => {
        obThis.updateDisabledState();
      }
    });
  }
  initOfferSelectHandler() {
    document.addEventListener('change', (event) => {
      const eventNode = event.target;
      const offerInput = eventNode.closest('[name="offer_id"]');
      if (!offerInput) {
        return;
      }

      const updateList = {
        'product/product-tabs/product-tab-details-ajax': '._product_tab_details',
        'product/offer-choose/offer-price': '._offer-price-wrapper',
      }
      const productWrapperNode = offerInput.closest('._shopaholic-product-wrapper');
      const hasOfferImage = productWrapperNode && productWrapperNode.dataset.hasOfferImage;
      if (hasOfferImage) {
        updateList['product/product-gallery/product-gallery-ajax'] = '.product-gallery-wrapper';
      }

      oc.ajax('onAjax', {
        data: {
          offer_id: offerInput.value,
        },
        update: updateList,
        complete: () => {
          if (hasOfferImage) {
            document.dispatchEvent(new CustomEvent('product:gallery.init'));
          }
        }
      });
    });
  }

  initPropertySelectHandler() {
    document.addEventListener('change', (event) => {
      const eventNode = event.target;
      const wrapperNode = eventNode.closest('._offer-choose-property');
      if (!wrapperNode) {
        return;
      }

      const inputList = document.querySelectorAll('._offer-choose-property-input');
      const valueList = {};
      inputList.forEach((inputNode) => {
        if (!inputNode.value) {
          return;
        }

        valueList[inputNode.dataset.propertyId] = inputNode.value;
      });

      const obThis = this;
      oc.ajax('onAjax', {
        data: {
          property: valueList,
        },
        update: {
          'product/offer-choose/offer-choose-property-list': '._offer-choose-property-list'
        },
        complete: () => {
          obThis.updateDisabledState();
          const offerSelect = document.querySelector('#offer_id');
          if (!offerSelect) {
            return;
          }

          offerSelect.dispatchEvent(
            new InputEvent("change", {
                bubbles: true,
                cancelable: true
              }
            ));
        }
      });
    });
  }

  updateDisabledState() {
    const offerSelect = document.querySelector('#offer_id');
    const addButtonNode = document.querySelector('._shopaholic-cart-add');
    const priceNode = document.querySelector('._product_price_loaded');
    if (!addButtonNode) {
      return;
    }

    if (offerSelect && priceNode) {
      addButtonNode.removeAttribute('disabled');
    } else {
      addButtonNode.setAttribute('disabled', 'disabled');
    }
  }

  initAddToCartHandler(){

    const shopaholicCartAdd = new ShopaholicCartAdd();
    shopaholicCartAdd.setAjaxRequestCallback((requestData, button) => {

      requestData.update = {'main/header/header-ajax': '._header-purchases'};
      requestData.complete = (data) => {
        shopaholicCartAdd.completeCallback(data, button);

        const obFlashMessage = new FlashMessage(window.messages.purchase_cart_add_success, 'success');
        obFlashMessage.show();
      };

      return requestData;
    }).init();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obOfferChoose = new OfferChoose();
  obOfferChoose.initOfferSelectHandler();
  obOfferChoose.initPropertySelectHandler();
  obOfferChoose.updateDisabledState();
  obOfferChoose.initAddToCartHandler();
  obOfferChoose.loadPrice();
});
