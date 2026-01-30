class DocumentsList {
  init() {
    const obThis = this;
    document.addEventListener('click', (event) => {
      const eventNode = event.target;
      const buttonNode = eventNode.closest('._show-more-documents');

      if (buttonNode) {
        obThis.sendAjax(buttonNode);
      }
    });
  }

  sendAjax(buttonNode) {
    const activePage = parseInt(buttonNode.dataset.page, 10);
    const nextPage = activePage + 1;
    const maxPage = parseInt(buttonNode.dataset.maxPage, 10);
    buttonNode.setAttribute('disabled', 'disabled');

    oc.ajax('onLoadMoreDocuments', {
      data: {
        page: nextPage
      },
      update: {
        'account/documents-list/documents-list-ajax': '@._documents-list'
      },
      complete: () => {
        if (nextPage >= maxPage) {
          buttonNode.remove();
        } else {
          buttonNode.dataset.page = nextPage.toString();
          buttonNode.removeAttribute('disabled');
        }
      }
    });
  }

}

document.addEventListener('DOMContentLoaded', () => {
  const obDocumentsList = new DocumentsList();
  obDocumentsList.init();
});
