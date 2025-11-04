class OrderList {
  init() {
    const obThis = this;
    document.addEventListener('click', (event) => {
      const eventNode = event.target;
      const buttonNode = eventNode.closest('._show-more-orders');
      if (!buttonNode) {
        return;
      }

      obThis.sendAjax(buttonNode);
    });
  }

  sendAjax(buttonNode) {
    const activePage = parseInt(buttonNode.dataset.page, 10);
    const nextPage = activePage + 1;
    const maxPage = parseInt(buttonNode.dataset.maxPage, 10);
    buttonNode.setAttribute('disabled', 'disabled');

    oc.ajax('ProductData::onAjaxRequest', {
      data: {page: nextPage},
      update: {'account/order-list/order-list-ajax': `@._orders-list`},
      complete: () => {
        if (nextPage >= maxPage) {
          buttonNode.remove();
        } else {
          buttonNode.dataset.page = nextPage.toString();
          buttonNode.removeAttribute('disabled');
        }
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obOrderList = new OrderList();
  obOrderList.init();
});
