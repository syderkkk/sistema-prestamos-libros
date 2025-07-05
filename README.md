# 📚 Sistema de Préstamos de Libros

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white) ![Oracle](https://img.shields.io/badge/Oracle-F80000?style=for-the-badge&logo=oracle&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

**Sistema web para la gestión de préstamos de libros en una biblioteca**

_Desarrollado con Laravel y Oracle Database_

</div>

----------

## 📋 Tabla de Contenidos

-   [✨ Características](#-características)
-   [⚙️ Requisitos](#️-requisitos)
-   [🚀 Instalación](#-instalación)
-   [🔧 Configuración](#-configuración)
-   [🗄️ Migraciones](#️-migraciones)
-   [💻 Uso](#-uso)
-   [🎯 Inicialización de Datos](#-inicialización-de-datos)
-   [📁 Estructura del Proyecto](#-estructura-del-proyecto)
-   [⚡ Comandos Útiles](#-comandos-útiles)
-   [🤝 Contribuciones](#-contribuciones)
-   [📄 Licencia](#-licencia)
-   [🆘 Soporte](#-soporte)

----------

## ✨ Características

| Funcionalidad | Descripción |
|---------------|-------------|
| 📖 **Gestión de Libros** | Registro, edición, eliminación y listado completo |
| 👤 **Gestión de Autores** | Administración de autores y categorías |
| 🔄 **Préstamos** | Sistema de préstamo y devolución de libros |
| 🎭 **Roles de Usuario** | Bibliotecario y Usuario con permisos diferenciados |
| 🔍 **Búsqueda Avanzada** | Filtros por título, autor y categoría |
| 🎛️ **Panel Admin** | Interfaz administrativa |
| 🔐 **Autenticación** | Sistema de registro y login |

----------

## ⚙️ Requisitos

> 🛠️ **Requisitos del Sistema**

-   ![PHP](https://img.shields.io/badge/PHP-%E2%89%A58.2-777BB4?style=flat-square&logo=php)
-   ![Composer](https://img.shields.io/badge/Composer-Latest-885630?style=flat-square&logo=composer)
-   ![Oracle](https://img.shields.io/badge/Oracle-21c-F80000?style=flat-square&logo=oracle)
-   📦 Extensión [yajra/laravel-oci8](https://github.com/yajra/laravel-oci8) para Laravel

### 🔌 Extensión PHP Requerida

```ini
extension=oci8_19
```

----------

## 🚀 Instalación

### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/syderkkk/sistema-prestamos-libros.git
cd sistema-prestamos-libros
```

### 2️⃣ Instalar Dependencias

```bash
composer install
```

### 3️⃣ Configurar Entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Configurar Base de Datos

Edita el archivo `.env` con tus credenciales:

```env
DB_CONNECTION=oracle
DB_HOST=localhost
DB_PORT=1521
DB_DATABASE=XE
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

### 5️⃣ Habilitar Extensión OCI8

Asegúrate de tener habilitada la extensión en `php.ini`:

```ini
extension=oci8_19
```

----------

## 🔧 Configuración

> ⚠️ **Configuración Importante**

-   ✅ Extensión `oci8_19` habilitada en PHP
-   ✅ Conexión Oracle 21c configurada correctamente
-   ✅ Variables de entorno configuradas en `.env`

----------

## 🗄️ Migraciones

Ejecuta las migraciones para crear la estructura de base de datos:

```bash
php artisan migrate
```

----------

## 💻 Uso

### 🌐 Iniciar el Servidor

```bash
php artisan serve
```

### 🔗 Acceder a la Aplicación

Visita: [http://localhost:8000](http://localhost:8000/)

----------

## 🎯 Inicialización de Datos

> 📌 **Configuración Inicial Obligatoria**

**¡IMPORTANTE!** Después de iniciar el servidor, es **NECESARIO** visitar las siguientes rutas para que la base de datos se configure correctamente con todos los paquetes y códigos PL/SQL necesarios:

| Orden | Ruta | Descripción | Estado |
|-------|------|-------------|--------|
| 1° | `http://localhost:8000/setup/usuarios` | 👥 Crear usuarios y roles iniciales | ⚠️ **Obligatorio** |
| 2° | `http://localhost:8000/setup/libros` | 📚 Crear datos base de libros, autores y categorías | ⚠️ **Obligatorio** |

### 📋 Pasos de Inicialización

1. **Inicia el servidor** con `php artisan serve`
2. **Visita primero**: `http://localhost:8000/setup/usuarios`
3. **Visita segundo**: `http://localhost:8000/setup/libros`

> ⚡ **Nota Importante:** 
> - Solo necesitas acceder **una vez** a cada ruta para inicializar el sistema
> - Estas rutas crean automáticamente todos los **paquetes PL/SQL** necesarios
> - Se configuran los **procedimientos almacenados** y **funciones** de Oracle
> - Se insertan los **datos iniciales** para el funcionamiento del sistema

### 🔄 ¿Qué se configura automáticamente?

- **Paquetes PL/SQL** para gestión de préstamos
- **Procedimientos almacenados** para operaciones complejas
- **Funciones Oracle** para cálculos y validaciones
- **Datos iniciales** (usuarios, roles, libros de ejemplo)
- **Estructura completa** de la base de datos

----------

## 📁 Estructura del Proyecto

```
📦 sistema-prestamos-libros/
├── 📂 app/Http/Controllers/    # Controladores de la aplicación
├── 📂 resources/views/         # Vistas Blade
├── 📂 routes/                  # Definición de rutas
│   └── 📄 web.php             # Rutas web
└── 📂 public/                  # Archivos públicos
```

----------

## ⚡ Comandos Útiles

### 🧹 Limpieza de Cachés

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 🔄 Otros Comandos

```bash
# Optimizar para producción
php artisan optimize

# Ver rutas disponibles
php artisan route:list

# Verificar configuración
php artisan config:show
```

----------

<sub>Desarrollado por <a href="https://github.com/syderkkk">syderkkk</a></sub>