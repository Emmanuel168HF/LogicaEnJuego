# 🧩 Lógica en juego: Aprendizaje Agéntico

**Logic in Play: Agentic Learning**

Plataforma web educativa orientada al aprendizaje de informática y lógica de programación para niñas, niños y adolescentes. Busca fortalecer el pensamiento lógico y computacional mediante módulos, lecciones, repasos y retos interactivos, con una evolución hacia un modelo de aprendizaje agéntico apoyado por inteligencia artificial.

> **Estado del proyecto: Fase de prototipo.** El proyecto cuenta con funcionalidades educativas desarrolladas y otras en proceso de diseño, experimentación y validación para su siguiente versión.

## 🌐 Demostración

- **Prototipo web:** http://logicaenjuego2026.scienceontheweb.net/

## ✨ Funcionalidades del prototipo

- Catálogo educativo organizado en 13 módulos.
- Lecciones interactivas.
- Cuestionarios y actividades de repaso.
- Retos de lógica y pensamiento computacional.
- Retroalimentación de respuestas.
- Seguimiento del progreso del estudiante.
- Sistema de puntos de lógica.
- Insignias y rachas de actividad.
- Perfil personalizable con avatar.
- Evaluaciones diagnósticas.
- Panel de administración de contenidos.
- Registro e inicio de sesión.

## 🚀 Próximas implementaciones

Se está trabajando en nuevas funcionalidades para evolucionar hacia el aprendizaje agéntico.

### 🤖 Tutor virtual inteligente

- Chat con IA desde el menú principal.
- Sugerencias de consultas.
- Opción «Extender más» en las lecciones.
- Explicaciones personalizadas.
- Generación de pistas inteligentes.
- Opción «Explicar con IA» en repasos y retos.

### 🪙 Sistema de tokens

Recompensas por participación y progreso:

| Acción | Tokens |
|---|---:|
| Inicio de sesión | +5 |
| Completar una lección | +5 |
| Completar un módulo | +15 |
| Completar un reto | +10 |
| Solicitar una pista | -12 |

Los valores son iniciales y podrán ajustarse durante las pruebas.

### 🧠 Construcción visual de algoritmos

Entorno interactivo para construir y ejecutar algoritmos mediante bloques de instrucciones.

- Secuencias.
- Variables.
- Condicionales.
- Ciclos.
- Operadores lógicos y matemáticos.
- Ejecución visual.
- Identificación y corrección de errores.

### 🔐 Autenticación mejorada

- Registro con correo electrónico y contraseña.
- Verificación de correo electrónico.
- Inicio de sesión rápido con Google.
- Inicio de sesión rápido con Facebook.
- Recuperación de contraseña.
- Administración de cuentas y sesiones.

**Nota:** Las funcionalidades anteriores se encuentran en fase de prototipo y desarrollo; todavía no se consideran terminadas ni disponibles en la versión actual.

## 🛠️ Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| HTML5 | Estructura |
| CSS3 | Diseño y estilos |
| JavaScript | Interactividad |
| PHP 8 | Backend |
| MySQL | Base de datos |
| PDO | Conexión y consultas |
| Apache | Servidor web |

## 📁 Estructura del proyecto

```text
LogicaEnJuego/
├── admin/
├── assets/
│   ├── css/
│   ├── images/
│   ├── img/
│   └── js/
├── auth/
├── config/
├── controllers/
├── database/
│   ├── migrations/
│   └── logica_en_juego.sql
├── includes/
├── models/
├── pages/
├── scripts/
├── .env.example
├── .gitignore
├── index.php
└── README.md
```

## ⚙️ Instalación

### Requisitos

- PHP 8.1 o superior.
- MySQL 8.0 o MariaDB 10.4.
- Apache.
- Extensiones PDO y PDO MySQL.
- Navegador actualizado.

### 1. Clonar el repositorio

```bash
git clone URL_DEL_REPOSITORIO
```

### 2. Configurar la base de datos

1. Iniciar Apache y MySQL.
2. Abrir phpMyAdmin.
3. Crear o seleccionar la base de datos.
4. Importar `database/logica_en_juego.sql`.

### 3. Configurar variables de entorno

Copiar `.env.example` y crear un archivo `.env`.

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=logica_en_juego
DB_USER=root
DB_PASSWORD=
```

Configurar los valores según el entorno local o servidor.

### 4. Ejecutar módulos adicionales

```bash
php database/seed_additional_modules.php
```

### 5. Acceder a la plataforma

```text
http://localhost/LogicaEnJuego/
```

## 🔒 Seguridad

- Consultas preparadas mediante PDO.
- Validación de datos en el servidor.
- Protección CSRF.
- Administración de sesiones.
- Control de permisos por roles.
- Protección de credenciales mediante variables de entorno.

**Importante:** No publicar archivos `.env`, contraseñas, tokens ni credenciales del servidor.

## 👨‍💻 Equipo de desarrollo

Proyecto desarrollado por estudiantes de la **Universidad Tecnológica Tula-Tepeji**.

| Integrante | GitHub |
|---|---|
| **Armando Emmanuel Gomez Almaraz** | [@Emmanuel168HF](https://github.com/Emmanuel168HF) |
| **Irving Yael Rojas Hurbano** | [@YaelRojasH](https://github.com/YaelRojasH) |
| **José Domingo Sarabia Hernández** | [@joseDomingoSH](https://github.com/joseDomingoSH) |

## 🎯 Visión del proyecto

Lógica en juego busca evolucionar hacia un entorno educativo de aprendizaje agéntico, donde la inteligencia artificial acompañe, oriente y personalice el proceso de aprendizaje.

La finalidad es integrar educación interactiva, pensamiento computacional, construcción de algoritmos e inteligencia artificial en una misma plataforma.

## 📄 Licencia

Proyecto académico en fase de prototipo. Los derechos sobre el código y los recursos permanecen reservados por sus respectivos autores mientras no se establezca una licencia.