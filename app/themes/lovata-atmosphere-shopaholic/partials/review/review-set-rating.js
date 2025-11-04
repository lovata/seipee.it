class ReviewSetRating {
  constructor() {
    this.wrapperNode = document.querySelector('._container-star-review');

    this.activeRating = 0;

    this.activeStarColor = '#F59E0B';
    this.defaultStarColor = '#D1D5DB';

    this.mouseOutTimeout = null;
    this.mouseOverTimeout = null;
  }

  init() {
    if (!this.wrapperNode) {
      return;
    }

    this.setStarsState(this.activeRating);
    this.clickHandler();
    this.mouseLeaveHandler();
    this.mouseEnterHandler();
  }

  setStarsState(count) {
    if (!this.wrapperNode) {
      return;
    }

    const svgNodeList = this.wrapperNode.querySelectorAll('path');
    svgNodeList.forEach((svgNode, i) => {
      const labelNode = svgNode.closest('label');
      svgNode.style.fill = !labelNode || parseInt(labelNode.dataset.rating) > count ?  this.defaultStarColor : this.activeStarColor;
    })
  }

  clickHandler() {
    const inputNodeList = this.wrapperNode.querySelectorAll('._input-wrapper-star-review');
    if (!inputNodeList) {
      return;
    }

    const obThis = this;
    inputNodeList.forEach((inputNode) => {
      inputNode.addEventListener('click', (event) => {
        const eventNode = event.target;
        const inputWrapperNode = eventNode.closest('._input-wrapper-star-review');
        const labelNode = inputWrapperNode ? inputWrapperNode.querySelector('label') : null;

        obThis.activeRating = labelNode ? labelNode.dataset.rating : 0;
        obThis.setStarsState(obThis.activeRating);
      })
    });
  }

  mouseLeaveHandler() {
    const obThis = this;
    this.wrapperNode.addEventListener('mouseleave', (event) => {
      const eventNode = event.target;
      if (!eventNode.classList.contains('_container-star-review')) {
        return;
      }

      if (obThis.mouseOutTimeout) {
        clearTimeout(obThis.mouseOutTimeout);
      }

      obThis.mouseOutTimeout = setTimeout(() => {
        obThis.setStarsState(obThis.activeRating > 0 ? obThis.activeRating : 0);
      }, 200);
    });
  }

  mouseEnterHandler() {
    const obThis = this;
    const labelNodeList = this.wrapperNode.querySelectorAll('label');

    labelNodeList.forEach((labelNode) => {
      labelNode.addEventListener('mouseenter', (event) => {
        if (obThis.mouseOverTimeout) {
          clearTimeout(obThis.mouseOverTimeout);
        }

        obThis.mouseOverTimeout = setTimeout(() => {
          obThis.setStarsState(labelNode ? labelNode.dataset.rating : 0);
        }, 20);
      });
    });
  }
}

document.addEventListener('off-canvas:open', (event) => {
  if (event.detail.id !== 'review_add') {
    return;
  }

  const obReviewSetRating = new ReviewSetRating();
  obReviewSetRating.init();
});
