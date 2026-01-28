function updateCashTotals(containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        return;
    }
    let total = 0;
    container.querySelectorAll('[data-denomination]').forEach((row) => {
        const denom = parseFloat(row.dataset.denomination || '0');
        const input = row.querySelector('input');
        const qty = parseInt(input.value || '0', 10);
        const subtotal = denom * qty;
        total += subtotal;
        const cell = row.querySelector('.subtotal');
        if (cell) {
            cell.textContent = subtotal.toLocaleString();
        }
    });
    const totalCell = container.querySelector('.total-cell');
    if (totalCell) {
        totalCell.textContent = total.toLocaleString();
    }
}

function initCashCount(containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        return;
    }
    container.addEventListener('input', () => updateCashTotals(containerId));
    updateCashTotals(containerId);
}
