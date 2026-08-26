function fitBoothCurvedText(svg) {
    const text = svg.querySelector('.booth-name-text');
    if (! text) return;

    const maxWidth = 780;
    const maxFont = 42;
    const minFont = 18;

    let fontSize = maxFont;
    text.setAttribute('font-size', fontSize);

    while (text.getBBox().width > maxWidth && fontSize > minFont) {
        fontSize -= 2;
        text.setAttribute('font-size', fontSize);
    }
}

window.fitBoothCurvedText = fitBoothCurvedText;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-booth-name-svg]').forEach(fitBoothCurvedText);
});

function closeModal(modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openModal(modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.booth-card-trigger');
    if (trigger) {
        const modal = document.getElementById(trigger.dataset.modalTarget);
        if (modal) openModal(modal);
        return;
    }

    const closeBtn = e.target.closest('.modal-close');
    if (closeBtn) {
        closeModal(closeBtn.closest('.booth-modal'));
        return;
    }

    const openModalEl = e.target.closest('.booth-modal:not(.hidden)');
    if (openModalEl && ! e.target.closest('.modal-panel')) {
        closeModal(openModalEl);
        return;
    }

    const applyTrigger = e.target.closest('.apply-trigger');
    if (applyTrigger) {
        const panel = applyTrigger.closest('.booth-modal').querySelector('.apply-panel');
        if (panel) {
            panel.classList.remove('hidden');
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.booth-modal:not(.hidden)').forEach(closeModal);
    }
});

function setCvDropzoneFile(dropzone, name) {
    const text = dropzone.querySelector('.cv-dropzone-text');
    const hint = dropzone.querySelector('.cv-dropzone-hint');
    const icon = dropzone.querySelector('.cv-dropzone-icon');
    if (! text || ! hint || ! icon) return;

    const form = dropzone.closest('.apply-form');
    const defaultText = form?.dataset.textUploadDefault ?? 'Click to upload your CV';
    const defaultHint = form?.dataset.hintUploadDefault ?? 'PDF, DOC, or DOCX · max 5MB';
    const readyHint = form?.dataset.hintUploadReady ?? 'Ready to submit — click to choose a different file';

    if (name) {
        text.textContent = name;
        hint.textContent = readyHint;
        dropzone.classList.add('border-emerald-300', 'bg-emerald-50/40');
        dropzone.classList.remove('border-gray-300');
        icon.classList.add('bg-emerald-50', 'text-emerald-600');
        icon.classList.remove('bg-indigo-50', 'text-indigo-600');
        icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    } else {
        text.textContent = defaultText;
        hint.textContent = defaultHint;
        dropzone.classList.remove('border-emerald-300', 'bg-emerald-50/40');
        dropzone.classList.add('border-gray-300');
        icon.classList.remove('bg-emerald-50', 'text-emerald-600');
        icon.classList.add('bg-indigo-50', 'text-indigo-600');
        icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>';
    }
}

document.addEventListener('change', (e) => {
    const input = e.target.closest('.cv-dropzone input[type="file"]');
    if (! input) return;

    const dropzone = input.closest('.cv-dropzone');
    setCvDropzoneFile(dropzone, input.files[0]?.name ?? null);
});

document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.apply-form');
    if (! form) return;

    e.preventDefault();

    const feedback = form.querySelector('.apply-feedback');
    const submitBtn = form.querySelector('.apply-submit');
    form.querySelectorAll('.field-error').forEach((el) => (el.textContent = ''));
    feedback.textContent = '';
    feedback.className = 'apply-feedback text-sm';
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-60');

    try {
        const response = await fetch(form.dataset.applyUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: new FormData(form),
        });

        const data = await response.json();

        if (response.status === 422 && data.errors) {
            Object.entries(data.errors).forEach(([field, messages]) => {
                const el = form.querySelector(`.field-error[data-field="${field}"]`);
                if (el) el.textContent = messages[0];
            });
            feedback.textContent = form.dataset.msgFixErrors || 'Please correct the errors above.';
            feedback.classList.add('text-red-600');
        } else if (! response.ok) {
            feedback.textContent = data.message || form.dataset.msgGenericError || 'Something went wrong. Please try again.';
            feedback.classList.add('text-red-600');
        } else {
            feedback.textContent = data.message || 'Application submitted successfully.';
            feedback.classList.add('text-green-600');
            form.reset();
            form.querySelectorAll('.cv-dropzone').forEach((dz) => setCvDropzoneFile(dz, null));
        }
    } catch (err) {
        feedback.textContent = form.dataset.msgNetworkError || 'Network error. Please try again.';
        feedback.classList.add('text-red-600');
    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-60');
    }
});
