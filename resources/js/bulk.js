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

    // one request helper for date / status / delete
    const send = async (method, url, payload, btn) => {
        btn.disabled = true;
        try {
            const res = await fetch(samePath(url), {
                method,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload),
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return await res.json();
        } catch (err) {
            toast(`Izmena nije uspela (${err.message}).`, true);
            return null;
        } finally {
            btn.disabled = false;
        }
    };

    const STATE_LABEL = { open: 'Otvoreno', invoiced: 'Fakturisano', paid: 'Plaćeno' };
    const pill = (state, card) => {
        if (state === 'paid') return '<span class="pill pill-green">Plaćeno</span>';
        if (state === 'invoiced') return '<span class="pill pill-amber">Fakturisano</span>';
        return card ? '' : '<span class="pill pill-gray">Otvoreno</span>';
    };

    // change the date of the selected entries
    bar.querySelector('[data-bulk-apply]').addEventListener('click', async (e) => {
        const date = bar.querySelector('[data-bulk-date]').value;
        if (!date) return toast('Izaberi datum.', true);
        const ids = [...sel];
        const data = await send('PATCH', scope.dataset.bulkUrl, { ids, worked_on: date }, e.currentTarget);
        if (!data) return;
        ids.forEach((id) => scope.querySelectorAll(`[data-date-cell="${id}"]`).forEach((c) => (c.textContent = data.date)));
        sel.clear();
        render();
        toast(`Datum promenjen za ${data.updated} stavki (${data.date})`);
    });

    // change the status marker (Otvoreno / Fakturisano / Plaćeno) of the selected entries
    bar.querySelector('[data-bulk-apply-state]').addEventListener('click', async (e) => {
        const state = bar.querySelector('[data-bulk-state]').value;
        const ids = [...sel];
        if (!confirm(`Postaviti status "${STATE_LABEL[state]}" za ${ids.length} stavki?\n\nMenja se samo oznaka na stavci, računi i dugovanje se ne menjaju.`)) return;
        const data = await send('PATCH', scope.dataset.bulkUrl, { ids, state }, e.currentTarget);
        if (!data) return;
        ids.forEach((id) =>
            scope.querySelectorAll(`[data-status-cell="${id}"]`).forEach((c) => {
                c.innerHTML = pill(state, c.tagName === 'SPAN');
            }),
        );
        sel.clear();
        render();
        toast(`Status „${STATE_LABEL[state]}“ postavljen za ${data.updated} stavki`);
    });

    // delete the selected entries
    bar.querySelector('[data-bulk-delete]').addEventListener('click', async (e) => {
        const ids = [...sel];
        if (!ids.length || !confirm(`Obrisati izabrane stavke (${ids.length})? Ovo se ne može vratiti.`)) return;
        const data = await send('DELETE', e.currentTarget.dataset.deleteUrl, { ids }, e.currentTarget);
        if (!data) return;
        ids.forEach((id) => scope.querySelectorAll(`[data-entry-id="${id}"]`).forEach((row) => row.remove()));
        sel.clear();
        render();
        toast(`Obrisano stavki: ${data.deleted}. Osvežavam…`);
        // totals, chart and balances are rendered by the server, so reload to keep them correct
        setTimeout(() => window.location.reload(), 900);
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
            const period = btn.dataset.period ? `&period=${encodeURIComponent(btn.dataset.period)}` : '';
            const res = await fetch(`${samePath(btn.dataset.url)}?type=${btn.dataset.type}&page=${page}${period}`, {
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
                btn.closest('[data-more-wrap]').classList.add('hidden');
            }
        } catch (err) {
            toast(`Učitavanje nije uspelo (${err.message}).`, true);
        }
        btn.disabled = false;
        btn.textContent = label;
    });
}

/* ---------- Chart bar click -> filter the work list to that month / year ---------- */
function periodFilter() {
    const btn = document.querySelector('[data-load-more][data-type="work"]');
    const chip = document.querySelector('[data-period-chip]');
    if (!btn || !chip) return;
    const wrap = btn.closest('[data-more-wrap]');
    const rows = document.querySelector(btn.dataset.rows);
    const cards = btn.dataset.cards ? document.querySelector(btn.dataset.cards) : null;
    const empty = '<tr><td colspan="9" class="p-4 text-slate-500">Nema poslova u ovom periodu.</td></tr>';
    let seq = 0;

    const apply = async (period, label) => {
        const mine = ++seq;
        btn.dataset.period = period || '';
        btn.dataset.page = 2;
        chip.classList.toggle('hidden', !period);
        chip.querySelector('[data-period-label]').textContent = label || '';
        try {
            const q = `?type=work&page=1${period ? `&period=${encodeURIComponent(period)}` : ''}`;
            const res = await fetch(samePath(btn.dataset.url) + q, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const d = await res.json();
            if (mine !== seq) return; // a newer click already won
            rows.innerHTML = d.rows || empty;
            if (cards) cards.innerHTML = d.cards || '<p class="p-4 text-sm text-slate-500">Nema poslova u ovom periodu.</p>';
            wrap.classList.toggle('hidden', !d.has_more);
            btn.disabled = false;
            document.dispatchEvent(new Event('bulk:refresh'));
        } catch (err) {
            toast(`Učitavanje nije uspelo (${err.message}).`, true);
        }
    };

    document.addEventListener('chart:period', (e) => apply(e.detail.key, e.detail.label));
    chip.addEventListener('click', () => {
        apply(null);
        document.dispatchEvent(new Event('chart:period-clear'));
    });
}

export function initBulk() {
    document.querySelectorAll('[data-bulk]').forEach(bulkScope);
    document.querySelectorAll('[data-load-more]').forEach(loadMore);
    periodFilter();
}
