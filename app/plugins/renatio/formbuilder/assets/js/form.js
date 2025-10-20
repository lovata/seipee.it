/*
 * Reset form after submission
 */
function resetForm(form) {
    if (window.grecaptcha) {
        grecaptcha.reset();
    }

    if (! form) {
        return;
    }

    form.reset();

    if (! form.dataset.formId) {
        return;
    }

    let alerts = document.getElementsByClassName('form-alert-' + form.dataset.formId);

    if (alerts.length) {
        window.scrollTo({top: window.scrollY + alerts[0].getBoundingClientRect().top - 100, behavior: 'smooth'});
    }
}

/*
 * Set invalid fields after form is validated
 */
addEventListener('ajax:invalid-field', function (event) {
    const {element, fieldName, errorMsg, isFirst} = event.detail;

    if (element.type === 'radio') {
        document.getElementsByName(fieldName).forEach(element => setInvalidElement(element))
    }

    if (element.type === 'checkbox') {
        document.getElementsByName(fieldName + '[]').forEach(element => setInvalidElement(element))
    }

    if (fieldName === 'g-recaptcha-response') {
        document.querySelectorAll('[data-validate-for="g-recaptcha-response"]').forEach(element => element.style.display = 'block');
    }

    if (fieldName.includes('.')) {
        const field_name = fieldName.substring(0, fieldName.indexOf('.'));

        const validationElement = document.querySelector('[data-validate-for="' + field_name + '"]');

        validationElement.innerHTML = errorMsg;

        validationElement.classList.add('oc-visible');
    }

    setInvalidElement(element)
});

/*
 * Clear errors on new form submission
 */
addEventListener('ajax:promise', function (event) {
    if (event.detail.context.handler === 'onChangeCountry') {
        return false;
    }

    if (! event.target.closest('form')) {
        return false;
    }

    event.target.closest('form').querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');

        document.querySelectorAll('[data-validate-for="g-recaptcha-response"]').forEach(element => element.style.display = 'none');
    });
});

function setInvalidElement(element) {
    element.classList.add('is-invalid');
}
