// A patient ID is confirmed only while its visible selection is unchanged.
// Shared by booking, waitlist, medical-record and prescription creation.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm-patient]').forEach(input => {
        const form = input.form;
        const patientId = form?.querySelector('[name="patient_id"]');
        const results = document.getElementById('patient_results');
        if (!patientId || !results) return;
        const message = input.dataset.confirmPatient;
        const hint = document.createElement('p');
        hint.id = input.id + '-selection-help';
        hint.className = 'text-sm text-gray-600 mt-2';
        hint.textContent = message;
        hint.hidden = true;
        input.insertAdjacentElement('afterend', hint);
        input.setAttribute('aria-describedby', hint.id);
        input.setAttribute('aria-controls', results.id);
        input.required = true;
        const validate = () => {
            input.setCustomValidity(patientId.value ? '' : message);
            input.setAttribute('aria-invalid', patientId.value ? 'false' : 'true');
        };
        validate();
        input.addEventListener('input', () => {
            patientId.value = '';
            results.classList.add('hidden');
            hint.hidden = !input.value.trim();
            validate();
        });
        input.addEventListener('invalid', () => {
            hint.hidden = false;
            hint.classList.replace('text-gray-600', 'text-red-600');
        });
        results.addEventListener('click', event => {
            if (!event.target.closest('.patient-result')) return;
            // The result's own handler has now assigned the confirmed ID.
            validate();
            hint.hidden = !!patientId.value;
            input.focus();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') results.classList.add('hidden');
            if (event.key === 'ArrowDown' && !results.classList.contains('hidden')) {
                event.preventDefault();
                results.querySelector('button.patient-result')?.focus();
            }
        });
    });
});
