import '/partials/review/review-list';
import '/partials/review/review-add';
import '/partials/product/product-gallery/product-gallery';
import '/partials/review/review-set-rating';
import '/partials/product/product-list-small/product-list-small';
import '/partials/product/offer-choose/offer-choose';
import VanillaTabs from '/partials/common/tabs/tabs';

document.addEventListener('DOMContentLoaded', () => {
  new VanillaTabs({
    'selector': '._tabs-product',
    'type': 'horizontal',
    'responsiveBreak': 840,
    'activeIndex' : 0
  });
});

