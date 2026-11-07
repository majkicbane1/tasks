/* ---------- Toast ---------- */
export function toast(message, error = false) {
    const t = Object.assign(document.createElement('div'), { className: 'toast' + (error ? ' err' : ''), textContent: message });
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2600);
}

// Server generated URLs may carry another scheme/host (proxy, www vs non-www); keep only the path
const samePath = (url) => {
    const u = new URL(url, window.location.href);
    return u.pathname + u.search;
};

/* ---------- Bulk select (click, Shift+click range) + bulk date edit ---------- */
function bulkScope(scope) {
    const sel = new Set();
    const bar = scope.querySelector('[data-bulk-bar]');
    const count = bar.querySelector('[data-bulk-count]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let last = null;

    const render = () => {
        scope.querySelectorAll('[data-bulk-item]').forEach((cb) => {
            cb.checked = sel.has(cb.value);
            const row = cb.closest('[data-entry-id]');
            if (row) row.dataset.selected = cb.checked ? 'true' : 'false';
        });
        scope.querySelectorAll('[data-bulk-all]').forEach((all) => {
            const items = [...scope.querySelectorAll('tbody [data-bulk-item]')];
            all.checked = items.length > 0 && items.every((i) => sel.has(i.value));
            all.indeterminate = !all.checked && items.some((i) => sel.has(i.value));
        });
        count.textContent = sel.size;
        bar.classList.toggle('hidden', sel.size === 0);
    };

    // Shift+click would otherwise highlight the text between the two rows
    scope.addEventListener('mousedown', (e) => {
        if (e.shiftKey && e.target.closest('[data-bulk-group]')) e.preventDefault();
    });

    scope.addEventListener('click', (e) => {
        const cb = e.target.closest('[data-bulk-item]');
        if (cb) {
            const group = cb.closest('[data-bulk-group]');
            const list = [...group.querySelectorAll('[data-bulk-item]')];
            if (e.shiftKey && last && last.group === group && list.includes(last.el)) {
                const a = list.indexOf(last.el);
                const b = list.indexOf(cb);
                list.slice(Math.min(a, b), Math.max(a, b) + 1).forEach((i) => (cb.checked ? sel.add(i.value) : sel.delete(i.value)));
            } else {
                cb.checked ? sel.add(cb.value) : sel.delete(cb.value);
            }
            last = { el: cb, group };
            render();
            return;
        }
        const all = e.target.closest('[data-bulk-all]');
        if (all) {
            scope.querySelectorAll('tbody [data-bulk-item]').forEach((i) => (all.checked ? sel.add(i.value) : sel.delete(i.value)));
            render();
        }
    });

    bar.querySelector('[data-bulk-clear]').addEventListener('click', () => { sel.clear(); render(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sel.size) { sel.clear(); render(); }
    });
    document.addEventListener('bulk:refresh', render);

    bar.querySelector('[data-bulk-apply]').addEventListener('click', async (e) => {
        const date = bar.querySelector('[data-bulk-date]').value;
        if (!date) return toast('Izaberi datum.', true);
        const btn = e.currentTarget;
        btn.disabled = true;
        try {
            const ids = [...sel];
            const res = await fetch(samePath(scope.dataset.bulkUrl), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids, worked_on: date }),
            });
            if (!res.ok) throw new Error('bulk update failed');
            const data = await res.json();
            ids.forEach((id) => scope.querySelectorAll(`[data-date-cell="${id}"]`).forEach((c) => (c.textContent = data.date)));
            sel.clear();
            render();
            toast(`Datum promenjen za ${data.updated} stavki (${data.date})`);
        } catch {
            toast('Izmena nije uspela.', true);
        } finally {
            btn.disabled = false;
        }
    });

    // invoice only the selected entries that are not invoiced yet
    scope.querySelector('[data-invoice-form]')?.addEventListener('submit', (e) => {
        const form = e.currentTarget;
        form.querySelectorAll('[data-gen]').forEach((n) => n.remove());
        const ids = [...sel].filter((id) => scope.querySelector(`[data-bulk-item][value="${id}"][data-invoiceable="1"]`));
        if (!ids.length) {
            e.preventDefault();
            return toast('Izaberi stavke koje još nisu fakturisane.', true);
        }
        ids.forEach((id) => form.insertAdjacentHTML('beforeend', `<input type="hidden" data-gen name="work_entry_ids[]" value="${id}">`));
    });
}

/* ---------- AJAX load more ---------- */
function loadMore(btn) {
    btn.addEventListener('click', async () => {
        const label = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Učitavam…';
        try {
            const page = Number(btn.dataset.page);
            const res = await fetch(`${samePath(btn.dataset.url)}?type=${btn.dataset.type}&page=${page}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const d = await res.json();
            document.querySelector(btn.dataset.rows).insertAdjacentHTML('beforeend', d.rows);
            if (d.cards && btn.dataset.cards) document.querySelector(btn.dataset.cards).insertAdjacentHTML('beforeend', d.cards);
            btn.dataset.page = page + 1;
            document.dispatchEvent(new Event('bulk:refresh'));
            if (!d.has_more) {
                btn.closest('[data-more-wrap]').remove();
                return;
            }
        } catch (err) {
            toast(`Učitavanje nije uspelo (${err.message}).`, true);
        }
        btn.disabled = false;
        btn.textContent = label;
    });
}

export function initBulk() {
    document.querySelectorAll('[data-bulk]').forEach(bulkScope);
    document.querySelectorAll('[data-load-more]').forEach(loadMore);
}
