import Bouncer from 'formbouncerjs';

export default class Validation {
  constructor(formSelector) {
    this.formSelector = formSelector;
    this.inValidSelector = '_invalid';
    this.isValid = true;
  }

  init() {
    this.validation = new Bouncer(this.formSelector, {
      fieldClass: 'validation-error',
      errorClass: 'validation-error__message text-red-700 text-sm',
      fieldPrefix: 'validation-error-',
      errorPrefix: 'validation-error-',
      messageAfterField: true,
      messageCustom: 'data-bouncer-message',
      messageTarget: 'data-bouncer-target',
      disableSubmit: true,
      messages: window.messages,
      customValidations: {
        valueMismatch: function (field) {
          const selector = field.getAttribute('data-bouncer-match');
          if (!selector) return false;
          const otherField = field.form.querySelector(selector);
          if (!otherField) return false;
          return otherField.value !== field.value;
        }
      },
    });

    document.addEventListener('bouncerFormValid', ({target}) => {
      this.isValid = true;
      target.classList.remove(this.inValidSelector);

    });
    document.addEventListener('bouncerFormInvalid', ({target}) => {
      this.isValid = false;
      target.classList.add(this.inValidSelector);
    });
  }

  validate() {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(this.isValid);
      }, 0);
    });
  }
}
