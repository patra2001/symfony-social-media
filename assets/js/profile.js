document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.needs-validation');

    const escapeSelector = (selector) => {
        if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
            return CSS.escape(selector);
        }

        return selector.replace(/([ #;?%&,.+*~':"!^$[\]()=>|\/@])/g, '\\$1');
    };

    Array.prototype.slice.call(forms).forEach((form) => {
        const feedbackElements = Array.prototype.slice.call(
            form.querySelectorAll('.js-custom-feedback[data-feedback-for]')
        );

        const fieldMap = feedbackElements.reduce((map, feedback) => {
            const fieldId = feedback.dataset.feedbackFor;

            if (!fieldId) {
                return map;
            }

            const input = form.querySelector(`#${escapeSelector(fieldId)}`);

            if (!input) {
                return map;
            }

            const message =
                feedback.dataset.customMessage || 'This field can not be empty.';

            feedback.classList.add('d-none');
            feedback.classList.remove('d-block');

            const updateFeedback = () => {
                const trimmedLength = input.value.trim().length;

                if (trimmedLength === 0) {
                    input.setCustomValidity(message);
                } else {
                    input.setCustomValidity('');
                }

                if (!input.validity.valid) {
                    feedback.textContent = input.validationMessage || message;
                    feedback.classList.remove('d-none');
                    feedback.classList.add('d-block');
                } else {
                    feedback.classList.add('d-none');
                    feedback.classList.remove('d-block');
                }
            };

            input.addEventListener('input', () => {
                if (input.value.trim().length === 0) {
                    input.setCustomValidity(message);
                } else {
                    input.setCustomValidity('');
                }

                feedback.classList.add('d-none');
                feedback.classList.remove('d-block');
            });

            input.addEventListener('blur', () => {
                updateFeedback();
            });

            input.addEventListener('invalid', () => {
                updateFeedback();
            });

            map.set(input, updateFeedback);

            return map;
        }, new Map());

        form.addEventListener(
            'submit',
            (event) => {
                fieldMap.forEach((updateFeedback) => updateFeedback());

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            },
            false
        );
    });
});