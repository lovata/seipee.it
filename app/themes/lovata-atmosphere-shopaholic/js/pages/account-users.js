import Validation from '/js/vendor/validation';

class AccountUsers {
  constructor() {
    this.formSelector = '#create-user-form';
    this.formNode = document.querySelector(this.formSelector);
    this.buttonNode = this.formNode ? this.formNode.querySelector('button[type="submit"]') : null;

    this.deleteButtonsSelector = '.js-delete-user';
  }

  initHandler() {
    if (!this.formNode) {
      return;
    }

    const obThis = this;
    this.validation = new Validation(this.formSelector);
    this.validation.init();

    this.formNode.addEventListener('submit', async (event) => {
      event.preventDefault();
      const isValid = await this.validation.validate();
      if (!isValid) {
        return;
      }

      obThis.sendRequest();
    });
  }

  sendRequest() {
    this.buttonNode.setAttribute('disabled', 'disabled');
    const obThis = this;

    oc.request(this.formSelector, 'UserChildrenPage::onAjax', {
      complete: (response) => {
        obThis.buttonNode.removeAttribute('disabled');
      },
      afterUpdate: (response) => {
        if (!response?.X_OCTOBER_FLASH_MESSAGES) {
          const form = document.querySelector(this.formSelector);
          if (form) form.reset();

          const usersList = document.querySelector('#usersListContainer ul');
          if (usersList) {
            const lastUser = usersList.querySelector('li:last-child');
            if (lastUser) {
              lastUser.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
        }
      },
    });
  }

  initDeleteUser() {
    document.addEventListener('click', (event) => {
      const button = event.target.closest(this.deleteButtonsSelector);
      if (!button) return;

      event.preventDefault();

      const userId = button.dataset.userId;
      if (!userId) return;

      const confirmMessage =
        button.dataset.confirm || 'Delete user?';

      if (!confirm(confirmMessage)) {
        return;
      }

      this.sendDeleteRequest(button, userId);
    });
  }

  sendDeleteRequest(button, userId) {
    button.setAttribute('disabled', 'disabled');

    oc.request(null, 'onDeleteUser', {
      data: {
        user_id: userId,
      },
      complete: () => {
        button.removeAttribute('disabled');
      },
      success: () => {
        // можно удалить элемент из DOM
        const userItem = button.closest('li');
        userItem?.remove();
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obAccountUsers = new AccountUsers();

  obAccountUsers.initHandler();
  obAccountUsers.initDeleteUser();
});
