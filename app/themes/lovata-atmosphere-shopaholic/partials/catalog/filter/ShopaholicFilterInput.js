import UrlGeneration from "@oc-shopaholic/url-generation";

export default class ShopaholicFilterInput {
  constructor({
                fieldName,
                inputSelector,
                inputName,
                obProductListHelper = null,
                callBackDelay = 400,
                numericOnly = false
              }) {
    this.fieldName = fieldName;
    this.inputSelector = inputSelector;
    this.inputName = inputName;
    this.obProductListHelper = obProductListHelper;
    this.callBackDelay = callBackDelay;
    this.numericOnly = numericOnly;
  }

  /**
   * Init Listener
   */
  init() {
    const obThis = this;

    document.addEventListener('input', (event) => {
      const inputNode = event.target;
      if (!inputNode.matches(`${obThis.inputSelector}[name="${obThis.inputName}"]`)) return;

      if (obThis.numericOnly) {
        inputNode.value = inputNode.value.replace(/[^\d]/g, '');
      }

      clearTimeout(obThis.timer);
      obThis.timer = setTimeout(() => {
        obThis.updateUrl();
      }, obThis.callBackDelay);
    });
  }


  updateUrl() {
    const inputNode = document.querySelector(`${this.inputSelector}[name="${this.inputName}"]`);
    if (!inputNode) return;

    const value = this.numericOnly ? parseInt(inputNode.value) : inputNode.value.trim();

    UrlGeneration.init();
    if (!value || (this.numericOnly && value <= 0)) {
      UrlGeneration.remove(this.fieldName);
    } else {
      UrlGeneration.set(this.fieldName, [value]);
    }

    UrlGeneration.remove('page');
    UrlGeneration.update();

    if (this.obProductListHelper) {
      this.obProductListHelper.send();
    }
  }

  /**
   * Сеттеры
   */
  setFieldName(name) {
    this.fieldName = name;
    return this;
  }

  setInputSelector(selector) {
    this.inputSelector = selector;
    return this;
  }

  setInputName(name) {
    this.inputName = name;
    return this;
  }

  setCallBackDelay(ms) {
    this.callBackDelay = ms;
    return this;
  }

  setNumericOnly(flag) {
    this.numericOnly = flag;
    return this;
  }
}
