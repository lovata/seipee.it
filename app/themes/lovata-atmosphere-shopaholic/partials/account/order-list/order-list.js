class OrderList {
  init() {
    const obThis = this;
    document.addEventListener('click', (event) => {
      const eventNode = event.target;
      const buttonNode = eventNode.closest('._show-more-orders');
      if (buttonNode) {
        obThis.sendAjax(buttonNode);
      }

      const orderAgainNode = eventNode.closest('._order_again_button');
      if (orderAgainNode) {
        this.sendOrderAgainRequest(orderAgainNode);
      }
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

  sendOrderAgainRequest(buttonNode) {
    const orderItemNode = buttonNode.closest('._order_item');
    const orderPositionNodes = orderItemNode ? orderItemNode.querySelectorAll('._order_position_list li') : [];
    if (!orderPositionNodes || orderPositionNodes.length === 0) {
      return;
    }

    buttonNode.setAttribute('disabled', 'disabled');
    const dataPositionRequest = [];
    orderPositionNodes.forEach(orderPositionNode => {
      dataPositionRequest.push({
        offer_id: parseInt(orderPositionNode.dataset.offerId),
        quantity: parseInt(orderPositionNode.dataset.quantity),
      });
    });

    oc.ajax('Cart::onSync', {
      data: {cart: dataPositionRequest},
      complete: () => {
        buttonNode.removeAttribute('disabled');
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obOrderList = new OrderList();
  obOrderList.init();
});
