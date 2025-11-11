import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['menu', 'preview', 'input', 'modal'];
  static values = {
    presets: Object,
    initial: { type: String, default: '{}' },
    formId: String
  };

  connect() {
    this.bootstrapModal = null;
    this.modalElement = this.hasModalTarget ? this.modalTarget : null;
    this.resolvePreviewTargets();
    this.presetsValue = this.normalizePresets(this.presetsValue || {});
    this.defaultKey = Object.keys(this.presetsValue)[0] ?? null;
    this.state = this.resolveInitialState();
    this.currentKey = this.state.key ?? this.defaultKey;
    this.updatePreview();
    this.setInitialInput();
  }

  resolveInitialState() {
    const parsed = this.parseJSON(this.initialValue);
    if (parsed && typeof parsed === 'object' && parsed.data) {
      const key = parsed.key ?? this.defaultKey;
      return {
        key,
        data: key ? { [key]: { ...this.presetsValue[key], ...(parsed.data?.[key] || {}) } } : {}
      };
    }

    const defaultKey = this.defaultKey;
    return {
      key: defaultKey,
      data: defaultKey ? { [defaultKey]: { ...this.presetsValue[defaultKey] } } : {}
    };
  }

  setInitialInput() {
    if (!this.hasInputTarget) return;
    this.inputTarget.value = JSON.stringify(this.state);
  }

  resolvePreviewTargets() {
    if (!this.hasPreviewTarget || !this.hasInputTarget) {
      const container = this.element.closest('[data-profile-extra-container]') || this.element.parentElement;
      const preview = container?.querySelector('[data-profile-extra-target="preview"]');
      const input = container?.querySelector('[data-profile-extra-target="input"]');
      if (preview && !this.hasPreviewTarget) {
        this.previewTarget = preview;
      }
      if (input && !this.hasInputTarget) {
        this.inputTarget = input;
      }
    }
  }

  select(event) {
    event.preventDefault();
    const key = event.currentTarget.dataset.profileExtraKey;
    if (!key || !this.presetsValue?.[key]) {
      return;
    }
    this.currentKey = key;
    this.openModal(key, this.presetsValue[key]);
  }

  openModal(key, preset) {
    this.currentKey = key;
    this.modal = this.getOrCreateModal();
    const fieldsContainer = this.modal.querySelector('[data-profile-extra-modal-target="fields"]');

    const currentValues = this.state?.data?.[key] || {};
    fieldsContainer.innerHTML = Object.entries(preset).map(([label, defaultValue]) => {
      const fieldId = `${this.formIdValue || 'profile'}_${key}_${label}`.replace(/[^a-zA-Z0-9_-]/g, '_');
      const value = currentValues[label] ?? defaultValue ?? '';
      return `
        <div class="col-12 col-md-6">
          <label class="form-label" for="${fieldId}">${this.formatLabel(label)}</label>
          <input type="text" class="form-control" id="${fieldId}" name="${label}" value="${value}">
        </div>`;
    }).join('');

    const modalInstance = this.ensureBootstrapModal();
    modalInstance.show();
  }

  submit(event) {
    event.preventDefault();
    if (!this.modal) return;

    const formData = new FormData(event.target);
    const payload = Object.fromEntries(formData.entries());

    this.state = {
      key: this.currentKey,
      data: {
        ...this.state.data,
        [this.currentKey]: {
          ...this.presetsValue[this.currentKey],
          ...payload
        }
      }
    };
    this.inputTarget.value = JSON.stringify(this.state);
    this.updatePreview();
    this.ensureBootstrapModal().hide();
  }

  updatePreview(state = this.state) {
    if (!this.hasPreviewTarget) return;

    const { key, data } = state || {};
    const effectiveKey = key || this.defaultKey;
    const defaults = effectiveKey ? this.presetsValue[effectiveKey] || {} : {};
    const current = effectiveKey ? data?.[effectiveKey] || {} : {};
    const merged = { ...defaults, ...current };
    const presetTitle = this.formatLabel(effectiveKey);

    const content = Object.entries(merged).map(([fieldKey, fieldValue]) => (
      `<div><strong class="text-primary">${this.formatLabel(fieldKey)}</strong>: ${fieldValue || '—'}</div>`
    ));

    this.previewTarget.innerHTML = content.length
      ? [`<div class="mb-2 d-flex align-items-center justify-content-between">
            <span class="badge bg-primary bg-opacity-10 text-primary">${presetTitle}</span>
            <button type="button" class="btn btn-sm btn-outline-primary" data-action="profile-extra#openFromPreview">Edit</button>
         </div>`, ...content].join('')
      : '<small class="text-muted">Use the dropdown to populate preset data</small>';
  }

  openFromPreview(event) {
    event?.preventDefault();
    const key = this.state?.key || this.defaultKey;
    if (!key || !this.presetsValue[key]) {
      return;
    }
    this.openModal(key, this.presetsValue[key]);
  }

  getOrCreateModal() {
    if (this.modalElement) {
      return this.modalElement;
    }
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'profileExtraModal';
    modal.tabIndex = -1;
    modal.innerHTML = `
      <div class="modal-dialog">
        <div class="modal-content">
          <form data-action="submit->profile-extra#submit">
            <div class="modal-header">
              <h5 class="modal-title">Customize Preset</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div data-profile-extra-modal-target="fields"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>`;
    document.body.appendChild(modal);
    this.modalElement = modal;
    return modal;
  }

  ensureBootstrapModal() {
    if (!window.bootstrap) {
      throw new Error('Bootstrap JavaScript is required for profile-extra controller.');
    }
    if (!this.modalElement) {
      this.getOrCreateModal();
    }
    this.bootstrapModal = window.bootstrap.Modal.getOrCreateInstance(this.modalElement);
    return this.bootstrapModal;
  }

  parseJSON(value) {
    if (!value) return {};
    try {
      return JSON.parse(value);
    } catch (error) {
      console.warn('[profile-extra] Failed to parse JSON:', error);
      return {};
    }
  }

  formatLabel(label) {
    return (label || '').replace(/[_-]/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
  }
}