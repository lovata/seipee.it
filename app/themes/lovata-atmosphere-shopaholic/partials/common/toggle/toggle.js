class Toggle {
  constructor(summaryNode) {
    this.summaryNode = summaryNode;
    this.toggleTextNode = this.summaryNode.querySelector('._toggle_text');
    this.isMobile = window.innerWidth < 1024;
  }

  opened() {
    this.summaryNode.setAttribute('aria-expanded', true);
    if (this.isMobile) {
      this.toggleTextNode.classList.add('text-gray-900');
    }

    this.toggleTextNode.innerHTML = this.toggleTextNode.dataset.hideText;
  }

  closed() {
    this.summaryNode.setAttribute('aria-expanded', false);
    if (this.isMobile) {
      this.toggleTextNode.classList.remove('text-gray-900');
    }

    this.toggleTextNode.innerHTML = this.toggleTextNode.dataset.showText;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('accordion:open', (event) => {
    const obToggle = new Toggle(event.detail.summary);
    obToggle.opened();
  });

  document.addEventListener('accordion:close', (event) => {
    const obToggle = new Toggle(event.detail.summary);
    obToggle.closed();
  });
});

