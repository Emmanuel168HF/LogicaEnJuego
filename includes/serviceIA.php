<?php
declare(strict_types=1);

class ServiceIA {
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct() {
        // Asegura que las variables del .env estén cargadas aunque no se haya abierto conexión a MySQL
        if (function_exists('cargarVariablesEntorno')) {
            cargarVariablesEntorno();
        }
        $this->apiKey = (string)(getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? ''));
    }

    public static function obtenerPerfilPorEdad(int $edad): array {
        // 6 a 9 años (Primaria baja)
        if ($edad >= 6 && $edad <= 9) {
            return [
                'tema_css' => 'tema-infantil',
                'nombre' => 'LIPAL Jr.',
                'subtitulo' => '¡Tu amigo de aventuras!',
                'avatar' => 'assets/images/logo.png',
                'activar_voz' => true,
                'system_prompt' => 'Eres LIPAL, una mascota amigable para niños de 6 a 9 años. Usa vocabulario muy simple, frases cortas, cuentos o analogías cotidianas y refuerzo positivo constante.'
            ];
        }

        // 10 a 13 años (Primaria alta / Secundaria)
        if ($edad >= 10 && $edad <= 13) {
            return [
                'tema_css' => 'tema-gamer',
                'nombre' => 'LIPAL Quest',
                'subtitulo' => 'Guía de misiones y retos',
                'avatar' => 'assets/images/logo.png',
                'activar_voz' => false,
                'system_prompt' => 'Eres LIPAL, un compañero estilo videojuego para estudiantes de 10 a 13 años. Usa un tono cercano y curioso, explicaciones paso a paso con ejemplos visuales y formato tipo trivia, sin dar la respuesta directa.'
            ];
        }

        // 14 a 18 años (Preparatoria)
        return [
            'tema_css' => 'tema-academico',
            'nombre' => 'LIPAL Tutor',
            'subtitulo' => 'Tu tutor de aprendizaje',
            'avatar' => 'assets/images/logo.png',
            'activar_voz' => false,
            'system_prompt' => 'Eres LIPAL, un tutor académico moderno para jóvenes de 14 a 18 años. Usa lenguaje formal pero accesible, enfoque socrático (guía con preguntas), pensamiento crítico y soporte para fórmulas y código.'
        ];
    }

    public function responder(string $mensajeUsuario, int $edad, string $contextoPagina = ''): string {
        if ($this->apiKey === '') {
            return 'Error de configuración: Falta agregar GEMINI_API_KEY en el archivo .env.';
        }

        $perfil = self::obtenerPerfilPorEdad($edad);
        $url = $this->apiUrl . '?key=' . urlencode($this->apiKey);

        $promptCompleto = $perfil['system_prompt'] . "\n" .
                          "Contexto actual en Lógica en Juego: " . $contextoPagina . "\n" .
                          "Pregunta del estudiante: " . $mensajeUsuario;

        $datos = [
            'contents' => [
                ['parts' => [['text' => $promptCompleto]]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $respuesta = curl_exec($ch);
        curl_close($ch);

        if (!$respuesta) {
            return 'No pude conectarme en este momento. Intenta de nuevo en unos segundos.';
        }

        $resultado = json_decode($respuesta, true);
        return $resultado['candidates'][0]['content']['parts'][0]['text'] 
               ?? 'No logré generar una respuesta clara. ¿Puedes reformular tu duda?';
    }
}