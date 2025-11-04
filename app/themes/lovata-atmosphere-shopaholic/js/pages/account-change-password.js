import Validation from '/js/vendor/validation';

class ChangePassword {
  constructor() {
    this.formSelector = '#change-password';
    this.formNode = document.querySelector(this.formSelector);
    this.buttonNode = this.formNode ? this.formNode.querySelector('button[type="submit"]') : null;
  }

  initHandler() {
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
    const obThis = this;

    oc.request(this.formSelector, 'ChangePassword::onAjax', {
      complete: (response) => {
        obThis.buttonNode.removeAttribute('disabled');
        obThis.formNode.reset();
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obChangePassword = new ChangePassword();
  obChangePassword.initHandler();
});
