import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

// Suggestions from the doctor's own medication vocabulary; no dosage is
// inferred or copied. textContent prevents names from becoming markup.
const medicationRequests = new WeakMap();
const medicationTimers = new WeakMap();
document.addEventListener('input', event => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.name.endsWith('[medication_name]')) return;
    const list = document.getElementById(input.getAttribute('list'));
    if (!list) return;
    medicationRequests.get(input)?.abort();
    clearTimeout(medicationTimers.get(input));
    if (input.value.trim().length < 2) { list.replaceChildren(); return; }
    const controller = new AbortController();
    medicationRequests.set(input, controller);
    medicationTimers.set(input, setTimeout(() => fetch('/api/medications/search?q=' + encodeURIComponent(input.value.trim()), { signal: controller.signal, headers: { Accept: 'application/json' } })
        .then(response => response.ok ? response.json() : [])
        .then(names => {
            if (controller.signal.aborted) return;
            list.replaceChildren(...names.map(name => {
                const option = document.createElement('option'); option.value = name; return option;
            }));
        }).catch(() => {}), 200)); // Manual entry always remains available.
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[data-original-content]').forEach(button => {
        button.innerHTML = button.dataset.originalContent;
        button.disabled = false;
        delete button.dataset.originalContent;
    });
});

// Guard against losing unsaved clinical observations during related navigation.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('active-consultation-form');
    if (!form) return;
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', event => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });
});
