@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-credit-card"></i> Gestión de Pagos y Facturación</h1>
        @if($facturasPendientes->count() > 0)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarPago">
            <i class="fas fa-plus-circle me-1"></i> Registrar Pago Pendiente
        </button>
        @endif
    </div>

    <!-- Resumen de Pagos -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="kpi-value text-success">${{ number_format($pagosHoy, 2) }}</div>
                <div class="kpi-label">Pagos Hoy ({{ $transaccionesHoy }} Trans.)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="kpi-value text-dark">${{ number_format($pagosSemana, 2) }}</div>
                <div class="kpi-label">Esta Semana ({{ $transaccionesSemana }} Trans.)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="kpi-value text-dark">${{ number_format($pagosMes, 2) }}</div>
                <div class="kpi-label">Este Mes ({{ $transaccionesMes }} Trans.)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#ef4444; color:#fff;"><i class="fas fa-clock"></i></div>
                <div class="kpi-value text-danger">${{ number_format($pendientesMonto, 2) }}</div>
                <div class="kpi-label">Pendientes ({{ $pendientesCantidad }} Facturas)</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('pagos') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado de Transacción</label>
                        <select name="estado" class="form-select">
                            <option {{ ($estadoFiltro ?? '') === 'Todos' ? 'selected' : '' }}>Todos</option>
                            <option value="Pagado" {{ ($estadoFiltro ?? '') === 'Pagado' ? 'selected' : '' }}>Pagado</option>
                            <option value="Pendiente" {{ ($estadoFiltro ?? '') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter me-1"></i> Aplicar Filtros</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Historial de Comprobantes de Pago</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nº Factura</th>
                            <th>Vehículo</th>
                            <th>Propietario</th>
                            <th>Monto Recaudado</th>
                            <th>Método Pago</th>
                            <th>Fecha & Hora</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($facturas as $f)
                        @php
                            $entrada = $f->salida->entrada ?? null;
                            $vehiculo = $entrada->vehiculo ?? null;
                            $propietario = $vehiculo->cliente->user->nombre ?? 'Cliente General';
                        @endphp
                        <tr>
                            <td><strong class="text-dark">#FAC-{{ str_pad($f->id_factura, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $vehiculo->placa ?? 'N/A' }}</td>
                            <td>{{ $propietario }}</td>
                            <td><strong class="text-dark">${{ number_format($f->monto_total, 2) }}</strong></td>
                            <td><span class="badge bg-dark">{{ $f->metodo_pago ?? 'Sin definir' }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($f->fecha_emision)->format('h:i A - d/m/Y') }}</td>
                            <td>
                                @if($f->estado_pago === 'PAGADO')
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-danger">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info me-1" onclick="verComprobante({{ $f->id_factura }})" title="Ver Recibo"><i class="fas fa-receipt"></i></button>
                                <button class="btn btn-sm btn-warning" onclick="imprimirComprobante({{ $f->id_factura }})" title="Imprimir Comprobante"><i class="fas fa-print"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No hay registro de facturas emitidas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Pago -->
    @if($facturasPendientes->count() > 0)
    <div class="modal fade" id="modalRegistrarPago" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i> Registrar Pago Pendiente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pagos.procesar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Factura Pendiente</label>
                            <select name="id_factura" id="select_factura_pendiente" class="form-select" required onchange="actualizarMontoFactura(this)">
                                <option value="">Seleccione una factura...</option>
                                @foreach($facturasPendientes as $fp)
                                    <option value="{{ $fp->id_factura }}" data-monto="{{ $fp->monto_total }}">
                                        #FAC-{{ str_pad($fp->id_factura, 4, '0', STR_PAD_LEFT) }} — {{ $fp->salida->entrada->placa ?? 'N/A' }} (${{ number_format($fp->monto_total, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto a Recaudar ($)</label>
                            <input type="number" name="monto_total" id="input_monto_pago" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Forma de Pago</label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TARJETA">Tarjeta de Crédito / Débito</option>
                                <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Ver Comprobante -->
    <div class="modal fade" id="modalVerComprobante" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-ticket-alt me-2"></i> Comprobante de Pago — PARKING<span class="text-warning">SURE</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="comprobante_cuerpo" class="p-3 border rounded-3 bg-light">
                        <h4 class="fw-bold mb-1">PARKINGSURE S.A.</h4>
                        <p class="text-muted small mb-3">Comprobante Digital de Servicio de Parqueadero</p>
                        <hr>
                        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Nº Factura:</span><span id="comp_numero">--</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Vehículo:</span><span id="comp_placa">--</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Cliente:</span><span id="comp_cliente">--</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Fecha Emisión:</span><span id="comp_fecha">--</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="fw-bold">Método Pago:</span><span id="comp_metodo">--</span></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark"><span>Total Cobrado:</span><span id="comp_total" class="text-success">$0.00</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function actualizarMontoFactura(select) {
            const selectedOption = select.options[select.selectedIndex];
            const monto = selectedOption.getAttribute('data-monto');
            if (monto) {
                document.getElementById('input_monto_pago').value = monto;
            }
        }

        function verComprobante(idFactura) {
            fetch('{{ route('pagos.comprobante', ':id') }}'.replace(':id', idFactura))
                .then(res => res.json())
                .then(data => {
                    document.getElementById('comp_numero').textContent = '#FAC-' + String(data.id_factura).padStart(4, '0');
                    document.getElementById('comp_placa').textContent = data.salida?.entrada?.placa || 'N/A';
                    document.getElementById('comp_cliente').textContent = data.salida?.entrada?.vehiculo?.cliente?.user?.name || 'Cliente General';
                    document.getElementById('comp_fecha').textContent = new Date(data.fecha_emision).toLocaleString();
                    document.getElementById('comp_metodo').textContent = data.metodo_pago || 'EFECTIVO';
                    document.getElementById('comp_total').textContent = '$' + parseFloat(data.monto_total).toFixed(2);

                    var modal = new bootstrap.Modal(document.getElementById('modalVerComprobante'));
                    modal.show();
                })
                .catch(err => console.error(err));
        }

        function imprimirComprobante(idFactura) {
            verComprobante(idFactura);
        }
    </script>
@endsection
