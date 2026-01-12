import { OffCanvasContainer } from "/partials/common/off-canvas/off-canvas";
import {FlashMessage} from "/partials/message/flash-message";

class AliasManager {
  constructor() {
    this.formSelector = '#edit-alias-form';
    this.aliasItemSelector = '._alias-item';
    this.editBtnSelector = '.edit-alias-btn';
    this.deleteBtnSelector = '.delete-alias';
    this.aliasModal = 'add_alias';
    this.aliasInputSelector = '#edit-alias-input';
    this.aliasOldInputSelector = '#edit-alias-old';
    this.aliasProductIdSelector = '#alias_product_id';
    this.productIdSelector = '#edit-alias-product-id';
    this.addBtnSelector = '.add-alias-btn';
    this.oldAliasValue = null;
  }

  init() {
    this.initEditHandler();
    this.initDeleteHandler();
    this.initAddHandler();
    this.formSendHandler();
  }

  initEditHandler() {
    document.addEventListener('click', (event) => {
      const btn = event.target.closest(this.editBtnSelector);
      if (!btn) return;

      const item = btn.closest(this.aliasItemSelector);
      if (!item) return;

      const span = item.querySelector('span');
      this.oldAliasValue = span ? span.textContent.trim() : '';

      const offCanvas = OffCanvasContainer.instance();
      offCanvas.open(this.aliasModal);

      const productIdInput = document.querySelector(this.productIdSelector);
      const productId = productIdInput ? productIdInput.value : null;

      const modal = offCanvas.find(this.aliasModal);
      const input = modal.dialogNode.querySelector(this.aliasInputSelector);
      const inputProductId = modal.dialogNode.querySelector(this.aliasProductIdSelector);
      const inputOld = modal.dialogNode.querySelector(this.aliasOldInputSelector);

      if (inputProductId) inputProductId.value = productId;
      if (input) input.value = this.oldAliasValue;
      if (inputOld) inputOld.value = this.oldAliasValue;
    });
  }

  initDeleteHandler() {
    document.addEventListener('click', (event) => {
      const btn = event.target.closest(this.deleteBtnSelector);
      if (!btn) return;


      if (!confirm(window.messages.product_alias_delete_confirm)) return;

      const item = btn.closest(this.aliasItemSelector);
      if (!item) return;

      const aliasValue = item.querySelector('span')?.textContent.trim();

      const productIdInput = document.querySelector(this.productIdSelector);
      const productId = productIdInput ? productIdInput.value : null;

      this.sendDeleteRequest(aliasValue, productId);
    });
  }

  initAddHandler() {
    const addBtn = document.querySelector(this.addBtnSelector);
    if (!addBtn) return;

    addBtn.addEventListener('click', () => {
      const offCanvas = OffCanvasContainer.instance();
      offCanvas.open(this.aliasModal);

      const modal = offCanvas.find(this.aliasModal);
      const input = modal.dialogNode.querySelector(this.aliasInputSelector);
      const inputOld = modal.dialogNode.querySelector(this.aliasOldInputSelector);
      const inputProductId = modal.dialogNode.querySelector(this.aliasProductIdSelector);

      const productIdInput = document.querySelector(this.productIdSelector);
      const productId = productIdInput ? productIdInput.value : null;

      if (inputProductId) inputProductId.value = productId;
      if (input) input.value = '';
      if (inputOld) inputOld.value = '';
    });
  }
  sendDeleteRequest(value, productId) {
    const form = document.createElement('form');

    const aliasInput = document.createElement('input');
    aliasInput.type = 'hidden';
    aliasInput.name = 'alias';
    aliasInput.value = value;
    form.appendChild(aliasInput);

    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    form.appendChild(productInput);

    oc.request(form, 'ProductAliasesManager::onDelete', {
      complete: () => {
        const obFlashMessage = new FlashMessage(window.messages.product_alias_delete, 'success');
        obFlashMessage.show();
      }
    });
  }

  formSendHandler() {
    document.addEventListener('submit', (event) => {
      const form = event.target.closest(this.formSelector);
      if (!form) return;

      event.preventDefault();

      const submitBtn = form.querySelector('[type="submit"]');
      if (submitBtn) submitBtn.setAttribute('disabled', 'disabled');

      const oldAliasInput = form.querySelector('[name="alias_old"]');
      const isEdit = oldAliasInput && oldAliasInput.value.trim() !== '';

      oc.request(form, 'ProductAliasesManager::onUpdate', {
        complete: (response) => {
          if (submitBtn) submitBtn.removeAttribute('disabled');

          let obFlashMessage;

          if (isEdit) {
            obFlashMessage = new FlashMessage(window.messages.product_alias_update, 'success');
          } else {
            obFlashMessage = new FlashMessage(window.messages.product_alias_create, 'success');
          }
          obFlashMessage.show();

          const offCanvas = OffCanvasContainer.instance();
          offCanvas.close(this.aliasModal);
        },
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const aliasManager = new AliasManager();
  aliasManager.init();
});
