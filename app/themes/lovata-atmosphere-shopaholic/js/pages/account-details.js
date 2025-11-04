import Validation from '/js/vendor/validation';

class AccountDetails {
  constructor() {
    this.formSelector = '#account-details';
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
      obThis.sendUpdateAddressRequest();
    });
  }

  sendRequest() {
    this.buttonNode.setAttribute('disabled', 'disabled');
    const obThis = this;

    oc.request(this.formSelector, 'UserPage::onAjax', {
      complete: (response) => {
        obThis.buttonNode.removeAttribute('disabled');
      },
    });
  }

  sendUpdateAddressRequest() {
    const inputIDNode = this.formNode.querySelector('[name="address_id"]');
    const inputCountryNode = this.formNode.querySelector('[name="country"]');
    const inputCityNode = this.formNode.querySelector('[name="city"]');
    const inputPostcodeNode = this.formNode.querySelector('[name="postcode"]');
    const inputAddressNode = this.formNode.querySelector('[name="address"]');

    oc.ajax('UserAddress::onUpdate', {
      data: {
        id: inputIDNode ? inputIDNode.value : null,
        country: inputCountryNode ? inputCountryNode.value : null,
        city: inputCityNode ? inputCityNode.value : null,
        postcode: inputPostcodeNode ? inputPostcodeNode.value : null,
        address1: inputAddressNode ? inputAddressNode.value : null,
      },
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obAccountDetails = new AccountDetails();

  obAccountDetails.initHandler();
});
