import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
  static targets = ['popup', 'form', 'formField', 'details', 'hiddenInput'];
  static values = { extrafields: Array, current: Object };
  connect() {console.log("extrafield controller connected");}
  openExtra() {
    const { web_url = '', data = '', others = '' } = this.currentValue || {};
    this.formFieldTarget.innerHTML = this._formHTML({ web_url, data, others });
    new bootstrap.Modal(this.popupTarget).show();
  }
  selectextrafield(event) {
    const extrafield = this.extrafieldsValue[event.target.value];
    this.currentValue = extrafield;
    this._fillFields(extrafield);
  }
  submitForm(event) {
    event.preventDefault();
    const jsonData = Object.fromEntries(new FormData(this.formTarget).entries());
    this.currentValue = jsonData;
    if (this.hasHiddenInputTarget) this.hiddenInputTarget.value = JSON.stringify(jsonData);
    if (this.hasDetailsTarget) this.detailsTarget.innerHTML = this._detailsHTML(jsonData);
    bootstrap.Modal.getInstance(this.popupTarget)?.hide();
  }
  _fillFields({ web_url = '', data = '', others = '' }) {
    ['web_url', 'data', 'others'].forEach(name => {
      const el = this.formFieldTarget.querySelector(`[name="${name}"]`);
      if (el) el.value = eval(name); // sets value dynamically
    });
  }
  _formHTML({ web_url, data, others }) {
    return `
      ${this._field('Web URL', 'web_url', web_url)}
      ${this._field('Data', 'data', data)}
      ${this._field('Others', 'others', others)}
    `;
  }
  _field(label, name, value) {
    return `
      <div class="mb-3">
        <label class="form-label">${label}</label>
        <input type="text" name="${name}" class="form-control" value="${value}">
      </div>`;
  }
  _detailsHTML({ web_url, data, others }) {
    return `
      <strong>Selected extrafield:</strong><br>
      <p><strong>Web URL:</strong> ${web_url}</p>
      <p><strong>Data:</strong> ${data}</p>
      <p><strong>Others:</strong> ${others}</p>
    `;
  }
}
