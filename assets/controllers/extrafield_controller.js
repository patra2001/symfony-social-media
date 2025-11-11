import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ['popup', 'form', 'formField', 'details', 'dropdown', 'hiddenInput'];
    static values = { extrafields: Array, current: Object };
    connect() {console.log("extrafield controller connected");}
    openExtra() {
        const current = this.currentValue || {};
        this.formFieldTarget.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Web URL</label>
                <input type="text" name="web_url" class="form-control" value="${current.web_url || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Data</label>
                <input type="text" name="data" class="form-control" value="${current.data || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Others</label>
                <input type="text" name="others" class="form-control" value="${current.others || ''}">
            </div>
        `;
        new bootstrap.Modal(this.popupTarget).show();
    }
    selectextrafield(event) {
        const index = event.target.value;
        const extrafield = this.extrafieldsValue[index];
        this.currentValue = extrafield;

        const webUrlField = this.formFieldTarget.querySelector('[name="web_url"]');
        const dataField = this.formFieldTarget.querySelector('[name="data"]');
        const othersField = this.formFieldTarget.querySelector('[name="others"]');
        if (webUrlField) webUrlField.value = extrafield.web_url || '';
        if (dataField) dataField.value = extrafield.data || '';
        if (othersField) othersField.value = extrafield.others || '';
    }
    submitForm(event) {
        event.preventDefault();
        const formData = new FormData(this.formTarget);
        const jsonData = Object.fromEntries(formData.entries());
        this.currentValue = jsonData;
        if (this.hasHiddenInputTarget) {
            this.hiddenInputTarget.value = JSON.stringify(jsonData);
        }
        if (this.hasDetailsTarget) {
            this.detailsTarget.innerHTML = `
                <strong>Selected extrafield:</strong><br>
                <p><strong>Web URL:</strong> ${jsonData.web_url}</p>
                <p><strong>Data:</strong> ${jsonData.data}</p>
                <p><strong>Others:</strong> ${jsonData.others}</p>
            `;
        }
        let modal = bootstrap.Modal.getInstance(this.popupTarget);
        if (!modal) {
            modal = new bootstrap.Modal(this.popupTarget);
        }
        modal.hide();
        // console.log("extrafield updated:", jsonData);
    }
}
