import Validation from '/js/vendor/validation';

class AccountUsers {
  constructor() {
    this.formSelector = '#create-user-form';
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
}

document.addEventListener('DOMContentLoaded', () => {
  const obAccountUsers = new AccountUsers();

  obAccountUsers.initHandler();
  /*
    const form = document.querySelector('#create-user-form');

    if (!form) return;

    document.querySelectorAll('.edit-user-btn').forEach(button => {
      button.addEventListener('click', () => {
        const firstName = button.dataset.first_name || '';
        const lastName = button.dataset.last_name || '';
        const email = button.dataset.email || '';
        const roleDepartment = button.dataset.role_department || '';
        const b2bPermission = button.dataset.b2b_permission == '1';

        form.querySelector('[name="first_name"]').value = firstName;
        form.querySelector('[name="last_name"]').value = lastName;
        form.querySelector('[name="email"]').value = email;
        form.querySelector('[name="role_department"]').value = roleDepartment;
        form.querySelector('[name="b2b_permission"]').checked = b2bPermission;

        form.scrollIntoView({ behavior: 'smooth' });
      });
    });*/
});
