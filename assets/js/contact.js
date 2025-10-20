(function () {
  "use strict";
  // Bootstrap-like validation for forms with .needs-validation
  var forms = document.querySelectorAll(".needs-validation");
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        var emailInput = form.querySelector('input[type="email"]');
        if (emailInput) emailInput.setCustomValidity("");

        if (emailInput && emailInput.value) {
          var emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
          if (!emailPattern.test(emailInput.value)) {
            emailInput.setCustomValidity("Please enter a valid email address.");
          }
        }

        var phoneInput = form.querySelector('input[name$="[phone]"]');
        if (phoneInput) {
          phoneInput.addEventListener("input", function (e) {
            var cleaned = this.value.replace(/[^0-9\+\s\-\(\)]/g, "");
            if (cleaned !== this.value) this.value = cleaned;
          });

          var digits = phoneInput.value.replace(/\D/g, "");
          if (phoneInput.value && digits.length < 6) {
            phoneInput.setCustomValidity(
              "Please enter a valid phone number with at least 6 digits."
            );
          } else {
            phoneInput.setCustomValidity("");
          }
        }

        var fileInput = form.querySelector(
          'input[type="file"][name$="[profile]"]'
        );
        if (fileInput && fileInput.files && fileInput.files.length) {
          var file = fileInput.files[0];
          // Max size 2MB
          var maxSize = 2 * 1024 * 1024;
          var allowedTypes = ["image/jpeg", "image/png", "image/gif"];
          if (file.size > maxSize) {
            fileInput.setCustomValidity("Profile file must be 2MB or less.");
          } else if (allowedTypes.indexOf(file.type) === -1) {
            fileInput.setCustomValidity("Allowed file types: JPG, PNG, GIF.");
          } else {
            fileInput.setCustomValidity("");
          }
        }

        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
      },
      false
    );
  });
})();

//js code for student form start--

(function () {
  const list = document.getElementById("subjects-list");
  const addButton = document.getElementById("add-subject");
  let index = list.children.length;

  function addSubjectForm() {
    const prototype = list.getAttribute("data-prototype");
    let newForm = prototype.replace(/__name__/g, index);

    // create container
    const li = document.createElement("li");
    li.className = "mb-3 border rounded p-3";

    li.innerHTML =
      newForm +
      '\n<div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger remove-subject">Remove</button></div>';

    Array.from(li.querySelectorAll("input, select, textarea")).forEach(
      function (el) {
        el.classList.add("form-control");
      }
    );

    list.appendChild(li);
    index++;
  }

  function removeSubject(e) {
    if (!e.target.classList.contains("remove-subject")) return;
    const li = e.target.closest("li");
    if (li) li.remove();
  }

  addButton.addEventListener("click", addSubjectForm);
  list.addEventListener("click", removeSubject);
})();
//js code for student form end--
