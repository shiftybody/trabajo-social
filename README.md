# Neurodesarrollo

## Trabajo Social

plataforma para la gestión de donaciones 💸

### Estructura del Proyecto

```plaintext
trabajo-social
├─ .htaccess
├─ app
│  ├─ Controllers
│  │  ├─ Api
│  │  │  ├─ AuthController.php
│  │  │  └─ UserController.php
│  │  └─ Web
│  │     ├─ AuthController.php
│  │     ├─ DashboardController.php
│  │     └─ UserController.php
│  ├─ Helpers
│  │  └─ validationHelper.php
│  ├─ Middlewares
│  │  ├─ ApiAuthMiddleware.php
│  │  ├─ ApiRoleMiddleware.php
│  │  ├─ AuthMiddleware.php
│  │  ├─ GuestMiddleware.php
│  │  └─ RolMiddleware.php
│  ├─ Models
│  │  ├─ mainModel.php
│  │  ├─ rolModel.php
│  │  └─ userModel.php
│  ├─ Routes
│  │  ├─ api.php
│  │  └─ web.php
│  ├─ Services
│  │  ├─ FileService.php
│  │  ├─ SessionService.php
│  │  └─ TokenService.php
│  ├─ Utils
│  │  └─ Logger.php
│  └─ Views
│     ├─ auth
│     │  └─ login.php
│     └─ dashboard
│        └─ index.php
├─ compose.yml
├─ composer.json
├─ composer.lock
├─ config
│  ├─ app.php
│  ├─ init.php
│  └─ server.php
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
│  ├─ inc
│  │  ├─ head.php
│  │  ├─ navbar.php
│  │  ├─ script.php
│  │  └─ session_start.php
│  ├─ index.php
│  ├─ js
│  │  ├─ datatables.min.js
│  │  └─ session-timeout.js
│  └─ photos
├─ README.md
├─ resources
│  ├─ docs
│  └─ fonts
│     └─ Inter.ttf
└─ vite.config.ts
```
