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
// column list carries data-drop-status. Dropping a card on another
// column sets that card's own move form to the column's status and
// submits it, so a move uses the same PATCH /tasks/{task} route as the
// Move button — no new endpoint, and without JavaScript the Move button
// still works. Dropping a card inside its own column reorders that
// column instead: the card's drop position becomes the column's new
// order, posted to the hidden reorder form (POST /tasks/reorder).
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

    // Dropping on another column moves the task there, as before.
    if (select.value !== column.dataset.dropStatus) {
        select.value = column.dataset.dropStatus;
        form.submit();
        return;
    }

    // Same column: reorder. The drop position is where the pointer
    // falls against the midpoint of each other card, so dropping
    // above a card's middle puts the dragged card before it.
    const reorderForm = document.querySelector('[data-reorder-form]');

    if (!reorderForm) {
        return;
    }

    const siblings = [...column.querySelectorAll('[data-task-id]')].filter((other) => other !== card);
    let index = siblings.findIndex((other) => {
        const rect = other.getBoundingClientRect();
        return event.clientY < rect.top + rect.height / 2;
    });

    if (index === -1) {
        index = siblings.length;
    }

    const ids = siblings.map((other) => other.dataset.taskId);
    ids.splice(index, 0, card.dataset.taskId);

    // Dropping a card where it already sits changes nothing.
    const current = [...column.querySelectorAll('[data-task-id]')].map((other) => other.dataset.taskId);

    if (ids.join(',') === current.join(',')) {
        return;
    }

    reorderForm.querySelector('input[name="status"]').value = column.dataset.dropStatus;
    reorderForm.querySelectorAll('input[name="ids[]"]').forEach((input) => input.remove());
    ids.forEach((id) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        reorderForm.appendChild(input);
    });
    reorderForm.submit();
});
