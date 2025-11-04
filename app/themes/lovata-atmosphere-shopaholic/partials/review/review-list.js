import Choices from 'choices.js';

class Reviews {
  initChoices() {
    const sortingNode = document.querySelector('._sorting-choice');
    if (!sortingNode) {
      return;
    }

    const choices = new Choices('._sorting-choice', {
      searchEnabled: false,
      searchChoices: false,
      allowHTML: false,
      itemSelectText: '',
      classNames: {
        containerOuter: 'choices w-full md:w-auto',
        containerInner: 'pe-4',
        listDropdown: 'js-choice__dropdown',
        itemChoice: 'choices__item--choice text-gray-700 text-sm'
      },
      callbackOnCreateTemplates: function (template) {
        return {
          item: ({classNames}, data) => {
            const wrapperNode = sortingNode.closest('._sorting-reviews');

            return template(`
                  <div class="${classNames.item}
                    ${data.highlighted ? classNames.highlightedState : classNames.itemSelectable}
                    ${data.placeholder ? classNames.placeholder : ''}text-gray-600 text-base"
                    data-item data-id="${data.id}"
                    data-value="${data.value}"
                    ${data.active ? 'aria-selected="true"' : ''}
                    ${data.disabled ? 'aria-disabled="true"' : ''}>
                  <span class="pe-2">${wrapperNode ? wrapperNode.dataset.activeText : ''}:</span> ${data.label}
                  </div>
              `);
          },
        };
      },
    });
  }

  initMoreButton() {
    const buttonNode = document.querySelector('._show-more-reviews');
    if (!buttonNode) {
      return;
    }

    buttonNode.addEventListener('click', () => {
      const iPage = parseInt(buttonNode.dataset.page, 10);
      const iNextPage = iPage + 1;
      const iMaxPage = parseInt(buttonNode.dataset.maxPage, 10);
      if (iNextPage <= iMaxPage) {
        oc.ajax('onAjax', {
          data: {page: iNextPage},
          update: {'review/review-list-ajax': `@._review-list`}
        });
      }

      if (iNextPage >= iMaxPage) {
        buttonNode.remove();
      } else {
        buttonNode.setAttribute('data-page', iNextPage.toString());
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obReviews = new Reviews();
  obReviews.initChoices();
});

document.addEventListener('off-canvas:open', (event) => {
  if (event.detail.id !== 'review_info') {
    return;
  }

  const obReviews = new Reviews();
  obReviews.initMoreButton();
});
