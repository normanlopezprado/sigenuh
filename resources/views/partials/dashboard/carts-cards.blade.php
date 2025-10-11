<div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5">
@forelse($carts as $cart)
    <div class="col">
        <div class="card h-100 card-hover">
            <div class="card-body d-flex flex-column">

                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex flex-column">
                        @php $dot = $cart->color ?: '#d1d5db'; @endphp
                        <div class="d-flex align-items-center mb-1">
                            <span class="cart-color-dot" style="background: {{ $dot }}"></span>
                            <h5 class="mb-0 ms-2 fw-semibold" style="font-size: 1.25rem;">{{ $cart->name }}</h5>
                        </div>
                        <div class="text-muted ms-4" style="font-size: 0.95rem;">
                            <i class="ri-hashtag"></i> <strong>{{ $cart->code_name ?? '—' }}</strong>
                        </div>
                    </div>
                    <span class="badge rounded-pill {{ $cart->status ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                        {{ $cart->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th>Dietas</th>
                                <th class="text-center">Bandejas</th>
                                <th class="text-center">Desechables</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statsForCart = $cartDietStats[$cart->id] ?? [];
                                $subTrays = $subDisp = 0;
                            @endphp

                            @forelse($dietTypes as $diet)
                                @php
                                    $t = (int)($statsForCart[$diet]['trays'] ?? 0);
                                    $d = (int)($statsForCart[$diet]['disposables'] ?? 0);
                                    $subTrays += $t; $subDisp += $d;
                                @endphp
                                <tr>
                                    <td>{{ $diet }}</td>
                                    <td class="text-center">{{ $t }}</td>
                                    <td class="text-center">{{ $d }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Esperando datos…</td></tr>
                            @endforelse

                            <tr class="fw-semibold"><td>Sub-Total</td><td class="text-center">{{ $subTrays }}</td><td class="text-center">{{ $subDisp }}</td></tr>
                            <tr class="table-secondary fw-semibold"><td>Total</td><td colspan="2" class="text-end">{{ $subTrays + $subDisp }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Orden: {{ $cart->order }}</span>
                    <span class="text-muted small"><i class="ri-history-line"></i> {{ $cart->updated_at?->diffForHumans() ?? '—' }}</span>
                </div>

            </div>
        </div>
    </div>
@empty
    <div class="col-12"><div class="alert alert-info mb-0">No hay carritos en este hospital.</div></div>
@endforelse
</div>
