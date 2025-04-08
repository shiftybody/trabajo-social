# Neurodesarrollo

## Trabajo Social

plataforma para la gestión de donaciones 💸

### Estructura del Proyecto

```
trabajo-social
├─ .htaccess
├─ app
│  ├─ Controllers
│  │  ├─ AuthController.php
│  │  ├─ DashboardController.php
│  │  ├─ PermissionController.php
│  │  └─ UserController.php
│  ├─ Core
│  │  ├─ Request.php
│  │  ├─ Response.php
│  │  └─ Router.php
│  ├─ Helpers
│  ├─ Middlewares
│  │  ├─ AuthMiddleware.php
│  │  ├─ PermissionMiddleware.php
│  │  └─ RolMiddleware.php
│  ├─ Models
│  │  ├─ mainModel.php
│  │  ├─ permissionModel.php
│  │  ├─ roleModel.php
│  │  └─ userModel.php
│  ├─ Routes
│  │  ├─ api.php
│  │  └─ web.php
│  ├─ Services
│  ├─ Utils
│  │  └─ FormValidator.php
│  └─ Views
│     ├─ auth
│     │  └─ login.php
│     ├─ dashboard
│     │  └─ index.php
│     ├─ errors
│     │  ├─ 401.php
│     │  ├─ 403.php
│     │  └─ 404.php
│     └─ users
│        ├─ create.php
│        └─ index.php
├─ compose.yml
├─ composer.json
├─ composer.lock
├─ config
│  ├─ env.php
│  └─ session.php
├─ Dockerfile
├─ LICENSE
├─ package-lock.json
├─ package.json
├─ public
│  ├─ css
│  │  ├─ custom.css
│  │  ├─ datatables.min.css
│  │  └─ styles.css
│  ├─ favicon.ico
│  ├─ icons
│  │  ├─ bell.svg
│  │  ├─ filtrar.svg
│  │  ├─ golf.svg
│  │  ├─ h-line.svg
│  │  ├─ home.svg
│  │  ├─ logout.svg
│  │  ├─ menu.svg
│  │  ├─ search-outline.svg
│  │  ├─ search-white.svg
│  │  ├─ search.svg
│  │  ├─ user.svg
│  │  ├─ users.svg
│  │  ├─ v-line.svg
│  │  └─ x.svg
│  ├─ images
│  │  ├─ favicon.ico
│  │  ├─ imagotipo-neurodesarrollo.png
│  │  ├─ logo-unam.svg
│  │  └─ logotipo-neurodesarrollo.png
│  ├─ inc
│  │  ├─ head.php
│  │  ├─ modal.php
│  │  ├─ navbar.php
│  │  ├─ passgen.php
│  │  └─ scripts.php
│  ├─ index.php
│  ├─ js
│  │  ├─ datatables.min.js
│  │  ├─ inactivity.js
│  │  ├─ main.js
│  │  ├─ navbar.js
│  │  └─ validations.js
│  └─ photos
│     └─ avatar.jpg
├─ README.md
├─ resources
│  ├─ docs
│  └─ fonts
│     └─ Inter.ttf
└─ vite.config.ts

```