<?php
declare(strict_types=1);

class ServiceIA
{
    private string $apiKey;

    public function __construct()
    {
        if (function_exists('cargarVariablesEntorno')) {
            cargarVariablesEntorno();
        }
        $this->apiKey = trim((string) (getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '')));

        // Respaldo: lee el archivo .env en la raíz del proyecto si no se cargó en $_ENV
        if ($this->apiKey === '' && is_file(__DIR__ . '/../.env')) {
            $lineas = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lineas as $linea) {
                if (str_starts_with(trim($linea), 'GEMINI_API_KEY=')) {
                    $valor = trim(substr(trim($linea), strlen('GEMINI_API_KEY=')));
                    $this->apiKey = trim($valor, "\"'");
                    break;
                }
            }
        }
    }

    public static function obtenerPerfilPorEdad(int $edad): array
    {
        if ($edad >= 6 && $edad <= 9) {
            return [
                'tema_css' => 'tema-infantil',
                'nombre' => 'LIPAL Jr.',
                'subtitulo' => '¡Tu amigo de aventuras!',
                'avatar' => 'assets/images/lipal_jr.png',
                'activar_voz' => true,
                'system_prompt' => 'Eres LIPAL, una mascota amigable para niños de 6 a 9 años. Usa vocabulario muy simple, frases cortas, analogías cotidianas y refuerzo positivo.',
            ];
        }

        if ($edad >= 10 && $edad <= 13) {
            return [
                'tema_css' => 'tema-gamer',
                'nombre' => 'LIPAL Quest',
                'subtitulo' => 'Guía de misiones y retos',
                'avatar' => 'assets/images/lipal_quest.png',
                'activar_voz' => false,
                'system_prompt' => 'Eres LIPAL, un compañero estilo videojuego para estudiantes de 10 a 13 años. Usa tono cercano, explicaciones paso a paso y ejemplos visuales.',
            ];
        }

        return [
            'tema_css' => 'tema-academico',
            'nombre' => 'LIPAL Tutor',
            'subtitulo' => 'Tu tutor de aprendizaje',
            'avatar' => 'assets/images/lipal_tutor.png',
            'activar_voz' => false,
            'system_prompt' => 'Eres LIPAL, un tutor académico moderno para jóvenes de 14 a 18 años. Usa lenguaje claro, enfoque socrático y pensamiento crítico.',
        ];
    }

    /**
     * Genera respuesta según el modo: 'pista', 'consejo' o 'chat'.
     */
    public function interactuar(
        string $modo,
        int $edad,
        string $mensajeUsuario = '',
        array $contexto = []
    ): array {
        // Al inicio de interactuar(), antes de llamar a consultarGemini():
    $claveCache = md5($modo . '|' . $edad . '|' . mb_strtolower($mensajeUsuario) . '|' . ($contexto['titulo'] ?? ''));
    if (isset($_SESSION['cache_ia'][$claveCache])) {
        return $_SESSION['cache_ia'][$claveCache];
    }
        $perfil = self::obtenerPerfilPorEdad($edad);

        $reglasPorModo = match ($modo) {
            'pista' => "Modo: PISTA DE EJERCICIO.\n"
                . "- Da una pista conceptual breve (máximo 3 oraciones) en español.\n"
                . "- PROHIBIDO dar la respuesta directa o decir qué elemento va en cada categoría.\n"
                . "- Ayuda al estudiante a razonar por sí mismo.",
            'consejo' => "Modo: CONSEJO DE ESTUDIO.\n"
                . "- Da un consejo práctico y motivador (2 a 3 oraciones) sobre cómo abordar el tema actual o mejorar su hábito de estudio.",
            default => "Modo: CHAT TUTOR.\n"
                . "- Responde dudas de informática y lógica computacional de forma clara y breve (máximo 4 oraciones).\n"
                . "- Si pregunta por la respuesta de un ejercicio, guíalo con preguntas sin resolverlo por él.",
        };

        $infoContexto = '';
        if (!empty($contexto['titulo'])) {
            $infoContexto .= "Actividad/Tema: " . $contexto['titulo'] . "\n";
        }
        if (!empty($contexto['descripcion'])) {
            $infoContexto .= "Contenido de la pantalla:\n" . $contexto['descripcion'] . "\n";
        }
        if (!empty($contexto['items']) && is_array($contexto['items'])) {
            $lista = array_map(static fn ($i): string => '- ' . ($i['prompt'] ?? ''), $contexto['items']);
            $infoContexto .= "Elementos del reto:\n" . implode("\n", $lista) . "\n";
        }

        // Agrega la memoria de los últimos mensajes de la sesión
        $textoHistorial = '';
        if (!empty($contexto['historial']) && is_array($contexto['historial'])) {
            $lineas = [];
            foreach ($contexto['historial'] as $turno) {
                $quien = ($turno['rol'] ?? '') === 'lipal' ? $perfil['nombre'] : 'Estudiante';
                $lineas[] = $quien . ': ' . ($turno['texto'] ?? '');
            }
            $textoHistorial = "Conversación previa:\n" . implode("\n", $lineas) . "\n\n";
        }

        $instruccionInteractiva = $modo !== 'pista'
            ? "\n- Termina tu respuesta con una pregunta breve e interesante para que el estudiante participe (por ejemplo, pidiéndole que dé un ejemplo o elija una opción)."
            : '';

        $promptCompleto = $perfil['system_prompt'] . "\n\n"
            . $reglasPorModo . $instruccionInteractiva . "\n\n"
            . ($infoContexto !== '' ? "Contexto actual:\n" . $infoContexto . "\n" : '')
            . $textoHistorial
            . ($mensajeUsuario !== '' ? "Mensaje actual del estudiante: " . $mensajeUsuario : 'Genera la intervención solicitada.');
        try {
            $texto = $this->consultarGemini($promptCompleto);
            return [
                'ok' => true,
                'texto' => $texto,
                'sugerencias' => $this->generarSugerencias($modo, $contexto),
                'origen' => 'ia',
                'mascota' => $perfil,
            ];
        } catch (Throwable $e) {
            error_log('[ServiceIA Fallback] ' . $e->getMessage());
            return [
                'ok' => true,
                'texto' => $this->generarRespaldo($modo, $contexto),
                'sugerencias' => $this->generarSugerencias($modo, $contexto),
                'origen' => 'respaldo',
                'mascota' => $perfil,
            ];
        }
    }

    private array $modelosFallback = [
        'gemini-3.1-flash-lite',
        'gemini-2.5-flash',
    ];

    private function consultarGemini(string $prompt): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Falta GEMINI_API_KEY.');
        }

        $ultimoError = 'Sin respuesta de los modelos.';

        foreach ($this->modelosFallback as $modelo) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . urlencode($this->apiKey);
            $payload = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ], JSON_UNESCAPED_UNICODE);
            $esLocal = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1'], true);

                    $ch = curl_init($url);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_CONNECTTIMEOUT => 5,
                        CURLOPT_TIMEOUT => 15,
                        CURLOPT_SSL_VERIFYPEER => !$esLocal,
                        CURLOPT_SSL_VERIFYHOST => $esLocal ? 0 : 2,
                    ]);

            $respuesta = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($respuesta !== false && $httpCode === 200) {
                $data = json_decode($respuesta, true);
                $texto = trim((string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
                if ($texto !== '') {
                    return $texto;
                }
            }

            $ultimoError = "Modelo {$modelo} falló (HTTP {$httpCode}" . ($curlError !== '' ? " - {$curlError}" : '') . ')';
        }

        throw new RuntimeException($ultimoError);
    }

    private function generarRespaldo(string $modo, array $contexto): string
    {
        $titulo = trim((string) ($contexto['titulo'] ?? ''));
        $descripcion = trim((string) ($contexto['descripcion'] ?? ''));

        if ($modo === 'pista') {
            if (str_contains(mb_strtolower($titulo, 'UTF-8'), 'hardware') && str_contains(mb_strtolower($titulo, 'UTF-8'), 'software')) {
                return 'Piensa en la diferencia entre lo físico y lo lógico: el hardware es lo que puedes tocar, mientras que el software son los programas e instrucciones que hacen funcionar al equipo.';
            }
            return $descripcion !== ''
                ? 'Revisa la consigna: "' . $descripcion . '". Analiza la función principal de cada elemento antes de ubicarlo.'
                : 'Observa cada elemento con calma, identifica su función principal y compáralo con las categorías disponibles.';
        }

        if ($modo === 'consejo') {
            $tema = $titulo !== '' ? " sobre «{$titulo}»" : '';
            return "Consejo de estudio{$tema}: lee cada párrafo buscando ideas clave y resúmelo con tus propias palabras. ¿Qué concepto de esta sección te gustaría repasar primero?";
        }

        // En modo chat, usa el tema y resumen de la lección actual si existen
        if ($titulo !== '' && $descripcion !== '') {
            $resumen = mb_substr(preg_replace('/\s+/', ' ', $descripcion), 0, 220, 'UTF-8');
            return "Sobre «{$titulo}»: {$resumen}...\n\n¿Quieres que te ponga un ejemplo práctico o prefieres una pregunta rápida para probar lo que llevas leído?";
        }

        return '¡Hola! Dime qué concepto o tema estás viendo ahora mismo y lo revisamos paso a paso con ejemplos. ¿Por dónde empezamos?';
    }

    private function generarSugerencias(string $modo, array $contexto): array
    {
        if ($modo === 'pista') {
            return [];
        }

        $titulo = trim((string) ($contexto['titulo'] ?? ''));
        if ($titulo !== '') {
            return [
                'Dame un ejemplo real',
                'Hazme una pregunta rápida',
            ];
        }

        return [
            'Dame un consejo de estudio',
            'Ponme un mini reto',
        ];
    }
}