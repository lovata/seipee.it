import UrlGeneration from "@oc-shopaholic/url-generation";

export default class ShopaholicFilterQuantity {

  constructor(obProductListHelper = null) {
    this.obProductListHelper = obProductListHelper;

    this.eventType = 'input';
    this.fieldName = 'quantity';
    this.inputName = 'filter-quantity';
    this.defaultInputClass = '_shopaholic-qty-filter';
    this.inputSelector = `.${this.defaultInputClass}`;

    this.callBackDelay = 400;
  }

  /**
   * Init listeners
   */
  init() {
    const obThis = this;

    document.addEventListener(this.eventType, (event) => {

      const inputNode = event.target.closest(obThis.inputSelector);

      if (!inputNode) return;

      clearTimeout(obThis.timer);
      obThis.timer = setTimeout(() => {
        obThis.quantityChanged();
      }, obThis.callBackDelay);
    });

    document.addEventListener('input', (event) => {
      const inputNode = event.target.closest(obThis.inputSelector);
      if (!inputNode) return;

      inputNode.value = inputNode.value.replace(/[^\d]/g, '');
    });
  }

  /**
   * Запуск обновления URL и списка
   */
  quantityChanged() {
    UrlGeneration.init();
    this.prepareRequestData();
    UrlGeneration.remove('page');
    UrlGeneration.update();

    if (this.obProductListHelper) {
      this.obProductListHelper.send();
    }
  }

  /**
   * Чтение одного поля + запись в URL
   */
  prepareRequestData() {
    const inputNode = document.querySelector(
      `${this.inputSelector}[name="${this.inputName}"]`
    );

    if (!inputNode) return;

    const value = inputNode.value ? parseInt(inputNode.value) : null;

    if (!value || value <= 0) {
      UrlGeneration.remove(this.fieldName);
      return;
    }

    UrlGeneration.set(this.fieldName, [value]);
  }

  /**
   * Setters
   */
  setInputSelector(selector) {
    this.inputSelector = selector;
    return this;
  }

  setInputName(name) {
    this.inputName = name;
    return this;
  }

  setEventType(type) {
    this.eventType = type;
    return this;
  }

  setFieldName(name) {
    this.fieldName = name;
    return this;
  }
}
