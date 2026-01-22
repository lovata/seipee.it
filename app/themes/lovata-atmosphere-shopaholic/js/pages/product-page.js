import '/partials/review/review-list';
import '/partials/review/review-add';
import '/partials/product/product-gallery/product-gallery';
import '/partials/review/review-set-rating';
import '/partials/product/product-list-small/product-list-small';
import '/partials/product/offer-choose/offer-choose';
import VanillaTabs from '/partials/common/tabs/tabs';
import {OffCanvasContainer} from "../../partials/common/off-canvas/off-canvas";

document.addEventListener('DOMContentLoaded', () => {
  new VanillaTabs({
    'selector': '._tabs-product',
    'type': 'horizontal',
    'responsiveBreak': 840,
    'activeIndex' : 0
  });

  initCustomProduct();
  initPropertySwitchers();
  initReqeustQuatation();
  initRequestQuotationSubmit();
});

function initPropertySwitchers() {
  const switchers = document.querySelectorAll('[data-property-switch]');

  switchers.forEach((switcher) => {
    const propertyId = switcher.dataset.propertySwitch;
    const wrapper = document.querySelector(
      `[data-property-wrapper="${propertyId}"]`
    );
    const select = document.querySelector(
      `[data-property-select="${propertyId}"]`
    );

    if (!wrapper || !select) return;

    applyState(switcher.checked, wrapper, select);

    switcher.addEventListener('change', () => {
      applyState(switcher.checked, wrapper, select);
    });
  });
}

function applyState(isEnabled, wrapper, select) {
  if (isEnabled) {
    wrapper.classList.remove('grid-rows-[0fr]', 'opacity-0', 'pointer-events-none');
    wrapper.classList.add('grid-rows-[1fr]', 'opacity-100');
    select.disabled = false;

    wrapper.style.marginTop = '20px';

    setTimeout(() => select.focus(), 200);
  } else {
    wrapper.classList.remove('grid-rows-[1fr]', 'opacity-100');
    wrapper.classList.add('grid-rows-[0fr]', 'opacity-0', 'pointer-events-none');
    select.value = '';
    select.disabled = true;

    wrapper.style.marginTop = '0';
  }
}

function initReqeustQuatation() {
  const addBtn = document.querySelector('#request-quotation-btn');
  if (!addBtn) return;

  addBtn.addEventListener('click', () => {
    const offCanvas = OffCanvasContainer.instance();
    offCanvas.open('request-quotation');

    const modal = offCanvas.find('request-quotation');
    if (!modal) return;

    const form = modal.dialogNode.querySelector('#request-quotation-form');
    if (!form) return;

    const hiddenInput = form.querySelector('#selected_variants');
    if (hiddenInput) {
      const variants = collectPropertyValues();
      hiddenInput.value = JSON.stringify(variants);
    }
  });
}

function collectPropertyValues() {
  const variants = {};

  const switchers = document.querySelectorAll('[data-property-switch]');

  switchers.forEach((switcher) => {
    const propertyId = switcher.dataset.propertySwitch;
    const select = document.querySelector(`[data-property-select="${propertyId}"]`);

    if (!select || !select.value) return;

    const propertyName = select.dataset.propertyName || propertyId;

    variants[propertyId] = {
      enabled: switcher.checked,
      value: select.value,
      name: propertyName
    };
  });

  return variants;
}

function initRequestQuotationSubmit() {
  document.addEventListener('submit', (event) => {
    const form = event.target.closest('#request-quotation-form');
    if (!form) return;

    event.preventDefault();

    const submitBtn = form.querySelector('[type="submit"]');
    if (submitBtn) submitBtn.setAttribute('disabled', 'disabled');

    const hiddenInput = form.querySelector('#selected_variants');
    if (hiddenInput) {
      hiddenInput.value = JSON.stringify(collectPropertyValues());
    }

    oc.request(form, 'RequestQuotation::onSend', {
      complete: (response) => {
        if (submitBtn) submitBtn.removeAttribute('disabled');
        const offCanvas = OffCanvasContainer.instance();
        offCanvas.close('request-quotation');
        if (response.redirect) {
          window.location.href = response.redirect;
        }
      },
      error: () => {
        if (submitBtn) submitBtn.removeAttribute('disabled');
      }
    });
  });
}

function updateCustomProductUI() {
  const checkboxes = document.querySelectorAll('input[name="variants[]"]');
  const defaultProducts = document.querySelectorAll('._default-product');
  const customProducts = document.querySelectorAll('._custom-product');

  const anyChecked = [...checkboxes].some(c => c.checked);

  if (anyChecked) {
    defaultProducts.forEach(el => el.classList.add('hidden'));
    customProducts.forEach(el => el.classList.remove('hidden'));
  } else {
    defaultProducts.forEach(el => el.classList.remove('hidden'));
    customProducts.forEach(el => el.classList.add('hidden'));
  }
}

function initCustomProduct() {
  const checkboxes = document.querySelectorAll('input[name="variants[]"]');

  checkboxes.forEach(cb => {
    cb.addEventListener("change", () => {
      updateCustomProductUI();
    });
  });

  updateCustomProductUI();
}

window.addEventListener('pageshow', (event) => {
  initCustomProduct();
  initPropertySwitchers();
});
