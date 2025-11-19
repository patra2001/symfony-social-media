import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ["alert", "alertMessage"];
    static values = { text: String };
    // static values = {entity: String, entityId: Number}
    // copyData() {
    // fetch(url)
    //     .then(response => response.json())
    //     .then(data => {
    //         const textToCopy = data.data || '';
    //         // console.log(textToCopy); // Check output in console
    //         navigator.clipboard.writeText(textToCopy)
    //             .then(() => {
    //                 this.showAlert("Copied to clipboard!");
    //             })
    //             .catch(err => {
    //                 this.showAlert("Failed to copy data.", "danger");
    //             });
    //     })
    //     .catch(err => {
    //         this.showAlert("Failed to fetch data.", "danger");
    //     });
    // }

    copyData() {
        // console.log("Text to copy:", this.textValue); // debug
        navigator.clipboard.writeText(this.textValue)
        .then(() => this.showAlert("Copied to clipboard!"))
        .catch(() => this.showAlert("Failed to copy.", "danger"));
    }
    showAlert(message, type = "success") {
        // console.log("Showing alert:", m`essage);return false;
        const lrt = this.alertTarget;
        const msg = this.alertMessageTarget;
        msg.textContent = message;
        lrt.className = `alert alert-${type} alert-dismissible fade show`;
        lrt.style.display = 'block';
        setTimeout(() => {
            lrt.style.display = 'none';
        }, 2000);
    }
}
