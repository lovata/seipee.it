/*import Validation from '/js/vendor/validation';*/
import '/partials/common/map/map';

import {FlashMessage} from '/partials/message/flash-message';

class RequestQuotationPage {
  constructor() {
    this.formSelector = '#request-quotation-form';
    this.formNode = document.querySelector(this.formSelector);
  }

  init() {
    if (!this.formNode) {
      return;
    }

    /*this.validation = new Validation(this.formSelector);
    this.validation.init();*/
    this.formNode.addEventListener('submit', (event) => {
      event.preventDefault();
      this.submitForm();
    });
  }

  async submitForm() {
    /*const obThis = this;
    const isValid = await this.validation.validate();
    if (!isValid) {
      return;
    }

    oc.request(this.formSelector, 'genericForm::onFormSubmit', {
      method: 'POST',
      form: this.formNode,
      complete: (response, status) => {
        const responseContent = response['#genericForm_forms_flash'] ?? null;
        obThis.showMessage(responseContent, status === 200 ? 'success' : 'error');
        if (status === 200) {
          this.formNode.reset();
        }
      },
    });*/
    window.location.href = '/request-complete';
  }

  showMessage(responseContent, type) {
    if (!responseContent) {
      return;
    }

    const templateNode = document.createElement('template');
    templateNode.innerHTML = responseContent;
    const responseNode = templateNode.content.firstChild;

    const messageNode = responseNode.querySelector('p');
    let messageNodeList = [];
    if (messageNode) {
      messageNodeList.push(messageNode);
    }

    messageNodeList = [...messageNodeList, ...responseNode.querySelectorAll('li')];
    if (!messageNodeList || messageNodeList.length === 0) {
      return;
    }

    messageNodeList.forEach((messageNode) => {
      const message = new FlashMessage(messageNode.textContent, type);
      message.show();
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const formHandler = new RequestQuotationPage();
  formHandler.init();
});
