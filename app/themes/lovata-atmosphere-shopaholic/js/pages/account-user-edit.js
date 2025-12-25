import Validation from '/js/vendor/validation';

class AccountUserEdit {
  constructor() {
    this.formSelector = '#edit-user-form';
    this.formNode = document.querySelector(this.formSelector);
    this.buttonNode = this.formNode
      ? this.formNode.querySelector('button[type="submit"]')
      : null;
  }

  initHandler() {
    if (!this.formNode) return;

    this.validation = new Validation(this.formSelector);
    this.validation.init();

    this.formNode.addEventListener('submit', async (event) => {
      event.preventDefault();

      const isValid = await this.validation.validate();
      if (!isValid) return;

      this.sendUpdateRequest();
    });
  }

  sendUpdateRequest() {
    if (!this.buttonNode) return;

    this.buttonNode.setAttribute('disabled', 'disabled');

    oc.request(this.formSelector, 'onUpdateUser', {
      complete: () => {
        this.buttonNode.removeAttribute('disabled');
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obAccountUserEdit = new AccountUserEdit();
  obAccountUserEdit.initHandler();
});
