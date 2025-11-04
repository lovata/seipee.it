class ExpandableTextNode {

  constructor (buttonNode) {
    this.duration = 500;
    this.buttonNode = buttonNode;
    this.rows = this.buttonNode.dataset.rows * 1;
    this.wrapperNode = buttonNode.closest(".js-expandable-wrapper");
    this.textNode = this.wrapperNode.querySelector(".js-expandable-text");
    this.hideButtonText = this.buttonNode.dataset.hideButtonText;
    this.showButtonText = this.buttonNode.dataset.showButtonText;
    this.scrollHeight = this.textNode.scrollHeight;
    this.heightText = this.rows > 0 ? this.textNode.clientHeight : 0;
    this.initExpandableText();
  }

  initExpandableText () {
    this.initLineClampVisible();
    this.initButtonEvent();
  }

  initLineClampVisible () {
    this.textNode.dataset.maxHeight = this.scrollHeight + "px";

    if (this.rows > 0) {
      this.textNode.style.maxHeight = this.heightText + "px";
    }

    if (this.scrollHeight > this.heightText) {
      this.textNode.classList.add("hide");
      this.buttonNode.classList.add("hide");
    } else if (this.rows !== 0) {
      this.buttonNode.classList.add("hidden");
    }
  }

  initButtonEvent () {
    this.buttonNode.addEventListener("click", (event) => {
      const eventNode = event.target;
      if (this.buttonNode.classList.contains("hide")) {
        this.showText();
      } else {
        this.hideText();
      }
    });
  }

  showText () {
    if (this.rows > 0) {
      this.textNode.classList.remove(`line-clamp-${this.rows}`, "hide");
    }
    this.buttonNode.classList.remove("hide");
    this.textNode.classList.remove("hide");
    this.textNode.style.maxHeight = this.textNode.scrollHeight + "px";

    if (this.hideButtonText) {
      this.setTextButton(this.hideButtonText);
    }
  }

  hideText () {
    this.buttonNode.classList.add("hide");
    this.textNode.classList.add("hide");
    this.textNode.style.maxHeight = this.heightText + "px";

    if (this.hideButtonText) {
      this.setTextButton(this.showButtonText);
    }

    setTimeout(() => {
      this.textNode.classList.add(`line-clamp-${this.rows}`);
    }, this.duration);
  }

  setTextButton (buttonText) {
    const buttonTextNode = this.buttonNode.querySelector(".js-expandable-button-text");
    if (!buttonTextNode) {
      return;
    }

    buttonTextNode.innerText = buttonText;
  }
}

class ExpandableText {
  constructor () {
    this.initExpandable();
  }

  initExpandable () {
    const buttonNodeList = document.querySelectorAll(".js-expandable-button");
    if (!buttonNodeList || buttonNodeList.length === 0) {
      return;
    }

    buttonNodeList.forEach((buttonNode, key) => {
      new ExpandableTextNode(buttonNode);
    });
  }
}

export const { initExpandable } = new ExpandableText();
