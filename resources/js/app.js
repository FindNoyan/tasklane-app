// Delete confirmation: a form marked with data-confirm-delete asks once
// before submitting, so a misclick on Remove cannot wipe a task. Without
// JavaScript the form still submits as before — the board keeps working,
// and status changes remain plain form submissions.
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm-delete]');

    if (!form) {
        return;
    }

    if (!window.confirm('Delete this task? This cannot be undone.')) {
        event.preventDefault();
    }
});
