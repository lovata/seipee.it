class ProductListSmall {
  constructor() {
    this.buttonNode = document.querySelector('._product-list-small-button-more');
  }

  init() {
    if (!this.buttonNode) {
      return
    }

    const obThis = this;
    this.buttonNode.addEventListener('click', (event) => {
      obThis.buttonNode.setAttribute('disabled', 'disabled');

      const filter = JSON.parse(obThis.buttonNode.dataset.filter);
      const iPage = parseInt(obThis.buttonNode.dataset.page);
      const iMaxPage = parseInt(obThis.buttonNode.dataset.maxPage);
      const partial = obThis.buttonNode.dataset.partial;
      const updateConfig = {};
      updateConfig[`product/product-list-small/${partial}`] = '@._product-list-small';

      oc.ajax('onInit', {
        data: {
          ...filter,
          'page': iPage + 1,
        },
        update: updateConfig,
        complete: function () {
          if (iPage + 1 >= iMaxPage) {
            obThis.buttonNode.remove();
          } else {
            obThis.buttonNode.removeAttribute('disabled');
            obThis.buttonNode.dataset.page = iPage + 1;
          }
        }
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obProductListSmall = new ProductListSmall();
  obProductListSmall.init();
});
