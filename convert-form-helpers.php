<?php
/**
 * Script para convertir laravelcollective/html Form:: helpers a HTML nativo en vistas Blade
 * Uso: php convert-form-helpers.php
 */

$viewsPath = __DIR__ . '/resources/views';

// Obtener todos los archivos .blade.php
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$converted = 0;
$errors = [];

foreach ($files as $file) {
    if ($file->getExtension() === 'php' && strpos($file->getPathname(), '.blade.php') !== false) {
        $filepath = $file->getPathname();
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        // 1. Convertir Form::select() a <select>
        // Patrón: {!! Form::select('name', $array, selected, ['attr' => 'val', ...]) !!}
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::select\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\$[a-zA-Z_\$\[\]\.>\-\>]+)\s*,\s*([^,]*?)\s*,\s*\[\s*([^\]]*?)\s*\]\s*\)\s*\!\!\}/s',
            function($matches) {
                $name = $matches[1];
                $array = $matches[2];
                $selected = trim($matches[3]);
                $attrsStr = $matches[4];
                
                // Parsear atributos
                $attrs = parseAttributes($attrsStr);
                $id = $attrs['id'] ?? $name;
                $class = $attrs['class'] ?? 'form-control';
                $required = isset($attrs['required']) ? ' required' : '';
                $disabled = isset($attrs['disabled']) ? ' disabled' : '';
                
                $output = "<select name=\"$name\" id=\"$id\" class=\"$class\"$required$disabled>\n";
                $output .= "                        @foreach($array as \$key => \$value)\n";
                $output .= "                            <option value=\"{{ \$key }}\" {{ \$key == $selected ? 'selected' : '' }}>{{ \$value }}</option>\n";
                $output .= "                        @endforeach\n";
                $output .= "                    </select>";
                
                return $output;
            },
            $content
        );
        
        // 2. Convertir Form::open() a <form>
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::open\s*\(\s*\[\s*([^\]]+?)\s*\]\s*\)\s*\!\!\}/s',
            function($matches) {
                $paramsStr = $matches[1];
                $params = parseArrayParams($paramsStr);
                
                $action = '';
                $method = 'POST';
                
                if (isset($params['route'])) {
                    $action = "{{ route({$params['route']}) }}";
                }
                if (isset($params['method'])) {
                    $method = strtoupper(trim($params['method'], '\'"'));
                }
                
                $methodAttr = $method !== 'GET' ? " method=\"$method\"" : '';
                
                return "<form action=\"$action\"$methodAttr>";
            },
            $content
        );
        
        // 3. Convertir Form::close() a </form>
        $content = preg_replace(
            '/\{\!\!\s*Form::close\s*\(\s*\)\s*\!\!\}/s',
            '</form>',
            $content
        );
        
        // 4. Convertir Form::label() a <label>
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::label\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"].*?\)\s*\!\!\}/s',
            function($matches) {
                $for = $matches[1];
                $label = $matches[2];
                return "<label for=\"$for\">$label</label>";
            },
            $content
        );
        
        // 5. Convertir Form::text() a <input type="text">
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::text\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([^,]*?)\s*,\s*\[\s*([^\]]*?)\s*\]\s*\)\s*\!\!\}/s',
            function($matches) {
                $name = $matches[1];
                $value = trim($matches[2]);
                $attrsStr = $matches[3];
                
                $attrs = parseAttributes($attrsStr);
                $class = $attrs['class'] ?? 'form-control';
                $placeholder = $attrs['placeholder'] ?? '';
                $required = isset($attrs['required']) ? ' required' : '';
                $id = $attrs['id'] ?? $name;
                
                $placeholderAttr = $placeholder ? " placeholder=\"$placeholder\"" : '';
                $valueAttr = $value !== 'null' ? " value=\"{{ $value }}\"" : '';
                
                return "<input type=\"text\" name=\"$name\" id=\"$id\" class=\"$class\"$placeholderAttr$valueAttr$required>";
            },
            $content
        );
        
        // 6. Convertir Form::password() a <input type="password">
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::password\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[\s*([^\]]*?)\s*\]\s*\)\s*\!\!\}/s',
            function($matches) {
                $name = $matches[1];
                $attrsStr = $matches[2];
                
                $attrs = parseAttributes($attrsStr);
                $class = $attrs['class'] ?? 'form-control';
                $placeholder = $attrs['placeholder'] ?? '';
                $required = isset($attrs['required']) ? ' required' : '';
                
                $placeholderAttr = $placeholder ? " placeholder=\"$placeholder\"" : '';
                
                return "<input type=\"password\" name=\"$name\" class=\"$class\"$placeholderAttr$required>";
            },
            $content
        );
        
        // 7. Convertir Form::submit() a <button type="submit">
        $content = preg_replace_callback(
            '/\{\!\!\s*Form::submit\s*\(\s*[\'"]([^\'"]*)[\'\"]\s*,\s*\[\s*([^\]]*?)\s*\]\s*\)\s*\!\!\}/s',
            function($matches) {
                $text = $matches[1];
                $attrsStr = $matches[2];
                
                $attrs = parseAttributes($attrsStr);
                $class = $attrs['class'] ?? 'btn btn-primary';
                
                return "<button type=\"submit\" class=\"$class\">$text</button>";
            },
            $content
        );
        
        // Si hubo cambios, guardar el archivo
        if ($content !== $originalContent) {
            file_put_contents($filepath, $content);
            $converted++;
            echo "✓ Convertido: " . str_replace(__DIR__ . '/', '', $filepath) . "\n";
        }
    }
}

echo "\n=== Resultado ===\n";
echo "Archivos convertidos: $converted\n";

if (!empty($errors)) {
    echo "\nErrores encontrados:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

// Funciones auxiliares
function parseAttributes($attrString) {
    $attrs = [];
    // Búsqueda simple de patrones 'key' => 'value' o 'key'
    preg_match_all(
        "/['\"]([^'\"]+)['\"]\s*=>\s*['\"]?([^'\"]*)['\"]?(?:,|])/",
        $attrString,
        $matches
    );
    
    for ($i = 0; $i < count($matches[1]); $i++) {
        $key = $matches[1][$i];
        $value = $matches[2][$i] ?? '';
        $attrs[$key] = $value;
    }
    
    // También detectar valores booleanos como 'required', 'disabled'
    if (preg_match("/'required'/", $attrString) || preg_match('/required/', $attrString)) {
        $attrs['required'] = true;
    }
    if (preg_match("/'disabled'/", $attrString) || preg_match('/disabled/', $attrString)) {
        $attrs['disabled'] = true;
    }
    
    return $attrs;
}

function parseArrayParams($paramString) {
    $params = [];
    preg_match_all(
        "/['\"]([^'\"]+)['\"]\s*=>\s*([^,\]]*)/",
        $paramString,
        $matches
    );
    
    for ($i = 0; $i < count($matches[1]); $i++) {
        $key = $matches[1][$i];
        $value = trim($matches[2][$i]);
        $params[$key] = $value;
    }
    
    return $params;
}
?>
