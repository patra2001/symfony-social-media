(() => {
  'use strict';

  // Bootstrap validation
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();

document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('form[id$="PostForm"]');

  forms.forEach(form => {
    const contentField = form.querySelector('[name="post[content]"]');
    const imageField = form.querySelector('[name="post[image]"]');
    const typeField = form.querySelector('[name="post[type]"]');

    // Error containers (searched inside this form)
    const typeErrorDiv = form.querySelector('#typeError');
    const contentErrorDiv = form.querySelector('#contentError');
    const imageErrorDiv = form.querySelector('#imageError');

    // Form Validation
    form.addEventListener('submit', function (event) {
      let isValid = true;

      [typeField, contentField, imageField].forEach(el => el?.classList.remove('is-invalid'));
      [typeErrorDiv, contentErrorDiv, imageErrorDiv].forEach(div => {
        div.textContent = '';
        div.classList.add('d-none');
      });

      if (typeField && typeField.value.trim() === '') {
        isValid = false;
        typeField.classList.add('is-invalid');
        typeErrorDiv.textContent = 'Please select a post type.';
        typeErrorDiv.classList.remove('d-none');
      }

      const contentValue = window.tinymce && tinymce.activeEditor
        ? tinymce.activeEditor.getContent({ format: 'text' }).trim()
        : contentField?.value.trim();

      if (contentField && contentValue === '') {
        isValid = false;
        contentField.classList.add('is-invalid');
        contentErrorDiv.textContent = 'Content cannot be empty.';
        contentErrorDiv.classList.remove('d-none');
      }

      if (imageField && imageField.files.length > 0) {
        const file = imageField.files[0];
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
          isValid = false;
          imageField.classList.add('is-invalid');
          imageErrorDiv.textContent = 'Only JPG, PNG, or WEBP images are allowed.';
          imageErrorDiv.classList.remove('d-none');
        } else if (file.size > 2 * 1024 * 1024) {
          isValid = false;
          imageField.classList.add('is-invalid');
          imageErrorDiv.textContent = 'Image size must be less than 2MB.';
          imageErrorDiv.classList.remove('d-none');
        }
      }

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
      }
    });

    /* 🧩 EXTRA modal logic */
    // const extraPicker = form.querySelector('#extraPicker');
    // const extraPreview = form.querySelector('#extraPreview');
    // const hiddenExtra = form.querySelector('[name="post[extraData]"]');

    // const postDataEl = document.getElementById('post-data');
    // let extraFields = {};
    // let post = {};

    // if (postDataEl) {
    //   try {
    //     extraFields = JSON.parse(postDataEl.dataset.extraFields);
    //     post = JSON.parse(postDataEl.dataset.post || '{}');
    //   } catch (e) {
    //     console.warn('Failed to parse post-data JSON:', e);
    //   }
    // }

    //   function formatPreview(data) {
    //   if (!data || !Object.keys(data).length) return 'Add web url, data and others';
    //   return Object.entries(data)
    //     .map(([key, val]) => `<strong style="color:#007bff; text-transform:capitalize;">${key.replace(/_/g, ' ')}</strong>: ${val}`)
    //     .join('<br>');
    // }

    // try {
    //   let initial = {};
    //   if (hiddenExtra && hiddenExtra.value) {
    //     initial = JSON.parse(hiddenExtra.value);
    //   }
    //   extraPreview.innerHTML = formatPreview(initial);
    // } catch (e) {
    //   console.warn('Error parsing initial extra data:', e);
    // }

    // if (extraPicker) {
    //   extraPicker.addEventListener('click', function () {
    //     const firstKey = Object.keys(extraFields)[0];
    //     const group = extraFields[firstKey] || {};
    //     const current = (hiddenExtra && hiddenExtra.value) ? JSON.parse(hiddenExtra.value) : {};

    //     const container = document.getElementById('exflcontainer');
    //     if (!container) return;
    //     container.innerHTML = '';

    //     Object.keys(group).forEach(rawName => {
    //       const key = rawName.replace(/-/g, '_');
    //       const fieldDiv = document.createElement('div');
    //       fieldDiv.className = 'mb-3';

    //       const label = document.createElement('label');
    //       label.className = 'form-label';
    //       label.textContent = rawName;
    //       label.setAttribute('for', `extra_${key}`);

    //       const input = document.createElement('input');
    //       input.type = 'text';
    //       input.className = 'form-control';
    //       input.id = `extra_${key}`;
    //       input.name = key;
    //       input.value = current[key] || group[rawName];

    //       fieldDiv.appendChild(label);
    //       fieldDiv.appendChild(input);
    //       container.appendChild(fieldDiv);
    //     });

    //     const modal = new bootstrap.Modal(document.getElementById('extraModal'));
    //     modal.show();
    //   });
    // }

    // const extraForm = document.getElementById('extraForm');
    // if (extraForm) {
    //   extraForm.addEventListener('submit', function (event) {
    //     event.preventDefault();

    //     const formData = new FormData(event.target);
    //     const payload = {};
    //     for (let [key, value] of formData.entries()) {
    //       payload[key] = value;
    //     }

    //     const json = JSON.stringify(payload);
    //     if (hiddenExtra) hiddenExtra.value = json;
    //     extraPreview.innerHTML = formatPreview(payload);

    //     const modal = bootstrap.Modal.getInstance(document.getElementById('extraModal'));
    //     modal.hide();
    //   });
    // }
  });
});
