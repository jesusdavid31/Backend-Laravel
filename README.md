<p align="center">
  <img src="https://laravel.com/img/logomark.min.svg" width="100" alt="Laravel Logo"/>
</p>

<h1 align="center">📰 Laravel Articles API</h1>

<p align="center">
  API RESTful para autenticación de usuarios y gestión de artículos, construida en Laravel 10+ con autenticación personalizada JWT, validaciones robustas y manejo de imágenes.
</p>

---

## 📚 Descripción

**Laravel Articles API** es un backend completo que permite:

- Registro e inicio de sesión con autenticación personalizada JWT.
- Gestión de perfiles de usuario (con avatares).
- CRUD completo de artículos con paginación.
- Subida de imágenes para perfiles y artículos.
- Búsqueda de artículos por término.
- Protección de rutas mediante middleware de autenticación.

Ideal para usar como base en blogs, redes sociales, o apps educativas.

---

## 📂 Estructura de Carpetas

```
├── app/
│   ├── Helpers/
│   │   └── JwtAuth.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── UserController.php
│   │       └── ArticleController.php
├── routes/
│   └── api.php
├── storage/app/avatars
├── storage/app/posters
```

---

## 🔐 Autenticación JWT Personalizada

La API implementa un helper `JwtAuth` que utiliza la librería `firebase/php-jwt` para:

- Generar un token seguro con información del usuario.
- Decodificar y validar el token en cada solicitud protegida.
- El token expira en 7 días (`exp`).

**Encabezado requerido:**
```
Authorization: Bearer {token}
```

---

## 🧪 Validaciones destacadas

- Password cifrado con `bcrypt`.
- Validaciones de campos robustas usando `Validator`.
- Subida de imágenes válida con `mimes: jpeg, png, gif, jpg` y máximo 2MB.
- Verificación de propiedad antes de modificar artículos.

---

## 🛠️ Requisitos

- PHP >= 8.1
- Composer
- Laravel >= 10
- Base de datos MySQL (o compatible)
- Librería `firebase/php-jwt` instalada

Instalación de la librería:
```bash
composer require firebase/php-jwt
```

---

## ⚙️ Instalación del Proyecto

```bash
# Clonar el repositorio
git clone https://github.com/jesusdavid31/Backend-Laravel.git
cd backend-laravel

# Instalar dependencias
composer install

# Copiar archivo de entorno y generar clave
cp .env.example .env
php artisan key:generate

# Configurar conexión a base de datos en .env

# Ejecutar migraciones
php artisan migrate

# Crear enlace simbólico para acceder a imágenes
php artisan storage:link

# Iniciar el servidor
php artisan serve
```

---

## 🧭 Rutas API

### 🔓 Rutas Públicas

#### 👤 Usuarios
| Método | Ruta                           | Descripción                        |
|--------|--------------------------------|------------------------------------|
| POST   | `/api/user/register`           | Registro de usuario                |
| POST   | `/api/user/login`              | Login y obtención de token         |
| GET    | `/api/user/profile/{id}`       | Ver perfil de usuario por ID       |
| GET    | `/api/user/avatar/{file}`      | Obtener imagen del avatar          |

#### 📝 Artículos
| Método | Ruta                                      | Descripción                            |
|--------|-------------------------------------------|----------------------------------------|
| GET    | `/api/articles/items/{page}`              | Artículos paginados                    |
| GET    | `/api/articles/item/{id}`                 | Ver artículo por ID                    |
| GET    | `/api/articles/user/{userId}`             | Ver artículos de un usuario específico |
| GET    | `/api/articles/search/{searchTerm}`       | Buscar artículos                       |
| GET    | `/api/articles/poster/{file}`             | Obtener imagen del artículo            |

---

### 🔒 Rutas Protegidas (requieren token)

#### 👤 Usuarios
| Método | Ruta                   | Descripción                    |
|--------|------------------------|--------------------------------|
| PUT    | `/api/user/update`     | Actualizar datos del usuario   |
| POST   | `/api/user/upload`     | Subir imagen/avatar del usuario|

#### 📝 Artículos
| Método | Ruta                           | Descripción                      |
|--------|--------------------------------|----------------------------------|
| POST   | `/api/articles/save`           | Crear artículo                   |
| PUT    | `/api/articles/update/{id}`    | Actualizar artículo propio       |
| DELETE | `/api/articles/delete/{id}`    | Eliminar artículo propio         |
| POST   | `/api/articles/upload/{id}`    | Subir imagen del artículo        |

---

## 🧠 Ejemplo de autenticación (login)

**Solicitud:**
```json
POST /api/user/login
{
  "email": "usuario@example.com",
  "password": "tu_clave_segura"
}
```

**Respuesta:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOi..."
}
```

---

## 🧼 Ejemplo de autorización en el frontend

Asegúrate de agregar el token en los headers:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...
```

---

## 📸 Gestión de Imágenes

- Las imágenes de usuarios se almacenan en `storage/app/avatars`.
- Las imágenes de artículos se almacenan en `storage/app/posters`.
- Acceso público a través de rutas `/api/user/avatar/{file}` y `/api/articles/poster/{file}`.

---

## ✨ Créditos

Este proyecto fue desarrollado con ❤️ usando Laravel y JWT.

---

## 📄 Licencia

MIT © (https://github.com/jesusdavid31)