import ShopaholicCart from "@oc-shopaholic/shopaholic-cart";

class Cart {
  init () {
    ShopaholicCart.instance();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obCart = new Cart();
  obCart.init();
});
