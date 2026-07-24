@extends('layouts.app')

@section('title', 'Parqueadero')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-parking"></i> Control y Monitoreo de Módulos</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarEntrada">
            <i class="fas fa-camera me-1"></i> Registrar Entrada con Foto
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Estado General de Módulos</h5>
                </div>
                <div class="card-body d-flex align-items-center">
                    <div class="row text-center w-100">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <h2 class="text-success font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $disponibles }}</h2>
                                <span class="badge bg-success">Módulos Libres</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <h2 class="text-danger font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $ocupados }}</h2>
                                <span class="badge bg-danger">Módulos Ocupados</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-3 bg-light">
                                <h2 class="text-warning font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $mantenimiento }}</h2>
                                <span class="badge bg-warning">Mantenimiento</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i> Ocupación de Módulos por Nivel</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark">Nivel A — Módulos Principales</span>
                            <span class="badge bg-warning">{{ $nivelA_Ocupados }} / 20 Módulos ({{ round(($nivelA_Ocupados/20)*100) }}%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ round(($nivelA_Ocupados/20)*100) }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark">Nivel B — Módulos Preferenciales</span>
                            <span class="badge bg-warning">{{ $nivelB_Ocupados }} / 20 Módulos ({{ round(($nivelB_Ocupados/20)*100) }}%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ round(($nivelB_Ocupados/20)*100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold text-dark">Nivel C — Módulos Subterráneos</span>
                            <span class="badge bg-warning">{{ $nivelC_Ocupados }} / 20 Módulos ({{ round(($nivelC_Ocupados/20)*100) }}%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" style="width: {{ round(($nivelC_Ocupados/20)*100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa de Módulos -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-th me-2"></i> Mapa interactivo de Módulos — Nivel A</h5>
            <div class="d-flex gap-3 text-white" style="font-size: 0.85rem; font-weight: 600;">
                <span><i class="fas fa-square text-success me-1"></i> Módulo Disponible</span>
                <span><i class="fas fa-square text-danger me-1"></i> Módulo Ocupado</span>
                <span><i class="fas fa-square text-warning me-1"></i> Mantenimiento</span>
            </div>
        </div>
        <div class="card-body">
            <div class="parking-map">
                @foreach ($modulosNivelA as $modulo)
                    @php
                        $entrada = $entradasActivas->get($modulo->id_modulo);
                    @endphp
                    <div class="parking-space {{ strtolower($modulo->estado) }}" 
                         data-id="{{ $modulo->id_modulo }}"
                         data-ubicacion="{{ $modulo->ubicacion }}"
                         data-estado="{{ $modulo->estado }}"
                         @if($entrada)
                             data-entrada-id="{{ $entrada->id_entrada }}"
                             data-placa="{{ $entrada->placa }}"
                             data-propietario="{{ $entrada->vehiculo->cliente->user->nombre ?? 'Cliente General' }}"
                             data-tarifa="{{ $entrada->tipoServicio->tarifa ?? 2.00 }}"
                             data-servicio="{{ $entrada->tipoServicio->nombre_tipo_servicio ?? 'Tarifa Estandar' }}"
                             data-hora="{{ \Carbon\Carbon::parse($entrada->fecha_hora_entrada)->format('h:i A - d/m/Y') }}"
                             data-foto="{{ $entrada->foto_entrada ? asset($entrada->foto_entrada) : '' }}"
                             onclick="abrirModalSalida(this)"
                         @elseif($modulo->estado === 'DISPONIBLE')
                             onclick="abrirModalEntradaConModulo({{ $modulo->id_modulo }})"
                         @endif
                    >
                        <span>{{ $modulo->ubicacion }}</span>
                        @if($entrada)
                            <small style="font-size:10px;font-weight:800;color:#991b1b;margin-top:2px;">{{ $entrada->placa }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Registrar Entrada con Captura de Foto -->
    <div class="modal fade" id="modalRegistrarEntrada" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-camera me-2"></i> Registrar Ingreso & Fotografía del Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="detenerCamara()"></button>
                </div>
                <form action="{{ route('parqueadero.entrada') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="foto_base64" id="foto_base64">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Columna de Datos de Entrada -->
                            <div class="col-md-6 border-end">
                                <div class="mb-3">
                                    <label class="form-label">Placa del Vehículo</label>
                                    <input type="text" name="placa" id="input_placa" class="form-control fw-bold" placeholder="Ej: ABC-123" required style="text-transform: uppercase; font-size:1.1rem;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Módulo Asignado</label>
                                    <select name="id_modulo" id="select_modulo" class="form-select" required>
                                        <option value="">Seleccione un Módulo disponible...</option>
                                        @foreach($modulosLibres as $libre)
                                            <option value="{{ $libre->id_modulo }}">Módulo {{ $libre->ubicacion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Servicio / Tarifa</label>
                                    <select name="id_tipo_servicio" class="form-select" required>
                                        @foreach($tiposServicio as $ts)
                                            <option value="{{ $ts->id_tipo_servicio }}">{{ $ts->nombre_tipo_servicio }} — ${{ number_format($ts->tarifa, 2) }}/hr</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Propietario (Opcional)</label>
                                    <input type="text" name="propietario" class="form-control" placeholder="Nombre completo">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Marca / Modelo (Opcional)</label>
                                    <input type="text" name="marca_modelo" class="form-control" placeholder="Ej: Toyota Corolla">
                                </div>
                            </div>

                            <!-- Columna de Captura de Fotografía por Cámara -->
                            <div class="col-md-6 text-center">
                                <label class="form-label fw-bold d-block text-start mb-2"><i class="fas fa-video me-1"></i> Foto de Entrada de Vehículo</label>

                                <!-- Visualizador de Cámara Webcam -->
                                <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="height: 220px; border: 2px dashed #cbd5e1;">
                                    <video id="webcam-feed" autoplay playsinline style="width:100%; height:100%; object-fit:cover; display:none;"></video>
                                    <img id="foto-preview" style="width:100%; height:100%; object-fit:cover; display:none;">
                                    <canvas id="webcam-canvas" style="display:none;"></canvas>
                                    
                                    <div id="camara-placeholder" class="p-3 text-muted">
                                        <i class="fas fa-camera fa-3x mb-2" style="color:#ffd700;"></i>
                                        <p class="small mb-0 text-white">Haz clic en <strong>"Iniciar Cámara"</strong> para capturar la foto de la placa o vehículo.</p>
                                    </div>
                                </div>

                                <!-- Botones de Control de Cámara -->
                                <div class="mt-3 d-flex gap-2 justify-content-center">
                                    <button type="button" id="btn-iniciar-camara" class="btn btn-dark btn-sm" onclick="iniciarCamara()">
                                        <i class="fas fa-video me-1"></i> Iniciar Cámara
                                    </button>
                                    <button type="button" id="btn-capturar" class="btn btn-primary btn-sm" style="display:none;" onclick="capturarFoto()">
                                        <i class="fas fa-camera me-1"></i> Capturar Foto
                                    </button>
                                    <button type="button" id="btn-recapturar" class="btn btn-dark btn-sm" style="display:none;" onclick="recapturarFoto()">
                                        <i class="fas fa-redo me-1"></i> Volver a Tomar
                                    </button>
                                </div>

                                <!-- Fallback Cargar Archivo de Imagen -->
                                <div class="mt-3 text-start">
                                    <label class="form-label small text-muted">O subir archivo de imagen:</label>
                                    <input type="file" name="foto_archivo" accept="image/*" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="detenerCamara()">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Registrar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Salida y Cobro de Módulo (con Fotografía de Salida) -->
    <div class="modal fade" id="modalRegistrarSalida" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2"></i> Procesar Salida, Fotografía y Cobro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="detenerCamaraSalida()"></button>
                </div>
                <form action="{{ route('parqueadero.salida') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_entrada" id="salida_id_entrada">
                    <input type="hidden" name="foto_base64_salida" id="foto_base64_salida">
                    <div class="modal-body">
                        <div class="row">

                            <!-- Columna Izquierda: Datos del módulo y pago -->
                            <div class="col-md-6 border-end">

                                <!-- Foto de Entrada (referencia) -->
                                <div id="salida_foto_entrada_container" class="mb-3" style="display:none;">
                                    <label class="form-label small fw-bold text-muted mb-1">
                                        <i class="fas fa-sign-in-alt me-1 text-success"></i>Foto registrada al Ingreso:
                                    </label>
                                    <div class="rounded-3 overflow-hidden border" style="height: 110px;">
                                        <img id="salida_foto_img" src="" alt="Foto de entrada" style="width:100%; height:110px; object-fit:cover;">
                                    </div>
                                </div>

                                <!-- Resumen del módulo -->
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
                                        <span class="fw-bold text-muted small">Tarifa:</span>
                                        <span class="text-success fw-bold small" id="salida_servicio">--</span>
                                    </div>
                                </div>

                                <!-- Método de pago -->
                                <div class="mb-0">
                                    <label class="form-label">Método de Pago</label>
                                    <select name="metodo_pago" class="form-select" required>
                                        <option value="EFECTIVO" selected>Efectivo</option>
                                        <option value="TARJETA">Tarjeta de Crédito / Débito</option>
                                        <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Columna Derecha: Captura de Fotografía de Salida -->
                            <div class="col-md-6 text-center">
                                <label class="form-label fw-bold d-block text-start mb-2">
                                    <i class="fas fa-camera me-1 text-warning"></i> Foto de Salida del Vehículo
                                </label>

                                <!-- Visor de cámara para salida -->
                                <div class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="height: 200px; border: 2px dashed #cbd5e1;">
                                    <video id="webcam-feed-salida" autoplay playsinline style="width:100%; height:100%; object-fit:cover; display:none;"></video>
                                    <img id="foto-preview-salida" style="width:100%; height:100%; object-fit:cover; display:none;">
                                    <canvas id="webcam-canvas-salida" style="display:none;"></canvas>

                                    <div id="camara-placeholder-salida" class="p-3 text-muted">
                                        <i class="fas fa-camera fa-3x mb-2" style="color:#ffd700;"></i>
                                        <p class="small mb-0 text-white">Captura la fotografía del vehículo <strong>al momento de salir</strong>.</p>
                                    </div>
                                </div>

                                <!-- Controles de cámara de salida -->
                                <div class="mt-3 d-flex gap-2 justify-content-center">
                                    <button type="button" id="btn-iniciar-camara-salida" class="btn btn-dark btn-sm" onclick="iniciarCamaraSalida()">
                                        <i class="fas fa-video me-1"></i> Iniciar Cámara
                                    </button>
                                    <button type="button" id="btn-capturar-salida" class="btn btn-primary btn-sm" style="display:none;" onclick="capturarFotoSalida()">
                                        <i class="fas fa-camera me-1"></i> Capturar Foto
                                    </button>
                                    <button type="button" id="btn-recapturar-salida" class="btn btn-dark btn-sm" style="display:none;" onclick="recapturarFotoSalida()">
                                        <i class="fas fa-redo me-1"></i> Volver a Tomar
                                    </button>
                                </div>

                                <!-- Fallback: subir archivo -->
                                <div class="mt-3 text-start">
                                    <label class="form-label small text-muted">O subir archivo de imagen:</label>
                                    <input type="file" name="foto_archivo_salida" accept="image/*" class="form-control form-control-sm">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="detenerCamaraSalida()">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> Liquidar y Liberar Módulo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let webcamStream = null;
        let webcamStreamSalida = null;

        // ─── Cámara de ENTRADA ──────────────────────────────────────────────
        function iniciarCamara() {
            const video = document.getElementById('webcam-feed');
            const placeholder = document.getElementById('camara-placeholder');
            const btnIniciar = document.getElementById('btn-iniciar-camara');
            const btnCapturar = document.getElementById('btn-capturar');

            navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'environment' } })
                .then(function(stream) {
                    webcamStream = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    btnIniciar.style.display = 'none';
                    btnCapturar.style.display = 'inline-block';
                })
                .catch(function(err) {
                    alert('No se pudo acceder a la cámara. Verifica permisos o usa la opción de subir archivo. Error: ' + err.message);
                });
        }

        function capturarFoto() {
            const video = document.getElementById('webcam-feed');
            const canvas = document.getElementById('webcam-canvas');
            const preview = document.getElementById('foto-preview');
            const hiddenInput = document.getElementById('foto_base64');

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            hiddenInput.value = dataUrl;
            preview.src = dataUrl;
            preview.style.display = 'block';
            video.style.display = 'none';
            detenerCamara();

            document.getElementById('btn-capturar').style.display = 'none';
            document.getElementById('btn-recapturar').style.display = 'inline-block';
        }

        function recapturarFoto() {
            document.getElementById('foto-preview').style.display = 'none';
            document.getElementById('foto_base64').value = '';
            document.getElementById('btn-recapturar').style.display = 'none';
            iniciarCamara();
        }

        function detenerCamara() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
        }

        // ─── Cámara de SALIDA ───────────────────────────────────────────────
        function iniciarCamaraSalida() {
            const video = document.getElementById('webcam-feed-salida');
            const placeholder = document.getElementById('camara-placeholder-salida');
            const btnIniciar = document.getElementById('btn-iniciar-camara-salida');
            const btnCapturar = document.getElementById('btn-capturar-salida');

            navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'environment' } })
                .then(function(stream) {
                    webcamStreamSalida = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    btnIniciar.style.display = 'none';
                    btnCapturar.style.display = 'inline-block';
                })
                .catch(function(err) {
                    alert('No se pudo acceder a la cámara. Verifica permisos o usa la opción de subir archivo. Error: ' + err.message);
                });
        }

        function capturarFotoSalida() {
            const video = document.getElementById('webcam-feed-salida');
            const canvas = document.getElementById('webcam-canvas-salida');
            const preview = document.getElementById('foto-preview-salida');
            const hiddenInput = document.getElementById('foto_base64_salida');

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            hiddenInput.value = dataUrl;
            preview.src = dataUrl;
            preview.style.display = 'block';
            video.style.display = 'none';
            detenerCamaraSalida();

            document.getElementById('btn-capturar-salida').style.display = 'none';
            document.getElementById('btn-recapturar-salida').style.display = 'inline-block';
        }

        function recapturarFotoSalida() {
            document.getElementById('foto-preview-salida').style.display = 'none';
            document.getElementById('foto_base64_salida').value = '';
            document.getElementById('btn-recapturar-salida').style.display = 'none';
            iniciarCamaraSalida();
        }

        function detenerCamaraSalida() {
            if (webcamStreamSalida) {
                webcamStreamSalida.getTracks().forEach(track => track.stop());
                webcamStreamSalida = null;
            }
        }

        // ─── Abrir Modal de Entrada con módulo preseleccionado ──────────────
        function abrirModalEntradaConModulo(idModulo) {
            const select = document.getElementById('select_modulo');
            if (select) select.value = idModulo;
            new bootstrap.Modal(document.getElementById('modalRegistrarEntrada')).show();
        }

        // ─── Abrir Modal de Salida ──────────────────────────────────────────
        function abrirModalSalida(element) {
            const entradaId  = element.getAttribute('data-entrada-id');
            const ubicacion  = element.getAttribute('data-ubicacion');
            const placa      = element.getAttribute('data-placa');
            const propietario = element.getAttribute('data-propietario');
            const hora       = element.getAttribute('data-hora');
            const servicio   = element.getAttribute('data-servicio');
            const fotoUrl    = element.getAttribute('data-foto');

            document.getElementById('salida_id_entrada').value  = entradaId;
            document.getElementById('salida_ubicacion').textContent = 'Módulo ' + ubicacion;
            document.getElementById('salida_placa').textContent      = placa;
            document.getElementById('salida_propietario').textContent = propietario;
            document.getElementById('salida_hora').textContent        = hora;
            document.getElementById('salida_servicio').textContent    = servicio;

            // Mostrar foto de entrada como referencia si existe
            const fotoContainer = document.getElementById('salida_foto_entrada_container');
            const fotoImg = document.getElementById('salida_foto_img');
            if (fotoUrl && fotoUrl !== '') {
                fotoImg.src = fotoUrl;
                fotoContainer.style.display = 'block';
            } else {
                fotoContainer.style.display = 'none';
            }

            // Resetear cámara de salida al abrir
            document.getElementById('foto-preview-salida').style.display = 'none';
            document.getElementById('foto_base64_salida').value = '';
            document.getElementById('btn-iniciar-camara-salida').style.display = 'inline-block';
            document.getElementById('btn-capturar-salida').style.display = 'none';
            document.getElementById('btn-recapturar-salida').style.display = 'none';
            document.getElementById('camara-placeholder-salida').style.display = 'block';
            document.getElementById('webcam-feed-salida').style.display = 'none';

            new bootstrap.Modal(document.getElementById('modalRegistrarSalida')).show();
        }

        // ─── Limpiar cámaras al cerrar modales ─────────────────────────────
        document.getElementById('modalRegistrarEntrada')?.addEventListener('hidden.bs.modal', function () {
            detenerCamara();
        });

        document.getElementById('modalRegistrarSalida')?.addEventListener('hidden.bs.modal', function () {
            detenerCamaraSalida();
        });
    </script>
@endsection
