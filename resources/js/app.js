// Delete confirmation: a form marked with data-confirm-delete asks once
// before submitting, so a misclick on Remove cannot wipe a task. A form
// marked with data-confirm-board-delete asks about the board instead,
// because deleting a board also deletes all its tasks. Without
// JavaScript the forms still submit as before — the board keeps working,
// and status changes remain plain form submissions.
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm-delete], [data-confirm-board-delete]');

    if (!form) {
        return;
    }

    const message = form.hasAttribute('data-confirm-board-delete')
        ? 'Delete this board and all its tasks? This cannot be undone.'
        : 'Delete this task? This cannot be undone.';

    if (!window.confirm(message)) {
        event.preventDefault();
    }
});
