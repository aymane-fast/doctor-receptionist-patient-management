import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

// Patient and date filters are submitted together; choosing a suggestion never
// navigates away before the user has entered the date range.
document.addEventListener('DOMContentLoaded', () => {
    // The top layer keeps row actions outside the table's scrolling/clipping area.
    document.querySelectorAll('.appointment-status-menu[popover]').forEach(menu => {
        const trigger = document.querySelector('[popovertarget="' + menu.id + '"]');
        const position = () => {
            if (!menu.matches(':popover-open') || !trigger) return;
            const rect = trigger.getBoundingClientRect();
            const width = menu.offsetWidth, height = menu.offsetHeight;
            menu.style.left = Math.max(8, Math.min(rect.right - width, innerWidth - width - 8)) + 'px';
            menu.style.top = Math.max(8, Math.min(rect.bottom + 6, innerHeight - height - 8)) + 'px';
        };
        menu.addEventListener('toggle', position);
        window.addEventListener('resize', position);
        document.addEventListener('scroll', position, true);
    });
    const rescheduleDialog = document.getElementById('quick-reschedule-dialog');
    let pendingReschedule = null;
    const approvedReschedules = new WeakSet();
    document.querySelectorAll('form[data-confirm-reschedule]').forEach(form => {
        form.addEventListener('submit', event => {
            if (approvedReschedules.has(form) || !rescheduleDialog) return;
            event.preventDefault();
            pendingReschedule = form;
            rescheduleDialog.querySelector('[data-reschedule-patient]').textContent = form.dataset.patientName;
            rescheduleDialog.showModal();
        });
    });
    document.querySelector('[data-confirm-reschedule-submit]')?.addEventListener('click', () => {
        const form = pendingReschedule;
        if (!form) return;
        approvedReschedules.add(form);
        rescheduleDialog.close();
        form.requestSubmit();
    });
    rescheduleDialog?.addEventListener('close', () => { pendingReschedule = null; });
    const practice = document.getElementById('clinic_specialty');
    const practiceSummary = document.querySelector('[data-practice-summary]');
    if (practice && practiceSummary) {
        const showSummary = () => { practiceSummary.textContent = practice.selectedOptions[0]?.dataset.summary || ''; };
        practice.addEventListener('change', showSummary);
        showSummary();
    }
    document.querySelectorAll('[data-clinical-filters]').forEach(form => {
        const input = form.querySelector('[name=search]');
        const patientId = form.querySelector('[data-patient-id]');
        const results = form.querySelector('.filter-suggestions');
        let timer, controller;
        const hide = () => { results.hidden = true; input.setAttribute('aria-expanded', 'false'); };
        input.addEventListener('input', () => {
            patientId.value = ''; clearTimeout(timer); controller?.abort(); hide();
            const query = input.value.trim();
            if (query.length < 2) return;
            controller = new AbortController();
            const signal = controller.signal;
            timer = setTimeout(async () => {
                try {
                    const response = await fetch(form.dataset.searchUrl + '?q=' + encodeURIComponent(query), {signal, headers:{Accept:'application/json'}});
                    if (!response.ok) return;
                    const patients = await response.json();
                    if (signal.aborted) return;
                    results.replaceChildren();
                    patients.forEach(patient => {
                        const button = document.createElement('button'); button.type = 'button';
                        const title = document.createElement('strong'); title.textContent = patient.name;
                        const detail = document.createElement('span'); detail.textContent = patient.phone || patient.id_card_number || '';
                        button.append(title, detail);
                        button.addEventListener('click', () => { input.value = patient.patient_name || patient.name; patientId.value = patient.id; hide(); input.focus(); });
                        results.append(button);
                    });
                    results.hidden = !patients.length; input.setAttribute('aria-expanded', String(!!patients.length));
                } catch { hide(); }
            }, 250);
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') hide();
            if (event.key === 'ArrowDown' && !results.hidden) { event.preventDefault(); results.querySelector('button')?.focus(); }
        });
        results.addEventListener('keydown', event => {
            const buttons = [...results.querySelectorAll('button')]; const index = buttons.indexOf(document.activeElement);
            if (event.key === 'ArrowDown') { event.preventDefault(); buttons[(index + 1) % buttons.length]?.focus(); }
            if (event.key === 'ArrowUp') { event.preventDefault(); if (index <= 0) input.focus(); else buttons[index - 1].focus(); }
            if (event.key === 'Escape') { hide(); input.focus(); }
        });
        document.addEventListener('click', event => { if (!form.contains(event.target)) hide(); });
    });
});

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

// Keep each task in the same patient workspace, including unsaved form values.
document.addEventListener('DOMContentLoaded', () => {
    const workspace = document.querySelector('[data-workspace]');
    const tabs = [...document.querySelectorAll('[data-workspace-tab]')];
    const panels = [...document.querySelectorAll('[data-workspace-panel]')];
    const stateKey = 'clinical-tab-' + (workspace?.dataset.visit || '');
    let activeTab = 'consultation';
    function selectTab(name, focus = false) {
        if (!tabs.some(tab => tab.dataset.workspaceTab === name)) name = 'consultation';
        activeTab = name;
        panels.forEach(panel => { panel.hidden = panel.id !== name; panel.setAttribute('role', 'tabpanel'); panel.setAttribute('aria-labelledby', 'tab-' + panel.id); });
        tabs.forEach(tab => {
            const selected = tab.dataset.workspaceTab === name;
            tab.setAttribute('role', 'tab'); tab.setAttribute('aria-controls', tab.dataset.workspaceTab);
            tab.setAttribute('aria-selected', String(selected)); tab.tabIndex = selected ? 0 : -1;
            if (selected && focus) tab.focus();
        });
        try { sessionStorage.setItem(stateKey, name); } catch {}
        if (name === 'prescription' && workspace?.dataset.hasRecord === '0') {
            const prompt = document.getElementById('prepare-prescription');
            if (prompt && !prompt.open) prompt.showModal();
        }
    }
    if (tabs.length) {
        document.querySelector('[data-workspace-tabs]').setAttribute('role', 'tablist');
        let stored = '';
        try { stored = sessionStorage.getItem(stateKey) || ''; } catch {}
        selectTab(workspace.dataset.initialTab || location.hash.slice(1) || stored);
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', event => { event.preventDefault(); selectTab(tab.dataset.workspaceTab); });
            tab.addEventListener('keydown', event => {
                let target;
                if (event.key === 'ArrowRight') target = (index + 1) % tabs.length;
                if (event.key === 'ArrowLeft') target = (index + tabs.length - 1) % tabs.length;
                if (event.key === 'Home') target = 0;
                if (event.key === 'End') target = tabs.length - 1;
                if (target !== undefined) { event.preventDefault(); selectTab(tabs[target].dataset.workspaceTab, true); }
            });
        });
        workspace.querySelectorAll('[data-workspace-go]').forEach(link => link.addEventListener('click', event => { event.preventDefault(); selectTab(link.dataset.workspaceGo, true); }));
        workspace.addEventListener('invalid', event => {
            const panel = event.target.closest('[data-workspace-panel]');
            if (panel && panel.id !== activeTab) selectTab(panel.id);
            event.target.closest('details')?.setAttribute('open', '');
        }, true);
    }

    const form = document.getElementById('active-consultation-form');
    document.querySelector('[data-review-consultation]')?.addEventListener('click', event => {
        event.currentTarget.closest('dialog').close();
        selectTab('consultation', true);
        form?.querySelector('#diagnosis')?.focus();
    });
    document.querySelector('[data-save-open-prescription]')?.addEventListener('click', event => {
        event.currentTarget.closest('dialog').close();
        // Reveal inputs before native validation focuses any invalid field.
        selectTab('consultation');
    });
    let dirty = false;
    const saveState = document.querySelector('[data-save-state]');
    const markDirty = () => { dirty = true; if (saveState) { saveState.textContent = saveState.dataset.dirty; saveState.classList.add('is-dirty'); } };
    form?.addEventListener('input', markDirty);
    form?.addEventListener('change', markDirty);
    form?.addEventListener('submit', () => { dirty = false; });
    // Validation restores submitted notes; they are not saved just because the
    // page reloaded. Keep the completion guard active until a successful save.
    if (workspace?.dataset.initialTab === 'consultation') markDirty();
    window.addEventListener('beforeunload', event => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });

    document.querySelectorAll('[data-dialog-open]').forEach(button => button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.dialogOpen);
        if (!dialog) return;
        if (dialog.id === 'finish-visit') {
            dialog.querySelector('[data-unsaved-warning]').hidden = !dirty;
            dialog.querySelector('[data-save-before-finish]').hidden = !dirty;
            dialog.querySelector('[data-finish-form]').hidden = dirty;
        }
        dialog.showModal();
    }));
    document.querySelectorAll('[data-dialog-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelector('[data-save-before-finish]')?.addEventListener('click', event => {
        event.target.closest('dialog').close(); selectTab('consultation', true);
        form?.querySelector('button[type=submit], button:not([type])')?.focus();
    });
    if (workspace?.dataset.initialTab === 'follow-up') document.getElementById('follow-up-dialog')?.showModal();
});
