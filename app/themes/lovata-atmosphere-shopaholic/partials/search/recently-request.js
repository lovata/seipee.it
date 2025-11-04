// createLastHistoryRequests set elements from last history
// hideRecentlyContainer hide block with last history
// appendSearchResultHistory render history list, params: array
// emulateInputEvent set value in input, params: text

export default class RecentlyRequest {
  _searchHistory = [];
  container = null;
  recentlyItem = null;
  listRecentlyCollection = document.querySelector(".js-recently-list");
  recentlyTemplate = document.querySelector(".js-recently-template");

  constructor(searchInput) {
    this.elemForSetHistory = null;
    this.searchInput = searchInput;
  }


  get searchHistory() {
    return this._searchHistory
  }

  #initElementsRecentlyItems() {
    this.container = document.querySelector(".js-recently");
    this.recentlyItem = document.querySelectorAll(".js-recently-item");
  }

  set elemForSetHistory(value) {
    return document.querySelector(`.${value}`);
  }

  get elemForSetHistory() {
    return this.elemForSetHistory;
  }

  createLastHistoryRequests() {
    this.#getSearchHistory();
    this.#fetchLastHistoryRequests();
  }

  hideRecentlyContainer() {
    if (!this.listRecentlyCollection?.length == 0) return false;
    this.listRecentlyCollection.innerHTML = "";
    this.container.classList.add("hidden");
  }

  updateSearchHistory(item) {
    this._searchHistory.push(item);
    this.#setLastHistoryRequests(this._searchHistory);
  }

  appendSearchResultHistory(searchHistory) {
    searchHistory.forEach(item => {
      if (!this.recentlyTemplate) {
        return;
      }

      const recently = this.recentlyTemplate.content.cloneNode(true);
      recently.querySelector(".js-recently-text").innerText = item;
      recently.querySelector(".js-recently-item").querySelector(".js-clear-recently").dataset.recently = item;
      recently.querySelector(".js-recently-item").dataset.recently = item;
      this.listRecentlyCollection.append(recently);
    });
    this.#initElementsRecentlyItems();
    this.#showRrecentlyContainer();
    this.#setEventsHistoryResult();
  }

  #getSearchHistory() {
    this._searchHistory = localStorage.searchHistory ? JSON.parse(localStorage.searchHistory) : [];
  }

  #fetchLastHistoryRequests() {
    if (this._searchHistory.length > 0) {
      const shortHistory = this._searchHistory.length > 5 ? [...this._searchHistory].slice(-5) : this._searchHistory;
      this.appendSearchResultHistory(shortHistory);
    }
  }

  #showRrecentlyContainer() {
    if (!this.container) {
      return;
    }

    this.container.classList.remove("hidden");
  }

  #setEventsHistoryResult() {
    this.#clearAllHistoryRequests();
    this.#setEventsHistoryRequests();
    this.#setEventsClearHistoryRequests();
  }

  #setEventsClearHistoryRequests() {
    const buttons = document.querySelectorAll(".js-clear-recently");
    buttons.forEach(button => {
      button.addEventListener("click", (e) => {
        e.stopPropagation();
        this.#removeElementHistoryRequests(button);
        this.#removeItemSearchHistory(button.dataset.recently);
      });
    });
  }

  #removeElementHistoryRequests(button) {
    const currentRecently = document.querySelector(`.js-recently-item[data-recently='${button.dataset.recently}']`);
    const list = currentRecently.closest(".js-recently-list").querySelectorAll(".js-recently-item");
    if (list.length == 1) this.hideRecentlyContainer();
    currentRecently.remove();
  }

  #removeItemSearchHistory(text) {
    const itemLowerCase = text.toLocaleLowerCase();
    const itemIndex = this._searchHistory.findIndex(el => el.toLocaleLowerCase() == itemLowerCase);
    this._searchHistory.splice(itemIndex, 1);
    this.#setLastHistoryRequests(this._searchHistory);
    if (this._searchHistory.length == 0) this.hideRecentlyContainer();
  }

  #setEventsHistoryRequests() {
    this.recentlyItem.forEach(item => {
      item.addEventListener("click", (e) => {
        this.emulateInputEvent(item.dataset.recently);
      });
    });
  }

  #clearAllHistoryRequests() {
    const clearAllNode = document.querySelector(".js-clear-recently-all");
    if (!clearAllNode) {
      return;
    }

    clearAllNode.addEventListener("click", () => {
      this.hideRecentlyContainer();
      this.#setLastHistoryRequests([]);
    });
  }

  emulateInputEvent(value) {
    const input = document.querySelector(`.${this.searchInput}`); // TODO
    input.value = value;
    const evt = new Event("input", {
      bubbles: true,
      cancelable: true
    });
    input.dispatchEvent(evt);
  }

  #setLastHistoryRequests(data) {
    localStorage.setItem("searchHistory", JSON.stringify(data));
  }
}
