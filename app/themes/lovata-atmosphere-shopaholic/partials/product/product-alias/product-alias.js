import {OffCanvasContainer} from "/partials/common/off-canvas/off-canvas";

const aliasModal = 'add_alias';
const aliasInput = '#edit-alias-input';
const aliasEditBtn = '.edit-alias-btn';
const aliasSaveBtn = '#edit-alias-save';
const aliasClass = '._alias-item';
document.addEventListener('DOMContentLoaded', () => {

  document.querySelectorAll(aliasEditBtn).forEach(btn => {
    btn.addEventListener('click', (event) => {

      const item = btn.closest('._alias-item');

      const id = item.dataset.idAlias;
      const alias = item.dataset.alias;

      OffCanvasContainer.instance().open(aliasModal);

      const offCanvas = OffCanvasContainer.instance().find(aliasModal);
      const inputValue = offCanvas.dialogNode.querySelector(aliasInput);

      inputValue.value = alias;
      inputValue.dataset.id = id;
    });
  });

  document.querySelectorAll('.delete-alias').forEach(btn => {
    btn.addEventListener('click', () => {
      if (confirm('Вы уверены, что хотите удалить этот alias?')) {
        const aliasItem = btn.closest(aliasClass);
        if (aliasItem) {
          aliasItem.remove();
        }
      }
    });
  });
});

document.addEventListener('click', (event) => {
  const btn = event.target.closest(aliasSaveBtn);
  if (!btn) return;

  const offCanvas = OffCanvasContainer.instance().find(aliasModal);
  const inputValue = offCanvas.dialogNode.querySelector(aliasInput);

  const aliasItem = document.querySelector(aliasClass + `[data-id-alias="${inputValue.dataset.id}"] span`);

  if (aliasItem) {
    aliasItem.textContent = inputValue.value;
    const parent = aliasItem.closest(aliasClass);
    parent.dataset.alias = inputValue.value;
  }

  OffCanvasContainer.instance().close(aliasModal);
});
