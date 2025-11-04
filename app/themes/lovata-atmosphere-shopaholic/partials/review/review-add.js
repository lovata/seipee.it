import {OffCanvasContainer} from '/partials/common/off-canvas/off-canvas'
import Validation from '/js/vendor/validation';

class AddReview {
  constructor() {
    this.formSelector = '#add-review-from';
    this.formNode = document.querySelector(this.formSelector);
    this.closeButtonNode = document.querySelector('._close_add_review');
  }

  init() {
    this.formHandler();
    this.closeHandler();
  }

  formHandler() {
    if (!this.formNode) {
      return;
    }

    this.validation = new Validation(this.formSelector);
    this.validation.init();

    this.formNode.addEventListener('submit', (event) => {
      event.preventDefault();
      this.sendRequest();
    });
  }

  async sendRequest(){
    const isValid = await this.validation.validate();
    if (!isValid) {
      return;
    }

    oc.request(this.formSelector, 'MakeReview::onCreate', {
      complete: (response) => {
        if (!response.status) {
          return;
        }

        this.formNode.classList.add('hidden');
        const messageNode = document.querySelector('._add_review_success');
        if (messageNode) {
          messageNode.classList.remove('hidden');
        }
      }
    });
  }

  closeHandler() {
    if (!this.closeButtonNode) {
      return;
    }

    this.closeButtonNode.addEventListener('click', () => {
      OffCanvasContainer.instance().close('review_add');
    })
  }
}

document.addEventListener('off-canvas:open', (event) => {
  if (event.detail.id !== 'review_add') {
    return;
  }

  const obAddReview = new AddReview();
  obAddReview.init();
});
