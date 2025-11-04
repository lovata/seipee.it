import ShopaholicCouponAdd from "@oc-shopaholic/shopaholic-coupon/shopaholic-coupon-add";
import ShopaholicCouponRemove from "@oc-shopaholic/shopaholic-coupon/shopaholic-coupon-remove";
import {FlashMessage} from "/partials/message/flash-message";

class Coupon {
  init() {
    this.initAddButtonHandler();
    this.initRemoveButtonHandler();
    this.changeCouponValue();
  }

  changeCouponValue() {
    const inputNode = document.querySelector("[name=\"coupon\"]");
    const buttonNode = document.querySelector(".js-shopaholic-coupon");
    if (!buttonNode || !inputNode) {
      return;
    }

    inputNode.addEventListener('input', () => {
      if (inputNode.value && buttonNode.disabled) {
        buttonNode.removeAttribute("disabled");
      }

      if (!inputNode.value && !buttonNode.disabled) {
        buttonNode.setAttribute("disabled", "disabled");
      }
    });
  }

  initAddButtonHandler() {
    const obShopaholicCouponAdd = new ShopaholicCouponAdd();
    obShopaholicCouponAdd.setAjaxRequestCallback((obRequestData, inputNode, buttonNode) => {
      obRequestData.update = this.getUpdateConfig();

      obRequestData.complete = (obResponse) => {
        obShopaholicCouponAdd.completeCallback(obResponse, inputNode, buttonNode);
        if (!obResponse || !obResponse.status) {
          const obFlashMessage = new FlashMessage(obResponse.message, "error");
          obFlashMessage.show();

          return;
        }

        inputNode.setAttribute("disabled", "disabled");
        buttonNode.classList.add("hidden");

        const removeButtonNode = document.querySelector("._shopaholic-coupon-remove");
        if (removeButtonNode) {
          removeButtonNode.classList.remove("hidden");
        }
      };

      return obRequestData;
    });

    obShopaholicCouponAdd.init();
  }

  initRemoveButtonHandler() {
    const obShopaholicCouponRemove = new ShopaholicCouponRemove();
    obShopaholicCouponRemove.setAjaxRequestCallback((obRequestData, inputNode, buttonNode) => {
      obRequestData.update = this.getUpdateConfig();

      obRequestData.complete = (obResponse) => {
        obShopaholicCouponRemove.completeCallback(obResponse, inputNode, buttonNode);
        if (!obResponse || !obResponse.status) {
          const obFlashMessage = new FlashMessage(obResponse.message, "error");
          obFlashMessage.show();

          return;
        }

        inputNode.removeAttribute("disabled", "disabled");
        buttonNode.classList.add("hidden");

        const addButtonNode = document.querySelector("._shopaholic-coupon-add");
        if (addButtonNode) {
          addButtonNode.classList.remove("hidden");
        }
      };

      return obRequestData;
    });

    obShopaholicCouponRemove.init();
  }

  getUpdateConfig() {
    return {
      "checkout/shipping-type-list": "._shipping_type_wrapper"
    };
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const obCoupon = new Coupon();
  obCoupon.init();
});
