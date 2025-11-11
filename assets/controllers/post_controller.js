import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
  static targets = ['formField', 'popup'];
  connect() {
    this.postDataEl = document.getElementById('post-data');
    this.extraFields = this.tryParse(this.postDataEl?.dataset.extraFields);
    const hiddenFld = this.element.querySelector('[name="post[extraData]"]');
    const preview = document.getElementById('extraPreview');
    if (hiddenFld && preview) this.updatePreview(this.tryParse(hiddenFld.value));
  }
  openExtra(event) {
    event?.preventDefault();
    const contr = this.formFieldTarget;
    const hiddenFld = this.element.querySelector('[name="post[extraData]"]');
    const current = this.tryParse(hiddenFld?.value);
    const group = Object.values(this.extraFields || {})[0] || {};

    contr.innerHTML = Object.entries(group).map(([label, val]) => {
      const key = label.replace(/-/g, '_');
      return `
        <div class="mb-3">
          <label class="form-label" for="extra_${key}">${label}</label>
          <input type="text" class="form-control" id="extra_${key}" name="${key}" value="${current[key] || val || ''}">
        </div>`;
    }).join('');
    new bootstrap.Modal(this.popupTarget).show();
  }
  submitExtra(event) {
    event?.preventDefault();
    const payload = Object.fromEntries(new FormData(event.target));
    const hiddenFld = this.element.querySelector('[name="post[extraData]"]');
    if (hiddenFld) hiddenFld.value = JSON.stringify(payload);
    this.updatePreview(payload);
    bootstrap.Modal.getInstance(this.popupTarget)?.hide();
  }
  updatePreview(data = {}) {
    const preview = document.getElementById('extraPreview');
    if (!preview) return;
    preview.innerHTML = !Object.keys(data).length
      ? 'Add web url, data and others'
      : Object.entries(data)
          .map(([k, v]) =>`<strong style="color:#007bff; text-transform:capitalize;">${k.replace(/_/g, ' ')}</strong>: ${v}`).join('<br>');
  }
  tryParse(str) { try { return JSON.parse(str || '{}'); } catch { return {}; }}
}
