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
            feedback.textContent = 'Please correct the errors above.';
            feedback.classList.add('text-red-600');
        } else if (! response.ok) {
            feedback.textContent = data.message || 'Something went wrong. Please try again.';
            feedback.classList.add('text-red-600');
        } else {
            feedback.textContent = data.message || 'Application submitted successfully.';
            feedback.classList.add('text-green-600');
            form.reset();
        }
    } catch (err) {
        feedback.textContent = 'Network error. Please try again.';
        feedback.classList.add('text-red-600');
    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-60');
    }
});
