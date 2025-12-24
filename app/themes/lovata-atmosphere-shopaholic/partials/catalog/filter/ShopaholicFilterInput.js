import UrlGeneration from "@oc-shopaholic/url-generation";

export default class ShopaholicFilterInput {
  constructor({
                fieldName,
                inputSelector,
                inputName,
                numericOnly = false
              }) {
    this.fieldName = fieldName;
    this.inputSelector = inputSelector;
    this.inputName = inputName;
    this.numericOnly = numericOnly;
  }

  apply() {
    const inputNode = document.querySelector(
      `${this.inputSelector}[name="${this.inputName}"]`
    );
    if (!inputNode) return;

    let value = inputNode.value.trim();

    if (this.numericOnly) {
      value = parseInt(value.replace(/[^\d]/g, ''), 10);
    }

    if (!value || (this.numericOnly && value <= 0)) {
      UrlGeneration.remove(this.fieldName);
    } else {
      UrlGeneration.set(this.fieldName, [value]);
    }
  }
}
