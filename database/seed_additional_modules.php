<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';

$modulos = [
    [
        'slug' => 'introduccion-a-java',
        'title' => 'Introducción a Java',
        'description' => 'Conoce Java, escribe tus primeras instrucciones y crea programas pequeños con variables, decisiones y repeticiones.',
        'objectives' => 'Explicar qué son Java y la JVM; reconocer la estructura básica de una clase y el método main; usar variables, tipos, salida, decisiones y ciclos sencillos.',
        'difficulty' => 'inicial',
        'minutes' => 40,
        'lessons' => [
            [
                'Primeros pasos con Java',
                'Java es un lenguaje de programación que se utiliza para crear muchos tipos de aplicaciones. Un programa escrito en Java se traduce a instrucciones que puede ejecutar la máquina virtual de Java, conocida como JVM. La JVM ayuda a que el mismo programa pueda funcionar en distintos sistemas que tengan una versión compatible.\n\nEl código fuente se escribe en archivos con extensión .java. En un programa sencillo, la clase puede tener el mismo nombre que el archivo. Por ejemplo, la clase HolaMundo normalmente se guarda en HolaMundo.java. Java distingue entre letras mayúsculas y minúsculas, así que HolaMundo y holamundo son nombres diferentes.\n\nUna estructura inicial se ve así:\n\npublic class HolaMundo {\n    public static void main(String[] args) {\n        System.out.println("¡Hola!");\n    }\n}\n\nLa clase agrupa el código. El método main es el punto de entrada: allí comienza la ejecución del programa. Las llaves delimitan bloques y los paréntesis agrupan información que recibe una instrucción. La línea System.out.println muestra un mensaje y termina escribiendo una nueva línea. Muchas instrucciones de Java terminan con punto y coma.\n\nLas palabras public, class y static tienen significados establecidos por el lenguaje y no se pueden usar como nombres arbitrarios en ese contexto. Los comentarios, que empiezan con // para una sola línea, permiten explicar una decisión sin cambiar lo que ejecuta el programa.\n\nAl ejecutar un programa, primero se compila el archivo y luego se inicia la clase correspondiente. Si aparece un error, revisa el nombre del archivo, las mayúsculas, las llaves, las comillas y los punto y coma. Leer el mensaje con calma es parte normal de programar. Empieza con mensajes pequeños, cambia una cosa a la vez y observa el resultado.',
                12,
            ],
            [
                'Datos, decisiones y repeticiones',
                'Los programas necesitan guardar datos. En Java, una variable tiene un nombre y un tipo que indica qué clase de valor puede contener. int sirve para números enteros, double para números con decimales, boolean para verdadero o falso y String para texto. Por ejemplo: int puntos = 10; String equipo = "Tortugas";. El texto se escribe entre comillas dobles.\n\nUna variable se declara con su tipo antes de utilizarla. El signo = asigna un valor; no significa una pregunta de igualdad. Para comparar valores se usa == en expresiones sencillas, mientras que para comparar texto normalmente se utiliza equals. El operador + puede sumar números o unir textos, según los tipos de los datos.\n\nUna condición permite elegir qué hacer. Por ejemplo, if (puntos >= 10) puede mostrar un mensaje si se alcanzó la meta. else indica qué hacer cuando la condición no se cumple. Las llaves reúnen las instrucciones que pertenecen a cada camino.\n\nUn ciclo repite una acción. Un for es útil cuando se conoce cuántas repeticiones se necesitan; un while continúa mientras su condición sea verdadera. Cada ciclo debe avanzar hacia una condición de salida. Si no cambia nada que permita detenerlo, puede repetirse indefinidamente.\n\nPara diseñar un programa de puntos, define primero las reglas: comienza con cero, suma un punto por respuesta correcta y muestra el total al terminar. Después identifica las variables, las decisiones y las repeticiones. Prueba con cero preguntas, una respuesta y varias respuestas para revisar distintos casos.\n\nJava comprueba muchos errores de tipo durante la compilación. Aun así, el programa puede tener errores de lógica, como sumar puntos en la respuesta equivocada. Escribe una expectativa, ejecuta una prueba pequeña y compara el resultado. No necesitas memorizarlo todo: comprender cada paso y practicar ayuda a mejorar.',
                14,
            ],
        ],
        'questions' => [
            ['¿Qué es Java?', 'Java es un lenguaje de programación que permite escribir instrucciones para crear programas.', ['Un lenguaje de programación', 'Una pieza física de la computadora', 'Un tipo de carpeta'], 0],
            ['¿Qué hace la JVM?', 'La máquina virtual de Java ejecuta programas Java en entornos compatibles.', ['Ayuda a ejecutar programas Java', 'Dibuja el teclado', 'Guarda el papel de una impresora'], 0],
            ['¿Qué extensión suele tener un archivo de código Java?', 'Los archivos de código fuente Java usan la extensión .java.', ['.java', '.mp3', '.jpg'], 0],
            ['¿Qué método es el punto de entrada habitual de un programa Java?', 'En un programa sencillo, la ejecución comienza en el método main.', ['main', 'color', 'carpeta'], 0],
            ['¿Qué instrucción muestra texto en la consola?', 'System.out.println muestra texto y termina la línea.', ['System.out.println', 'int.println', 'class.output'], 0],
            ['¿Con qué signo terminan muchas instrucciones Java?', 'Muchas instrucciones Java terminan con punto y coma.', ['Punto y coma (;)', 'Signo de interrogación (?)', 'Dos puntos (:)'], 0],
            ['¿Qué tipo usarías para guardar un número entero de puntos?', 'int es un tipo habitual para números enteros.', ['int', 'String', 'boolean'], 0],
            ['¿Qué tipo representa texto en Java?', 'String se utiliza para guardar una secuencia de caracteres.', ['String', 'double', 'int'], 0],
            ['¿Qué estructura permite elegir entre dos caminos?', 'if y else permiten ejecutar instrucciones distintas según una condición.', ['if / else', 'import / package', 'class / file'], 0],
            ['¿Qué debes comprobar si el compilador encuentra un error sencillo?', 'Revisar nombres, mayúsculas, llaves, comillas y punto y coma ayuda a localizar errores.', ['Las llaves y los punto y coma', 'Solo el color de la pantalla', 'El nombre de la carpeta de imágenes'], 0],
        ],
        'exercise_type' => 'identify',
        'exercise_title' => 'Reto: Exploradores de Java',
        'exercise_description' => 'Identifica conceptos y partes básicas de un programa Java.',
        'exercises' => [
            ['¿Qué palabra declara una clase?', ['class', 'repeat', 'screen'], 'class'],
            ['¿Qué tipo guardarías para contar monedas enteras?', ['int', 'boolean', 'String'], 'int'],
            ['¿Qué instrucción muestra “Hola” en consola?', ['System.out.println("Hola");', 'int Hola;', 'if ("Hola")'], 'System.out.println("Hola");'],
            ['¿Qué estructura permite repetir instrucciones mientras se cumple una condición?', ['while', 'String', 'import'], 'while'],
            ['¿Qué tipo puede guardar verdadero o falso?', ['boolean', 'double', 'class'], 'boolean'],
        ],
    ],
    [
        'slug' => 'introduccion-a-python',
        'title' => 'Introducción a Python',
        'description' => 'Aprende a escribir tus primeros programas con Python, usando datos, decisiones, ciclos y funciones.',
        'objectives' => 'Reconocer la sintaxis básica de Python; crear variables y expresiones; mostrar y recibir información; construir decisiones, repeticiones y funciones sencillas.',
        'difficulty' => 'inicial',
        'minutes' => 40,
        'lessons' => [
            [
                'Conoce Python y escribe instrucciones',
                'Python es un lenguaje de programación conocido por una sintaxis legible. Se utiliza para automatizar tareas, explorar datos, crear herramientas y aprender conceptos de programación. Para ejecutar código necesitas un intérprete de Python; puedes escribir instrucciones en un archivo .py o probarlas en un entorno interactivo autorizado.\n\nLa instrucción print() muestra información. Por ejemplo, print("Hola, mundo") presenta un texto en la pantalla. Los textos se escriben entre comillas; los números pueden escribirse sin ellas. Los paréntesis indican qué información recibe la función.\n\nUna variable guarda un dato para utilizarlo después. Con nombre = "Luna", la variable nombre guarda texto; con puntos = 8, puntos guarda un número entero. En Python no hace falta escribir por separado el tipo de cada variable, pero conviene elegir nombres claros que expliquen su propósito.\n\nLos tipos comunes incluyen int para enteros, float para números decimales, str para texto y bool para verdadero o falso. El signo = asigna un valor. Las operaciones +, -, * y / permiten sumar, restar, multiplicar y dividir. Si se usa + con dos textos, se pueden unir.\n\nLa instrucción input() permite pedir texto a la persona. Por ejemplo, respuesta = input("¿Cuál es tu color favorito? "). El resultado de input es texto, incluso si la persona escribe dígitos. Para hacer una operación numérica puedes convertirlo con int() o float(), comprobando que el valor tenga un formato válido.\n\nPython utiliza la indentación, es decir, los espacios al inicio de las líneas, para mostrar qué instrucciones pertenecen a un bloque. Mantén la misma sangría dentro de una decisión o una función. Los comentarios de una línea comienzan con # y sirven para explicar el código sin ejecutarse.\n\nEscribe un programa pequeño, ejecútalo y modifica un mensaje. Si algo falla, revisa comillas, paréntesis, nombres e indentación. Probar paso a paso ayuda a descubrir qué hace cada instrucción.',
                12,
            ],
            [
                'Decisiones, ciclos y funciones',
                'Una condición ayuda a que un programa elija qué hacer. En Python, if comprueba una expresión y ejecuta el bloque indentado cuando es verdadera. elif permite revisar otra condición y else indica qué hacer si ninguna de las anteriores se cumple. Los operadores ==, !=, < y > comparan valores; and y or combinan condiciones.\n\nPor ejemplo, si puntos >= 10, el programa puede mostrar “¡Meta alcanzada!”. Recuerda que = guarda un valor y == compara dos valores. Confundirlos es un error común. Prueba tanto el caso verdadero como el falso para asegurarte de que los dos caminos tengan sentido.\n\nLos ciclos repiten instrucciones. for recorre una secuencia o un rango conocido; range(5) produce cinco posiciones, empezando en cero. while repite mientras una condición siga siendo verdadera. En un while, modifica los datos de forma que algún día la condición deje de cumplirse.\n\nUna función agrupa instrucciones para reutilizarlas. Se define con def, un nombre y paréntesis. Puede recibir información mediante parámetros y devolver un resultado usando return. Por ejemplo, una función que suma dos números puede recibir a y b y devolver a + b. Los nombres descriptivos facilitan entender para qué sirve.\n\nAl diseñar un programa de adivinanzas, separa el objetivo en pasos: guardar una respuesta secreta, pedir un intento, comparar el intento y mostrar una pista. El programa puede repetir la pregunta con un ciclo y detenerse cuando la respuesta sea correcta o se alcance un límite.\n\nLos errores de sintaxis ocurren cuando las instrucciones no siguen las reglas, como olvidar los dos puntos después de if. Los errores de ejecución aparecen mientras corre el programa. Los errores lógicos producen un resultado inesperado aunque el código se ejecute.\n\nPrueba con datos variados: texto vacío, el valor mínimo, uno mayor y una respuesta inesperada. Si el programa lee números desde input(), convierte el texto con cuidado. Es normal corregir varias veces: cambiar una parte, volver a ejecutar y observar permite entender cómo mejora la solución.',
                14,
            ],
        ],
        'questions' => [
            ['¿Qué función muestra información en Python?', 'print() presenta texto u otros datos en la salida.', ['print()', 'input()', 'range()'], 0],
            ['¿Qué función recibe texto escrito por la persona?', 'input() solicita información y devuelve el valor como texto.', ['input()', 'print()', 'len()'], 0],
            ['¿Qué extensión suelen usar los archivos Python?', 'Los archivos de código Python suelen terminar en .py.', ['.py', '.java', '.png'], 0],
            ['¿Qué tipo de dato representa texto?', 'str representa cadenas de texto en Python.', ['str', 'int', 'bool'], 0],
            ['¿Qué símbolo comienza un comentario de una línea?', 'En Python, # inicia un comentario de una línea.', ['#', '&&', '$$'], 0],
            ['¿Qué indica la indentación en Python?', 'La sangría muestra qué instrucciones pertenecen al mismo bloque.', ['Los bloques de instrucciones', 'El tamaño del archivo', 'La velocidad del teclado'], 0],
            ['¿Qué palabra inicia una condición?', 'if permite ejecutar instrucciones cuando una condición es verdadera.', ['if', 'def', 'import'], 0],
            ['¿Qué operador compara si dos valores son iguales?', '== compara igualdad; = se utiliza para asignar valores.', ['==', '=', '=>'], 0],
            ['¿Qué palabra permite definir una función?', 'def inicia la definición de una función en Python.', ['def', 'classroom', 'show'], 0],
            ['¿Para qué se usa return dentro de una función?', 'return entrega un resultado al código que llamó a la función.', ['Para devolver un resultado', 'Para cambiar el color', 'Para repetir siempre'], 0],
        ],
        'exercise_type' => 'identify',
        'exercise_title' => 'Reto: Detectives de Python',
        'exercise_description' => 'Completa conceptos y reconoce instrucciones de Python.',
        'exercises' => [
            ['¿Qué muestra un mensaje en pantalla?', ['print("Hola")', 'input("Hola")', 'range("Hola")'], 'print("Hola")'],
            ['¿Qué palabra inicia una decisión?', ['if', 'return', 'print'], 'if'],
            ['¿Qué tipo corresponde al número entero 7?', ['int', 'str', 'bool'], 'int'],
            ['¿Qué palabra define una función?', ['def', 'while', 'input'], 'def'],
            ['¿Qué símbolo se usa para comenzar un comentario?', ['#', '==', '()'], '#'],
        ],
    ],
    [
        'slug' => 'introduccion-a-la-inteligencia-artificial',
        'title' => 'Introducción a la inteligencia artificial',
        'description' => 'Descubre qué es la inteligencia artificial, cómo aprende de ejemplos y por qué las personas deben revisar sus resultados.',
        'objectives' => 'Describir la IA como tecnología creada por personas; diferenciar datos, modelos y predicciones; reconocer usos y límites; usar herramientas con privacidad y pensamiento crítico.',
        'difficulty' => 'inicial',
        'minutes' => 40,
        'lessons' => [
            [
                '¿Qué es la inteligencia artificial?',
                'La inteligencia artificial, o IA, es un conjunto de técnicas con las que una computadora realiza tareas que asociamos con capacidades humanas, como reconocer imágenes, encontrar patrones, traducir texto o generar contenido. Eso no significa que la máquina piense, sienta o comprenda el mundo como una persona. Es tecnología diseñada, entrenada y utilizada por seres humanos.\n\nUn sistema de IA suele procesar datos mediante un modelo. Los datos pueden ser ejemplos de texto, sonidos, imágenes u otros registros. El modelo contiene reglas y parámetros que se ajustan para encontrar patrones. Después puede usar esos patrones para clasificar algo, calcular una probabilidad o generar una respuesta.\n\nEn el aprendizaje automático, las personas proporcionan ejemplos y una medida para comparar predicciones con resultados esperados. Durante el entrenamiento, el sistema ajusta sus parámetros para reducir algunos errores. Luego se prueba con ejemplos que no utilizó durante ese ajuste para revisar si puede responder a casos nuevos.\n\nUna predicción no es una certeza. Si un sistema recibe una fotografía, puede calcular qué objeto parece más probable entre las categorías disponibles. La iluminación, el ángulo y la variedad de ejemplos pueden afectar el resultado. Una persona debe revisar la predicción antes de tomar decisiones importantes.\n\nLa IA generativa produce contenido nuevo, como texto, imágenes, audio o código, a partir de instrucciones y patrones aprendidos. Puede ayudar a explorar ideas o explicar un tema, pero también puede inventar datos, citar fuentes inexistentes o reflejar errores de sus ejemplos. Que una respuesta suene segura no demuestra que sea correcta.\n\nLas herramientas de IA tienen distintos objetivos y capacidades. Un filtro de correo, un sistema que recomienda música y un asistente conversacional no hacen exactamente lo mismo. Para entender una herramienta, pregunta qué tarea realiza, qué información necesita, quién la diseñó y cómo comprobarás su resultado.\n\nLa IA es una herramienta, no un sustituto automático del criterio humano. Aprender sus conceptos básicos permite aprovecharla con curiosidad, reconocer sus límites y pedir orientación cuando algo no es claro.',
                12,
            ],
            [
                'Datos, entrenamiento y resultados responsables',
                'Para construir un sistema de aprendizaje automático se recopilan ejemplos apropiados, se prepara la información y se define qué resultado se desea estimar. Los ejemplos deben representar razonablemente los casos en que se utilizará el sistema. Si faltan grupos, situaciones o perspectivas, sus resultados pueden funcionar peor para algunas personas.\n\nEn el entrenamiento, el modelo ajusta sus parámetros con ejemplos. La validación ayuda a comparar alternativas durante el desarrollo. Una prueba final con datos que se mantuvieron aparte da una idea más honesta de cómo se comporta con ejemplos nuevos. Usar repetidamente los mismos datos para enseñar y evaluar puede ocultar errores.\n\nLos datos con errores, etiquetas confusas o sesgos pueden influir en el resultado. Por ejemplo, un clasificador entrenado con fotos tomadas casi siempre con la misma luz podría equivocarse en un entorno distinto. Revisar la calidad de los ejemplos y probar distintos casos ayuda a encontrar límites, aunque ninguna prueba garantiza que un sistema jamás se equivoque.\n\nLa privacidad también importa. No compartas contraseñas, direcciones, información médica, documentos de identidad ni datos privados de otras personas en una herramienta sin autorización. Aprende qué información guarda el servicio, quién puede verla y qué reglas establecen tu familia o escuela. En caso de duda, usa ejemplos inventados y pregunta a una persona adulta de confianza.\n\nUna respuesta de IA debe verificarse. Contrasta afirmaciones con libros, materiales de clase, fuentes confiables o especialistas. Comprueba fechas, operaciones, citas y explicaciones; pide una aclaración si algo parece contradictorio. En tareas escolares, sigue las reglas de tu docente y explica con honestidad qué ayuda utilizaste.\n\nLas personas que diseñan, compran y usan sistemas de IA son responsables de considerar efectos y corregir problemas. Conviene preguntar quién se beneficia, quién podría resultar perjudicado, qué datos se usan y cómo solicitar una revisión humana. Estas preguntas ayudan a usar la tecnología de forma segura, justa y transparente.',
                14,
            ],
        ],
        'questions' => [
            ['¿Qué describe mejor la inteligencia artificial?', 'La IA reúne técnicas informáticas creadas por personas para realizar tareas específicas.', ['Tecnología diseñada para realizar tareas con datos y modelos', 'Una máquina que siente como una persona', 'Un dispositivo que siempre tiene razón'], 0],
            ['¿Qué es un dato para un sistema de IA?', 'Los datos son ejemplos o información que el sistema procesa.', ['Información o ejemplos que procesa', 'La contraseña del usuario', 'Una garantía de que la respuesta es cierta'], 0],
            ['¿Qué es un modelo de aprendizaje automático?', 'Un modelo ajusta patrones a partir de ejemplos para producir resultados.', ['Un sistema que ajusta patrones a partir de datos', 'Un archivo que siempre contiene respuestas perfectas', 'Una persona que etiqueta una pantalla'], 0],
            ['¿Qué significa que una IA haga una predicción?', 'Una predicción es una estimación; puede acertar o equivocarse.', ['Estima un resultado posible', 'Garantiza un hecho futuro', 'Comprende todas las situaciones'], 0],
            ['¿Qué puede hacer una IA generativa?', 'La IA generativa produce contenido, pero puede incluir errores.', ['Generar texto, imágenes u otros contenidos', 'Garantizar que todo dato sea verdadero', 'Sentir emociones humanas'], 0],
            ['¿Por qué se prueban modelos con ejemplos separados del entrenamiento?', 'Los ejemplos separados ayudan a comprobar el desempeño en datos que no se usaron para ajustar el modelo.', ['Para revisar cómo funciona con ejemplos nuevos', 'Para evitar revisar resultados', 'Para convertir todas las predicciones en certezas'], 0],
            ['¿Qué es un sesgo en los resultados de IA?', 'Los datos o decisiones de diseño pueden favorecer ciertos resultados y perjudicar otros.', ['Una tendencia sistemática que puede afectar resultados', 'Una contraseña difícil', 'Un método para apagar una computadora'], 0],
            ['¿Qué debes hacer antes de confiar en una respuesta de IA?', 'Revisar información importante con fuentes confiables reduce el riesgo de aceptar errores.', ['Verificarla con fuentes confiables', 'Compartirla sin leer', 'Suponer que siempre es correcta'], 0],
            ['¿Qué información no debes compartir en una herramienta sin autorización?', 'Las contraseñas y los datos privados deben mantenerse protegidos.', ['Contraseñas y datos personales privados', 'Un ejemplo inventado', 'Una pregunta general de clase'], 0],
            ['¿Quién es responsable de revisar el uso y los efectos de una IA?', 'Las personas que diseñan y utilizan herramientas deben considerar sus resultados y consecuencias.', ['Las personas que la diseñan y la utilizan', 'Solo la computadora', 'Nadie, porque es automática'], 0],
        ],
        'exercise_type' => 'classify',
        'exercise_title' => 'Reto: ¿Qué sabemos de la IA?',
        'exercise_description' => 'Distingue hechos, predicciones y prácticas responsables.',
        'exercises' => [
            ['Una aplicación reconoce plantas a partir de fotos. ¿Qué procesa?', ['Datos e imágenes', 'Emociones humanas', 'Una garantía de acierto'], 'Datos e imágenes'],
            ['La IA estima que una foto contiene un gato. ¿Qué es esa respuesta?', ['Una predicción', 'Una certeza absoluta', 'Una contraseña'], 'Una predicción'],
            ['Una herramienta inventa una cita. ¿Qué conviene hacer?', ['Verificarla en una fuente confiable', 'Copiarla sin revisar', 'Compartirla como verdadera'], 'Verificarla en una fuente confiable'],
            ['¿Qué dato debes mantener privado?', ['Tu contraseña', 'Un ejemplo ficticio', 'Una pregunta sobre planetas'], 'Tu contraseña'],
            ['Un conjunto de fotos no representa muchos tipos de plantas. ¿Qué riesgo existe?', ['Resultados menos confiables para casos distintos', 'Que el sistema sienta emociones', 'Que todas las respuestas sean perfectas'], 'Resultados menos confiables para casos distintos'],
        ],
    ],
    [
        'slug' => 'el-mundo-con-la-ia',
        'title' => 'El mundo con la IA',
        'description' => 'Explora cómo se usa la IA en la vida cotidiana, la ciencia y el trabajo, y piensa en sus beneficios, riesgos y efectos.',
        'objectives' => 'Identificar aplicaciones de IA en distintos sectores; valorar beneficios y limitaciones; reconocer impactos en las personas; proponer prácticas seguras, justas y sostenibles.',
        'difficulty' => 'intermedio',
        'minutes' => 40,
        'lessons' => [
            [
                'La IA a nuestro alrededor',
                'La inteligencia artificial ya aparece en muchas herramientas. Un servicio de mapas puede estimar el tráfico; una aplicación de fotos puede agrupar imágenes parecidas; una plataforma puede recomendar música según interacciones anteriores. Estas funciones reconocen patrones y calculan resultados, pero no conocen las preferencias de una persona con la profundidad de una amistad.\n\nEn la salud, algunas herramientas ayudan a organizar información o señalar imágenes que un profesional debe revisar. No sustituyen el diagnóstico de personal médico. En agricultura, modelos pueden ayudar a observar cambios en cultivos y planear el riego. En ciencia, sirven para explorar grandes colecciones de datos. En educación, una aplicación puede ofrecer ejercicios adaptados, mientras que el docente acompaña, explica y comprende las necesidades del grupo.\n\nEn ciudades y transporte, los modelos pueden estimar flujos de vehículos y ayudar a planear rutas. En accesibilidad, el reconocimiento de voz, los subtítulos automáticos y la conversión de texto a voz pueden apoyar a personas con distintas necesidades. La utilidad depende de que la herramienta funcione bien para quienes la necesitan y respete su privacidad.\n\nLos sistemas de recomendación seleccionan qué contenido mostrar usando señales como clics, tiempo de reproducción o temas elegidos. Esto puede ahorrar tiempo, pero también puede encerrar a una persona en opciones parecidas. Buscar distintas fuentes y decidir conscientemente qué consumir ayuda a mantener la curiosidad.\n\nNo toda automatización usa IA: un temporizador puede seguir reglas fijas sin aprender patrones. Para identificar IA, pregunta si la herramienta analiza datos, reconoce patrones, estima resultados o genera contenido. También pregunta qué tarea concreta realiza y en qué situaciones podría equivocarse.\n\nToda herramienta debe evaluarse según su propósito. Un error en la sugerencia de una canción tiene consecuencias distintas de un error en salud o seguridad. Mientras más importante sea la decisión, más necesario es contar con revisión humana, pruebas cuidadosas y explicaciones claras.',
                12,
            ],
            [
                'Beneficios, límites e impacto social',
                'La IA puede ayudar a encontrar patrones en grandes cantidades de información, reducir tareas repetitivas y crear nuevas formas de comunicación. Estos beneficios no ocurren por sí solos: alguien elige el objetivo, los datos, el diseño y las personas que podrán utilizar la herramienta. También se necesita evaluar si realmente resuelve el problema y para quién.\n\nUn sistema puede cometer errores porque los datos son incompletos, las situaciones cambian o el modelo no fue preparado para un caso particular. Si una herramienta participa en decisiones sobre personas, un resultado no debería tratarse como una verdad incuestionable. Las personas afectadas necesitan una explicación apropiada y la posibilidad de solicitar revisión humana.\n\nLa privacidad requiere atención. Una aplicación podría recoger más información de la necesaria o guardarla por más tiempo del esperado. Antes de utilizarla, revisa qué solicita, qué opciones de privacidad ofrece y qué normas aplican. En escuela, trabajo o familia, pide autorización antes de compartir datos de otras personas.\n\nLa equidad también importa. Un sistema entrenado con ejemplos limitados puede funcionar distinto para grupos distintos. Probarlo con situaciones variadas y escuchar a quienes lo utilizan ayuda a detectar problemas. La transparencia sobre lo que una herramienta puede y no puede hacer evita promesas exageradas.\n\nLa IA utiliza computadoras, redes y energía. Crear y operar sistemas requiere recursos materiales y electricidad, por lo que también vale la pena considerar consumo, duración de los equipos y beneficios obtenidos. Una buena solución busca el equilibrio entre utilidad, seguridad, equidad y cuidado del entorno.\n\nComo usuario, puedes preguntar: ¿qué objetivo tiene?, ¿qué información usa?, ¿quién revisa sus errores?, ¿qué pasa con mis datos?, ¿a quién podría afectar? Usa la IA como apoyo, confirma información importante y conversa con una persona adulta o experta cuando una decisión tenga consecuencias importantes. La responsabilidad es compartida y siempre incluye a las personas.',
                14,
            ],
        ],
        'questions' => [
            ['¿Qué puede estimar una aplicación de mapas?', 'Los datos de rutas pueden usarse para estimar tráfico y proponer recorridos.', ['El tráfico o una ruta posible', 'El destino que una persona siente sin datos', 'Un camino que jamás cambia'], 0],
            ['¿Qué papel tiene la IA en algunas herramientas de salud?', 'Puede apoyar a organizar datos o señalar imágenes para revisión profesional, no sustituir al personal médico.', ['Puede apoyar el análisis para revisión profesional', 'Reemplaza siempre al personal médico', 'Garantiza todos los diagnósticos'], 0],
            ['¿Cómo pueden ayudar los subtítulos automáticos?', 'Los subtítulos pueden apoyar la accesibilidad, aunque es importante revisar su precisión.', ['Facilitan el acceso a contenido hablado', 'Eliminan la necesidad de escuchar a las personas', 'Siempre transcriben sin errores'], 0],
            ['¿Qué utiliza un sistema de recomendación?', 'Las recomendaciones pueden depender de interacciones como clics o temas seleccionados.', ['Señales de uso para sugerir contenido', 'Solo la temperatura del dispositivo', 'Una lectura de pensamientos'], 0],
            ['¿Un temporizador sencillo necesariamente usa IA?', 'Un temporizador puede seguir reglas fijas sin aprender patrones.', ['No; puede seguir reglas fijas', 'Sí; todos los programas son IA', 'Solo si tiene una pantalla'], 0],
            ['¿Por qué una decisión importante requiere revisión humana?', 'Los modelos pueden equivocarse y las decisiones pueden afectar de forma significativa a las personas.', ['Porque los modelos tienen límites y las decisiones afectan a personas', 'Porque la IA siempre siente preocupación', 'Para impedir toda tecnología'], 0],
            ['¿Qué riesgo puede surgir de datos poco variados?', 'Los sistemas pueden responder peor para situaciones o grupos menos representados.', ['Resultados menos adecuados para algunos casos', 'Resultados infalibles', 'Que los datos dejen de existir'], 0],
            ['¿Qué conviene revisar antes de usar una aplicación nueva?', 'Las solicitudes de datos y opciones de privacidad ayudan a entender cómo se tratará la información.', ['Qué datos solicita y sus opciones de privacidad', 'Solo el color del icono', 'Si tiene muchos botones'], 0],
            ['¿Por qué considerar el impacto ambiental de la IA?', 'Operar sistemas requiere equipos, energía y otros recursos que conviene evaluar.', ['Porque los sistemas usan recursos y energía', 'Porque la IA solo funciona con papel', 'Porque ninguna computadora consume energía'], 0],
            ['¿Qué pregunta ayuda a evaluar responsablemente una herramienta?', 'Preguntar por su objetivo, datos, límites y revisión ayuda a tomar decisiones informadas.', ['¿Qué datos usa y cómo se revisan sus errores?', '¿Cómo hago para nunca comprobarla?', '¿Puede decidir todo sin personas?'], 0],
        ],
        'exercise_type' => 'classify',
        'exercise_title' => 'Reto: IA en la vida diaria',
        'exercise_description' => 'Relaciona usos comunes de IA con sus beneficios y límites.',
        'exercises' => [
            ['Una app propone una ruta con menos tráfico. ¿Qué uso de IA es?', ['Apoyo para estimar rutas', 'Diagnóstico médico definitivo', 'Una contraseña'], 'Apoyo para estimar rutas'],
            ['Una plataforma recomienda canciones. ¿Qué debes recordar?', ['La recomendación se basa en señales y puede limitar opciones', 'Conoce todos tus gustos', 'No usa información alguna'], 'La recomendación se basa en señales y puede limitar opciones'],
            ['Un subtítulo automático tiene errores. ¿Qué haces?', ['Lo revisas y corriges si es importante', 'Lo consideras perfecto siempre', 'Compartes datos personales'], 'Lo revisas y corriges si es importante'],
            ['Un sistema influye en una decisión de alto impacto. ¿Qué es importante?', ['Revisión humana y explicación', 'Aceptar todo sin preguntar', 'Ocultar cómo funciona'], 'Revisión humana y explicación'],
            ['Una aplicación pide datos que no necesita. ¿Qué haces?', ['Consultas sus opciones de privacidad y pides orientación', 'Compartes más información', 'Usas la contraseña de otra persona'], 'Consultas sus opciones de privacidad y pides orientación'],
        ],
    ],
    [
        'slug' => 'prompts-en-la-inteligencia-artificial',
        'title' => 'Prompts en la inteligencia artificial',
        'description' => 'Aprende a redactar instrucciones claras para la IA, mejorar respuestas y verificar resultados con seguridad.',
        'objectives' => 'Definir un prompt; aportar objetivo, contexto y formato; mejorar instrucciones mediante iteración; verificar resultados; proteger la privacidad y respetar las reglas de uso.',
        'difficulty' => 'inicial',
        'minutes' => 40,
        'lessons' => [
            [
                'Cómo escribir instrucciones claras',
                'Un prompt es la instrucción o pregunta que se entrega a una herramienta de inteligencia artificial. Un prompt claro comunica qué necesitas. Por ejemplo, “Explícame los planetas” es una pregunta amplia; “Explícame qué es una órbita en tres frases para alguien que está aprendiendo ciencias” indica tema, extensión y audiencia.\n\nPuedes organizar un prompt con cuatro partes: objetivo, contexto, restricciones y formato. El objetivo explica qué tarea necesitas. El contexto agrega información útil que no sea privada. Las restricciones indican límites, como “usa palabras sencillas” o “no incluyas una respuesta inventada”. El formato indica cómo presentar el resultado, por ejemplo una lista numerada o una tabla pequeña.\n\nUna buena instrucción no necesita ser larga. Incluye solo los detalles relevantes y separa tareas diferentes. Si solicitas una historia, puedes indicar quién es el personaje, el lugar, la edad de la audiencia y el tono. Si pides una explicación escolar, puedes decir qué conceptos ya conoces y pedir un ejemplo paso a paso.\n\nLa IA puede interpretar una solicitud de forma distinta a la esperada. Si el resultado no ayuda, revisa qué faltó: quizá no señalaste el nivel, el idioma o la extensión. Luego modifica un detalle y vuelve a preguntar. Esta mejora gradual se llama iterar. Mantén el propósito original y compara si la respuesta se acerca más a lo que necesitas.\n\nPide al sistema que admita cuando no sepa algo, pero recuerda que esa petición no garantiza exactitud. No incluyas tu nombre completo si no hace falta, contraseñas, dirección, escuela, información médica ni datos privados de otras personas. Para practicar, inventa nombres y situaciones ficticias.\n\nUn prompt sirve para orientar una herramienta, no para obligarla a responder correctamente. Lee el resultado, comprueba los datos y sigue las reglas de tu clase o familia. Si vas a usar la respuesta en una tarea, aprende de la explicación y sé claro sobre la ayuda que recibiste.',
                12,
            ],
            [
                'Mejora, verifica y usa tus prompts con responsabilidad',
                'Para mejorar un prompt, empieza con la tarea principal y agrega información que cambie realmente la respuesta. Puedes especificar audiencia, alcance, estilo, cantidad de pasos o formato. Por ejemplo: “Propón tres experimentos seguros sobre plantas para hacer en casa, sin fuego ni productos químicos, usando materiales comunes; explica qué observar”. La instrucción establece límites y una forma de respuesta útil.\n\nSi la respuesta resulta confusa, pide una aclaración concreta: “Explica la palabra evaporación con un ejemplo de la vida diaria”. Si contiene demasiado texto, pide un resumen. Si necesitas aprender, solicita pistas antes de pedir la solución completa. Comprueba que cada cambio de prompt ayude al objetivo.\n\nNo uses la IA para producir datos sobre una persona real sin permiso ni para compartir información privada. Si un ejercicio requiere datos, reemplázalos por ejemplos ficticios. Evita introducir documentos, contraseñas, números de teléfono, direcciones o imágenes privadas. Revisa las condiciones y reglas del servicio; algunas herramientas pueden guardar conversaciones.\n\nLas respuestas generadas pueden contener información incorrecta, parcial o inventada. Verifica hechos con libros, sitios institucionales o fuentes indicadas por tu docente. Comprueba operaciones por tu cuenta y abre las fuentes mencionadas para asegurarte de que existan y digan lo que la respuesta afirma. Pide apoyo a una persona adulta cuando el tema sea delicado o importante.\n\nLa IA puede ayudar a hacer una lluvia de ideas, explicar un concepto de otra manera o proponer preguntas de práctica. No debe reemplazar tu comprensión ni presentarse como trabajo propio cuando las reglas piden hacerlo sin ayuda. Sigue las instrucciones de tu escuela y reconoce el uso de herramientas cuando corresponda.\n\nUn proceso seguro tiene cinco pasos: define lo que quieres aprender, escribe una instrucción específica, excluye datos privados, revisa y verifica la respuesta, y decide qué utilizar siguiendo las reglas. Guarda solo lo necesario y cierra la herramienta al terminar si estás usando un dispositivo compartido.\n\nAl practicar, evalúa cada respuesta con preguntas: ¿responde a mi objetivo?, ¿es comprensible?, ¿puedo confirmar sus datos?, ¿respeta a las personas y su privacidad? Si la respuesta falla, ajusta la instrucción o usa otra fuente. Tú sigues siendo responsable de las decisiones y del contenido que compartes.',
                14,
            ],
        ],
        'questions' => [
            ['¿Qué es un prompt?', 'Un prompt es la instrucción o pregunta que se proporciona a una herramienta de IA.', ['Una instrucción para una herramienta de IA', 'Una pieza interna de la computadora', 'Una contraseña universal'], 0],
            ['¿Qué detalle mejora la instrucción “Explícame los planetas”?', 'Indicar la audiencia o el nivel ayuda a adaptar la explicación.', ['Para quién y con qué nivel explicar', 'La dirección de tu casa', 'Tu contraseña escolar'], 0],
            ['¿Qué parte de un prompt especifica cómo organizar la respuesta?', 'El formato indica si quieres una lista, tabla, resumen u otra presentación.', ['El formato', 'La contraseña', 'El fondo de pantalla'], 0],
            ['Si una respuesta es demasiado larga, ¿qué puedes pedir?', 'Una petición concreta de resumen puede ayudar a ajustar la respuesta.', ['Un resumen más breve', 'Que ignore la pregunta', 'Que muestre datos privados'], 0],
            ['¿Qué significa iterar un prompt?', 'Iterar es revisar y modificar la instrucción para acercar la respuesta al objetivo.', ['Mejorar una instrucción mediante cambios y nuevas pruebas', 'Compartir la contraseña', 'Aceptar siempre la primera respuesta'], 0],
            ['¿Qué no debes incluir en un prompt sin autorización?', 'Los datos personales privados deben mantenerse fuera de prompts innecesarios.', ['Tu dirección o contraseña', 'Un ejemplo inventado', 'Una pregunta general'], 0],
            ['¿Pedir a la IA que no se equivoque garantiza que será correcta?', 'La petición no asegura exactitud: los resultados todavía necesitan verificarse.', ['No; hay que verificar los datos', 'Sí, siempre', 'Solo si la respuesta es larga'], 0],
            ['¿Cómo puedes comprobar una afirmación importante de una respuesta?', 'Contrastar con fuentes confiables permite revisar los hechos y referencias.', ['Consultar fuentes confiables', 'Confiar únicamente en el tono seguro', 'Copiarla sin leer'], 0],
            ['¿Qué puedes pedir si quieres aprender sin recibir toda la solución?', 'Pedir pistas ayuda a practicar el razonamiento en lugar de copiar una respuesta completa.', ['Pistas paso a paso', 'La contraseña de otra persona', 'Una respuesta sin explicación siempre'], 0],
            ['¿Qué debes hacer con las reglas de tu escuela sobre IA?', 'Es importante seguir las reglas e indicar el uso de herramientas cuando corresponda.', ['Seguirlas y reconocer la ayuda cuando corresponda', 'Ignorarlas', 'Compartir datos de compañeros'], 0],
        ],
        'exercise_type' => 'identify',
        'exercise_title' => 'Reto: Diseñadores de prompts',
        'exercise_description' => 'Elige instrucciones claras, útiles y seguras.',
        'exercises' => [
            ['¿Cuál prompt explica mejor lo que necesitas?', ['Resume el ciclo del agua en cinco pasos para primaria', 'Dime todo', 'Adivina lo que quiero'], 'Resume el ciclo del agua en cinco pasos para primaria'],
            ['¿Qué contexto es seguro para practicar?', ['Un personaje ficticio llamado Sol', 'La contraseña de tu correo', 'La dirección de otra persona'], 'Un personaje ficticio llamado Sol'],
            ['Una respuesta es confusa. ¿Qué prompt ayuda?', ['Explica la palabra con un ejemplo cotidiano', 'Haz cualquier cosa', 'Usa mis datos personales'], 'Explica la palabra con un ejemplo cotidiano'],
            ['La respuesta incluye una cita sorprendente. ¿Qué haces?', ['Verificas que la fuente exista y confirme la cita', 'La compartes sin revisar', 'La presentas como propia'], 'Verificas que la fuente exista y confirme la cita'],
            ['¿Qué instrucción pone un límite claro a una actividad?', ['Propón un experimento seguro sin fuego ni químicos', 'Haz algo interesante', 'Incluye información privada'], 'Propón un experimento seguro sin fuego ni químicos'],
        ],
    ],
];

$db = obtenerConexion();
$db->beginTransaction();

try {
    $insertarModulo = $db->prepare(
        'INSERT INTO modules (slug, title, description, objectives, difficulty, estimated_minutes, sort_order, is_published)
         VALUES (:slug, :title, :description, :objectives, :difficulty, :minutes, :sort_order, 1)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description),
            objectives = VALUES(objectives), difficulty = VALUES(difficulty),
            estimated_minutes = VALUES(estimated_minutes), sort_order = VALUES(sort_order),
            is_published = 1, archived_at = NULL'
    );
    $buscarModulo = $db->prepare('SELECT id FROM modules WHERE slug = :slug');
    $buscarLeccion = $db->prepare('SELECT id FROM lessons WHERE module_id = :module_id AND title = :title LIMIT 1');
    $insertarLeccion = $db->prepare(
        'INSERT INTO lessons (module_id, title, content, estimated_minutes, sort_order, is_published)
         VALUES (:module_id, :title, :content, :minutes, :sort_order, 1)'
    );
    $buscarActividad = $db->prepare('SELECT id FROM activities WHERE module_id = :module_id AND type = :type AND title = :title LIMIT 1');
    $insertarActividad = $db->prepare(
        'INSERT INTO activities (module_id, lesson_id, type, title, description, max_attempts, passing_score, is_published)
         VALUES (:module_id, :lesson_id, :type, :title, :description, NULL, 70, 1)'
    );
    $buscarPregunta = $db->prepare('SELECT id FROM questions WHERE activity_id = :activity_id AND prompt = :prompt LIMIT 1');
    $insertarPregunta = $db->prepare(
        'INSERT INTO questions (activity_id, prompt, explanation, sort_order) VALUES (:activity_id, :prompt, :explanation, :sort_order)'
    );
    $buscarOpcion = $db->prepare('SELECT id FROM answer_options WHERE question_id = :question_id AND sort_order = :sort_order');
    $insertarOpcion = $db->prepare(
        'INSERT INTO answer_options (question_id, label, is_correct, sort_order)
         VALUES (:question_id, :label, :is_correct, :sort_order)
         ON DUPLICATE KEY UPDATE label = VALUES(label), is_correct = VALUES(is_correct)'
    );
    $buscarEjercicio = $db->prepare('SELECT id FROM exercise_items WHERE activity_id = :activity_id AND prompt = :prompt LIMIT 1');
    $insertarEjercicio = $db->prepare(
        'INSERT INTO exercise_items (activity_id, prompt, choices, correct_answer, sort_order)
         VALUES (:activity_id, :prompt, :choices, :correct_answer, :sort_order)'
    );

    foreach ($modulos as $indice => $modulo) {
        $insertarModulo->execute([
            'slug' => $modulo['slug'],
            'title' => $modulo['title'],
            'description' => $modulo['description'],
            'objectives' => $modulo['objectives'],
            'difficulty' => $modulo['difficulty'],
            'minutes' => $modulo['minutes'],
            'sort_order' => 9 + $indice,
        ]);
        $buscarModulo->execute(['slug' => $modulo['slug']]);
        $moduleId = (int) $buscarModulo->fetchColumn();

        $lessonIds = [];
        foreach ($modulo['lessons'] as $orden => [$lessonTitle, $content, $minutes]) {
            $buscarLeccion->execute(['module_id' => $moduleId, 'title' => $lessonTitle]);
            $lessonId = $buscarLeccion->fetchColumn();
            if (!$lessonId) {
                $insertarLeccion->execute([
                    'module_id' => $moduleId,
                    'title' => $lessonTitle,
                    'content' => str_replace('\\n', "\n", $content),
                    'minutes' => $minutes,
                    'sort_order' => $orden + 1,
                ]);
                $lessonId = $db->lastInsertId();
            }
            $lessonIds[] = (int) $lessonId;
        }

        $quizTitle = 'Repaso: ' . $modulo['title'];
        $buscarActividad->execute(['module_id' => $moduleId, 'type' => 'quiz', 'title' => $quizTitle]);
        $quizId = $buscarActividad->fetchColumn();
        if (!$quizId) {
            $insertarActividad->execute([
                'module_id' => $moduleId,
                'lesson_id' => $lessonIds[0],
                'type' => 'quiz',
                'title' => $quizTitle,
                'description' => 'Comprueba lo aprendido en las lecciones de este módulo.',
            ]);
            $quizId = $db->lastInsertId();
        }
        $quizId = (int) $quizId;

        foreach ($modulo['questions'] as $orden => [$prompt, $explanation, $choices, $correctIndex]) {
            $buscarPregunta->execute(['activity_id' => $quizId, 'prompt' => $prompt]);
            $questionId = $buscarPregunta->fetchColumn();
            if (!$questionId) {
                $insertarPregunta->execute([
                    'activity_id' => $quizId,
                    'prompt' => $prompt,
                    'explanation' => $explanation,
                    'sort_order' => $orden + 1,
                ]);
                $questionId = $db->lastInsertId();
            }
            $questionId = (int) $questionId;
            $rotation = $orden % count($choices);
            $orderedChoices = array_merge(array_slice($choices, $rotation), array_slice($choices, 0, $rotation));
            $orderedCorrectIndex = ($correctIndex - $rotation + count($choices)) % count($choices);
            foreach ($orderedChoices as $choiceOrder => $label) {
                $insertarOpcion->execute([
                    'question_id' => $questionId,
                    'label' => $label,
                    'is_correct' => $choiceOrder === $orderedCorrectIndex ? 1 : 0,
                    'sort_order' => $choiceOrder,
                ]);
            }
        }

        $exerciseTitle = $modulo['exercise_title'];
        $buscarActividad->execute([
            'module_id' => $moduleId,
            'type' => $modulo['exercise_type'],
            'title' => $exerciseTitle,
        ]);
        $exerciseId = $buscarActividad->fetchColumn();
        if (!$exerciseId) {
            $insertarActividad->execute([
                'module_id' => $moduleId,
                'lesson_id' => $lessonIds[1],
                'type' => $modulo['exercise_type'],
                'title' => $exerciseTitle,
                'description' => $modulo['exercise_description'],
            ]);
            $exerciseId = $db->lastInsertId();
        }
        $exerciseId = (int) $exerciseId;

        foreach ($modulo['exercises'] as $orden => [$prompt, $choices, $answer]) {
            $buscarEjercicio->execute(['activity_id' => $exerciseId, 'prompt' => $prompt]);
            if (!$buscarEjercicio->fetchColumn()) {
                $insertarEjercicio->execute([
                    'activity_id' => $exerciseId,
                    'prompt' => $prompt,
                    'choices' => json_encode($choices, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'correct_answer' => $answer,
                    'sort_order' => $orden + 1,
                ]);
            }
        }
    }

    $db->commit();
    echo "Se publicaron cinco módulos nuevos con diez lecciones, cincuenta preguntas y veinticinco retos.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'No se pudieron cargar los módulos: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
