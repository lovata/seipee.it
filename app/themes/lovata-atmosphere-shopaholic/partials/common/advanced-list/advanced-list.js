class AdvancedList {
  constructor() {
  }

  init() {
    const buttonShowMoreNodeList = document.querySelectorAll('._advanced-list-button-show-all');
    if (!buttonShowMoreNodeList || buttonShowMoreNodeList.length === 0) {
      return;
    }

    buttonShowMoreNodeList.forEach((buttonShowMoreNode) => {
      buttonShowMoreNode.addEventListener('click', () => {
        buttonShowMoreNode.classList.add('hidden');
        const mainWrapperNode = buttonShowMoreNode.closest('section');
        const wrapperNode = mainWrapperNode ? mainWrapperNode.querySelector('._advanced-list') : null;
        const buttonShowMoreHiddenNode = mainWrapperNode ? mainWrapperNode.querySelector('._advanced-list-button-show-all-hidden') : null;
        if (!wrapperNode) {
          return;
        }

        buttonShowMoreHiddenNode.classList.remove('hidden');

        setTimeout(() => {
          const advancedNodeList = wrapperNode.querySelectorAll('li.hidden')
          advancedNodeList.forEach(advancedNode => {
            advancedNode.removeAttribute('aria-hidden');
            advancedNode.classList.remove('hidden');
          })

          buttonShowMoreHiddenNode.classList.add('hidden');
        }, 300);
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obAdvancedList = new AdvancedList();
  obAdvancedList.init();
});
