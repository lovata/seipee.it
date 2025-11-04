import ShopaholicSearch from "@oc-shopaholic/shopaholic-search";
import RecentlyRequest from "./recently-request";

export default class Search extends RecentlyRequest {
  elemNoResult = document.querySelector(".js-no-result");
  btnSearchInputClear = document.querySelector(".js-search-input-clear");

  constructor(searchInput) {
    super(searchInput);
    this.searchInput = "js-shopaholic-search-input";
    this.elemNoResult = null;
  }

  init() {
    this.createLastHistoryRequests();
    this.fetchInputResults();
    this.setEventInput();
    this.setEventClearInput();
  }

  setEventClearInput() {
    if (!this.btnSearchInputClear) {
      return;
    }

    this.btnSearchInputClear.addEventListener("click", (e) => {
      this.emulateInputEvent("");
    });
  }

  setEventInput() {
    const searchInputNode = document.querySelector(`.${this.searchInput}`);
    if (!searchInputNode) {
      return;
    }

    searchInputNode.addEventListener("input", (event) => {
      const value = event.target.value;
      if (value && value.length > 1) {
        this.showCleanInputValue();
      } else {
        this.hideCleanInputValue();
      }
    });
  }

  showCleanInputValue() {
    if (this.btnSearchInputClear.classList.contains("hidden")) {
      this.btnSearchInputClear.classList.remove("hidden");
    }
  }

  hideCleanInputValue() {
    if (!this.btnSearchInputClear.classList.contains("hidden")) {
      this.btnSearchInputClear.classList.add("hidden");
    }
  }

  fetchInputResultsComplete(search) {
    if (search.length > 2) {
      this.searchResult(search);
    } else {
      this.hideRecentlyContainer();
    }
  }

  fetchInputResults() {
    const _this = this;

    const obHelper = new ShopaholicSearch();
    obHelper.setSearchDelay(1000).setSearchLimit(3).setAjaxRequestCallback(function (obRequestData) {
      obRequestData.update = {"search/search-result": "._search-result-wrapper"};
      obRequestData.complete = () => {
        _this.fetchInputResultsComplete(obRequestData.data.search);
      };
      return obRequestData;
    }).init();

  }

  searchResult(search) {
    const hasResult = !!document.querySelectorAll(".js-search-result-wrapper li").length;
    if (hasResult) {
      this.setCurrentSearchHistoryText(search);
      this.setCurrentSearchHistoryList(search);
      const lastHistory = document.querySelectorAll(".js-recently .js-recently-text");
      this.decorateAccentSearchList(search, lastHistory);
      const lastInputResult = document.querySelectorAll(".js-product-container .js-product-name");
      this.decorateAccentSearchList(search, lastInputResult);
      this.hideNoResultContainer();
    } else {
      this.showNoResultContainer(search);
    }
  }

  setCurrentSearchHistoryText(search) {
    search = search.toLocaleLowerCase();
    if (!this.searchHistory.find(el => el.toLocaleLowerCase() === search.toLocaleLowerCase())) {
      this.updateSearchHistory(search);
    }
  }

  setCurrentSearchHistoryList(search) {
    const searchHistoryFilter = this.searchHistory.filter(el => el !== search && el.toLocaleLowerCase().indexOf(search.toLocaleLowerCase()) >= 0);
    this.hideRecentlyContainer();
    if (searchHistoryFilter.length) this.appendSearchResultHistory(searchHistoryFilter);
  }

  showNoResultContainer(search) {
    if (this.elemNoResult?.classList.contains("hidden")) {
      this.elemNoResult.classList.remove("hidden");
      this.hideRecentlyContainer();
    }
  }

  hideNoResultContainer() {
    if (this.elemNoResult && !this.elemNoResult.classList.contains("hidden")) {
      this.elemNoResult.classList.add("hidden");
    }
  }

  decorateAccentSearchList(search, classList) {
    classList.forEach(el => {
      const text = el.innerText;
      const textLowerCase = text.toLocaleLowerCase();
      const firstIndexText = textLowerCase.indexOf(search.toLocaleLowerCase());
      const newText = text.substring(firstIndexText, search.length);
      el.innerHTML = text.replace(newText, `<b>${newText}</b>`);
    });
  }

  setFirstHistoryItem() {
    document.querySelector(".js-recently-list");
  }

  static make(container) {
    const obContainer = document.getElementsByClassName(`${container}`);
    setTimeout(() => {
      Array.from(obContainer).forEach(function (e) {
        const containerNav = new Search(e);
        containerNav.init();
      });
    })

  }
}

document.addEventListener("off-canvas:open", (event) => {
  Search.make("_offCanvasContent");
});
