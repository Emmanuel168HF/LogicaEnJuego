-- Esquema para MySQL 8.0+. La cuenta administradora inicial se crea con
-- credenciales temporales de abajo; cámbialas después del primer inicio de sesión.
-- Crea y selecciona la base para permitir importarla desde phpMyAdmin sin
-- seleccionar previamente un esquema.
CREATE DATABASE IF NOT EXISTS logica_en_juego
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE logica_en_juego;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Administrador inicial del programa (no es el usuario de MySQL).
-- Inicio de sesión: admin@logicaenjuego.local
-- Contraseña temporal: 90cbb1f726e4ebf1e115bcdb
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(24) NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL DEFAULT '',
    avatar VARCHAR(16) NOT NULL DEFAULT '🧠',
    country VARCHAR(80) NOT NULL DEFAULT 'México',
    state VARCHAR(60) NOT NULL DEFAULT '',
    age TINYINT UNSIGNED NULL,
    description VARCHAR(280) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_user_profiles_username (username),
    CONSTRAINT fk_user_profiles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_activity_days (
    user_id BIGINT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    login_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    active_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, activity_date),
    CONSTRAINT fk_user_activity_days_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password_hash, role, is_active)
VALUES (
    'Administrador',
    'admin@logicaenjuego.local',
    '$2y$10$JLUQKT5CoVfnI7M4RHYCceNTA4QS7mkg1zN09aQpCzb903oir/n6G',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE email = VALUES(email);

CREATE TABLE IF NOT EXISTS modules (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(180) NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    objectives TEXT NULL,
    difficulty ENUM('inicial', 'intermedio', 'avanzado') NULL,
    estimated_minutes SMALLINT UNSIGNED NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_modules_slug (slug),
    KEY idx_modules_catalog (is_published, archived_at, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    estimated_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lessons_module_order (module_id, is_published, sort_order),
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NULL,
    type ENUM('quiz', 'identify', 'classify', 'order') NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    max_attempts SMALLINT UNSIGNED NULL,
    passing_score TINYINT UNSIGNED NOT NULL DEFAULT 70,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activities_module (module_id, is_published, type),
    KEY idx_activities_lesson (lesson_id),
    CONSTRAINT fk_activities_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE,
    CONSTRAINT fk_activities_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE SET NULL,
    CONSTRAINT chk_activities_passing_score CHECK (passing_score <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    prompt TEXT NOT NULL,
    explanation TEXT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_questions_activity_order (activity_id, sort_order),
    CONSTRAINT fk_questions_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS answer_options (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    question_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(1000) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_answer_option_order (question_id, sort_order),
    KEY idx_answer_options_question (question_id),
    CONSTRAINT fk_answer_options_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exercise_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    prompt TEXT NOT NULL,
    choices JSON NOT NULL,
    correct_answer VARCHAR(1000) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_exercise_items_order (activity_id, sort_order),
    CONSTRAINT fk_exercise_items_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_progress (
    user_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NOT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (user_id, lesson_id),
    KEY idx_lesson_progress_lesson (lesson_id, completed_at),
    CONSTRAINT fk_lesson_progress_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_lesson_progress_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    attempt_number SMALLINT UNSIGNED NOT NULL,
    score SMALLINT UNSIGNED NOT NULL,
    max_score SMALLINT UNSIGNED NOT NULL,
    percentage TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quiz_attempt_number (user_id, activity_id, attempt_number),
    KEY idx_quiz_attempt_history (user_id, created_at),
    KEY idx_quiz_attempt_activity (activity_id, user_id),
    CONSTRAINT fk_quiz_attempt_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT chk_quiz_attempt_score CHECK (percentage <= 100 AND score <= max_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempt_answers (
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_option_id BIGINT UNSIGNED NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (attempt_id, question_id),
    KEY idx_quiz_answer_question (question_id),
    CONSTRAINT fk_quiz_answer_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_answer_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_answer_option FOREIGN KEY (selected_option_id) REFERENCES answer_options (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    attempt_number SMALLINT UNSIGNED NOT NULL,
    score SMALLINT UNSIGNED NOT NULL,
    max_score SMALLINT UNSIGNED NOT NULL,
    percentage TINYINT UNSIGNED NOT NULL,
    answers JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_activity_attempt_number (user_id, activity_id, attempt_number),
    KEY idx_activity_attempt_history (user_id, created_at),
    KEY idx_activity_attempt_activity (activity_id, user_id),
    CONSTRAINT fk_activity_attempt_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE,
    CONSTRAINT fk_activity_attempt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT chk_activity_attempt_score CHECK (percentage <= 100 AND score <= max_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS badges (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(120) NOT NULL,
    name VARCHAR(160) NOT NULL,
    description VARCHAR(500) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_badges_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_badges (
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    awarded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO badges (slug,name,description)
VALUES
    ('primer-paso','Primer paso','Completaste tu primera lección.'),
    ('primer-reto','Primer reto','Registraste tu primer intento en una actividad.'),
    ('arquitecto-de-silicio','Arquitecto de Silicio','Respondiste correctamente todo un cuestionario.'),
    ('ruta-completa','Ruta completa','Completaste todas las lecciones publicadas.')
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description);

CREATE TABLE IF NOT EXISTS evaluation_questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluation_type ENUM('diagnostic', 'post') NOT NULL,
    field_key VARCHAR(80) NOT NULL,
    prompt TEXT NOT NULL,
    input_type ENUM('choice', 'rating', 'text') NOT NULL DEFAULT 'choice',
    choices JSON NOT NULL,
    correct_answer VARCHAR(1000) NULL,
    is_scored TINYINT(1) NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evaluation_field (evaluation_type, field_key),
    KEY idx_evaluation_questions (evaluation_type, is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evaluation_submissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    evaluation_type ENUM('diagnostic', 'post') NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    guardian_consent TINYINT(1) NOT NULL DEFAULT 0,
    answers JSON NOT NULL,
    score DECIMAL(6,2) NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_evaluation_type_date (evaluation_type, submitted_at),
    KEY idx_evaluation_user (user_id, evaluation_type),
    CONSTRAINT fk_evaluation_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT chk_diagnostic_consent CHECK (evaluation_type <> 'diagnostic' OR guardian_consent = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nombres y orden tomados del catálogo ya presente en los diseños originales.
-- Los módulos quedan sin publicar hasta que el administrador complete y revise
-- sus objetivos, contenido y demás información pedagógica.
INSERT INTO modules (slug, title, sort_order, is_published)
VALUES
    ('conoce-tu-computadora', 'Conoce tu computadora', 1, 0),
    ('hardware-y-software', 'Hardware y software', 2, 0),
    ('fundamentos-digitales', 'Fundamentos digitales', 3, 0),
    ('seguridad-digital', 'Seguridad digital', 4, 0),
    ('pensamiento-logico', 'Pensamiento lógico', 5, 0),
    ('algoritmos', 'Algoritmos', 6, 0),
    ('diagramas-y-pseudocodigo', 'Diagramas y pseudocódigo', 7, 0),
    ('fundamentos-de-programacion', 'Fundamentos de programación', 8, 0)
ON DUPLICATE KEY UPDATE title = VALUES(title), sort_order = VALUES(sort_order);

-- Contenido inicial listo para que el catálogo y el recorrido educativo
-- funcionen inmediatamente después de importar el esquema.
UPDATE modules
SET description = COALESCE(description, CASE slug
        WHEN 'conoce-tu-computadora' THEN 'Explora las partes de una computadora y descubre cómo colaboran para ejecutar programas.'
        WHEN 'hardware-y-software' THEN 'Aprende a distinguir los componentes físicos de los programas que usamos cada día.'
        WHEN 'fundamentos-digitales' THEN 'Organiza archivos, reconoce datos y conoce las herramientas básicas del entorno digital.'
        WHEN 'seguridad-digital' THEN 'Practica hábitos para proteger tus cuentas, dispositivos e información personal.'
        WHEN 'pensamiento-logico' THEN 'Resuelve problemas con instrucciones claras, patrones y decisiones ordenadas.'
        WHEN 'algoritmos' THEN 'Diseña y prueba secuencias de pasos para resolver problemas cotidianos.'
        WHEN 'diagramas-y-pseudocodigo' THEN 'Representa soluciones con diagramas de flujo y pseudocódigo fácil de leer.'
        WHEN 'fundamentos-de-programacion' THEN 'Conoce las instrucciones, variables y decisiones que forman un programa.'
    END),
    objectives = COALESCE(objectives, CASE slug
        WHEN 'conoce-tu-computadora' THEN 'Identificar los componentes principales; explicar la función de la CPU y la memoria; relacionar entrada, procesamiento y salida.'
        WHEN 'hardware-y-software' THEN 'Distinguir hardware y software; clasificar periféricos y programas; explicar cómo trabajan en conjunto.'
        WHEN 'fundamentos-digitales' THEN 'Diferenciar archivos y carpetas; reconocer formatos y aplicaciones; organizar información digital.'
        WHEN 'seguridad-digital' THEN 'Detectar riesgos comunes; elegir contraseñas seguras; reconocer acciones para proteger la privacidad.'
        WHEN 'pensamiento-logico' THEN 'Dividir un problema en pasos; reconocer patrones; tomar decisiones con condiciones sencillas.'
        WHEN 'algoritmos' THEN 'Definir entradas y resultados; ordenar instrucciones; probar una solución y corregirla.'
        WHEN 'diagramas-y-pseudocodigo' THEN 'Reconocer símbolos de diagramas; expresar instrucciones en pseudocódigo; seguir una secuencia.'
        WHEN 'fundamentos-de-programacion' THEN 'Reconocer instrucciones y variables; predecir resultados; identificar una condición en un programa.'
    END),
    difficulty = COALESCE(difficulty, CASE
        WHEN slug IN ('conoce-tu-computadora', 'hardware-y-software', 'fundamentos-digitales') THEN 'inicial'
        WHEN slug IN ('seguridad-digital', 'pensamiento-logico', 'algoritmos') THEN 'intermedio'
        ELSE 'avanzado'
    END),
    estimated_minutes = COALESCE(estimated_minutes, CASE
        WHEN slug IN ('diagramas-y-pseudocodigo', 'fundamentos-de-programacion') THEN 30
        WHEN slug IN ('seguridad-digital', 'algoritmos') THEN 25
        ELSE 20
    END)
WHERE slug IN (
    'conoce-tu-computadora', 'hardware-y-software', 'fundamentos-digitales',
    'seguridad-digital', 'pensamiento-logico', 'algoritmos',
    'diagramas-y-pseudocodigo', 'fundamentos-de-programacion'
);

INSERT INTO lessons (module_id, title, content, estimated_minutes, sort_order, is_published)
SELECT m.id, seed.title, seed.content, seed.estimated_minutes, 1, 1
FROM (
    SELECT 'conoce-tu-computadora' AS slug, 'Las partes de una computadora' AS title,
        'Una computadora recibe datos, los procesa y produce resultados. El teclado y el ratón permiten ingresar información; la pantalla y las bocinas muestran o reproducen resultados.\n\nLa CPU ejecuta instrucciones y coordina las operaciones. La memoria RAM guarda temporalmente los datos que se están usando, mientras que el almacenamiento conserva archivos incluso cuando apagas el equipo.\n\nHardware es la parte física que puedes tocar. El software son los programas e instrucciones que le indican al hardware qué hacer. Ambos trabajan en conjunto.' AS content, 8 AS estimated_minutes
    UNION ALL SELECT 'hardware-y-software', 'Hardware y software trabajan juntos',
        'El hardware incluye las partes físicas de un equipo: teclado, pantalla, memoria y procesador. Los periféricos permiten ingresar o recibir información.\n\nEl software incluye el sistema operativo y las aplicaciones. Un navegador, por ejemplo, es un programa; necesita el procesador, la memoria y la pantalla para funcionar.\n\nCuando abres una aplicación, el sistema operativo coordina los recursos del hardware para que el programa pueda realizar su tarea.' , 8
    UNION ALL SELECT 'fundamentos-digitales', 'Archivos, carpetas y datos',
        'Un dato es una representación de información, como un número, una palabra o una imagen. Los programas pueden guardar esos datos en archivos.\n\nCada archivo tiene un nombre y, con frecuencia, una extensión que indica su formato: .jpg para imágenes o .txt para texto. Una carpeta ayuda a reunir archivos relacionados.\n\nUsa nombres claros y carpetas ordenadas para encontrar tu trabajo con facilidad. Antes de borrar un archivo, revisa que no sea necesario.' , 8
    UNION ALL SELECT 'seguridad-digital', 'Cuida tu información personal',
        'La seguridad digital comienza con hábitos sencillos. Usa contraseñas largas y diferentes para tus cuentas, y activa la verificación en dos pasos cuando esté disponible.\n\nDesconfía de mensajes inesperados que piden datos, prometen premios o presionan para abrir un enlace. Comprueba quién lo envía y consulta a una persona de confianza antes de compartir información.\n\nMantén tus aplicaciones actualizadas y cierra sesión en dispositivos compartidos. Nunca publiques tu dirección, contraseñas u otros datos privados.' , 9
    UNION ALL SELECT 'pensamiento-logico', 'Resuelve problemas paso a paso',
        'Para resolver un problema, primero explica qué necesitas lograr. Después identifica la información disponible y divide la tarea en pasos pequeños.\n\nBusca patrones que te permitan reutilizar una solución. Si una condición cambia, piensa qué acción corresponde en cada caso: si llueve, llevar paraguas; de lo contrario, salir sin él.\n\nAl final revisa el resultado. Si no resuelve el problema, encuentra el paso que debes cambiar e inténtalo de nuevo.' , 8
    UNION ALL SELECT 'algoritmos', 'Diseña un algoritmo',
        'Un algoritmo es una secuencia ordenada y finita de instrucciones para resolver un problema. Una receta es un ejemplo: indica ingredientes y pasos para obtener un resultado.\n\nAntes de escribirlo, define el problema, las entradas que necesitas y el resultado esperado. Luego ordena los pasos para que otra persona pueda seguirlos sin adivinar.\n\nPrueba el algoritmo con ejemplos. Si falta un paso o el orden no funciona, corrígelo y vuelve a probar.' , 9
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'Representa una solución',
        'Un diagrama de flujo muestra visualmente los pasos de un algoritmo. El óvalo suele representar el inicio o el final, el rectángulo una acción y el rombo una decisión.\n\nEl pseudocódigo describe esos pasos con palabras y una estructura parecida a la programación, sin depender de un lenguaje específico.\n\nLee cada camino desde el inicio hasta el final. En una decisión, comprueba que las alternativas cubran los resultados posibles.' , 10
    UNION ALL SELECT 'fundamentos-de-programacion', 'Tu primer programa',
        'Un programa es un conjunto de instrucciones que la computadora ejecuta. Cada instrucción debe estar escrita de forma clara y en el orden correcto.\n\nUna variable guarda un dato que el programa puede consultar o cambiar. Una condición permite elegir qué instrucciones ejecutar según una situación.\n\nPrueba tu programa con distintos datos y revisa el resultado. Los errores son una oportunidad para localizar una instrucción que falta o necesita corregirse.' , 10
) AS seed
JOIN modules m ON m.slug = seed.slug AND m.archived_at IS NULL
WHERE NOT EXISTS (
    SELECT 1 FROM lessons existing
    WHERE existing.module_id = m.id AND existing.title = seed.title
);

UPDATE lessons l
JOIN modules m ON m.id = l.module_id
SET l.content = CASE m.slug
    WHEN 'conoce-tu-computadora' THEN
        'Una computadora recibe datos, los procesa y produce resultados. El teclado y el ratón permiten ingresar información; la pantalla y las bocinas muestran o reproducen resultados. A este recorrido se le conoce como entrada, procesamiento y salida.\n\nLa CPU (unidad central de procesamiento) interpreta instrucciones y coordina las operaciones. La memoria RAM mantiene temporalmente los datos que la computadora está usando para que la CPU pueda acceder a ellos con rapidez. Cuando se apaga el equipo, su contenido se borra.\n\nEl almacenamiento, como un disco duro o una unidad SSD, conserva archivos y programas aunque la computadora esté apagada. La memoria RAM y el almacenamiento cumplen funciones distintas: una ayuda durante el trabajo actual y el otro guarda información a largo plazo.\n\nLa tarjeta madre conecta componentes como la CPU, la memoria y las unidades de almacenamiento. La fuente de poder entrega energía y el sistema de enfriamiento ayuda a evitar el exceso de calor. No todos estos componentes se ven desde fuera, pero trabajan juntos dentro del equipo.\n\nHardware es la parte física que puedes tocar. El software son los programas e instrucciones que le indican al hardware qué hacer. Por ejemplo, al escribir un documento, el teclado envía datos, un programa los procesa y la pantalla muestra el texto.\n\nLos dispositivos periféricos amplían lo que puedes hacer con la computadora. Un micrófono y una cámara envían sonido e imagen como datos de entrada; una impresora y unos audífonos presentan resultados como salida. Algunos dispositivos, como una pantalla táctil, pueden recibir y mostrar información.\n\nCuando abres una aplicación, el sistema operativo la carga desde el almacenamiento y usa la memoria RAM para mantener sus datos disponibles. La CPU ejecuta las instrucciones y la tarjeta gráfica ayuda a preparar las imágenes para la pantalla. Así, una acción sencilla puede involucrar varias partes del equipo.\n\nSi la computadora se siente lenta, puede haber muchas aplicaciones usando memoria al mismo tiempo, poco espacio de almacenamiento o una tarea que exige mucho procesamiento. No significa automáticamente que una pieza esté dañada: conviene cerrar programas que no necesitas y pedir ayuda antes de cambiar ajustes importantes.\n\nPara recordar el recorrido, piensa en una búsqueda: escribes una pregunta con el teclado (entrada), el equipo procesa los datos y la pantalla presenta páginas o respuestas (salida). Identificar cada parte te ayuda a entender qué hace la computadora y a explicar dónde podría estar un problema.'
    WHEN 'hardware-y-software' THEN
        'El hardware incluye las partes físicas de un equipo: teclado, pantalla, memoria, procesador, cámara y otros dispositivos. Algunos componentes están dentro de la computadora y otros se conectan por fuera. Los dispositivos de entrada envían datos, mientras que los de salida presentan información; algunos, como una pantalla táctil, pueden cumplir ambas funciones.\n\nEl software incluye el sistema operativo y las aplicaciones. El sistema operativo administra recursos como la memoria, los archivos y los dispositivos conectados. Una aplicación, como un navegador o un editor de texto, permite realizar una tarea concreta.\n\nHardware y software dependen uno del otro. Un navegador necesita procesador y memoria para ejecutarse, y necesita una pantalla para mostrar las páginas. El sistema operativo coordina esos recursos para que la aplicación pueda funcionar.\n\nLos controladores o drivers ayudan al sistema operativo a comunicarse con dispositivos específicos, como una impresora. Si falta un controlador o un dispositivo no está conectado correctamente, es posible que el equipo no pueda aprovechar todas sus funciones.\n\nPara clasificar un elemento, pregúntate si es una pieza física o un conjunto de instrucciones. Un teclado es hardware; el programa que usas para dibujar es software. Ambos colaboran cada vez que haces una tarea en la computadora.\n\nEl firmware es un tipo de software básico que viene integrado en algunos dispositivos y ayuda a que realicen sus funciones iniciales. Aunque está guardado en un componente físico, sigue siendo instrucciones digitales y por eso no se clasifica como una pieza de hardware.\n\nLos periféricos se conectan al equipo para agregar funciones. Una impresora necesita hardware para imprimir y software para recibir el documento y preparar la impresión. Si algo no funciona, revisa de manera segura la conexión y consulta a una persona adulta o al responsable del equipo.\n\nUna aplicación no es lo mismo que un archivo creado con esa aplicación. Un editor de texto es software; el documento que guardas es un archivo de datos. El documento puede permanecer en el almacenamiento aunque cierres el programa con que lo escribiste.\n\nCuando instalas un programa, el sistema operativo administra sus archivos y permisos. Descarga aplicaciones solo desde fuentes confiables y pide autorización en dispositivos compartidos. Distinguir hardware y software también ayuda a entender qué se puede tocar, qué se puede actualizar y qué tipo de ayuda pedir.'
    WHEN 'fundamentos-digitales' THEN
        'Un dato es una representación de información: puede ser un número, una palabra, una fecha o una imagen. Los programas organizan y procesan datos para que podamos utilizarlos. Por ejemplo, una lista de calificaciones reúne números y nombres para mostrar resultados.\n\nLos archivos guardan información creada o utilizada por una aplicación. Cada archivo tiene un nombre y con frecuencia una extensión después del punto, como .jpg para imágenes, .pdf para documentos o .mp3 para audio. La extensión da una pista sobre el formato, aunque no siempre confirma por sí sola el contenido.\n\nUna carpeta reúne archivos relacionados y puede contener otras carpetas. Esta organización crea una estructura que ayuda a encontrar trabajos. Usa nombres claros, agrupa por tema o fecha y evita guardar muchas copias con nombres casi iguales.\n\nAl guardar un archivo, revisa la carpeta de destino y el nombre. Si compartes un dispositivo, cierra tus documentos y no dejes información personal a la vista. Antes de borrar o mover algo, confirma qué archivo seleccionaste; algunas acciones pueden ser difíciles de deshacer.\n\nTambién es útil hacer copias de seguridad de documentos importantes en un lugar autorizado, como una unidad externa o un servicio escolar. Una copia de seguridad permite recuperar archivos si el dispositivo falla o se pierde.\n\nLa ruta de un archivo indica las carpetas que debes recorrer para encontrarlo. Por ejemplo, la carpeta Ciencias puede estar dentro de Tareas y contener el archivo experimento.pdf. Una estructura ordenada evita tener que abrir muchas carpetas al azar.\n\nLos archivos pueden tener tamaños y formatos distintos. Una fotografía de alta calidad puede ocupar más espacio que un documento de texto sencillo. Si el dispositivo muestra un aviso de poco espacio, no borres archivos sin revisar: pregunta qué se puede conservar o mover de manera segura.\n\nAl compartir un documento, revisa quién tendrá permiso para verlo o editarlo. Un enlace compartido puede permitir acceso a más personas de las que imaginas. Utiliza las opciones autorizadas por tu escuela y evita incluir datos personales que no sean necesarios.\n\nUna copia de seguridad debe guardarse en un lugar distinto al archivo original y actualizarse cuando cambian documentos importantes. Así existe otra versión si el archivo se elimina por accidente o el dispositivo deja de funcionar. Comprueba con una persona adulta cuál es el método permitido.'
    WHEN 'seguridad-digital' THEN
        'La seguridad digital reúne hábitos que ayudan a proteger tus cuentas, dispositivos y datos. La información personal incluye datos como tu dirección, teléfono, contraseñas, ubicación y fotografías privadas. Antes de compartir algo, piensa quién podrá verlo y para qué lo necesita.\n\nUsa contraseñas largas y diferentes para tus cuentas. No las compartas con amistades ni las publiques en mensajes o redes. Cuando esté disponible, activa la verificación en dos pasos: además de la contraseña, la cuenta pide una confirmación adicional para iniciar sesión.\n\nDesconfía de mensajes inesperados que prometen premios, piden datos o presionan para abrir un enlace rápidamente. Revisa con cuidado quién lo envía y consulta a una persona adulta de confianza antes de responder. No descargues archivos ni escribas tu contraseña en una página abierta desde un enlace sospechoso.\n\nMantén las aplicaciones actualizadas y utiliza el bloqueo de pantalla en dispositivos compartidos. Al terminar, cierra sesión y no guardes tu contraseña en equipos que otras personas usan. Si algo te hace sentir incómodo o parece una amenaza, no respondas, conserva la evidencia y pide ayuda a una persona adulta de confianza.\n\nLa seguridad no depende de una sola herramienta. Combinar contraseñas únicas, actualizaciones, privacidad cuidadosa y consultas oportunas reduce riesgos y ayuda a tomar decisiones más seguras.\n\nLos permisos de una aplicación controlan a qué recursos puede acceder, como la cámara, el micrófono o la ubicación. Si una aplicación pide un permiso que no parece necesario, detente y consulta a la persona responsable del dispositivo antes de aceptarlo.\n\nAl usar una computadora pública o compartida, no marques la opción para recordar tu contraseña. Cierra sesión al terminar y revisa que tus documentos privados no queden abiertos. Evita guardar información personal en un dispositivo que no controlas.\n\nLos mensajes falsos pueden imitar el nombre o la imagen de una organización conocida. No basta con que el diseño parezca real: revisa el contenido con cuidado y confirma la solicitud por un canal oficial que ya conozcas, sin usar el enlace del mensaje dudoso.\n\nSi recibes una imagen, comentario o solicitud que te preocupa, no tienes que resolverlo a solas. No reenvíes contenido dañino; informa a una persona adulta de confianza o al responsable de la plataforma para recibir ayuda.'
    WHEN 'pensamiento-logico' THEN
        'El pensamiento lógico ayuda a resolver problemas de manera ordenada. Primero define qué quieres lograr y cómo reconocerás que lo conseguiste. Después reúne la información necesaria y separa los datos importantes de los que no ayudan a resolver la tarea.\n\nDivide un problema grande en partes pequeñas. Por ejemplo, preparar una mochila puede separarse en revisar el horario, buscar los materiales, guardarlos y comprobar que no falte nada. Cada paso es más fácil de entender y verificar que una instrucción general como prepara todo.\n\nBusca patrones y semejanzas. Si una actividad se repite cada día, puedes reutilizar pasos que ya funcionan. También identifica las decisiones: si tienes educación física, guarda la ropa deportiva; de lo contrario, continúa con los materiales habituales.\n\nUna solución debe revisarse con ejemplos. Sigue cada paso en orden y observa si el resultado coincide con el objetivo. Si algo falla, cambia una parte a la vez para descubrir qué causó el problema, en lugar de comenzar de nuevo sin analizarlo.\n\nExplicar tu razonamiento permite que otra persona compruebe la solución. No siempre existe una única manera de resolver un problema, pero los pasos deben ser claros, coherentes y adecuados para el objetivo.\n\nLa descomposición consiste en separar una tarea compleja en problemas más sencillos. Si quieres organizar una exposición, puedes planear la búsqueda de información, la preparación de imágenes, la escritura del guion y la práctica oral como tareas diferentes.\n\nLa abstracción ayuda a concentrarte en lo importante. Para calcular cuánto tiempo toma llegar a la escuela, quizá importan la distancia y el transporte, pero no el color de la mochila. Elegir los datos relevantes simplifica la solución.\n\nUn patrón es algo que se repite o sigue una regla. Observar una secuencia de números, pasos o formas puede ayudar a predecir qué sigue. Después comprueba tu idea con más ejemplos para evitar concluir a partir de un solo caso.\n\nLas condiciones permiten tomar decisiones según la situación. Describe qué pasa cuando una condición se cumple y qué pasa cuando no. Considerar ambos caminos hace que una solución sea más completa y evita que una situación quede sin respuesta.'
    WHEN 'algoritmos' THEN
        'Un algoritmo es una secuencia ordenada y finita de instrucciones para resolver un problema o completar una tarea. Una receta es un ejemplo: indica qué ingredientes se necesitan, qué hacer con ellos y en qué orden para obtener un resultado.\n\nAntes de escribir un algoritmo, define el problema. Identifica las entradas, es decir, los datos necesarios, y el resultado esperado. Si quieres calcular el total de dos cantidades, las entradas son esos valores y la salida es su suma.\n\nEscribe instrucciones claras y ordénalas. Evita pasos que obliguen a adivinar qué hacer. Cuando hay una decisión, indica qué camino seguir para cada resultado posible. Cuando una acción se repite, explica cuántas veces o bajo qué condición debe continuar.\n\nPrueba el algoritmo con ejemplos distintos, incluyendo valores pequeños y situaciones límite. Sigue las instrucciones exactamente como están escritas; así puedes detectar pasos que faltan, instrucciones ambiguas o decisiones incompletas.\n\nSi la prueba falla, corrige el paso relacionado y vuelve a probar. Diseñar algoritmos suele requerir varios intentos. Un buen algoritmo es comprensible, termina en un tiempo razonable y produce el resultado esperado para los casos previstos.\n\nUn algoritmo puede describirse con instrucciones escritas, pseudocódigo o un diagrama de flujo. Cambia la forma de representar los pasos, pero no el objetivo: que la solución sea entendible y pueda revisarse antes de llevarla a un programa.\n\nLas entradas son los datos que necesita la solución; las salidas son los resultados que produce. En una receta, los ingredientes funcionan como entradas y el platillo terminado es la salida. En un programa de suma, se reciben dos números y se muestra el total.\n\nAlgunos algoritmos incluyen una repetición. Por ejemplo, revisar cada libro de una lista repite la misma acción hasta llegar al último. Es importante indicar cuándo termina la repetición para que el proceso no continúe para siempre.\n\nUna tabla de seguimiento puede ayudar a probar un algoritmo. Anota los valores de entrada, cada cambio y el resultado final. Si el resultado no coincide con lo esperado, revisa las instrucciones en orden y localiza el paso donde se produce la diferencia.'
    WHEN 'diagramas-y-pseudocodigo' THEN
        'Un diagrama de flujo representa visualmente los pasos de un algoritmo. El óvalo suele indicar el inicio o el final, el rectángulo representa una acción y el rombo presenta una decisión. Las flechas muestran el sentido en que se recorren los pasos.\n\nUna decisión debe tener caminos que indiquen qué ocurre ante cada respuesta posible, por ejemplo sí y no. Después de cada camino, el flujo puede continuar en pasos diferentes o reunirse más adelante. Las flechas evitan que el orden de las instrucciones quede a interpretación de quien lee.\n\nEl pseudocódigo describe una solución con palabras y una estructura parecida a la programación, sin depender de un lenguaje específico. Puede usar expresiones como LEER, MOSTRAR, SI y SI NO para indicar entradas, salidas y decisiones.\n\nAl leer un diagrama o pseudocódigo, comienza por el inicio y sigue cada flecha o instrucción en orden. Anota los valores que cambian y revisa qué camino se toma en cada decisión. Esto ayuda a anticipar el resultado sin ejecutar un programa.\n\nAntes de compartir una representación, comprueba que todos los caminos tengan una salida y que no falten pasos. Una persona que no conoce tu idea debería poder seguir el diagrama y obtener el mismo resultado.\n\nLas flechas deben conectar claramente los símbolos y apuntar hacia el paso siguiente. Mantener el recorrido de arriba hacia abajo o de izquierda a derecha ayuda a leerlo. Si las líneas se cruzan demasiado, reorganiza los elementos para que el diagrama no resulte confuso.\n\nEn pseudocódigo, cada instrucción debe expresar una acción concreta. Por ejemplo: LEER edad, SI edad es mayor o igual a 12, MOSTRAR un mensaje. No es necesario usar la sintaxis exacta de un lenguaje, pero sí aclarar qué dato se consulta y qué sucede en cada caso.\n\nUna decisión suele dividir el flujo en dos o más caminos. Etiqueta los caminos con sus resultados, como sí y no, y prueba cada uno. Si solo existe un camino, quizá no se esté mostrando toda la decisión.\n\nPara comprobar una representación, elige datos de ejemplo y recorre el diagrama paso a paso. Anota qué instrucciones se ejecutan y qué resultado aparece. Repite con valores que lleven por caminos distintos para verificar que todos estén definidos.\n\nLos diagramas y el pseudocódigo son herramientas para planear, comunicar y revisar una solución antes de programarla. No ejecutan por sí mismos las instrucciones, pero permiten encontrar errores de orden o decisiones que faltan con más facilidad.'
    WHEN 'fundamentos-de-programacion' THEN
        'Un programa es un conjunto de instrucciones que una computadora ejecuta para realizar una tarea. Las instrucciones deben escribirse de forma clara y en el orden correcto. En Python, print(\"Hola\") muestra un mensaje en pantalla y input(\"¿Cómo te llamas?\") permite recibir un dato escrito por la persona.\n\nUna variable es un nombre asociado a un dato que el programa puede consultar o cambiar. Por ejemplo, edad = 12 guarda el número 12 con el nombre edad. Una variable puede representar texto, números u otros valores; usar nombres descriptivos hace más fácil entender el programa.\n\nUna operación combina valores para calcular un resultado. Si puntos = 3 y se suman 2 puntos, el nuevo total puede guardarse como puntos = puntos + 2. El programa sigue las instrucciones una por una, por eso el orden puede cambiar el resultado.\n\nUna condición permite elegir qué instrucciones ejecutar según una situación. Por ejemplo, si la respuesta es correcta, el programa puede mostrar un mensaje de felicitación; si no, puede ofrecer una pista. La condición revisa si una expresión es verdadera o falsa.\n\nPrueba tu programa con varios datos y compara los resultados con lo esperado. Cuando aparece un error, revisa el mensaje y localiza la instrucción relacionada. Cambia una parte a la vez y vuelve a probar: equivocarse y corregir es parte normal del aprendizaje de programación.\n\nLos valores pueden ser de tipos diferentes, como números y texto. El número 5 permite hacer operaciones matemáticas, mientras que el texto \"cinco\" representa caracteres. Antes de combinar valores, comprueba que la operación tenga sentido para el tipo de dato utilizado.\n\nLos nombres de las variables deben ser claros y seguir las reglas del lenguaje. edad_usuario explica mejor su contenido que x. En Python se distingue entre mayúsculas y minúsculas, así que puntos y Puntos pueden ser nombres diferentes.\n\nLa indentación, es decir, los espacios al inicio de algunas líneas, muestra qué instrucciones pertenecen a una condición. Mantenerla consistente facilita leer el programa y ayuda a evitar errores. También puedes usar comentarios breves para explicar por qué una parte importante está escrita de cierta forma.\n\nUn error de sintaxis ocurre cuando una instrucción no respeta las reglas del lenguaje, por ejemplo si falta un paréntesis. Un error lógico ocurre cuando el programa se ejecuta pero produce un resultado distinto del esperado. En ambos casos, prueba con datos sencillos y revisa paso a paso.\n\nEscribe primero una solución pequeña y agrega funciones poco a poco. Guarda una versión que funciona antes de hacer cambios grandes. Así podrás comparar resultados y reconocer qué modificación produjo un problema.'
END,
    l.estimated_minutes = CASE
        WHEN m.slug IN ('conoce-tu-computadora', 'hardware-y-software', 'fundamentos-digitales', 'pensamiento-logico') THEN 12
        WHEN m.slug IN ('seguridad-digital', 'algoritmos', 'diagramas-y-pseudocodigo', 'fundamentos-de-programacion') THEN 15
        ELSE l.estimated_minutes
    END
WHERE l.title = CASE m.slug
    WHEN 'conoce-tu-computadora' THEN 'Las partes de una computadora'
    WHEN 'hardware-y-software' THEN 'Hardware y software trabajan juntos'
    WHEN 'fundamentos-digitales' THEN 'Archivos, carpetas y datos'
    WHEN 'seguridad-digital' THEN 'Cuida tu información personal'
    WHEN 'pensamiento-logico' THEN 'Resuelve problemas paso a paso'
    WHEN 'algoritmos' THEN 'Diseña un algoritmo'
    WHEN 'diagramas-y-pseudocodigo' THEN 'Representa una solución'
    WHEN 'fundamentos-de-programacion' THEN 'Tu primer programa'
END;

UPDATE lessons l
JOIN modules m ON m.id = l.module_id
SET l.content = 'Un programa es un conjunto de instrucciones que la computadora ejecuta para resolver una tarea. Escribir código consiste en expresar esas instrucciones con reglas claras y en un orden que el equipo pueda seguir.\n\nUna variable guarda un dato que el programa puede consultar o cambiar. Por ejemplo, puntos = 4 guarda el número 4 bajo el nombre puntos. Si después sumas 2, el nuevo valor de puntos será 6.\n\nLos datos pueden ser números, texto o valores de verdadero y falso. El texto se escribe entre comillas, como nombre = \"Ana\". Elegir el tipo de dato adecuado ayuda a realizar operaciones correctas.\n\nLa instrucción print() muestra un mensaje o resultado en la pantalla. La instrucción input() permite pedir un dato a la persona que utiliza el programa. Si guardas el resultado de input en una variable, podrás usarlo después.\n\nUna condición permite elegir qué hacer según una situación. Por ejemplo, si la edad cumple una regla, el programa puede mostrar un mensaje; si no, puede realizar otra acción. Las condiciones hacen que un programa responda de distintas maneras.\n\nEn Python, la indentación —los espacios al inicio de algunas líneas— indica qué instrucciones forman parte de una condición. Mantener la sangría correcta es importante para que el programa entienda la estructura.\n\nUn error de sintaxis ocurre cuando el código no sigue las reglas del lenguaje, como olvidar cerrar comillas o paréntesis. Un error lógico sucede cuando el programa corre, pero produce un resultado distinto al esperado. Leer el mensaje de error y revisar cada paso ayuda a encontrarlos.\n\nPrueba los programas con datos distintos, incluso casos sencillos como cero o una respuesta vacía. Anota qué resultado esperas y compáralo con lo que aparece. Cambiar una cosa a la vez facilita descubrir dónde está el problema.\n\nAl aprender a programar, no necesitas memorizar todo. Puedes dividir una tarea en pasos pequeños, explicar qué quieres que ocurra, escribir instrucciones y probarlas. Pedir ayuda y corregir errores forma parte normal del proceso.' ,
    l.estimated_minutes = 15
WHERE m.slug = 'fundamentos-de-programacion2'
  AND l.sort_order = 1;

INSERT INTO activities (module_id, lesson_id, type, title, description, max_attempts, passing_score, is_published)
SELECT m.id, l.id, seed.type, seed.title, seed.description, NULL, 70, 0
FROM (
    SELECT 'conoce-tu-computadora' AS slug, 'quiz' AS type, 'Repaso: Conoce tu computadora' AS title,
        'Comprueba lo que aprendiste sobre los componentes del equipo.' AS description
    UNION ALL SELECT 'hardware-y-software', 'quiz', 'Repaso: Hardware y software', 'Distingue los componentes físicos de los programas.'
    UNION ALL SELECT 'fundamentos-digitales', 'quiz', 'Repaso: Fundamentos digitales', 'Practica cómo organizar y reconocer información digital.'
    UNION ALL SELECT 'seguridad-digital', 'quiz', 'Repaso: Seguridad digital', 'Elige hábitos que ayudan a proteger tu información.'
    UNION ALL SELECT 'pensamiento-logico', 'quiz', 'Repaso: Pensamiento lógico', 'Aplica pasos y condiciones para resolver problemas.'
    UNION ALL SELECT 'algoritmos', 'quiz', 'Repaso: Algoritmos', 'Comprueba cómo se diseña y prueba un algoritmo.'
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'quiz', 'Repaso: Diagramas y pseudocódigo', 'Interpreta símbolos y estructuras de una solución.'
    UNION ALL SELECT 'fundamentos-de-programacion', 'quiz', 'Repaso: Fundamentos de programación', 'Practica instrucciones, variables y resultados.'
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Reto: Clasifica hardware y software', 'Clasifica cada elemento como hardware o software.'
    UNION ALL SELECT 'hardware-y-software', 'identify', 'Reto: Identifica los componentes', 'Elige el componente que corresponde a cada descripción.'
    UNION ALL SELECT 'fundamentos-digitales', 'classify', 'Reto: Organiza la información', 'Identifica si cada ejemplo es un archivo, una carpeta o un programa.'
    UNION ALL SELECT 'seguridad-digital', 'classify', 'Reto: Decisiones seguras', 'Clasifica cada acción como segura o riesgosa.'
    UNION ALL SELECT 'pensamiento-logico', 'identify', 'Reto: Piensa paso a paso', 'Selecciona la acción lógica para resolver cada situación.'
    UNION ALL SELECT 'algoritmos', 'order', 'Reto: Ordena un algoritmo', 'Selecciona cada paso una sola vez y ponlos en el orden correcto.'
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'identify', 'Reto: Lee un diagrama', 'Reconoce la función de cada símbolo y estructura.'
    UNION ALL SELECT 'fundamentos-de-programacion', 'identify', 'Reto: Predice el programa', 'Elige la instrucción o resultado que completa cada ejemplo.'
) AS seed
JOIN modules m ON m.slug = seed.slug AND m.archived_at IS NULL
LEFT JOIN lessons l ON l.module_id = m.id AND l.title = CASE m.slug
    WHEN 'conoce-tu-computadora' THEN 'Las partes de una computadora'
    WHEN 'hardware-y-software' THEN 'Hardware y software trabajan juntos'
    WHEN 'fundamentos-digitales' THEN 'Archivos, carpetas y datos'
    WHEN 'seguridad-digital' THEN 'Cuida tu información personal'
    WHEN 'pensamiento-logico' THEN 'Resuelve problemas paso a paso'
    WHEN 'algoritmos' THEN 'Diseña un algoritmo'
    WHEN 'diagramas-y-pseudocodigo' THEN 'Representa una solución'
    WHEN 'fundamentos-de-programacion' THEN 'Tu primer programa'
END
WHERE NOT EXISTS (
    SELECT 1 FROM activities existing
    WHERE existing.module_id = m.id AND existing.type = seed.type AND existing.title = seed.title
);

INSERT INTO questions (activity_id, prompt, explanation, sort_order)
SELECT a.id, seed.prompt, seed.explanation, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué componente ejecuta instrucciones y coordina operaciones?' AS prompt, 'La CPU procesa instrucciones y coordina las operaciones del equipo.' AS explanation, 1 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué hace principalmente la memoria RAM?', 'La RAM conserva temporalmente los datos de los programas que están en uso.', 2
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es hardware?', 'El teclado es un componente físico que puedes tocar; por eso es hardware.', 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es software?', 'Un navegador es un programa, así que pertenece al software.', 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una carpeta?', 'Una carpeta permite reunir y organizar archivos relacionados.', 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué indica normalmente la extensión de un archivo?', 'La extensión ayuda a reconocer el formato o tipo de archivo.', 2
    UNION ALL SELECT 'seguridad-digital', '¿Qué conviene hacer si recibes un enlace inesperado que pide tu contraseña?', 'No abras el enlace ni compartas tu contraseña; verifica el mensaje con una fuente confiable.', 1
    UNION ALL SELECT 'seguridad-digital', '¿Cuál es una buena práctica para proteger una cuenta?', 'Una contraseña larga y diferente para cada cuenta reduce el riesgo de acceso no autorizado.', 2
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene definir primero al resolver un problema?', 'Primero aclara qué problema quieres resolver y cuál es el resultado esperado.', 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué debes hacer si una solución no produce el resultado esperado?', 'Revisa los pasos, identifica qué debe cambiar y vuelve a probar la solución.', 2
    UNION ALL SELECT 'algoritmos', '¿Qué describe un algoritmo?', 'Un algoritmo es una secuencia ordenada y finita de instrucciones para resolver un problema.', 1
    UNION ALL SELECT 'algoritmos', '¿Por qué se prueba un algoritmo con ejemplos?', 'Las pruebas ayudan a encontrar pasos faltantes o un orden que no funciona.', 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rombo en un diagrama de flujo?', 'El rombo representa una decisión que puede conducir a diferentes caminos.', 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene el pseudocódigo?', 'Permite describir los pasos de una solución sin depender de un lenguaje de programación específico.', 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve una variable?', 'Una variable guarda un dato que un programa puede consultar o cambiar.', 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué permite hacer una condición en un programa?', 'Una condición permite elegir qué instrucciones ejecutar según una situación.', 2
) AS seed
JOIN modules m ON m.slug = seed.slug
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz' AND a.title = CONCAT('Repaso: ', m.title)
WHERE NOT EXISTS (
    SELECT 1 FROM questions existing
    WHERE existing.activity_id = a.id AND existing.prompt = seed.prompt
);

INSERT INTO questions (activity_id, prompt, explanation, sort_order)
SELECT a.id, seed.prompt, seed.explanation, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué componente conserva tus archivos cuando apagas la computadora?' AS prompt, 'El almacenamiento, como un disco duro o SSD, conserva la información sin energía.' AS explanation, 3 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un dispositivo de salida?', 'La pantalla presenta información de la computadora; por eso es un dispositivo de salida.', 4
    UNION ALL SELECT 'hardware-y-software', '¿Qué tarea realiza principalmente el sistema operativo?', 'El sistema operativo administra recursos del equipo y permite que las aplicaciones funcionen.', 3
    UNION ALL SELECT 'hardware-y-software', '¿Qué necesita un programa para mostrar una imagen en pantalla?', 'El programa usa hardware como el procesador y la pantalla para procesar y mostrar la imagen.', 4
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué información sugiere la extensión .jpg?', 'La extensión .jpg suele identificar un archivo de imagen.', 3
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a encontrar un documento más fácilmente?', 'Un nombre descriptivo y una carpeta organizada facilitan encontrar los archivos.', 4
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve la verificación en dos pasos?', 'Añade una confirmación adicional para proteger el acceso a una cuenta.', 3
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer antes de publicar una foto personal?', 'Piensa quién podrá verla y pide ayuda si no estás seguro de compartirla.', 4
    UNION ALL SELECT 'pensamiento-logico', '¿Qué estrategia ayuda a resolver una tarea grande?', 'Dividir una tarea en partes pequeñas facilita planear y revisar cada paso.', 3
    UNION ALL SELECT 'pensamiento-logico', '¿Qué puedes hacer para reconocer un patrón?', 'Comparar ejemplos permite notar qué elementos se repiten y usar esa información.', 4
    UNION ALL SELECT 'algoritmos', '¿Qué característica debe tener una secuencia de instrucciones para ser un algoritmo?', 'Un algoritmo tiene pasos claros, ordenados y finitos para obtener un resultado.', 3
    UNION ALL SELECT 'algoritmos', '¿Qué conviene hacer después de cambiar un paso de un algoritmo?', 'Probar de nuevo permite comprobar que el cambio resuelva el problema sin crear otro.', 4
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rectángulo en un diagrama de flujo?', 'El rectángulo suele representar una acción o proceso.', 3
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué indican las flechas de un diagrama de flujo?', 'Las flechas muestran el orden y la dirección que sigue el recorrido.', 4
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué muestra en pantalla la instrucción print(\"Hola\")?', 'La instrucción print presenta en pantalla el texto que recibe.', 3
    UNION ALL SELECT 'fundamentos-de-programacion', 'Si puntos vale 4, ¿qué valor tendrá después de puntos = puntos + 2?', 'La operación suma 2 al valor anterior de puntos, por lo que el nuevo valor es 6.', 4
) AS seed
JOIN modules m ON m.slug = seed.slug
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz' AND a.title = CONCAT('Repaso: ', m.title)
WHERE NOT EXISTS (
    SELECT 1 FROM questions existing
    WHERE existing.activity_id = a.id AND existing.prompt = seed.prompt
);

INSERT INTO questions (activity_id, prompt, explanation, sort_order)
SELECT a.id, seed.prompt, seed.explanation, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué parte recibe las instrucciones de los programas y realiza gran parte del procesamiento?' AS prompt, 'La CPU ejecuta instrucciones y realiza operaciones para que los programas funcionen.' AS explanation, 5 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué sucede con la información que está en la memoria RAM cuando apagas el equipo?', 'La RAM es temporal: su contenido se borra al apagar la computadora.', 6
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente ayuda a conectar la CPU, la memoria y otras piezas?', 'La tarjeta madre conecta y permite la comunicación entre distintos componentes.', 7
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un ejemplo de dispositivo de entrada?', 'El micrófono envía sonido al equipo, así que es un dispositivo de entrada.', 8
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué tarea realiza principalmente la fuente de poder?', 'La fuente de poder suministra energía eléctrica a los componentes.', 9
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué describe mejor la salida de información?', 'La salida presenta resultados, por ejemplo, al mostrar texto en la pantalla.', 10
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos elementos es un sistema operativo?', 'Un sistema operativo es software que administra recursos del equipo.', 5
    UNION ALL SELECT 'hardware-y-software', '¿Qué es un controlador o driver?', 'Un controlador es software que ayuda al sistema operativo a comunicarse con un dispositivo.', 6
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos dispositivos puede ser de entrada y de salida?', 'Una pantalla táctil recibe toques y también muestra información.', 7
    UNION ALL SELECT 'hardware-y-software', '¿Qué diferencia un archivo de una aplicación?', 'La aplicación es un programa; el archivo guarda información que se puede abrir con un programa.', 8
    UNION ALL SELECT 'hardware-y-software', '¿Qué combinación se necesita para imprimir un documento?', 'La impresora aporta hardware y el programa y controlador aportan software.', 9
    UNION ALL SELECT 'hardware-y-software', '¿Cuál es un ejemplo de hardware interno?', 'La memoria RAM es un componente físico instalado dentro del equipo.', 10
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué es una ruta de archivo?', 'La ruta muestra la ubicación de un archivo pasando por sus carpetas.', 5
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué conviene hacer antes de borrar un archivo?', 'Revisa que sea el archivo correcto y que ya no lo necesites; si dudas, pregunta antes.', 6
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una copia de seguridad?', 'Una copia de seguridad permite recuperar archivos si se pierde o daña el original.', 7
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué debes revisar al compartir un documento?', 'Comprueba quién puede abrirlo o editarlo y comparte solo con las personas autorizadas.', 8
    UNION ALL SELECT 'fundamentos-digitales', '¿Cuál es un ejemplo de dato?', 'Una fecha, una palabra o un número pueden ser datos que un programa organiza.', 9
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a distinguir una carpeta de un archivo?', 'Una carpeta contiene y organiza elementos; un archivo guarda información concreta.', 10
    UNION ALL SELECT 'seguridad-digital', '¿Qué es un dato personal?', 'La ubicación puede identificar información privada y debe compartirse con cuidado.', 5
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer si una página inesperada te pide tu contraseña?', 'No escribas la contraseña desde un enlace sospechoso y verifica la solicitud por un canal confiable.', 6
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve revisar los permisos de una aplicación?', 'Revisar permisos ayuda a decidir si la aplicación necesita acceso a cámara, micrófono u otros datos.', 7
    UNION ALL SELECT 'seguridad-digital', '¿Qué hábito es recomendable en una computadora compartida?', 'Cerrar sesión evita que otra persona acceda a tu cuenta después de usar el equipo.', 8
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer si algo que ocurre en línea te preocupa?', 'Pide ayuda a una persona adulta de confianza y no tienes que resolverlo a solas.', 9
    UNION ALL SELECT 'seguridad-digital', '¿Por qué conviene instalar actualizaciones autorizadas?', 'Las actualizaciones pueden corregir problemas y mejorar la protección del dispositivo.', 10
    UNION ALL SELECT 'pensamiento-logico', '¿Qué significa descomponer un problema?', 'Descomponer significa separar un problema complejo en partes más pequeñas.', 5
    UNION ALL SELECT 'pensamiento-logico', '¿Qué información es más útil para resolver un problema?', 'Conviene reunir los datos relacionados con el objetivo y dejar de lado los que no influyen.', 6
    UNION ALL SELECT 'pensamiento-logico', '¿Qué es una condición en una solución?', 'Una condición permite elegir qué hacer según se cumpla o no una situación.', 7
    UNION ALL SELECT 'pensamiento-logico', '¿Cómo puedes comprobar si tu solución funciona?', 'Prueba los pasos con ejemplos y compara el resultado con el objetivo definido.', 8
    UNION ALL SELECT 'pensamiento-logico', '¿Qué es la abstracción?', 'La abstracción consiste en enfocarse en los detalles importantes para resolver la tarea.', 9
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene hacer si encuentras un error?', 'Revisa los pasos y cambia una parte a la vez para identificar qué causó el error.', 10
    UNION ALL SELECT 'algoritmos', '¿Qué se considera una entrada de un algoritmo?', 'La entrada es un dato que el algoritmo recibe para poder resolver la tarea.', 5
    UNION ALL SELECT 'algoritmos', '¿Qué significa que un algoritmo sea finito?', 'Un algoritmo finito termina después de una cantidad limitada de pasos.', 6
    UNION ALL SELECT 'algoritmos', '¿Para qué sirve una repetición dentro de un algoritmo?', 'La repetición permite realizar varias veces una acción, con una regla para saber cuándo detenerse.', 7
    UNION ALL SELECT 'algoritmos', '¿Qué es una salida de un algoritmo?', 'La salida es el resultado que produce el algoritmo al procesar sus entradas.', 8
    UNION ALL SELECT 'algoritmos', '¿Qué ayuda a encontrar un error en una secuencia de pasos?', 'Una tabla de seguimiento permite revisar cómo cambian los datos paso a paso.', 9
    UNION ALL SELECT 'algoritmos', '¿Qué debes hacer si una instrucción puede entenderse de dos maneras?', 'Aclárala para que todas las personas puedan seguir el paso sin adivinar.', 10
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué figura suele representar el inicio o el final?', 'El óvalo suele utilizarse para marcar el inicio o el final del flujo.', 5
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué debe indicar cada camino que sale de una decisión?', 'Los caminos deben señalar qué hacer ante cada posible respuesta, como sí o no.', 6
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué instrucción de pseudocódigo puede recibir un dato?', 'LEER indica que el algoritmo recibe un dato de entrada.', 7
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene recorrer un diagrama con datos de ejemplo?', 'Seguirlo con datos permite comprobar el camino y el resultado sin ejecutar un programa.', 8
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ayuda a que un diagrama sea fácil de leer?', 'Conectar los símbolos con flechas claras y evitar cruces innecesarios facilita seguir el recorrido.', 9
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué palabra puede indicar una salida en pseudocódigo?', 'MOSTRAR puede indicar que se presenta un mensaje o resultado.', 10
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué tipo de dato representa el valor 12?', '12 es un número entero que puede utilizarse en operaciones aritméticas.', 5
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué hace input(\"¿Cómo te llamas?\") en Python?', 'input muestra la pregunta y permite que la persona escriba un dato.', 6
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué indica una condición SI en un programa?', 'SI permite ejecutar instrucciones cuando una expresión resulta verdadera.', 7
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué es un error de sintaxis?', 'Un error de sintaxis ocurre cuando una instrucción no respeta las reglas del lenguaje.', 8
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve la indentación en Python?', 'La indentación agrupa instrucciones y muestra cuáles pertenecen a una condición.', 9
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué es un error lógico?', 'Un error lógico permite que el programa se ejecute, pero el resultado no es el esperado.', 10
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué tipo de dato representa el valor 12?', '12 es un número entero que puede utilizarse en operaciones aritméticas.', 5
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué hace input(\"¿Cómo te llamas?\") en Python?', 'input muestra la pregunta y permite que la persona escriba un dato.', 6
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué indica una condición SI en un programa?', 'SI permite ejecutar instrucciones cuando una expresión resulta verdadera.', 7
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué es un error de sintaxis?', 'Un error de sintaxis ocurre cuando una instrucción no respeta las reglas del lenguaje.', 8
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Para qué sirve la indentación en Python?', 'La indentación agrupa instrucciones y muestra cuáles pertenecen a una condición.', 9
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué es un error lógico?', 'Un error lógico permite que el programa se ejecute, pero el resultado no es el esperado.', 10
) AS seed
JOIN modules m ON m.slug = seed.slug AND m.archived_at IS NULL
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz'
    AND a.title = CASE
        WHEN m.slug = 'fundamentos-de-programacion2' THEN 'Repaso: Fundamentos de programación'
        ELSE CONCAT('Repaso: ', m.title)
    END
WHERE NOT EXISTS (
    SELECT 1 FROM questions existing
    WHERE existing.activity_id = a.id AND existing.prompt = seed.prompt
);

INSERT INTO answer_options (question_id, label, is_correct, sort_order)
SELECT q.id, seed.label, seed.is_correct, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué componente ejecuta instrucciones y coordina operaciones?' AS prompt, 'La CPU' AS label, 1 AS is_correct, 0 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente ejecuta instrucciones y coordina operaciones?', 'El monitor', 0, 1
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente ejecuta instrucciones y coordina operaciones?', 'El teclado', 0, 2
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué hace principalmente la memoria RAM?', 'Guardar temporalmente datos en uso', 1, 0
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué hace principalmente la memoria RAM?', 'Imprimir documentos', 0, 1
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué hace principalmente la memoria RAM?', 'Mostrar imágenes en la pantalla', 0, 2
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es hardware?', 'El teclado', 1, 0
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es hardware?', 'El navegador web', 0, 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es hardware?', 'El sistema operativo', 0, 2
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es software?', 'Un navegador web', 1, 0
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es software?', 'Una memoria RAM', 0, 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos ejemplos es software?', 'Un teclado', 0, 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una carpeta?', 'Para organizar archivos relacionados', 1, 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una carpeta?', 'Para aumentar la memoria RAM', 0, 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una carpeta?', 'Para conectarse a Internet', 0, 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué indica normalmente la extensión de un archivo?', 'Su formato o tipo', 1, 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué indica normalmente la extensión de un archivo?', 'Quién lo creó', 0, 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué indica normalmente la extensión de un archivo?', 'Cuánto tiempo lleva encendida la computadora', 0, 2
    UNION ALL SELECT 'seguridad-digital', '¿Qué conviene hacer si recibes un enlace inesperado que pide tu contraseña?', 'No abrirlo y verificar el mensaje', 1, 0
    UNION ALL SELECT 'seguridad-digital', '¿Qué conviene hacer si recibes un enlace inesperado que pide tu contraseña?', 'Ingresar la contraseña rápidamente', 0, 1
    UNION ALL SELECT 'seguridad-digital', '¿Qué conviene hacer si recibes un enlace inesperado que pide tu contraseña?', 'Compartirlo con tus contactos', 0, 2
    UNION ALL SELECT 'seguridad-digital', '¿Cuál es una buena práctica para proteger una cuenta?', 'Usar una contraseña larga y única', 1, 0
    UNION ALL SELECT 'seguridad-digital', '¿Cuál es una buena práctica para proteger una cuenta?', 'Usar la misma contraseña en todas partes', 0, 1
    UNION ALL SELECT 'seguridad-digital', '¿Cuál es una buena práctica para proteger una cuenta?', 'Publicar la contraseña para no olvidarla', 0, 2
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene definir primero al resolver un problema?', 'Qué problema quiero resolver', 1, 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene definir primero al resolver un problema?', 'La decoración de la solución', 0, 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene definir primero al resolver un problema?', 'El último paso sin conocer el objetivo', 0, 2
    UNION ALL SELECT 'pensamiento-logico', '¿Qué debes hacer si una solución no produce el resultado esperado?', 'Revisar los pasos y volver a probar', 1, 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué debes hacer si una solución no produce el resultado esperado?', 'Repetir exactamente lo mismo sin revisar', 0, 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué debes hacer si una solución no produce el resultado esperado?', 'Ignorar el resultado', 0, 2
    UNION ALL SELECT 'algoritmos', '¿Qué describe un algoritmo?', 'Pasos ordenados para resolver un problema', 1, 0
    UNION ALL SELECT 'algoritmos', '¿Qué describe un algoritmo?', 'Una pieza física de la computadora', 0, 1
    UNION ALL SELECT 'algoritmos', '¿Qué describe un algoritmo?', 'Una imagen guardada en una carpeta', 0, 2
    UNION ALL SELECT 'algoritmos', '¿Por qué se prueba un algoritmo con ejemplos?', 'Para encontrar pasos faltantes o errores', 1, 0
    UNION ALL SELECT 'algoritmos', '¿Por qué se prueba un algoritmo con ejemplos?', 'Para cambiar el nombre de la computadora', 0, 1
    UNION ALL SELECT 'algoritmos', '¿Por qué se prueba un algoritmo con ejemplos?', 'Para evitar conocer el resultado', 0, 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rombo en un diagrama de flujo?', 'Una decisión', 1, 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rombo en un diagrama de flujo?', 'El nombre de un archivo', 0, 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rombo en un diagrama de flujo?', 'Una parte física del teclado', 0, 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene el pseudocódigo?', 'Describe una solución sin depender de un lenguaje específico', 1, 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene el pseudocódigo?', 'Solo puede leerse en una computadora', 0, 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene el pseudocódigo?', 'Reemplaza la necesidad de ordenar pasos', 0, 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve una variable?', 'Para guardar un dato que el programa usa', 1, 0
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve una variable?', 'Para limpiar la pantalla físicamente', 0, 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve una variable?', 'Para conectar el teclado a la computadora', 0, 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué permite hacer una condición en un programa?', 'Elegir instrucciones según una situación', 1, 0
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué permite hacer una condición en un programa?', 'Aumentar el tamaño de la pantalla', 0, 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué permite hacer una condición en un programa?', 'Guardar todos los archivos automáticamente', 0, 2
) AS seed
JOIN modules m ON m.slug = seed.slug
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz' AND a.title = CONCAT('Repaso: ', m.title)
JOIN questions q ON q.activity_id = a.id AND q.prompt = seed.prompt
WHERE NOT EXISTS (
    SELECT 1 FROM answer_options existing
    WHERE existing.question_id = q.id AND existing.sort_order = seed.sort_order
);

INSERT INTO answer_options (question_id, label, is_correct, sort_order)
SELECT q.id, seed.label, seed.is_correct, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué componente conserva tus archivos cuando apagas la computadora?' AS prompt, 'La unidad SSD o el disco duro' AS label, 1 AS is_correct, 0 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente conserva tus archivos cuando apagas la computadora?', 'La memoria RAM', 0, 1
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente conserva tus archivos cuando apagas la computadora?', 'La CPU', 0, 2
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un dispositivo de salida?', 'La pantalla', 1, 0
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un dispositivo de salida?', 'El teclado', 0, 1
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un dispositivo de salida?', 'El ratón', 0, 2
    UNION ALL SELECT 'hardware-y-software', '¿Qué tarea realiza principalmente el sistema operativo?', 'Administra recursos y permite ejecutar aplicaciones', 1, 0
    UNION ALL SELECT 'hardware-y-software', '¿Qué tarea realiza principalmente el sistema operativo?', 'Es una pieza que muestra imágenes', 0, 1
    UNION ALL SELECT 'hardware-y-software', '¿Qué tarea realiza principalmente el sistema operativo?', 'Guarda energía dentro del teclado', 0, 2
    UNION ALL SELECT 'hardware-y-software', '¿Qué necesita un programa para mostrar una imagen en pantalla?', 'Procesador y pantalla', 1, 0
    UNION ALL SELECT 'hardware-y-software', '¿Qué necesita un programa para mostrar una imagen en pantalla?', 'Solo una carpeta', 0, 1
    UNION ALL SELECT 'hardware-y-software', '¿Qué necesita un programa para mostrar una imagen en pantalla?', 'Una contraseña impresa', 0, 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué información sugiere la extensión .jpg?', 'Que suele ser una imagen', 1, 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué información sugiere la extensión .jpg?', 'Que siempre es una carpeta', 0, 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué información sugiere la extensión .jpg?', 'Que es un programa para escribir código', 0, 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a encontrar un documento más fácilmente?', 'Usar un nombre claro y una carpeta organizada', 1, 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a encontrar un documento más fácilmente?', 'Guardar todos los archivos sin nombre', 0, 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a encontrar un documento más fácilmente?', 'Cambiar la extensión al azar', 0, 2
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve la verificación en dos pasos?', 'Añade una confirmación para proteger el acceso', 1, 0
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve la verificación en dos pasos?', 'Hace pública tu contraseña', 0, 1
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve la verificación en dos pasos?', 'Elimina la necesidad de actualizar aplicaciones', 0, 2
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer antes de publicar una foto personal?', 'Pensar quién podrá verla y consultar si tienes dudas', 1, 0
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer antes de publicar una foto personal?', 'Compartirla con cualquier persona que la solicite', 0, 1
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer antes de publicar una foto personal?', 'Publicar también tu dirección para dar contexto', 0, 2
    UNION ALL SELECT 'pensamiento-logico', '¿Qué estrategia ayuda a resolver una tarea grande?', 'Dividirla en partes pequeñas', 1, 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué estrategia ayuda a resolver una tarea grande?', 'Hacer todos los pasos a la vez', 0, 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué estrategia ayuda a resolver una tarea grande?', 'Evitar definir el resultado', 0, 2
    UNION ALL SELECT 'pensamiento-logico', '¿Qué puedes hacer para reconocer un patrón?', 'Comparar varios ejemplos y buscar elementos repetidos', 1, 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué puedes hacer para reconocer un patrón?', 'Cambiar todos los datos antes de observarlos', 0, 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué puedes hacer para reconocer un patrón?', 'Elegir una respuesta sin revisar los ejemplos', 0, 2
    UNION ALL SELECT 'algoritmos', '¿Qué característica debe tener una secuencia de instrucciones para ser un algoritmo?', 'Ser clara, ordenada y tener un final', 1, 0
    UNION ALL SELECT 'algoritmos', '¿Qué característica debe tener una secuencia de instrucciones para ser un algoritmo?', 'No indicar el orden de los pasos', 0, 1
    UNION ALL SELECT 'algoritmos', '¿Qué característica debe tener una secuencia de instrucciones para ser un algoritmo?', 'Depender de que alguien adivine qué hacer', 0, 2
    UNION ALL SELECT 'algoritmos', '¿Qué conviene hacer después de cambiar un paso de un algoritmo?', 'Probarlo de nuevo con un ejemplo', 1, 0
    UNION ALL SELECT 'algoritmos', '¿Qué conviene hacer después de cambiar un paso de un algoritmo?', 'Borrar el objetivo del problema', 0, 1
    UNION ALL SELECT 'algoritmos', '¿Qué conviene hacer después de cambiar un paso de un algoritmo?', 'Suponer que ya funciona sin revisarlo', 0, 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rectángulo en un diagrama de flujo?', 'Una acción o proceso', 1, 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rectángulo en un diagrama de flujo?', 'El inicio o el final', 0, 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué representa normalmente un rectángulo en un diagrama de flujo?', 'Una conexión a Internet', 0, 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué indican las flechas de un diagrama de flujo?', 'El orden y la dirección del recorrido', 1, 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué indican las flechas de un diagrama de flujo?', 'El color de los datos', 0, 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué indican las flechas de un diagrama de flujo?', 'El nombre de quien dibujó el diagrama', 0, 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué muestra en pantalla la instrucción print(\"Hola\")?', 'El mensaje Hola', 1, 0
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué muestra en pantalla la instrucción print(\"Hola\")?', 'Una nueva variable llamada Hola', 0, 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué muestra en pantalla la instrucción print(\"Hola\")?', 'El contenido de la memoria RAM', 0, 2
    UNION ALL SELECT 'fundamentos-de-programacion', 'Si puntos vale 4, ¿qué valor tendrá después de puntos = puntos + 2?', '6', 1, 0
    UNION ALL SELECT 'fundamentos-de-programacion', 'Si puntos vale 4, ¿qué valor tendrá después de puntos = puntos + 2?', '2', 0, 1
    UNION ALL SELECT 'fundamentos-de-programacion', 'Si puntos vale 4, ¿qué valor tendrá después de puntos = puntos + 2?', '42', 0, 2
) AS seed
JOIN modules m ON m.slug = seed.slug
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz' AND a.title = CONCAT('Repaso: ', m.title)
JOIN questions q ON q.activity_id = a.id AND q.prompt = seed.prompt
WHERE NOT EXISTS (
    SELECT 1 FROM answer_options existing
    WHERE existing.question_id = q.id AND existing.sort_order = seed.sort_order
);

INSERT INTO answer_options (question_id, label, is_correct, sort_order)
SELECT q.id,
    CASE positions.sort_order
        WHEN 0 THEN seed.option_a
        WHEN 1 THEN seed.option_b
        ELSE seed.option_c
    END,
    positions.sort_order = seed.correct_order,
    positions.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, '¿Qué parte recibe las instrucciones de los programas y realiza gran parte del procesamiento?' AS prompt, 'La CPU' AS option_a, 'El teclado' AS option_b, 'La bocina' AS option_c, 0 AS correct_order
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué sucede con la información que está en la memoria RAM cuando apagas el equipo?', 'Se borra porque es temporal', 'Se imprime en papel', 'Se convierte en una carpeta', 0
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué componente ayuda a conectar la CPU, la memoria y otras piezas?', 'El monitor', 'La tarjeta madre', 'El ratón', 1
    UNION ALL SELECT 'conoce-tu-computadora', '¿Cuál es un ejemplo de dispositivo de entrada?', 'La impresora', 'Los audífonos', 'El micrófono', 2
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué tarea realiza principalmente la fuente de poder?', 'Suministrar energía a los componentes', 'Guardar documentos', 'Mostrar páginas web', 0
    UNION ALL SELECT 'conoce-tu-computadora', '¿Qué describe mejor la salida de información?', 'Escribir datos con el teclado', 'Presentar resultados en una pantalla', 'Guardar archivos en una carpeta', 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos elementos es un sistema operativo?', 'Linux', 'Una memoria RAM', 'Un cable USB', 0
    UNION ALL SELECT 'hardware-y-software', '¿Qué es un controlador o driver?', 'Una pieza que enfría la CPU', 'Software que ayuda al sistema a comunicarse con un dispositivo', 'Un tipo de archivo de imagen', 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál de estos dispositivos puede ser de entrada y de salida?', 'Un teclado', 'Un micrófono', 'Una pantalla táctil', 2
    UNION ALL SELECT 'hardware-y-software', '¿Qué diferencia un archivo de una aplicación?', 'La aplicación es un programa; el archivo guarda información', 'El archivo siempre es hardware', 'La aplicación solo puede ser una carpeta', 0
    UNION ALL SELECT 'hardware-y-software', '¿Qué combinación se necesita para imprimir un documento?', 'Solo una carpeta', 'Impresora y programa o controlador', 'Solo un teclado', 1
    UNION ALL SELECT 'hardware-y-software', '¿Cuál es un ejemplo de hardware interno?', 'Un navegador', 'Un documento de texto', 'La memoria RAM', 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué es una ruta de archivo?', 'La ubicación de un archivo a través de sus carpetas', 'El nombre de la persona que lo creó', 'El tamaño de la pantalla', 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué conviene hacer antes de borrar un archivo?', 'Borrar todos los archivos de la carpeta', 'Comprobar que sea el archivo correcto y que ya no se necesite', 'Cambiarle la extensión al azar', 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Para qué sirve una copia de seguridad?', 'Para cambiar el color de un documento', 'Para hacer que la computadora sea más rápida siempre', 'Para recuperar archivos si se pierde o daña el original', 2
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué debes revisar al compartir un documento?', 'Quién puede verlo o editarlo', 'Cuántas teclas tiene el teclado', 'El brillo de la pantalla', 0
    UNION ALL SELECT 'fundamentos-digitales', '¿Cuál es un ejemplo de dato?', 'Una silla del salón', 'Una fecha guardada en una lista', 'El cable de corriente', 1
    UNION ALL SELECT 'fundamentos-digitales', '¿Qué ayuda a distinguir una carpeta de un archivo?', 'La carpeta siempre tiene extensión .jpg', 'El archivo contiene otras carpetas siempre', 'La carpeta organiza elementos y el archivo guarda información', 2
    UNION ALL SELECT 'seguridad-digital', '¿Cuál de estos puede ser un dato personal?', 'Tu ubicación', 'El color de una ventana del programa', 'El nombre de una pieza de hardware', 0
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer si una página inesperada te pide tu contraseña?', 'Escribirla rápido para cerrar el aviso', 'No ingresarla y verificar la solicitud por un canal confiable', 'Publicarla para preguntar si es segura', 1
    UNION ALL SELECT 'seguridad-digital', '¿Para qué sirve revisar los permisos de una aplicación?', 'Para elegir el fondo de pantalla', 'Para borrar automáticamente todos tus mensajes', 'Para saber si necesita acceso a cámara, micrófono u otros datos', 2
    UNION ALL SELECT 'seguridad-digital', '¿Qué hábito es recomendable en una computadora compartida?', 'Cerrar sesión al terminar', 'Dejar la cuenta abierta', 'Guardar la contraseña en un lugar público', 0
    UNION ALL SELECT 'seguridad-digital', '¿Qué debes hacer si algo que ocurre en línea te preocupa?', 'Responder con datos personales', 'Pedir ayuda a una persona adulta de confianza', 'Compartirlo con desconocidos', 1
    UNION ALL SELECT 'seguridad-digital', '¿Por qué conviene instalar actualizaciones autorizadas?', 'Para cambiar el nombre de usuario', 'Para que todos vean tus archivos', 'Pueden corregir problemas y mejorar la protección', 2
    UNION ALL SELECT 'seguridad-digital', '¿Qué es un dato personal?', 'Información que puede identificarte, como tu dirección o teléfono', 'Una pieza física de la computadora', 'Una instrucción para imprimir texto', 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué significa descomponer un problema?', 'Separarlo en partes más pequeñas', 'Cambiar el objetivo por uno distinto', 'Resolverlo sin revisar información', 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué información es más útil para resolver un problema?', 'Todos los datos aunque no tengan relación', 'Los datos relacionados con el objetivo', 'Solo el dato más reciente', 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué es una condición en una solución?', 'Una lista de colores', 'Una pieza de la computadora', 'Una regla que permite elegir según una situación', 2
    UNION ALL SELECT 'pensamiento-logico', '¿Cómo puedes comprobar si tu solución funciona?', 'Probar los pasos con ejemplos y revisar el resultado', 'Cambiar todos los pasos sin probar', 'Evitar definir el objetivo', 0
    UNION ALL SELECT 'pensamiento-logico', '¿Qué es la abstracción?', 'Repetir una tarea sin pensar', 'Enfocarse en los detalles importantes para resolver una tarea', 'Guardar información en una carpeta', 1
    UNION ALL SELECT 'pensamiento-logico', '¿Qué conviene hacer si encuentras un error?', 'Ignorarlo y continuar', 'Reiniciar el problema desde cero siempre', 'Revisar los pasos y cambiar una parte a la vez', 2
    UNION ALL SELECT 'algoritmos', '¿Qué se considera una entrada de un algoritmo?', 'Un dato que el algoritmo recibe para resolver una tarea', 'El resultado que aparece al final', 'Una figura del diagrama de flujo', 0
    UNION ALL SELECT 'algoritmos', '¿Qué significa que un algoritmo sea finito?', 'Que solo funciona con computadoras nuevas', 'Que termina después de una cantidad limitada de pasos', 'Que no tiene instrucciones', 1
    UNION ALL SELECT 'algoritmos', '¿Para qué sirve una repetición dentro de un algoritmo?', 'Para cambiar el nombre del archivo', 'Para evitar que el algoritmo termine', 'Para realizar varias veces una acción según una regla', 2
    UNION ALL SELECT 'algoritmos', '¿Qué es una salida de un algoritmo?', 'El resultado que produce al procesar sus entradas', 'Un dato que se escribe antes de empezar', 'Una instrucción que no se ejecuta', 0
    UNION ALL SELECT 'algoritmos', '¿Qué ayuda a encontrar un error en una secuencia de pasos?', 'Una imagen de fondo', 'Una tabla de seguimiento de los datos paso a paso', 'Cambiar el nombre del algoritmo', 1
    UNION ALL SELECT 'algoritmos', '¿Qué debes hacer si una instrucción puede entenderse de dos maneras?', 'Dejar que cada persona adivine', 'Eliminar todas las instrucciones', 'Aclararla para que se pueda seguir sin adivinar', 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué figura suele representar el inicio o el final?', 'Un óvalo', 'Un rombo', 'Una flecha', 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué debe indicar cada camino que sale de una decisión?', 'El nombre del autor', 'Qué hacer ante cada posible respuesta, por ejemplo sí o no', 'El color del papel', 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué instrucción de pseudocódigo puede recibir un dato?', 'MOSTRAR', 'FIN', 'LEER', 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ventaja tiene recorrer un diagrama con datos de ejemplo?', 'Permite comprobar el camino y resultado sin ejecutar un programa', 'Cambia los símbolos automáticamente', 'Hace innecesario ordenar los pasos', 0
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué ayuda a que un diagrama sea fácil de leer?', 'Usar flechas sin conectarlas', 'Conectar los símbolos claramente y evitar cruces innecesarios', 'Colocar todos los pasos en una sola figura', 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', '¿Qué palabra puede indicar una salida en pseudocódigo?', 'LEER', 'INICIO', 'MOSTRAR', 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué tipo de dato representa el valor 12?', 'Un número entero', 'Una imagen', 'Una condición', 0
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué hace input(\"¿Cómo te llamas?\") en Python?', 'Imprime el nombre sin preguntar', 'Muestra una pregunta y permite escribir un dato', 'Cierra el programa', 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué indica una condición SI en un programa?', 'Que se borren todas las variables', 'Que siempre se muestre el mismo mensaje', 'Que se ejecuten instrucciones si una expresión es verdadera', 2
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué es un error de sintaxis?', 'Un código que no respeta las reglas del lenguaje', 'Un resultado correcto', 'Una variable que guarda un número', 0
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Para qué sirve la indentación en Python?', 'Para elegir el color de una ventana', 'Para indicar qué instrucciones pertenecen a una condición', 'Para guardar los archivos', 1
    UNION ALL SELECT 'fundamentos-de-programacion', '¿Qué es un error lógico?', 'Un error que siempre impide abrir la computadora', 'Un mensaje de bienvenida', 'El programa corre, pero el resultado no es el esperado', 2
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué tipo de dato representa el valor 12?', 'Un número entero', 'Una imagen', 'Una condición', 0
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué hace input(\"¿Cómo te llamas?\") en Python?', 'Imprime el nombre sin preguntar', 'Muestra una pregunta y permite escribir un dato', 'Cierra el programa', 1
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué indica una condición SI en un programa?', 'Que se borren todas las variables', 'Que siempre se muestre el mismo mensaje', 'Que se ejecuten instrucciones si una expresión es verdadera', 2
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué es un error de sintaxis?', 'Un código que no respeta las reglas del lenguaje', 'Un resultado correcto', 'Una variable que guarda un número', 0
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Para qué sirve la indentación en Python?', 'Para elegir el color de una ventana', 'Para indicar qué instrucciones pertenecen a una condición', 'Para guardar los archivos', 1
    UNION ALL SELECT 'fundamentos-de-programacion2', '¿Qué es un error lógico?', 'Un error que siempre impide abrir la computadora', 'Un mensaje de bienvenida', 'El programa corre, pero el resultado no es el esperado', 2
) AS seed
JOIN modules m ON m.slug = seed.slug AND m.archived_at IS NULL
JOIN activities a ON a.module_id = m.id AND a.type = 'quiz'
    AND a.title = CASE
        WHEN m.slug = 'fundamentos-de-programacion2' THEN 'Repaso: Fundamentos de programación'
        ELSE CONCAT('Repaso: ', m.title)
    END
JOIN questions q ON q.activity_id = a.id AND q.prompt = seed.prompt
CROSS JOIN (
    SELECT 0 AS sort_order
    UNION ALL SELECT 1
    UNION ALL SELECT 2
) AS positions
WHERE NOT EXISTS (
    SELECT 1 FROM answer_options existing
    WHERE existing.question_id = q.id AND existing.sort_order = positions.sort_order
);

INSERT INTO exercise_items (activity_id, prompt, choices, correct_answer, sort_order)
SELECT a.id, seed.prompt, seed.choices, seed.correct_answer, seed.sort_order
FROM (
    SELECT 'conoce-tu-computadora' AS slug, 'classify' AS type, 'Teclado' AS prompt, JSON_ARRAY('Hardware', 'Software') AS choices, 'Hardware' AS correct_answer, 1 AS sort_order
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Navegador web', JSON_ARRAY('Hardware', 'Software'), 'Software', 2
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Memoria RAM', JSON_ARRAY('Hardware', 'Software'), 'Hardware', 3
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Sistema operativo', JSON_ARRAY('Hardware', 'Software'), 'Software', 4
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Monitor', JSON_ARRAY('Hardware', 'Software'), 'Hardware', 5
    UNION ALL SELECT 'conoce-tu-computadora', 'classify', 'Aplicación de dibujo', JSON_ARRAY('Hardware', 'Software'), 'Software', 6
    UNION ALL SELECT 'hardware-y-software', 'identify', '¿Qué componente ejecuta instrucciones y realiza cálculos?', JSON_ARRAY('CPU', 'Ratón', 'Monitor'), 'CPU', 1
    UNION ALL SELECT 'hardware-y-software', 'identify', '¿Dónde se conservan archivos aunque apagues el equipo?', JSON_ARRAY('Unidad SSD', 'Memoria RAM', 'Ventilador'), 'Unidad SSD', 2
    UNION ALL SELECT 'hardware-y-software', 'identify', '¿Cuál de estos elementos puedes tocar?', JSON_ARRAY('Ratón', 'Navegador web', 'Editor de texto'), 'Ratón', 3
    UNION ALL SELECT 'fundamentos-digitales', 'classify', 'Una fotografía guardada con formato JPG', JSON_ARRAY('Archivo', 'Carpeta', 'Programa'), 'Archivo', 1
    UNION ALL SELECT 'fundamentos-digitales', 'classify', 'Una carpeta llamada Tareas', JSON_ARRAY('Archivo', 'Carpeta', 'Programa'), 'Carpeta', 2
    UNION ALL SELECT 'fundamentos-digitales', 'classify', 'Una aplicación de hojas de cálculo', JSON_ARRAY('Archivo', 'Carpeta', 'Programa'), 'Programa', 3
    UNION ALL SELECT 'fundamentos-digitales', 'classify', 'Un documento llamado notas.txt', JSON_ARRAY('Archivo', 'Carpeta', 'Programa'), 'Archivo', 4
    UNION ALL SELECT 'seguridad-digital', 'classify', 'Activar la verificación en dos pasos', JSON_ARRAY('Seguro', 'Riesgoso'), 'Seguro', 1
    UNION ALL SELECT 'seguridad-digital', 'classify', 'Compartir tu contraseña con otra persona', JSON_ARRAY('Seguro', 'Riesgoso'), 'Riesgoso', 2
    UNION ALL SELECT 'seguridad-digital', 'classify', 'Abrir un enlace urgente de alguien desconocido', JSON_ARRAY('Seguro', 'Riesgoso'), 'Riesgoso', 3
    UNION ALL SELECT 'seguridad-digital', 'classify', 'Usar una contraseña larga y diferente para cada cuenta', JSON_ARRAY('Seguro', 'Riesgoso'), 'Seguro', 4
    UNION ALL SELECT 'pensamiento-logico', 'identify', 'Antes de resolver una tarea, ¿qué debes aclarar?', JSON_ARRAY('El objetivo', 'El color de la pantalla', 'El nombre del archivo'), 'El objetivo', 1
    UNION ALL SELECT 'pensamiento-logico', 'identify', 'Si el resultado no funciona, ¿qué paso sigue?', JSON_ARRAY('Revisar y ajustar los pasos', 'Borrar todo sin revisar', 'Ignorar el resultado'), 'Revisar y ajustar los pasos', 2
    UNION ALL SELECT 'pensamiento-logico', 'identify', '¿Qué ayuda a resolver una tarea grande?', JSON_ARRAY('Dividirla en pasos pequeños', 'Hacer todos los pasos a la vez', 'No definir el resultado'), 'Dividirla en pasos pequeños', 3
    UNION ALL SELECT 'algoritmos', 'order', 'Definir el problema', JSON_ARRAY(), '', 1
    UNION ALL SELECT 'algoritmos', 'order', 'Identificar las entradas necesarias', JSON_ARRAY(), '', 2
    UNION ALL SELECT 'algoritmos', 'order', 'Ordenar los pasos de la solución', JSON_ARRAY(), '', 3
    UNION ALL SELECT 'algoritmos', 'order', 'Probar el resultado y corregirlo', JSON_ARRAY(), '', 4
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'identify', '¿Qué símbolo suele indicar inicio o fin?', JSON_ARRAY('Óvalo', 'Rombo', 'Rectángulo'), 'Óvalo', 1
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'identify', '¿Qué símbolo suele representar una decisión?', JSON_ARRAY('Rombo', 'Óvalo', 'Flecha'), 'Rombo', 2
    UNION ALL SELECT 'diagramas-y-pseudocodigo', 'identify', '¿Qué símbolo suele representar una acción o proceso?', JSON_ARRAY('Rectángulo', 'Rombo', 'Óvalo'), 'Rectángulo', 3
    UNION ALL SELECT 'fundamentos-de-programacion', 'identify', '¿Qué instrucción de Python muestra un mensaje en pantalla?', JSON_ARRAY('print(\"Hola\")', 'input(\"Hola\")', 'variable = \"Hola\"'), 'print(\"Hola\")', 1
    UNION ALL SELECT 'fundamentos-de-programacion', 'identify', '¿Qué es una variable en un programa?', JSON_ARRAY('Un nombre que guarda un dato', 'Una pieza física del monitor', 'Un tipo de carpeta'), 'Un nombre que guarda un dato', 2
    UNION ALL SELECT 'fundamentos-de-programacion', 'identify', '¿Qué estructura permite elegir entre alternativas?', JSON_ARRAY('Una condición', 'Un archivo JPG', 'Una memoria USB'), 'Una condición', 3
) AS seed
JOIN modules m ON m.slug = seed.slug
JOIN activities a ON a.module_id = m.id AND a.type = seed.type AND a.title = CASE m.slug
    WHEN 'conoce-tu-computadora' THEN 'Reto: Clasifica hardware y software'
    WHEN 'hardware-y-software' THEN 'Reto: Identifica los componentes'
    WHEN 'fundamentos-digitales' THEN 'Reto: Organiza la información'
    WHEN 'seguridad-digital' THEN 'Reto: Decisiones seguras'
    WHEN 'pensamiento-logico' THEN 'Reto: Piensa paso a paso'
    WHEN 'algoritmos' THEN 'Reto: Ordena un algoritmo'
    WHEN 'diagramas-y-pseudocodigo' THEN 'Reto: Lee un diagrama'
    WHEN 'fundamentos-de-programacion' THEN 'Reto: Predice el programa'
END
WHERE NOT EXISTS (
    SELECT 1 FROM exercise_items existing
    WHERE existing.activity_id = a.id AND existing.prompt = seed.prompt
);

UPDATE activities a
SET is_published = 1
WHERE a.is_published = 0
  AND EXISTS (
      SELECT 1 FROM modules m
      WHERE m.id = a.module_id
        AND m.slug IN (
            'conoce-tu-computadora', 'hardware-y-software', 'fundamentos-digitales',
            'seguridad-digital', 'pensamiento-logico', 'algoritmos',
            'diagramas-y-pseudocodigo', 'fundamentos-de-programacion'
        )
        AND (
            a.title = CONCAT('Repaso: ', m.title)
            OR a.title = CASE m.slug
                WHEN 'conoce-tu-computadora' THEN 'Reto: Clasifica hardware y software'
                WHEN 'hardware-y-software' THEN 'Reto: Identifica los componentes'
                WHEN 'fundamentos-digitales' THEN 'Reto: Organiza la información'
                WHEN 'seguridad-digital' THEN 'Reto: Decisiones seguras'
                WHEN 'pensamiento-logico' THEN 'Reto: Piensa paso a paso'
                WHEN 'algoritmos' THEN 'Reto: Ordena un algoritmo'
                WHEN 'diagramas-y-pseudocodigo' THEN 'Reto: Lee un diagrama'
                WHEN 'fundamentos-de-programacion' THEN 'Reto: Predice el programa'
            END
        )
  )
  AND (
    (a.type = 'quiz' AND EXISTS (SELECT 1 FROM questions q WHERE q.activity_id = a.id)
        AND NOT EXISTS (
            SELECT 1 FROM questions q
            WHERE q.activity_id = a.id
              AND (SELECT COUNT(*) FROM answer_options o WHERE o.question_id = q.id AND o.is_correct = 1) <> 1
        ))
    OR (a.type <> 'quiz' AND EXISTS (SELECT 1 FROM exercise_items i WHERE i.activity_id = a.id))
);

UPDATE modules m
SET is_published = 1
WHERE m.archived_at IS NULL
  AND m.slug IN (
      'conoce-tu-computadora', 'hardware-y-software', 'fundamentos-digitales',
      'seguridad-digital', 'pensamiento-logico', 'algoritmos',
      'diagramas-y-pseudocodigo', 'fundamentos-de-programacion'
  )
  AND EXISTS (SELECT 1 FROM lessons l WHERE l.module_id = m.id AND l.is_published = 1);

-- Preguntas tomadas de los dos cuestionarios ya diseñados.
INSERT INTO evaluation_questions
    (evaluation_type, field_key, prompt, input_type, choices, correct_answer, is_scored, is_required, sort_order, is_published)
VALUES
    ('diagnostic', 'q1', '¿Para qué sirve la CPU?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','procesar','label','Procesar instrucciones'),JSON_OBJECT('value','guardar','label','Guardar archivos'),JSON_OBJECT('value','ns','label','No lo sé')),
     'procesar', 1, 1, 1, 1),
    ('diagnostic', 'q2', '¿Cuál es un ejemplo de software?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','teclado','label','Teclado'),JSON_OBJECT('value','navegador','label','Navegador web'),JSON_OBJECT('value','ns','label','No lo sé')),
     'navegador', 1, 1, 2, 1),
    ('diagnostic', 'q3', '¿Qué es un algoritmo?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','pasos','label','Una lista de pasos ordenada'),JSON_OBJECT('value','pantalla','label','Un tipo de pantalla física'),JSON_OBJECT('value','ns','label','No lo sé')),
     'pasos', 1, 1, 3, 1),
    ('diagnostic', 'q4', '¿Qué componente almacena temporalmente los datos de programas en ejecución?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','ram','label','Memoria RAM'),JSON_OBJECT('value','disco','label','Disco duro'),JSON_OBJECT('value','ns','label','No lo sé')),
     'ram', 1, 1, 4, 1),
    ('post', 'p1', '¿Para qué sirve la CPU?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','procesar','label','Procesar instrucciones'),JSON_OBJECT('value','guardar','label','Guardar archivos permanentemente'),JSON_OBJECT('value','nsnc','label','No lo sé')),
     'procesar', 1, 1, 1, 1),
    ('post', 'p2', '¿Qué es un algoritmo?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','pasos','label','Una lista de pasos ordenada para resolver un problema'),JSON_OBJECT('value','pantalla','label','Un tipo de pantalla de computadora'),JSON_OBJECT('value','nsnc','label','No lo sé')),
     'pasos', 1, 1, 2, 1),
    ('post', 'p3', '¿Qué diferencia al software del hardware?', 'choice',
     JSON_ARRAY(JSON_OBJECT('value','logico_fisico','label','El software son los programas lógicos y el hardware los componentes físicos'),JSON_OBJECT('value','ambos_mecanicos','label','Ambos son partes de metal dentro del gabinete'),JSON_OBJECT('value','nsnc','label','No lo sé')),
     'logico_fisico', 1, 1, 3, 1),
    ('post', 'likert_facilidad', '¿Qué tan fácil fue usar la plataforma?', 'rating',
     JSON_ARRAY(JSON_OBJECT('value','1','label','1'),JSON_OBJECT('value','2','label','2'),JSON_OBJECT('value','3','label','3'),JSON_OBJECT('value','4','label','4'),JSON_OBJECT('value','5','label','5')),
     NULL, 0, 1, 4, 1),
    ('post', 'likert_claridad', '¿Qué tan claras fueron las explicaciones didácticas?', 'rating',
     JSON_ARRAY(JSON_OBJECT('value','1','label','1'),JSON_OBJECT('value','2','label','2'),JSON_OBJECT('value','3','label','3'),JSON_OBJECT('value','4','label','4'),JSON_OBJECT('value','5','label','5')),
     NULL, 0, 1, 5, 1),
    ('post', 'comentarios', '¿Qué fue lo que más te gustó de los retos de programación? (Opcional)', 'text',
     JSON_ARRAY(), NULL, 0, 0, 6, 1)
ON DUPLICATE KEY UPDATE prompt = VALUES(prompt), choices = VALUES(choices), sort_order = VALUES(sort_order);
