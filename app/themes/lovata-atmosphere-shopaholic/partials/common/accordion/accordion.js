class Accordion {
  constructor(summaryNode) {
    this.summaryNode = summaryNode;
    this.detailNode = this.summaryNode.closest('.js-details');
    this.contentNode = this.detailNode.querySelector('.js-accordion-content');

    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;
  }

  onClick() {
    this.detailNode.style.overflow = 'hidden';
    if (this.isClosing || !this.detailNode.open) {
      this.open();
    } else if (this.isExpanding || this.detailNode.open) {
      this.shrink();
    }
  }

  shrink() {
    this.isClosing = true;
    const startHeight = `${this.detailNode.offsetHeight}px`;
    const endHeight = `${this.summaryNode.offsetHeight}px`;
    if (this.animation) {
      this.animation.cancel();
    }

    this.animation = this.detailNode.animate({
      height: [startHeight, endHeight]
    }, {
      duration: 400,
      easing: 'linear',
    });

    document.dispatchEvent(new CustomEvent('accordion:close', {
      detail: {
        detail: this.detailNode,
        summary: this.summaryNode,
        content: this.contentNode,
      }
    }));

    this.animation.onfinish = () => {
      this.onAnimationFinish(false);
    };
    this.animation.oncancel = () => {
      this.isClosing = false;
    };
  }

  open() {
    this.detailNode.style.height = `${this.detailNode.offsetHeight}px`;
    this.detailNode.open = true;
    window.requestAnimationFrame(() => this.expand());
  }

  expand() {
    this.isExpanding = true;
    const startHeight = `${this.detailNode.offsetHeight}px`;
    const endHeight = `${this.summaryNode.offsetHeight + this.contentNode.offsetHeight}px`;

    if (this.animation) {
      this.animation.cancel();
    }

    this.animation = this.detailNode.animate({
      height: [startHeight, endHeight]
    }, {
      duration: 400,
      easing: 'linear',
    });

    document.dispatchEvent(new CustomEvent('accordion:open', {
      detail: {
        detail: this.detailNode,
        summary: this.summaryNode,
        content: this.contentNode,
      }
    }));

    this.animation.onfinish = () => {
      this.onAnimationFinish(true);
    };
    this.animation.oncancel = () => {
      this.isExpanding = false;
    };
  }

  onAnimationFinish(open) {
    this.detailNode.open = open;
    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;
    this.detailNode.style.height = this.detailNode.style.overflow = "";

    document.dispatchEvent(new CustomEvent(`accordion:${open ? 'opened' : 'closed'}`, {
      detail: {
        detail: this.detailNode,
        summary: this.summaryNode,
        content: this.contentNode,
      }
    }));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', (event) => {
    const eventNode = event.target;
    const summaryNode = eventNode.closest('.js-accordion-summary');
    if (!summaryNode) {
      return;
    }

    event.preventDefault();

    const obAccordion = new Accordion(summaryNode);
    obAccordion.onClick();
  });
});
