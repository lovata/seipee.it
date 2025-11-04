export default new class Header {

  constructor () {
    this.init();
  }

  init () {
    this.initVariables();
    if (!this.navItems) return;
    window.addEventListener("DOMContentLoaded", () => this.initMenu());
  }

  initVariables () {
    this.navHeight = 26;
    this.nav = document.querySelector(".js-nav");
    this.navList = document.querySelector(".js-nav-list");
    this.navItems = document.querySelectorAll(".js-nav-items");
    this.template = document.querySelector("#nav-more");
    this.isMoreActive = false;
    this.space = 80;
  }

  initMenu () {
    this.adjustMenu();
    window.addEventListener("resize", () => this.adjustMenu());
  }

  adjustMenu () {
    const scrollHeight = this.navList.scrollHeight;

    if (scrollHeight > this.navHeight) {
      if (!this.isMoreActive) this.showMore();
      this.relocateNavItems();
    }

    if (this.isMoreActive) {
      this.checkReturnItem();
    }
  }

  checkReturnItem () {
    if (!this.isMoreActive) return;
    const navWidth = this.nav.clientWidth;
    const navList = this.navList.clientWidth;
    const elemNavMore = document.querySelector(".js-nav-more");
    const isLastMoreItem = elemNavMore.querySelectorAll(".js-nav-items").length == 1;
    const widthButton = isLastMoreItem ? 0 : elemNavMore.clientWidth;
    const portableElem = elemNavMore.querySelector(".js-nav-items");
    if(!portableElem) return;
    const portableElemWidth = portableElem.clientWidth;
    const calcMenuWithNewItem = navWidth - (navList + widthButton + portableElemWidth + this.space);

    if (calcMenuWithNewItem > 0) {
      this.navList.appendChild(portableElem);
      this.checkReturnItem()
      if (isLastMoreItem) this.removeMore();
    }
  }

  relocateNavItems () {
    const length = this.navItems.length - 1;
    const navList = document.querySelector(".js-nav-more .js-more-dropdown");
    for (let i = length; i >= 0; i--) {
      navList.insertBefore(this.navItems[i], navList.firstChild); // Вставляем новый элемент перед первым
      const scrollHeight = this.navList.scrollHeight;
      if (scrollHeight <= this.navHeight) break;
    }
  }

  removeMore () {
    document.querySelector(".js-nav-more").remove();
    this.isMoreActive = false;
  }

  showMore () {
    const templateNode = this.template.content.cloneNode(true);
    templateNode.querySelector("ul").classList.add("js-nav-more");
    this.navList.parentNode.appendChild(templateNode);
    this.isMoreActive = true;
  }
};
