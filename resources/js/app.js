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

// Drag and drop: a card marked draggable carries its task id, and each
// column list carries data-drop-status. Dropping a card on a column sets
// that card's own move form to the column's status and submits it, so a
// drag uses the same PATCH /tasks/{task} route as the Move button — no
// new endpoint, and without JavaScript the Move button still works.
// Dragging from a link, button, select, or input inside a card is
// ignored, so using the card's own controls never starts a drag.
document.addEventListener('dragstart', (event) => {
    const card = event.target.closest('[data-task-id]');

    if (!card || event.target.closest('a, button, select, input, textarea')) {
        event.preventDefault();
        return;
    }

    event.dataTransfer.setData('text/plain', card.dataset.taskId);
    event.dataTransfer.effectAllowed = 'move';
    card.classList.add('is-dragging');
});

document.addEventListener('dragend', () => {
    document.querySelectorAll('.is-dragging, .is-drop-target').forEach((element) => {
        element.classList.remove('is-dragging', 'is-drop-target');
    });
});

document.addEventListener('dragover', (event) => {
    const column = event.target.closest('[data-drop-status]');

    if (!column) {
        return;
    }

    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    column.classList.add('is-drop-target');
});

document.addEventListener('dragleave', (event) => {
    const column = event.target.closest('[data-drop-status]');

    if (!column || (event.relatedTarget && column.contains(event.relatedTarget))) {
        return;
    }

    column.classList.remove('is-drop-target');
});

document.addEventListener('drop', (event) => {
    const column = event.target.closest('[data-drop-status]');

    if (!column) {
        return;
    }

    event.preventDefault();
    column.classList.remove('is-drop-target');

    const card = document.querySelector(`[data-task-id="${event.dataTransfer.getData('text/plain')}"]`);
    const form = card && card.querySelector('[data-move-form]');
    const select = form && form.querySelector('select[name="status"]');

    if (!card || !form || !select) {
        return;
    }

    // Dropping a card on its own column changes nothing.
    if (select.value === column.dataset.dropStatus) {
        return;
    }

    select.value = column.dataset.dropStatus;
    form.submit();
});
