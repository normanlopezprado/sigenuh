{{-- resources/views/dashboard-carts.blade.php --}}
@extends('partials.layouts.master3')

@section('title', 'SIGENUH')
@section('sub-title', 'Resumen y Destino Carritos')
@section('pagetitle', 'Dashboard • Carritos')
@section('buttonTitle', 'Actualizar')

    @section('css')
    <style>
    /* ===========================
        mobile
        =========================== */
    .carts-grid {
        display: grid;
        grid-template-columns: 1fr; 
        gap: 1rem;
    }

    /* ===========================
        Cards / UI base
        =========================== */
    .cart-card {
        border: 1px solid var(--tblr-border-color, #e9ecef);
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
        transition: transform .15s ease, box-shadow .15s ease;
        background: #fff;
        overflow: hidden;
    }

    .cart-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .cart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: clamp(.6rem, 1vw + .25rem, .85rem);
        border-bottom: 1px dashed #efefef;
    }

    .cart-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-width: 0;
    }

    .cart-dot {
        width: clamp(10px, 1.1vw + 6px, 12px);
        height: clamp(10px, 1.1vw + 6px, 12px);
        border-radius: 999px;
        display: inline-block;
        outline: 2px solid rgba(0, 0, 0, .06);
        flex: 0 0 auto;
    }

    .cart-title-text {
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: clamp(1.06rem, 1.2vw + .6rem, 1.25rem);
    }

    .cart-submeta {
        font-size: clamp(.82rem, .6vw + .55rem, .95rem);
        color: #6c757d;
    }

    .badge-soft {
        padding: .25rem .5rem;
        border-radius: 999px;
        font-size: clamp(.72rem, .6vw + .45rem, .8rem);
        font-weight: 600;
    }

    .badge-soft-success { background: #e9f7ef; color: #0f5132; }
    .badge-soft-secondary { background: #f1f2f4; color: #495057; }

    .cart-body {
        padding: clamp(.75rem, 1.2vw + .25rem, 1rem);
    }

    .muted { color: #6c757d; }

    .skeleton {
        background: linear-gradient(90deg, #eee, #f6f6f6, #eee);
        background-size: 200% 100%;
        animation: shine 1.2s linear infinite;
        border-radius: 10px;
    }

    @keyframes shine {
        to { background-position-x: -200%; }
    }

    .empty-state {
        border: 2px dashed #e9ecef;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }

    /* ===========================
        Tabla
        =========================== */
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: .5rem;
    }

    .cart-table thead th {
        background: #f8f9fa;
        font-weight: 700;
    }

    .cart-table tfoot th,
    .cart-table tfoot td {
        background: #fafbfc;
        font-weight: 700;
    }

    .cart-table th,
    .cart-table td {
        border-top: 1px solid #e9ecef;
        padding: clamp(.35rem, 0.8vw + .2rem, .5rem);
        vertical-align: middle;
        font-size: clamp(.9rem, .6vw + .55rem, 1rem);
    }

    .text-end { text-align: right; }
    .qty-input { width: 100%; max-width: 110px; }
    .row-note { font-size: clamp(.78rem, .5vw + .5rem, .9rem); color: #6c757d; }

    /* ===========================
        Servicios asignados
        =========================== */
    .cart-services {
        margin-top: .75rem;
        line-height: 1.35;
        font-size: clamp(.92rem, 0.7vw + .55rem, 1.05rem);
    }

    .cart-services small {
        display: block;
    
        font-size: 1em;
    }

    .cart-services .svc {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .toolbar { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }
    .last-updated { font-size: clamp(.85rem, .4vw + .6rem, .95rem); }
    .alert-mini { display: none; margin-bottom: 1rem; }
    .summary-window { margin-top: .25rem; }
    .text-center { text-align: center; }

    /* ===========================
        Escala general
        =========================== */
    :root { --card-scale: 1; }
    
    @media (min-width: 576px) {
        .carts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    
    @media (min-width: 992px) {
        .carts-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        :root { --card-scale: 1.1; }
    }


    @media (min-width: 1200px) {
        :root { --card-scale: 1.15; }
        .cart-title-text {
        font-size: clamp(1.12rem, .9vw + .7rem, 1.35rem);
        }
    }


    @media (min-width: 2560px) and (max-width: 3839.98px) {
        .carts-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        :root { --card-scale: 1.3; }

        .cart-header { padding: 1rem; }
        .cart-body   { padding: 1.2rem; }

        .cart-title-text { font-size: 1.35rem; }     
        .cart-submeta    { font-size: 1rem; }        
        .cart-table th,
        .cart-table td   { font-size: 1.06rem; }     
        .badge-soft      { font-size: calc(.82rem * var(--card-scale)); }

        
        .cart-services { font-size: 1.18rem; }       
    }

    
    @media (min-width: 3840px) {
        .carts-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        :root { --card-scale: 1.5; }

        .cart-header { padding: 1.3rem; }
        .cart-body   { padding: 1.6rem; }

        .cart-title-text { font-size: 2.1rem; }      
        .cart-submeta    { font-size: 1.9rem; }      
        .cart-table th,
        .cart-table td   { font-size: 1.7rem; }     
        .badge-soft      { font-size: calc(.85rem * var(--card-scale)); }

    }
    </style>
    @endsection



@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="toolbar">
                    <div class="flex-grow-1">
                        <label class="form-label mb-1">Selecciona un hospital</label>
                        <select id="hospitalSelect" class="form-select">
                            <option value="">— Selecciona —</option>
                            @foreach($hospitals as $_h)
                                <option value="{{ $_h->id }}" @selected(($hospital?->id) === $_h->id)>{{ $_h->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Primero elige el hospital para ver sus carritos.</div>
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button id="btnManualRefresh" type="button" class="btn btn-outline-primary">
                            <i class="ri-refresh-line"></i> Actualizar
                        </button>
                        <div class="form-check form-switch ms-2">
                            <input class="form-check-input" type="checkbox" id="autoRefreshSwitch" checked>
                            <label class="form-check-label" for="autoRefreshSwitch">Auto-refresh (5s)</label>
                        </div>
                    </div>
                </div>

                <div id="alertMini" class="alert alert-danger alert-mini" role="alert"></div>

                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="last-updated">
                        <span class="muted">Última actualización:</span>
                        <span id="lastUpdatedAt">—</span>
                    </div>
                    <div class="text-end">
                        <span class="badge-soft-secondary badge-soft" id="activeWindowBadge" style="display:none"></span>
                        <div class="summary-window muted" id="summaryWindow" style="display:none">
                            Resumen de recolección: <strong id="summaryWindowLabel">—</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contenedor principal --}}
        <div id="cartsRegion"
            data-live-url="{{ $liveDataUrl ?? '' }}"
            data-fallback-url="/dashboard/carts/live"
            {{-- Opcional: si algún día mandas esto desde el backend, lo toma --}}
            data-diet-types='@json($dietTypes ?? null)'>
            {{-- Estado inicial / vacío --}}
            <div id="emptyState" class="empty-state" @if($hospital) style="display:none" @endif>
                <i class="ri-shopping-cart-2-line" style="font-size:2rem"></i>
                <p class="mb-0 mt-2">Selecciona un hospital para ver sus carritos en tiempo real.</p>
            </div>

            {{-- Skeletons de carga --}}
            <div id="skeletons" style="display:none">
                <div class="carts-grid">
                    @for($i=0; $i<5; $i++)
                        <div class="cart-card">
                            <div class="cart-header">
                                <div class="d-flex align-items-center">
                                    <span class="cart-dot skeleton" style="width:12px;height:12px;"></span>
                                    <div class="skeleton" style="width:200px;height:14px;margin-left:.5rem;"></div>
                                </div>
                                <div class="skeleton" style="width:80px;height:12px;"></div>
                            </div>
                            <div class="cart-body">
                                <div class="skeleton" style="width:90%;height:14px;margin-bottom:.5rem;"></div>
                                <div class="skeleton" style="width:80%;height:12px;"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Contenedor de cards --}}
            <div id="cartsContainer" @if(!$hospital) style="display:none" @endif></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const hospitalSelect    = document.getElementById('hospitalSelect');
    const cartsContainer    = document.getElementById('cartsContainer');
    const skeletons         = document.getElementById('skeletons');
    const emptyState        = document.getElementById('emptyState');
    const lastUpdatedAtEl   = document.getElementById('lastUpdatedAt');
    const autoRefreshSw     = document.getElementById('autoRefreshSwitch');
    const btnManualRefresh  = document.getElementById('btnManualRefresh');
    const region            = document.getElementById('cartsRegion');
    const activeWindowBadge = document.getElementById('activeWindowBadge');
    const summaryWindow     = document.getElementById('summaryWindow');
    const summaryWindowLbl  = document.getElementById('summaryWindowLabel');
    const alertMini         = document.getElementById('alertMini');

    let timer = null;
    let currentHospitalId = hospitalSelect ? hospitalSelect.value : '';
    let liveUrl = (region?.dataset.liveUrl || '').trim();
    const FALLBACK_URL = (region?.dataset.fallbackUrl || '/dashboard/carts/live').trim();
    if (!liveUrl) liveUrl = FALLBACK_URL;

    
    let lastWindowKey = null;
    let resetTimer = null;
    let serverTimeOffsetMs = 0; 

    
    let cachedDietTypes = null;

    function showAlert(msg) {
        if (!alertMini) return;
        alertMini.textContent = msg;
        alertMini.style.display = 'block';
        setTimeout(() => {
            if (alertMini.textContent === msg) alertMini.style.display = 'none';
        }, 6000);
    }
    function clearAlert() {
        if (!alertMini) return;
        alertMini.style.display = 'none';
        alertMini.textContent = '';
    }
    function isUUID(str) {
        return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(str||''));
    }
    function fmtDateTime(d = new Date()) {
        const opts = { hour12: true, hour: '2-digit', minute: '2-digit' };
        const time = new Intl.DateTimeFormat(undefined, opts).format(d);
        return d.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' • ' + time;
    }
    function setLoading(loading) {
        if (loading) { skeletons.style.display = 'block'; cartsContainer.style.display = 'none'; }
        else { skeletons.style.display = 'none'; cartsContainer.style.display = 'block'; }
    }
    function renderEmpty(msg = 'No se encontraron carritos para este hospital.') {
        cartsContainer.innerHTML = `
            <div class="empty-state">
                <i class="ri-inbox-archive-line" style="font-size:2rem"></i>
                <p class="mb-0 mt-2">${msg}</p>
            </div>
        `;
    }
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }


    async function getDietTypes() {
        try {
            const fromAttr = region?.dataset.dietTypes;
            if (fromAttr) {
                const parsed = JSON.parse(fromAttr);
                if (Array.isArray(parsed) && parsed.length) return parsed; 
            }
        } catch (_) {}
        try {
            const res = await fetch('/api/diet-types', { headers: { 'Accept': 'application/json' }});
            if (res.ok) {
                const arr = await res.json();
                if (Array.isArray(arr) && arr.length) return arr; 
            }
        } catch (_) {}
        
        return [
            'Libre',
            'Blanda',
            'Hiposódica',
            'Diabético 1,200',
            'Diabético 1,500',
            'Renal',
            'Licuada',
            'Blanda 8m',
            'Papilla',
            'Especial',
        ];
    }

    function renderCards(carts = [], dietTypes = []) {
        if (!Array.isArray(carts) || carts.length === 0) {
            renderEmpty();
            return;
        }

        const grid = carts.map(c => {
            const color = (c.color && /^#?[0-9a-f]{3,6}$/i.test(c.color))
                ? (c.color.startsWith('#') ? c.color : `#${c.color}`)
                : '#9aa0a6';
            const statusBadge = c.status
                ? `<span class="badge-soft badge-soft-success">Activo</span>`
                : `<span class="badge-soft badge-soft-secondary">Inactivo</span>`;

            const notes = c.notes
                ? `<div class="row-note mt-1"><small class="muted">${escapeHtml(String(c.notes))}</small></div>`
                : '';

            const paths = Array.isArray(c.service_paths) ? c.service_paths : [];
            const count = Number(c.services_count ?? paths.length ?? 0);

            const rowsHtml = (Array.isArray(dietTypes) && dietTypes.length)
                ? dietTypes.map(dt => `
                    <tr>
                        <td>${escapeHtml(dt)}</td>
                        <td class="text-center qty-bandeja" data-val="0">0</td>
                        <td class="text-center qty-desechable" data-val="0">0</td>
                    </tr>
                `).join('')
                : `<tr><td colspan="3" class="text-center muted">Sin tipos de dieta configurados.</td></tr>`;

            const servicesHtml = count > 0
                ? `
                    <div class="cart-services">
                        <small style="opacity:1">Servicios asignados (${count}):</small>
                        ${paths.map(p => `<small class="svc muted">${escapeHtml(p)}</small>`).join('')}
                    </div>
                `
                : `
                    <div class="cart-services">
                        <small class="muted">No asignado a servicios de este hospital.</small>
                    </div>
                `;

            const titleMain = escapeHtml(
                (c.name && String(c.name).trim() !== '')
                    ? c.name
                    : `Carrito #${Number(c.order ?? 0)}`
            );
            const subMeta   = escapeHtml(c.code_name ?? '—');

            return `
                <div class="cart-card">
                    <div class="cart-header">
                        <div class="cart-title">
                            <span class="cart-dot" style="background:${color}"></span>
                            <div class="cart-title-text">
                                ${titleMain}
                                <div class="cart-submeta">${subMeta}</div>
                            </div>
                        </div>
                        <div>${statusBadge}</div>
                    </div>

                    <div class="cart-body">
                        ${notes}
                        <table class="cart-table" data-cart-id="${escapeHtml(c.id)}">
                            <thead>
                                <tr>
                                    <th>Dietas</th>
                                    <th class="text-center">Bandeja</th>
                                    <th class="text-center">Desechable</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                            <tfoot>
                                <tr class="subtotal-row">
                                    <th>Subtotal</th>
                                    <th class="text-center subtotal-bandeja">0</th>
                                    <th class="text-center subtotal-desechable">0</th>
                                </tr>
                                <tr class="total-row">
                                    <th>Total</th>
                                    <th class="text-center" colspan="2"><span class="total-general">0</span></th>
                                </tr>
                            </tfoot>
                        </table>
                        ${servicesHtml}
                    </div>
                </div>
            `;
        }).join('');

        cartsContainer.innerHTML = `<div class="carts-grid">${grid}</div>`;
        
        bindTotalsLogic();
    }

    function bindTotalsLogic() {
        const tables = cartsContainer.querySelectorAll('.cart-table');
        tables.forEach(tbl => {
            const recalc = () => {
                let sumB = 0, sumD = 0;
                tbl.querySelectorAll('td.qty-bandeja').forEach(td => {
                    const v = parseInt(td.getAttribute('data-val') || td.textContent || '0', 10) || 0;
                    sumB += v;
                });
                tbl.querySelectorAll('td.qty-desechable').forEach(td => {
                    const v = parseInt(td.getAttribute('data-val') || td.textContent || '0', 10) || 0;
                    sumD += v;
                });
                const sb = tbl.querySelector('.subtotal-bandeja');
                const sd = tbl.querySelector('.subtotal-desechable');
                const tg = tbl.querySelector('.total-general');

                if (sb) sb.textContent = sumB.toLocaleString();

                if (sd) sd.textContent = sumD.toLocaleString();
                
                if (tg) tg.textContent = (sumB + sumD).toLocaleString();
            };
            
            tbl._recalcTotals = recalc;
            recalc();
        });
    }

    
    function applyCountsToTables(carts = [], dietTypes = []) {
        const byId = new Map(carts.map(c => [String(c.id), c]));
        cartsContainer.querySelectorAll('.cart-table').forEach(tbl => {
            const cartId = tbl.getAttribute('data-cart-id');
            const cart = byId.get(String(cartId));

            if (!cart) return;
            const counts = cart.counts || {}; 

            
            dietTypes.forEach(dt => {
                
                const row = Array.from(tbl.tBodies[0]?.rows || []).find(r => {
                    const cell = r.cells?.[0];
                    return cell && cell.textContent.trim() === dt;
                });

                if (!row) return;
                const bandTd = row.querySelector('td.qty-bandeja');
                const desTd  = row.querySelector('td.qty-desechable');
                const val = counts[dt] || { bandeja: 0, desechable: 0, total: 0 };

                if (bandTd) {
                    bandTd.setAttribute('data-val', String(val.bandeja || 0));
                    bandTd.textContent = (val.bandeja || 0).toLocaleString();
                }

                if (desTd) {
                    desTd.setAttribute('data-val', String(val.desechable || 0));
                    desTd.textContent = (val.desechable || 0).toLocaleString();
                }
            });

            if (typeof tbl._recalcTotals === 'function') tbl._recalcTotals();
        });
    }

    function scheduleReset(isoWhen) {

        if (resetTimer) { clearTimeout(resetTimer); resetTimer = null; }

        if (!isoWhen) return;
        const due = new Date(isoWhen).getTime() - (Date.now() + serverTimeOffsetMs);

        if (due > 0) {
            resetTimer = setTimeout(() => {
                cartsContainer.innerHTML = '';
                lastWindowKey = null; 
                fetchCarts();
            }, due);
        }
    }

    async function fetchCarts() {
        if (!currentHospitalId) return;
        if (!isUUID(currentHospitalId)) { showAlert('El hospital seleccionado no es un UUID válido.'); return; }
        if (!liveUrl) { showAlert('No se configuró el endpoint de datos en vivo.'); return; }

        clearAlert();
        setLoading(true);
        try {

            if (!cachedDietTypes) cachedDietTypes = await getDietTypes();

            const url = new URL(liveUrl, window.location.origin);
            url.searchParams.set('hospital_id', currentHospitalId);
            url.searchParams.set('v', Date.now());

            const res = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                let bodyText = '';
                try { bodyText = await res.text(); } catch {}
                if (res.status === 422) showAlert('Faltan parámetros o el hospital_id no es válido.');
                else if (res.status === 404) showAlert('Hospital no encontrado o inactivo.');
                else showAlert('Error al cargar datos (' + res.status + ').');
                renderEmpty('No se pudo cargar la información. Intenta nuevamente.');
                return;
            }

            let data;
            try {
                const txt = await res.text();
                data = txt ? JSON.parse(txt) : {};
            } catch (err) {
                showAlert('Respuesta inválida del servidor.');
                renderEmpty('No se pudo interpretar la respuesta.');
                return;
            }

            
            if (data?.server_time) {
                const serverNow = new Date(data.server_time).getTime();
                serverTimeOffsetMs = serverNow - Date.now();
            }

            
            const wkey = data?.window_key || null;

            if (wkey && wkey !== lastWindowKey) {
                cartsContainer.innerHTML = '';
                lastWindowKey = wkey;
            }
            scheduleReset(data?.window?.reset_at || null);

            const effectiveWindow = data?.active_window || null;

            if (effectiveWindow) {
                activeWindowBadge.textContent = effectiveWindow;
                activeWindowBadge.style.display = 'inline-block';
                summaryWindowLbl.textContent = effectiveWindow;
                summaryWindow.style.display = 'block';

            } else {

                activeWindowBadge.style.display = 'none';
                summaryWindow.style.display = 'none';
            }

        
            const carts = Array.isArray(data?.carts) ? data.carts : (Array.isArray(data) ? data : []);

            if (!carts.length) {
                renderEmpty();

            } else {

                renderCards(carts, cachedDietTypes);
                applyCountsToTables(carts, cachedDietTypes);
            }

            lastUpdatedAtEl.textContent = fmtDateTime(new Date());
        } catch (e) {
            console.error(e);
            showAlert('Error de red. Revisa tu conexión.');
            renderEmpty('No se pudo cargar la información. Puedes intentar nuevamente.');
        } finally {
            setLoading(false);
        }
    }

    function startAutoRefresh() {
        stopAutoRefresh();

        if (autoRefreshSw && autoRefreshSw.checked) timer = setInterval(fetchCarts, 5000);
    }
    function stopAutoRefresh() { if (timer) clearInterval(timer); timer = null; }

    hospitalSelect?.addEventListener('change', () => {
        currentHospitalId = hospitalSelect.value;
        lastUpdatedAtEl.textContent = '—';

        if (!currentHospitalId) {
            stopAutoRefresh();
            cartsContainer.style.display = 'none';
            emptyState.style.display = 'block';
            activeWindowBadge.style.display = 'none';
            summaryWindow.style.display = 'none';
            return;
        }
        emptyState.style.display = 'none';
        cartsContainer.style.display = 'block';
        lastWindowKey = null;
        if (resetTimer) { clearTimeout(resetTimer); resetTimer = null; }
        fetchCarts();
        startAutoRefresh();
    });

    autoRefreshSw?.addEventListener('change', () => {

        if (!currentHospitalId) return;
        if (autoRefreshSw.checked) startAutoRefresh(); else stopAutoRefresh();
    });

    btnManualRefresh?.addEventListener('click', () => {

        if (!currentHospitalId) { showAlert('Primero selecciona un hospital.'); return; }
        fetchCarts();
    });

    if (currentHospitalId) {
        emptyState.style.display = 'none';
        cartsContainer.style.display = 'block';
        fetchCarts();
        startAutoRefresh();
    } else {
        emptyState.style.display = 'block';
        cartsContainer.style.display = 'none';
        stopAutoRefresh();
    }
})();
</script>

@endpush