import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static values = {entity: String, entityId: Number}
    static targets = ["alert", "alertMessage"];
    copyData() {
    const url = `/get-copied-data/${this.entityValue}/${this.entityIdValue}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const textToCopy = data.data || '';
            // console.log(textToCopy); // Check output in console
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    this.showAlert("Copied to clipboard!");
                })
                .catch(err => {
                    this.showAlert("Failed to copy data.", "danger");
                });
        })
        .catch(err => {
            this.showAlert("Failed to fetch data.", "danger");
        });
}


    showAlert(message, type = "success") {
        // console.log("Showing alert:", m`essage);return false;
        const alertElement = this.alertTarget;
        const messageElement = this.alertMessageTarget;
        messageElement.textContent = message;
        alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        alertElement.style.display = 'block';
        setTimeout(() => {
            alertElement.style.display = 'none';
        }, 3000);
    }
}
