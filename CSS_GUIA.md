# PARKINGSURE - Guía de Clases CSS

## 📋 Estructura de Estilos

Todos los estilos están centralizados en `resources/css/parkingsure.css`. No hay estilos embebidos en las vistas.

---

## 🎨 Clases Disponibles

### Navbar y Navegación
```html
<!-- Contenedor principal -->
<nav class="topbar">
  <!-- Logo -->
  <a class="logo" href="#">
    <div class="logo-mark">P</div>
    <div class="logo-text">PARKING<em>SURE</em></div>
  </a>
  
  <!-- Botón hamburger (móvil) -->
  <button class="hamburger" id="hamburger-btn">
    <span></span>
    <span></span>
    <span></span>
  </button>
  
  <!-- Links de navegación -->
  <div class="nav-links" id="nav-links">
    <a class="nb active" href="#">Dashboard</a>
    <a class="nb" href="#">Otro</a>
  </div>
  
  <!-- Info del usuario -->
  <div class="nav-right">
    <div class="user-avatar">J</div>
    <div class="user-info">
      <div class="u-name">Juan</div>
      <div class="u-role">ADMINISTRADOR</div>
    </div>
    <button class="btn-logout">Salir</button>
  </div>
</nav>
```

### Page Headers
```html
<div class="page-header">
  <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
</div>
```

### Cards
```html
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Título</h5>
  </div>
  <div class="card-body">
    Contenido
  </div>
</div>
```

### Parking Map
```html
<div class="parking-map">
  <div class="parking-space available">A-01</div>
  <div class="parking-space occupied">A-02</div>
</div>
```

**Clases disponibles:**
- `parking-space.available` - Espacio disponible
- `parking-space.occupied` - Espacio ocupado

### Botones
```html
<!-- Bootstrap buttons -->
<button class="btn btn-primary">Primario</button>
<button class="btn btn-success">Éxito</button>
<button class="btn btn-danger">Peligro</button>
<button class="btn btn-warning">Advertencia</button>
<button class="btn btn-secondary">Secundario</button>
```

### Badges
```html
<span class="badge bg-success">Activo</span>
<span class="badge bg-danger">Inactivo</span>
<span class="badge bg-warning">Pendiente</span>
<span class="badge bg-info">Información</span>
```

### Tablas
```html
<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Columna 1</th>
        <th>Columna 2</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Dato 1</td>
        <td>Dato 2</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Alertas
```html
<div class="alert alert-success alert-dismissible fade show">
  Mensaje de éxito
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<div class="alert alert-danger alert-dismissible fade show">
  Mensaje de error
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

### Formularios
```html
<div class="mb-3">
  <label class="form-label">Etiqueta</label>
  <input type="text" class="form-control">
</div>

<div class="mb-3">
  <label class="form-label">Seleccionar</label>
  <select class="form-select">
    <option>Opción 1</option>
  </select>
</div>

<textarea class="form-control" rows="3"></textarea>
```

### Modales
```html
<div class="modal fade" id="miModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Título</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Contenido
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
```

---

## 🎯 Variables CSS Globales

Se puede usar en archivos CSS personalizados:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --danger-color: #e74c3c;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --warning-color: #ffc107;
    --dark-bg: #2c3e50;
    --light-bg: #f4f6f9;
    --border-light: #ddd;
    --text-muted: #6c757d;
}
```

---

## 📱 Responsive Breakpoints

Bootstrap 5:
- `xs`: < 576px (móvil pequeño)
- `sm`: ≥ 576px (móvil)
- `md`: ≥ 768px (tablet)
- `lg`: ≥ 992px (desktop pequeño)
- `xl`: ≥ 1200px (desktop)
- `xxl`: ≥ 1400px (desktop grande)

**Ejemplo con Bootstrap:**
```html
<div class="col-12 col-md-6 col-lg-3">
  Esto será 100% en móvil, 50% en tablet, 25% en desktop
</div>
```

---

## 🔌 Cómo Agregar Estilos Personalizados

Si necesitas agregar estilos específicos:

1. **Opción 1: Actualizar parkingsure.css**
```css
/* Al final del archivo parkingsure.css */
.mi-clase-personalizada {
    /* tus estilos */
}
```

2. **Opción 2: Crear archivo CSS nuevo**
```html
<!-- En app.blade.php después de parkingsure.css -->
<link rel="stylesheet" href="{{ asset('css/mi-estilo.css') }}">
```

3. **Opción 3: Estilos inline (solo si es necesario)**
```html
<div style="color: red; font-weight: bold;">
  Texto rojo y negrita
</div>
```

---

## 🎪 Ejemplos Completos

### Card con Contenido
```html
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Mi Tarjeta</h5>
  </div>
  <div class="card-body">
    <p>Contenido de la tarjeta</p>
    <button class="btn btn-primary">Acción</button>
  </div>
</div>
```

### Tabla Responsiva
```html
<div class="card">
  <div class="card-header">
    <h5>Tabla de Datos</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Ejemplo</td>
            <td><span class="badge bg-success">Activo</span></td>
            <td>
              <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
              <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
              <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
```

### Grid Responsiva
```html
<div class="row">
  <div class="col-md-3 mb-4">
    <div class="card">
      <div class="card-body text-center">
        <i class="fas fa-parking fa-3x text-primary mb-3"></i>
        <h5 class="card-title">Parqueaderos</h5>
        <h2 class="text-success">45</h2>
      </div>
    </div>
  </div>
  
  <div class="col-md-3 mb-4">
    <div class="card">
      <div class="card-body text-center">
        <i class="fas fa-car fa-3x text-info mb-3"></i>
        <h5 class="card-title">Vehículos</h5>
        <h2 class="text-info">28</h2>
      </div>
    </div>
  </div>
</div>
```

---

## 🔍 Font Awesome Icons

Usa los iconos de Font Awesome 6:

```html
<i class="fas fa-dashboard"></i> <!-- Dashboard -->
<i class="fas fa-parking"></i> <!-- Parqueadero -->
<i class="fas fa-car"></i> <!-- Vehículos -->
<i class="fas fa-credit-card"></i> <!-- Pagos -->
<i class="fas fa-users"></i> <!-- Usuarios -->
<i class="fas fa-cogs"></i> <!-- Servicios -->
<i class="fas fa-chart-bar"></i> <!-- Reportes -->
<i class="fas fa-eye"></i> <!-- Ver -->
<i class="fas fa-edit"></i> <!-- Editar -->
<i class="fas fa-trash"></i> <!-- Eliminar -->
<i class="fas fa-plus"></i> <!-- Agregar -->
<i class="fas fa-search"></i> <!-- Buscar -->
<i class="fas fa-download"></i> <!-- Descargar -->
<i class="fas fa-print"></i> <!-- Imprimir -->
```

---

## ✅ Beneficios de esta Estructura

✅ **Mantenibilidad**: Un archivo CSS centralizado es más fácil de actualizar  
✅ **Rendimiento**: Los estilos se cargan una sola vez  
✅ **Consistencia**: Todos los componentes usan el mismo tema  
✅ **Reutilización**: Las clases pueden usarse en cualquier vista  
✅ **Responsivo**: Bootstrap + CSS personalizado integrados  

---

## 📝 Notas

- Bootstrap 5 se incluye automáticamente en el layout base
- Font Awesome 6 está disponible para iconos
- Chart.js se carga en la vista de reportes según sea necesario
- Los estilos personalizados están en `resources/css/parkingsure.css`
- El navbar es responsive y tiene hamburger menu en móviles
