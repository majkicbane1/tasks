import flatpickr from 'flatpickr';
import { Serbian } from 'flatpickr/dist/esm/l10n/sr.js';
import { initBulk } from './bulk.js';

/* ---------- Date picker: shows 31.12.2026., submits Y-m-d ---------- */
function datePickers(root = document) {
    root.querySelectorAll('input[type="date"]').forEach((input) => {
        if (input._flatpickr) return;
        flatpickr(input, {
            locale: { ...Serbian, firstDayOfWeek: 1 },
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd.m.Y.',
            altInputClass: input.className,
            disableMobile: true,
            allowInput: false,
            monthSelectorType: 'dropdown',
        });
    });
}


const NS = 'http://www.w3.org/2000/svg';
const ACCENT = '#2fa862';

const el = (tag, attrs = {}, parent) => {
    const n = document.createElementNS(NS, tag);
    for (const [k, v] of Object.entries(attrs)) n.setAttribute(k, v);
    parent?.appendChild(n);
    return n;
};

const fmt = (v, cur = 'EUR') =>
    new Intl.NumberFormat('sr-RS', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' ' + cur;
const short = (v) => (v >= 1000 ? (v / 1000).toFixed(1).replace('.0', '') + 'k' : String(Math.round(v)));

function niceMax(max) {
    if (max <= 0) return 4;
    const pow = 10 ** Math.floor(Math.log10(max));
    for (const m of [1, 2, 2.5, 4, 5, 8, 10]) if (m * pow >= max) return m * pow;
    return max;
}

/* ---------- Bar chart (revenue insights) ---------- */
function barChart(root) {
    const data = JSON.parse(root.dataset.series);
    const cur = root.dataset.currency || 'EUR';
    const state = { range: 'months', metric: 'work' };
    const stage = root.querySelector('[data-stage]');
    const totalEl = root.querySelector('[data-total]');
    // data-unit switches the chart from money to plain counts (used on client facing pages)
    // data-units='{"work":"stavki","paid":"uplata"}' gives each tab its own unit
    const units = root.dataset.units ? JSON.parse(root.dataset.units) : null;
    const unitOf = () => (units ? units[state.metric] : root.dataset.unit) || null;
    const tip = document.createElement('div');
    tip.className = 'chart-tip';
    tip.style.display = 'none';
    stage.appendChild(tip);

    const updateTotal = () => {
        if (totalEl) totalEl.textContent = fmt(data[state.range].reduce((a, r) => a + r[state.metric], 0), cur);
    };

    const draw = () => {
        stage.querySelector('svg')?.remove();
        const rows = data[state.range];
        const vals = rows.map((r) => r[state.metric]);
        const W = stage.clientWidth || 320, H = 300;
        const m = { l: 44, r: 8, t: 36, b: 28 };
        const iw = W - m.l - m.r, ih = H - m.t - m.b;
        const unit = unitOf();
        const top = unit ? Math.max(4, Math.ceil(Math.max(...vals) / 4) * 4) : niceMax(Math.max(...vals));
        const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, width: W, height: H, style: 'max-width:100%', role: 'img', 'aria-label': 'Grafikon' });
        stage.insertBefore(svg, tip);

        const defs = el('defs', {}, svg);
        const pat = el('pattern', { id: 'stripe', width: 6, height: 6, patternUnits: 'userSpaceOnUse', patternTransform: 'rotate(45)' }, defs);
        el('rect', { width: 6, height: 6, fill: 'var(--c-stripe-a)' }, pat);
        el('rect', { width: 2.4, height: 6, fill: 'var(--c-stripe-b)' }, pat);
        const g = el('linearGradient', { id: 'hot', x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
        el('stop', { offset: '0%', 'stop-color': ACCENT }, g);
        el('stop', { offset: '100%', 'stop-color': ACCENT, 'stop-opacity': 0.15 }, g);

        for (let i = 0; i <= 4; i++) {
            const y = m.t + ih - (ih * i) / 4;
            el('line', { x1: m.l, x2: W - m.r, y1: y, y2: y, stroke: 'var(--c-grid)', 'stroke-dasharray': '4 5' }, svg);
            el('text', { x: m.l - 10, y: y + 4, 'text-anchor': 'end', 'font-size': 12, fill: 'var(--c-label)' }, svg).textContent = unit ? String(Math.round((top * i) / 4)) : short((top * i) / 4);
        }

        const slot = iw / rows.length, bw = Math.min(34, slot * 0.58);
        const bars = [];
        const dot = el('circle', { r: 6, fill: 'var(--surface)', stroke: ACCENT, 'stroke-width': 3, opacity: 0 }, svg);

        rows.forEach((r, i) => {
            const h = Math.max(vals[i] > 0 ? 6 : 0, (vals[i] / top) * ih);
            const x = m.l + slot * i + (slot - bw) / 2, y = m.t + ih - h;
            const bar = el('rect', { x, y, width: bw, height: h, rx: Math.min(bw / 2, h / 2), fill: 'url(#stripe)', class: 'bar-grow', style: `animation-delay:${i * 35}ms` }, svg);
            bars.push({ bar, x, y });
            el('text', { x: x + bw / 2, y: H - 6, 'text-anchor': 'middle', 'font-size': 12, fill: 'var(--c-label)' }, svg).textContent = r.label;
            const hit = el('rect', { x: m.l + slot * i, y: m.t, width: slot, height: ih + 6, fill: 'transparent', style: 'cursor:pointer' }, svg);
            hit.addEventListener('mouseenter', () => focus(i));
            hit.addEventListener('click', () => {
                focus(i);
                // click again on the chosen bar to clear the filter
                state.sel = state.sel === r.key ? null : r.key;
                document.dispatchEvent(new CustomEvent('chart:period', { detail: { key: state.sel, label: r.key && r.key.length === 4 ? r.key : `${r.label} ${(r.key || '').slice(0, 4)}` } }));
                markSel();
            });
        });

        function markSel() {
            bars.forEach((b, j) => b.bar.setAttribute('stroke', rows[j].key === state.sel ? ACCENT : 'none'));
        }

        function focus(i) {
            bars.forEach((b, j) => b.bar.setAttribute('fill', j === i ? 'url(#hot)' : 'url(#stripe)'));
            const b = bars[i];
            dot.setAttribute('cx', b.x + bw / 2);
            dot.setAttribute('cy', b.y);
            dot.setAttribute('opacity', vals[i] > 0 ? 1 : 0);
            tip.textContent = unit ? `${vals[i]} ${unit}` : fmt(vals[i], cur);
            tip.style.display = vals[i] > 0 ? 'block' : 'none';
            tip.style.left = b.x + bw / 2 + 'px';
            tip.style.top = b.y - 12 + 'px';
        }
        focus(Math.max(0, vals.indexOf(Math.max(...vals))));
        markSel();
    };

    document.addEventListener('chart:period-clear', () => {
        state.sel = null;
        draw();
    });

    root.querySelectorAll('[data-range] button').forEach((b) =>
        b.addEventListener('click', () => {
            state.range = b.dataset.value;
            root.querySelectorAll('[data-range] button').forEach((x) => x.classList.toggle('on', x === b));
            updateTotal();
            draw();
        }),
    );
    root.querySelectorAll('[data-metric]').forEach((b) =>
        b.addEventListener('click', () => {
            state.metric = b.dataset.metric;
            root.querySelectorAll('[data-metric]').forEach((x) => {
                if (x.classList.contains('tab')) {
                    x.classList.toggle('on', x === b);
                    x.setAttribute('aria-selected', x === b ? 'true' : 'false');
                } else {
                    x.classList.toggle('opacity-40', x !== b);
                }
            });
            updateTotal();
            draw();
        }),
    );
    let w = stage.clientWidth;
    new ResizeObserver(() => {
        if (Math.abs(stage.clientWidth - w) > 8) {
            w = stage.clientWidth;
            draw();
        }
    }).observe(stage);
    updateTotal();
    draw();
}

/* ---------- Segmented gauge ---------- */
function gauge(root) {
    const pct = Math.max(0, Math.min(100, parseFloat(root.dataset.value) || 0));
    const N = 12, W = 340, H = 190, cx = W / 2, cy = 172, R = 150, r = 88, cr = 3;
    const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, class: 'mx-auto w-full max-w-[340px]' }, root);
    const a0 = Math.PI * 1.0, a1 = Math.PI * 2.0;
    const step = (a1 - a0) / N, gap = 0.028;
    const filled = Math.round((pct / 100) * N);
    const pt = (a, rad) => [cx + rad * Math.cos(a), cy + rad * Math.sin(a)];
    for (let i = 0; i < N; i++) {
        const s = a0 + step * i + gap, e = a0 + step * (i + 1) - gap;
        // shrink by the corner radius, the stroke gives the rounded look back
        const Ro = R - cr, Ri = r + cr, ds = cr / R, di = cr / r;
        const [x1, y1] = pt(s + ds, Ro), [x2, y2] = pt(e - ds, Ro), [x3, y3] = pt(e - di, Ri), [x4, y4] = pt(s + di, Ri);
        const on = i < filled;
        const op = on ? 1 - 0.6 * (i / (N - 1)) : 1;
        const fill = on ? ACCENT : 'var(--c-track)';
        el('path', {
            d: `M${x1} ${y1} A${Ro} ${Ro} 0 0 1 ${x2} ${y2} L${x3} ${y3} A${Ri} ${Ri} 0 0 0 ${x4} ${y4}Z`,
            fill, stroke: fill, opacity: op, 'stroke-width': cr * 2, 'stroke-linejoin': 'round',
            class: 'seg-pop', style: `animation-delay:${i * 55}ms`,
        }, svg);
    }
    el('text', { x: cx, y: cy - 36, 'text-anchor': 'middle', 'font-size': 40, 'font-weight': 600, fill: 'var(--ink)' }, svg).textContent =
        pct.toFixed(1).replace('.0', '') + '%';
    el('text', { x: cx, y: cy - 12, 'text-anchor': 'middle', 'font-size': 13, fill: 'var(--c-label)' }, svg).textContent = root.dataset.label || '';
}

/* ---------- Donut ---------- */
function donut(root) {
    const items = JSON.parse(root.dataset.items).filter((i) => i.value > 0);
    if (!items.length) {
        root.innerHTML = '<p class="py-10 text-center text-sm text-slate-500">Nema podataka.</p>';
        return;
    }
    const total = items.reduce((a, i) => a + i.value, 0);
    const S = 190, c = S / 2, R = 76, sw = 22, C = 2 * Math.PI * R;
    const svg = el('svg', { viewBox: `0 0 ${S} ${S}`, width: S, height: S }, root);
    el('circle', { cx: c, cy: c, r: R, fill: 'none', stroke: 'var(--c-track)', 'stroke-width': sw }, svg);
    const center = el('text', { x: c, y: c + 4, 'text-anchor': 'middle', 'font-size': 22, 'font-weight': 600, fill: 'var(--ink)' }, svg);
    const sub = el('text', { x: c, y: c + 22, 'text-anchor': 'middle', 'font-size': 11, fill: 'var(--c-label)' }, svg);
    const reset = () => { center.textContent = short(total); sub.textContent = root.dataset.label || 'Ukupno'; };
    reset();
    let off = 0;
    items.forEach((it, i) => {
        const len = (it.value / total) * C;
        const seg = el('circle', {
            cx: c, cy: c, r: R, fill: 'none', stroke: it.color, 'stroke-width': sw,
            'stroke-dasharray': `${Math.max(len - 3, 0.1)} ${C}`, 'stroke-dashoffset': -off,
            transform: `rotate(-90 ${c} ${c})`, style: 'cursor:pointer', class: 'seg-pop',
        }, svg);
        seg.style.animationDelay = i * 90 + 'ms';
        seg.addEventListener('mouseenter', () => { center.textContent = short(it.value); sub.textContent = it.label.slice(0, 20); });
        seg.addEventListener('mouseleave', reset);
        off += len;
    });
}

/* ---------- Sparkline ---------- */
function spark(root) {
    const v = JSON.parse(root.dataset.values);
    if (v.length < 2) return;
    const W = 96, H = 32, max = Math.max(...v, 1), min = Math.min(...v, 0);
    const xs = (i) => (i / (v.length - 1)) * (W - 4) + 2;
    const ys = (n) => H - 4 - ((n - min) / (max - min || 1)) * (H - 8);
    const d = v.map((n, i) => `${i ? 'L' : 'M'}${xs(i)} ${ys(n)}`).join('');
    const color = root.dataset.color || ACCENT;
    const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, width: W, height: H }, root);
    el('path', { d: `${d}L${xs(v.length - 1)} ${H}L${xs(0)} ${H}Z`, fill: color, 'fill-opacity': 0.1 }, svg);
    el('path', { d, fill: 'none', stroke: color, 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', class: 'draw', style: '--len:400' }, svg);
}

/* ---------- Table search ---------- */
function tableFilter(input) {
    const rows = document.querySelectorAll(input.dataset.filter);
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        rows.forEach((r) => (r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none'));
    });
}

/* ---------- Copy to clipboard ---------- */
function copyButton(btn) {
    btn.addEventListener('click', async () => {
        const text = btn.dataset.copy;
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const ta = Object.assign(document.createElement('textarea'), { value: text });
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
        }
        const label = btn.querySelector('span');
        label.textContent = 'Kopirano ✓';
        btn.classList.add('!border-emerald-300', '!bg-emerald-50', '!text-emerald-700');
        setTimeout(() => {
            label.textContent = btn.dataset.label;
            btn.classList.remove('!border-emerald-300', '!bg-emerald-50', '!text-emerald-700');
        }, 1600);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-copy]').forEach(copyButton);
    datePickers();
    initBulk();

    // theme toggle (light / dark), remembered in localStorage
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) =>
        btn.addEventListener('click', () => {
            const root = document.documentElement;
            root.classList.add('theme-anim');
            const dark = root.classList.toggle('dark');
            try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch { /* private mode */ }
            setTimeout(() => root.classList.remove('theme-anim'), 300);
        }),
    );
    document.querySelectorAll('[data-chart="bars"]').forEach(barChart);
    document.querySelectorAll('[data-chart="gauge"]').forEach(gauge);
    document.querySelectorAll('[data-chart="donut"]').forEach(donut);
    document.querySelectorAll('[data-chart="spark"]').forEach(spark);
    document.querySelectorAll('[data-filter]').forEach(tableFilter);

    // 2FA setup: draw the otpauth:// QR code in the browser (the secret never leaves the page)
    const qr = document.querySelector('[data-qr]');
    if (qr) {
        import('qrcode').then(({ default: QRCode }) =>
            QRCode.toCanvas(qr, qr.dataset.qr, { width: 192, margin: 1 }),
        );
    }

    const search = document.getElementById('globalSearch');
    document.addEventListener('keydown', (e) => {
        if (e.key === 'k' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            search?.focus();
        }
    });
});
