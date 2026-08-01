@extends('layouts.app')

@section('title', 'Parqueadero')

@section('content')

{{-- ── Encabezado ─────────────────────────────────────────────────────── --}}
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1><i class="fas fa-parking"></i> Parqueadero — Módulos y Operaciones</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarEntrada">
            <i class="fas fa-camera me-1"></i> Registrar Entrada
        </button>
        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalCrearModulo">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Módulo
        </button>
    </div>
</div>

{{-- ── Tarjetas KPI ────────────────────────────────────────────────────── --}}
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-value">{{ $disponibles }}</div>
            <div class="kpi-label">Módulos Disponibles</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon"><i class="fas fa-car"></i></div>
            <div class="kpi-value">{{ $ocupados }}</div>
            <div class="kpi-label">Módulos Ocupados</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card h-100">
            <div class="kpi-icon"><i class="fas fa-tools"></i></div>
            <div class="kpi-value">{{ $mantenimiento }}</div>
            <div class="kpi-label">En Mantenimiento</div>
        </div>
    </div>
</div>

{{-- ── Mapa de módulos ─────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="fas fa-th me-2"></i> Mapa de Módulos</h5>
        <div class="mapa-leyenda">
            <span class="text-white"><i class="fas fa-square text-success me-1"></i> Disponible</span>
            <span class="text-white"><i class="fas fa-square text-danger me-1"></i> Ocupado</span>
            <span class="text-white"><i class="fas fa-square text-warning me-1"></i> Mantenimiento</span>
        </div>
    </div>
    <div class="card-body">
        @if($modulos->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-th-large fa-3x mb-3"></i>
                <p>No hay módulos registrados. Usa "Nuevo Módulo" para comenzar.</p>
            </div>
        @else
        <div class="parking-map">
            @foreach($modulos as $modulo)
                @php $entrada = $entradasActivas->get($modulo->id_modulo); @endphp
                <div class="parking-space {{ strtolower($modulo->estado) }}"
                    @if($modulo->estado === 'DISPONIBLE')
                        onclick="abrirModalCambioEstado({{ $modulo->id_modulo }}, '{{ addslashes($modulo->ubicacion) }}', 'DISPONIBLE')"
                        title="Disponible — clic para cambiar estado"
                    @elseif($modulo->estado === 'OCUPADO' && $entrada)
                        data-entrada-id="{{ $entrada->id_entrada }}"
                        data-placa="{{ $entrada->placa }}"
                        data-ubicacion="{{ $modulo->ubicacion }}"
                        data-propietario="{{ $entrada->vehiculo->cliente->user->nombre ?? 'Cliente General' }}"
                        data-tarifa="{{ $entrada->tipoServicio->tarifa ?? 2.00 }}"
                        data-servicio="{{ $entrada->tipoServicio->nombre_tipo_servicio ?? 'Tarifa Estándar' }}"
                        data-hora="{{ \Carbon\Carbon::parse($entrada->fecha_hora_entrada)->format('h:i A - d/m/Y') }}"
                        data-foto="{{ $entrada->foto_entrada ? asset($entrada->foto_entrada) : '' }}"
                        onclick="abrirModalSalida(this)"
                        title="Ocupado — clic para registrar salida"
                    @elseif($modulo->estado === 'MANTENIMIENTO')
                        onclick="abrirModalCambioEstado({{ $modulo->id_modulo }}, '{{ addslashes($modulo->ubicacion) }}', 'MANTENIMIENTO')"
                        title="En mantenimiento — clic para cambiar estado"
                    @endif
                >
                    <span>{{ $modulo->ubicacion }}</span>
                    @if($modulo->estado === 'OCUPADO' && $entrada)
                        <small class="modulo-placa">{{ $entrada->placa }}</small>
                    @elseif($modulo->estado === 'MANTENIMIENTO')
                        <small class="modulo-mant-icon"><i class="fas fa-tools"></i></small>
                    @endif
                    <div class="module-actions" onclick="event.stopPropagation()">
                        <button class="btn-mod" title="Editar"
                            onclick="editarModulo({{ $modulo->id_modulo }},'{{ addslashes($modulo->ubicacion) }}','{{ $modulo->estado }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-mod del" title="Eliminar"
                            onclick="eliminarModulo({{ $modulo->id_modulo }},'{{ addslashes($modulo->ubicacion) }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ════════════════════ MODAL: REGISTRAR ENTRADA ════════════════════ --}}
<div class="modal fade" id="modalRegistrarEntrada" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sign-in-alt me-2 text-success"></i> Registrar Ingreso de Vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="detenerCamara()"></button>
            </div>
            <form action="{{ route('parqueadero.entrada') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="foto_base64" id="foto_base64">

                <div class="modal-body">

                    {{-- ── Selector de modo ── --}}
                    <ul class="nav nav-tabs mb-4" id="tabsModoEntrada" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="tab-ocr-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-ocr"
                                type="button" role="tab" onclick="detenerCamara()">
                                <i class="fas fa-camera me-1"></i> Con Fotografía / OCR
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="tab-manual-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-manual"
                                type="button" role="tab" onclick="detenerCamara()">
                                <i class="fas fa-keyboard me-1"></i> Manual
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- ══════ TAB 1: OCR CON CÁMARA ══════ --}}
                        <div class="tab-pane fade show active" id="tab-ocr" role="tabpanel">
                            <div class="row">
                                {{-- Datos --}}
                                <div class="col-md-6 border-end">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Módulo Asignado</label>
                                        <select name="id_modulo" id="select_modulo" class="form-select" required>
                                            <option value="">Seleccione un módulo disponible...</option>
                                            @foreach($modulosLibres as $libre)
                                                <option value="{{ $libre->id_modulo }}">Módulo {{ $libre->ubicacion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Placa detectada</label>
                                        <input type="text" name="placa" id="input_placa"
                                            class="form-control fw-bold input-placa"
                                            placeholder="Se completa al extraer placa…" required>
                                        <small class="text-muted">Puedes editarla manualmente si es necesario.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tipo de Servicio / Tarifa</label>
                                        <select name="id_tipo_servicio" id="select_servicio_ocr" class="form-select" required>
                                            @foreach($tiposServicio as $ts)
                                                <option value="{{ $ts->id_tipo_servicio }}">
                                                    {{ $ts->nombre_tipo_servicio }} — ${{ number_format($ts->tarifa,2) }}/hr
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Propietario</label>
                                        <input type="text" name="propietario" id="ocr_propietario"
                                            class="form-control" placeholder="Se completa si el vehículo está registrado">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Marca / Modelo</label>
                                        <input type="text" name="marca_modelo" id="ocr_marca_modelo"
                                            class="form-control" placeholder="Se completa si el vehículo está registrado">
                                    </div>
                                </div>
                                {{-- Cámara --}}
                                <div class="col-md-6 text-center">
                                    <label class="form-label fw-bold d-block text-start mb-2">
                                        <i class="fas fa-video me-1"></i> Captura de Fotografía
                                    </label>
                                    <div class="camara-viewport">
                                        <video id="webcam-feed" autoplay playsinline class="camara-video d-none"></video>
                                        <img id="foto-preview" class="camara-preview d-none" alt="Vista previa">
                                        <canvas id="webcam-canvas" class="d-none"></canvas>
                                        <div id="camara-placeholder" class="p-3 text-center">
                                            <i class="fas fa-camera fa-3x mb-2 camara-placeholder-icon"></i>
                                            <p class="small mb-0 text-white">
                                                Clic en <strong>"Iniciar Cámara"</strong> para capturar la foto del vehículo.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                                        <button type="button" id="btn-iniciar-camara" class="btn btn-dark btn-sm" onclick="iniciarCamara()">
                                            <i class="fas fa-video me-1"></i> Iniciar Cámara
                                        </button>
                                        <button type="button" id="btn-capturar" class="btn btn-primary btn-sm d-none" onclick="capturarFoto()">
                                            <i class="fas fa-camera me-1"></i> Capturar
                                        </button>
                                        <button type="button" id="btn-recapturar" class="btn btn-dark btn-sm d-none" onclick="recapturarFoto()">
                                            <i class="fas fa-redo me-1"></i> Volver a Tomar
                                        </button>
                                        <button type="button" id="btn-extraer-placa" class="btn btn-success btn-sm d-none" onclick="extraerPlaca()">
                                            <i class="fas fa-id-card me-1"></i> Leer Placa con OCR
                                        </button>
                                    </div>
                                    <div class="mt-3 text-start">
                                        <label class="form-label small text-muted">O subir imagen desde archivo:</label>
                                        <input type="file" name="foto_archivo" id="foto_archivo_ocr" accept="image/*" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ══════ TAB 2: MANUAL ══════ --}}
                        <div class="tab-pane fade" id="tab-manual" role="tabpanel">
                            <div class="row">

                            {{-- Columna izquierda: selección y datos del vehículo --}}
                                <div class="col-md-6 border-end">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Módulo Asignado</label>
                                        <select name="id_modulo" id="select_modulo_manual" class="form-select" required>
                                            <option value="">Seleccione un módulo disponible...</option>
                                            @foreach($modulosLibres as $libre)
                                                <option value="{{ $libre->id_modulo }}">Módulo {{ $libre->ubicacion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Seleccionar Vehículo por Placa</label>
                                        <select id="select_vehiculo_manual" class="form-select">
                                            <option value="">Buscar placa registrada...</option>
                                            @foreach($vehiculosRegistrados as $v)
                                                <option value="{{ $v['placa'] }}"
                                                    data-cedula="{{ $v['cedula'] }}"
                                                    data-propietario="{{ $v['propietario'] }}"
                                                    data-marca="{{ $v['marca'] }}"
                                                    data-modelo="{{ $v['modelo'] }}">
                                                    {{ $v['placa'] }} — {{ $v['marca'] }} {{ $v['modelo'] }} ({{ $v['propietario'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Al seleccionar se completa el formulario automáticamente.</small>
                                    </div>

                                    {{-- Campos hidden para el POST --}}
                                    <input type="hidden" name="placa"        id="manual_placa_hidden">
                                    <input type="hidden" name="cedula"       id="manual_cedula_hidden">
                                    <input type="hidden" name="propietario"  id="manual_propietario_hidden">
                                    <input type="hidden" name="marca_modelo" id="manual_marca_hidden">

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Placa</label>
                                        <input type="text" id="manual_placa_display"
                                            class="form-control fw-bold input-placa"
                                            placeholder="Se completa al seleccionar" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cédula del Propietario</label>
                                        <input type="text" id="manual_cedula_display"
                                            class="form-control" placeholder="Se completa al seleccionar" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Propietario</label>
                                        <input type="text" id="manual_propietario_display"
                                            class="form-control" placeholder="Se completa al seleccionar" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Marca / Modelo</label>
                                        <input type="text" id="manual_marca_display"
                                            class="form-control" placeholder="Se completa al seleccionar" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tipo de Servicio / Tarifa</label>
                                        <select name="id_tipo_servicio" id="select_servicio_manual" class="form-select" required>
                                            @foreach($tiposServicio as $ts)
                                                <option value="{{ $ts->id_tipo_servicio }}">
                                                    {{ $ts->nombre_tipo_servicio }} — ${{ number_format($ts->tarifa,2) }}/hr
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Columna derecha: cámara obligatoria --}}
                                <div class="col-md-6 text-center">
                                    <label class="form-label fw-bold d-block text-start mb-2">
                                        <i class="fas fa-camera me-1 text-warning"></i> Foto del Vehículo
                                        <span class="text-danger fw-bold ms-1">*</span>
                                    </label>
                                    <div class="camara-viewport">
                                        <video id="webcam-feed-manual" autoplay playsinline class="camara-video d-none"></video>
                                        <img id="foto-preview-manual" class="camara-preview d-none" alt="Vista previa manual">
                                        <canvas id="webcam-canvas-manual" class="d-none"></canvas>
                                        <div id="camara-placeholder-manual" class="p-3 text-center">
                                            <i class="fas fa-camera fa-3x mb-2 camara-placeholder-icon"></i>
                                            <p class="small mb-0 text-white">
                                                Clic en <strong>"Iniciar Cámara"</strong> para tomar la foto del vehículo.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                                        <button type="button" id="btn-iniciar-camara-manual" class="btn btn-dark btn-sm" onclick="iniciarCamaraManual()">
                                            <i class="fas fa-video me-1"></i> Iniciar Cámara
                                        </button>
                                        <button type="button" id="btn-capturar-manual" class="btn btn-primary btn-sm d-none" onclick="capturarFotoManual()">
                                            <i class="fas fa-camera me-1"></i> Capturar
                                        </button>
                                        <button type="button" id="btn-recapturar-manual" class="btn btn-dark btn-sm d-none" onclick="recapturarFotoManual()">
                                            <i class="fas fa-redo me-1"></i> Volver a Tomar
                                        </button>
                                    </div>
                                    <div class="mt-3 text-start">
                                        <label class="form-label small text-muted">O subir imagen desde archivo:</label>
                                        <input type="file" name="foto_archivo" id="foto_archivo_manual" accept="image/*" class="form-control form-control-sm">
                                    </div>
                                    <div id="manual_foto_error" class="alert alert-danger py-2 mt-2 d-none">
                                        <i class="fas fa-exclamation-triangle me-1"></i> La fotografía es obligatoria.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="detenerCamara()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" onclick="return prepararFormEntrada()">
                        <i class="fas fa-check-circle me-1"></i> Registrar Ingreso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════ MODAL: REGISTRAR SALIDA ════════════════════ --}}
<div class="modal fade" id="modalRegistrarSalida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sign-out-alt me-2 text-danger"></i> Procesar Salida y Cobro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="detenerCamaraSalida()"></button>
            </div>
            <form action="{{ route('parqueadero.salida') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_entrada"         id="salida_id_entrada">
                <input type="hidden" name="foto_base64_salida" id="foto_base64_salida">
                <div class="modal-body">
                    <div class="row">
                        {{-- Datos del cobro --}}
                        <div class="col-md-6 border-end">
                            <div id="salida_foto_entrada_container" class="mb-3 d-none">
                                <label class="form-label small fw-bold text-muted mb-1">
                                    <i class="fas fa-sign-in-alt me-1 text-success"></i> Foto al ingreso:
                                </label>
                                <div class="rounded-3 overflow-hidden border">
                                    <img id="salida_foto_img" src="" alt="Foto entrada" class="foto-entrada-thumb">
                                </div>
                            </div>
                            <div class="p-3 border rounded-3 bg-light mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-muted small">Módulo:</span>
                                    <span class="fw-bold text-dark" id="salida_ubicacion">--</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-muted small">Placa:</span>
                                    <span class="fw-bold text-dark" id="salida_placa">--</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-muted small">Propietario:</span>
                                    <span class="text-dark small" id="salida_propietario">--</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-muted small">Hora de Entrada:</span>
                                    <span class="text-dark small" id="salida_hora">--</span>
                                </div>
                                <div class="d-flex justify-content-between mb-0">
                                    <span class="fw-bold text-muted small">Servicio / Tarifa:</span>
                                    <span class="text-success fw-bold small" id="salida_servicio">--</span>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Método de Pago</label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="EFECTIVO" selected>Efectivo</option>
                                    <option value="TARJETA">Tarjeta de Crédito / Débito</option>
                                    <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                </select>
                            </div>
                        </div>
                        {{-- Cámara salida --}}
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-bold d-block text-start mb-2">
                                <i class="fas fa-camera me-1 text-warning"></i> Foto de Salida
                            </label>
                            <div class="camara-viewport camara-viewport-sm">
                                <video id="webcam-feed-salida" autoplay playsinline class="camara-video d-none"></video>
                                <img id="foto-preview-salida" class="camara-preview d-none" alt="Vista previa salida">
                                <canvas id="webcam-canvas-salida" class="d-none"></canvas>
                                <div id="camara-placeholder-salida" class="p-3 text-center">
                                    <i class="fas fa-camera fa-3x mb-2 camara-placeholder-icon"></i>
                                    <p class="small mb-0 text-white">Captura la foto al momento de la salida.</p>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                                <button type="button" id="btn-iniciar-camara-salida" class="btn btn-dark btn-sm" onclick="iniciarCamaraSalida()">
                                    <i class="fas fa-video me-1"></i> Iniciar Cámara
                                </button>
                                <button type="button" id="btn-capturar-salida" class="btn btn-primary btn-sm d-none" onclick="capturarFotoSalida()">
                                    <i class="fas fa-camera me-1"></i> Capturar
                                </button>
                                <button type="button" id="btn-recapturar-salida" class="btn btn-dark btn-sm d-none" onclick="recapturarFotoSalida()">
                                    <i class="fas fa-redo me-1"></i> Volver a Tomar
                                </button>
                            </div>
                            <div class="mt-3 text-start">
                                <label class="form-label small text-muted">O subir imagen desde archivo:</label>
                                <input type="file" name="foto_archivo_salida" accept="image/*" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="detenerCamaraSalida()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check-circle me-1"></i> Liquidar y Liberar Módulo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════ MODAL: CREAR MÓDULO ════════════════════ --}}
<div class="modal fade" id="modalCrearModulo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Crear Nuevo Módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('parqueadero.modulos.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ubicación del Módulo</label>
                        <input type="text" name="ubicacion" class="form-control input-uppercase"
                            placeholder="Ej: A-01, MOTO-03" required>
                        <small class="text-muted">Máximo 20 caracteres, debe ser único.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado Inicial</label>
                        <select name="estado" class="form-select" required>
                            <option value="DISPONIBLE">DISPONIBLE</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Crear Módulo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════ MODAL: EDITAR MÓDULO ════════════════════ --}}
<div class="modal fade" id="modalEditarModulo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarModulo" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">ID del Módulo (no modificable)</label>
                        <input type="text" id="edit_id_modulo" class="form-control form-control-sm" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ubicación</label>
                        <input type="text" name="ubicacion" id="edit_ubicacion"
                            class="form-control input-uppercase" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" id="edit_estado" class="form-select" required>
                            <option value="DISPONIBLE">DISPONIBLE</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            El estado <strong>OCUPADO</strong> lo asigna el sistema automáticamente al registrar una entrada.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════ MODAL: ELIMINAR MÓDULO ════════════════════ --}}
<div class="modal fade" id="modalEliminarModulo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-trash me-2"></i> Eliminar Módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEliminarModulo" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>¿Eliminar el módulo <strong id="eliminar_ubicacion"></strong>?</p>
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Esta acción no se puede deshacer. No se puede eliminar si tiene un vehículo activo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════ MODAL: CAMBIO DE ESTADO (desde mapa) ════════════════════ --}}
<div class="modal fade" id="modalCambioEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i> Cambiar Estado del Módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Módulo: <strong id="ce_ubicacion"></strong></p>
                <p class="mb-3 text-muted small">Estado actual: <span id="ce_estado_actual" class="fw-bold"></span></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cambiar a</label>
                    <select id="ce_nuevo_estado" class="form-select">
                        <option value="DISPONIBLE">DISPONIBLE</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                    </select>
                    <small class="text-muted">
                        El estado <strong>OCUPADO</strong> lo asigna el sistema automáticamente al registrar una entrada.
                    </small>
                </div>
                <div id="ce_error" class="alert alert-danger d-none py-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let webcamStream = null, webcamStreamSalida = null;

// ── Utilidades de visibilidad ─────────────────────────────────────────────
function show(id) { document.getElementById(id).classList.remove('d-none'); }
function hide(id) { document.getElementById(id).classList.add('d-none'); }

// ── Abrir modal de Entrada preseleccionando módulo ────────────────────────
function abrirModalEntradaConModulo(idModulo) {
    const sel = document.getElementById('select_modulo');
    if (sel) sel.value = idModulo;
    new bootstrap.Modal(document.getElementById('modalRegistrarEntrada')).show();
}

// ── Abrir modal de Salida con datos del módulo ────────────────────────────
function abrirModalSalida(el) {
    document.getElementById('salida_id_entrada').value       = el.dataset.entradaId   || '';
    document.getElementById('salida_placa').textContent       = el.dataset.placa       || '--';
    document.getElementById('salida_ubicacion').textContent   = el.dataset.ubicacion   || '--';
    document.getElementById('salida_propietario').textContent = el.dataset.propietario || '--';
    document.getElementById('salida_hora').textContent        = el.dataset.hora        || '--';
    document.getElementById('salida_servicio').textContent    =
        (el.dataset.servicio || '--') + ' — $' + (el.dataset.tarifa || '0') + '/hr';

    const cont = document.getElementById('salida_foto_entrada_container');
    const img  = document.getElementById('salida_foto_img');
    if (el.dataset.foto) {
        img.src = el.dataset.foto;
        cont.classList.remove('d-none');
    } else {
        cont.classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('modalRegistrarSalida')).show();
}

// ── CRUD Módulos ──────────────────────────────────────────────────────────
function editarModulo(id, ubicacion, estado) {
    document.getElementById('edit_id_modulo').value = id;
    document.getElementById('edit_ubicacion').value = ubicacion;
    document.getElementById('edit_estado').value    = estado;
    document.getElementById('formEditarModulo').action =
        '{{ route("parqueadero.modulos.update", ":id") }}'.replace(':id', id);
    new bootstrap.Modal(document.getElementById('modalEditarModulo')).show();
}

function eliminarModulo(id, ubicacion) {
    document.getElementById('eliminar_ubicacion').textContent = ubicacion;
    document.getElementById('formEliminarModulo').action =
        '{{ route("parqueadero.modulos.destroy", ":id") }}'.replace(':id', id);
    new bootstrap.Modal(document.getElementById('modalEliminarModulo')).show();
}

// ── Cámara de Entrada ─────────────────────────────────────────────────────
function iniciarCamara() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 } } })
        .then(stream => {
            webcamStream = stream;
            const v = document.getElementById('webcam-feed');
            v.srcObject = stream;
            show('webcam-feed');
            hide('camara-placeholder');
            hide('btn-iniciar-camara');
            show('btn-capturar');
        })
        .catch(e => alert('Cámara no disponible: ' + e.message));
}

function capturarFoto() {
    const v = document.getElementById('webcam-feed');
    const c = document.getElementById('webcam-canvas');
    const p = document.getElementById('foto-preview');
    c.width  = v.videoWidth  || 640;
    c.height = v.videoHeight || 480;
    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
    const dataUrl = c.toDataURL('image/jpeg', 0.85);
    document.getElementById('foto_base64').value = dataUrl;
    p.src = dataUrl;
    show('foto-preview');
    hide('webcam-feed');
    detenerCamara();
    hide('btn-capturar');
    show('btn-recapturar');
    show('btn-extraer-placa');
}

function recapturarFoto() {
    hide('foto-preview');
    document.getElementById('foto_base64').value = '';
    hide('btn-recapturar');
    hide('btn-extraer-placa');
    iniciarCamara();
}

function detenerCamara() {
    if (webcamStream) { webcamStream.getTracks().forEach(t => t.stop()); webcamStream = null; }
}

// ── Cámara de Salida ──────────────────────────────────────────────────────
function iniciarCamaraSalida() {
    navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 } } })
        .then(stream => {
            webcamStreamSalida = stream;
            const v = document.getElementById('webcam-feed-salida');
            v.srcObject = stream;
            show('webcam-feed-salida');
            hide('camara-placeholder-salida');
            hide('btn-iniciar-camara-salida');
            show('btn-capturar-salida');
        })
        .catch(e => alert('Cámara no disponible: ' + e.message));
}

function capturarFotoSalida() {
    const v = document.getElementById('webcam-feed-salida');
    const c = document.getElementById('webcam-canvas-salida');
    const p = document.getElementById('foto-preview-salida');
    c.width  = v.videoWidth  || 640;
    c.height = v.videoHeight || 480;
    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
    const dataUrl = c.toDataURL('image/jpeg', 0.85);
    document.getElementById('foto_base64_salida').value = dataUrl;
    p.src = dataUrl;
    show('foto-preview-salida');
    hide('webcam-feed-salida');
    detenerCamaraSalida();
    hide('btn-capturar-salida');
    show('btn-recapturar-salida');
}

function recapturarFotoSalida() {
    hide('foto-preview-salida');
    document.getElementById('foto_base64_salida').value = '';
    hide('btn-recapturar-salida');
    iniciarCamaraSalida();
}

function detenerCamaraSalida() {
    if (webcamStreamSalida) { webcamStreamSalida.getTracks().forEach(t => t.stop()); webcamStreamSalida = null; }
}

// ── OCR Placa ─────────────────────────────────────────────────────────────
function extraerPlaca() {
    const foto = document.getElementById('foto_base64').value;
    const btn  = document.getElementById('btn-extraer-placa');
    if (!foto) { alert('Captura la foto primero.'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';

    fetch('{{ route("parqueadero.ocr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ foto })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-id-card me-1"></i> Leer Placa con OCR';
        if (data.success) {
            document.getElementById('input_placa').value = data.placa;
            if (data.vehiculo_existe && data.vehiculo) {
                const v = data.vehiculo;
                if (v.propietario) document.getElementById('ocr_propietario').value  = v.propietario;
                if (v.marca)       document.getElementById('ocr_marca_modelo').value = v.marca + ' ' + (v.modelo || '');
            }
        } else {
            alert(data.message || 'No se pudo detectar la placa. Ingrésala manualmente.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-id-card me-1"></i> Leer Placa con OCR';
        alert('Error al procesar la imagen.');
    });
}

// ── Datos de vehículos para autocomplete (inyectados desde PHP) ───────────
const vehiculos = @json($vehiculosRegistrados);

// ── Determinar tab activo ─────────────────────────────────────────────────
function tabActivo() {
    return document.getElementById('tab-ocr').classList.contains('show') ? 'ocr' : 'manual';
}

// ── Validar y unificar campos antes del submit ────────────────────────────
function prepararFormEntrada() {
    const tab = tabActivo();

    if (tab === 'manual') {
        // Validar vehículo seleccionado
        const placa = document.getElementById('manual_placa_hidden').value;
        if (!placa) { alert('Selecciona un vehículo de la lista.'); return false; }

        // Sincronizar módulo del tab manual al campo principal del form
        document.getElementById('select_modulo').value =
            document.getElementById('select_modulo_manual').value;

        // Validar foto obligatoria en tab manual
        const fotoBase64  = document.getElementById('foto_base64').value;
        const fotoArchivo = document.getElementById('foto_archivo_manual').files.length;
        const errFoto     = document.getElementById('manual_foto_error');
        if (!fotoBase64 && !fotoArchivo) {
            errFoto.classList.remove('d-none');
            return false;
        }
        errFoto.classList.add('d-none');

        // Si la foto fue tomada con la cámara manual, ya está en foto_base64
        // Si fue archivo, ya está en foto_archivo con name="foto_archivo"
    } else {
        // Tab OCR: validar foto obligatoria también
        const fotoBase64  = document.getElementById('foto_base64').value;
        const fotoArchivo = document.getElementById('foto_archivo_ocr').files.length;
        if (!fotoBase64 && !fotoArchivo) {
            alert('La fotografía es obligatoria. Captura o sube una foto del vehículo.');
            return false;
        }
    }

    return true;
}

// ── Cámara del tab Manual ─────────────────────────────────────────────────
let webcamStreamManual = null;

function iniciarCamaraManual() {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 } } })
        .then(stream => {
            webcamStreamManual = stream;
            const v = document.getElementById('webcam-feed-manual');
            v.srcObject = stream;
            show('webcam-feed-manual'); hide('camara-placeholder-manual');
            hide('btn-iniciar-camara-manual'); show('btn-capturar-manual');
        })
        .catch(e => alert('Cámara no disponible: ' + e.message));
}

function capturarFotoManual() {
    const v = document.getElementById('webcam-feed-manual');
    const c = document.getElementById('webcam-canvas-manual');
    const p = document.getElementById('foto-preview-manual');
    c.width = v.videoWidth || 640; c.height = v.videoHeight || 480;
    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
    const dataUrl = c.toDataURL('image/jpeg', 0.85);
    // Reutilizamos el mismo campo hidden foto_base64 del form
    document.getElementById('foto_base64').value = dataUrl;
    p.src = dataUrl;
    show('foto-preview-manual'); hide('webcam-feed-manual');
    detenerCamaraManual();
    hide('btn-capturar-manual'); show('btn-recapturar-manual');
    hide('manual_foto_error');
}

function recapturarFotoManual() {
    hide('foto-preview-manual');
    document.getElementById('foto_base64').value = '';
    hide('btn-recapturar-manual');
    iniciarCamaraManual();
}

function detenerCamaraManual() {
    if (webcamStreamManual) { webcamStreamManual.getTracks().forEach(t => t.stop()); webcamStreamManual = null; }
}

// ── Modal de cambio de estado (desde módulo en mapa) ─────────────────────
let ceModuloId = null;

function abrirModalCambioEstado(idModulo, ubicacion, estadoActual) {
    ceModuloId = idModulo;
    document.getElementById('ce_ubicacion').textContent     = ubicacion;
    document.getElementById('ce_estado_actual').textContent = estadoActual;
    document.getElementById('ce_error').classList.add('d-none');
    const sel = document.getElementById('ce_nuevo_estado');
    sel.value = (estadoActual === 'DISPONIBLE') ? 'MANTENIMIENTO' : 'DISPONIBLE';
    new bootstrap.Modal(document.getElementById('modalCambioEstado')).show();
}

function confirmarCambioEstado() {
    const nuevoEstado = document.getElementById('ce_nuevo_estado').value;
    const errEl       = document.getElementById('ce_error');
    errEl.classList.add('d-none');

    fetch('{{ route("parqueadero.modulos.estado", ":id") }}'.replace(':id', ceModuloId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalCambioEstado')).hide();
            location.reload();
        } else {
            errEl.textContent = data.message || 'Error al cambiar el estado.';
            errEl.classList.remove('d-none');
        }
    })
    .catch(() => {
        errEl.textContent = 'Error de conexión. Intenta nuevamente.';
        errEl.classList.remove('d-none');
    });
}

// ── Limpiar cámaras al cerrar modales ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Autocomplete al seleccionar vehículo en modo manual
    const selVeh = document.getElementById('select_vehiculo_manual');
    if (selVeh) {
        selVeh.addEventListener('change', function () {
            const opt         = this.options[this.selectedIndex];
            const placa       = opt.value;
            const cedula      = opt.dataset.cedula || '';
            const propietario = opt.dataset.propietario || '';
            const marca       = opt.dataset.marca       || '';
            const modelo      = opt.dataset.modelo      || '';
            const marcaModelo = marca + (modelo ? ' ' + modelo : '');

            document.getElementById('manual_placa_hidden').value       = placa;
            document.getElementById('manual_cedula_hidden').value      = cedula;
            document.getElementById('manual_propietario_hidden').value = propietario;
            document.getElementById('manual_marca_hidden').value       = marcaModelo;

            document.getElementById('manual_placa_display').value       = placa;
            document.getElementById('manual_cedula_display').value      = cedula;
            document.getElementById('manual_propietario_display').value = propietario;
            document.getElementById('manual_marca_display').value       = marcaModelo;
        });
    }

    document.getElementById('modalRegistrarEntrada').addEventListener('hidden.bs.modal', () => {
        detenerCamara();
        detenerCamaraManual();
        hide('foto-preview'); hide('webcam-feed'); show('camara-placeholder');
        show('btn-iniciar-camara'); hide('btn-capturar');
        hide('btn-recapturar'); hide('btn-extraer-placa');
        hide('foto-preview-manual'); hide('webcam-feed-manual'); show('camara-placeholder-manual');
        show('btn-iniciar-camara-manual'); hide('btn-capturar-manual'); hide('btn-recapturar-manual');
        document.getElementById('foto_base64').value = '';
        document.getElementById('manual_foto_error').classList.add('d-none');
    });

    document.getElementById('modalRegistrarSalida').addEventListener('hidden.bs.modal', () => {
        detenerCamaraSalida();
        hide('foto-preview-salida'); hide('webcam-feed-salida'); show('camara-placeholder-salida');
        show('btn-iniciar-camara-salida'); hide('btn-capturar-salida');
        hide('btn-recapturar-salida');
        document.getElementById('foto_base64_salida').value = '';
    });
});
</script>

@endsection
