import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['extraFieldsContainer', 'extraModal'];
    connect() {
        this.postDataEl = document.getElementById('post-data');
        this.extraFields = {};
        if (this.postDataEl) {
            try {
                this.extraFields = JSON.parse(this.postDataEl.dataset.extraFields || '{}');
                this.post = JSON.parse(this.postDataEl.dataset.post || '{}');
            } catch (e) {
            }
        }
        try {
            const hiddenExtra = this.element.querySelector('[name="post[extraData]"]');
            const preview = document.getElementById('extraPreview');
            if (hiddenExtra && hiddenExtra.value && preview) {
                const initial = JSON.parse(hiddenExtra.value);
                preview.innerHTML = this.formatPreview(initial);
            }
        } catch (e) {
        }
    }

    openExtra(event) {
        event && event.preventDefault();
        const firstKey = Object.keys(this.extraFields)[0];
        const group = this.extraFields[firstKey] || {};
        const container = this.hasExtraFieldsContainerTarget ? this.extraFieldsContainerTarget : document.getElementById('extraFieldsContainer');
        if (!container) return;
        container.innerHTML = '';

        const hiddenExtra = this.element.querySelector('[name="post[extraData]"]');
        let current = {};
        try { current = hiddenExtra && hiddenExtra.value ? JSON.parse(hiddenExtra.value) : {}; } catch (e) { current = {}; }

        Object.keys(group).forEach(rawName => {
            const key = rawName.replace(/-/g, '_');
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'mb-3';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = rawName;
            label.setAttribute('for', `extra_${key}`);

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            // ensure id and name don't contain spaces
            input.id = `extra_${key}`;
            input.name = key;
            input.value = current[key] || group[rawName];

            fieldDiv.appendChild(label);
            fieldDiv.appendChild(input);
            container.appendChild(fieldDiv);
        });

        const modalEl = this.hasExtraModalTarget ? this.extraModalTarget : document.getElementById('extraModal');
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    submitExtra(event) {
        event && event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const payload = {};
        for (let [key, value] of formData.entries()) {
            payload[key] = value;
        }

        const json = JSON.stringify(payload);
        const hiddenExtra = this.element.querySelector('[name="post[extraData]"]');
        if (hiddenExtra) hiddenExtra.value = json;

        const preview = document.getElementById('extraPreview');
        if (preview) preview.innerHTML = this.formatPreview(payload);

        const modalEl = this.hasExtraModalTarget ? this.extraModalTarget : document.getElementById('extraModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }

    formatPreview(data) {
        if (!data || !Object.keys(data).length) return 'Add web url, data and others';
        return Object.entries(data)
            .map(([key, val]) => `<strong style="color:#007bff; text-transform:capitalize;">${key.replace(/_/g, ' ')}</strong>: ${val}`)
            .join('<br>');
    }
}
