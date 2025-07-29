<?php

namespace Frontend;

class ShortcodeParser {
	private array $definitions = [];

    public function register(string $name, array $definition): void {
        $this->definitions[$name] = $definition;
    }

    public function parse(string $text): string {
		
        foreach ($this->definitions as $shortcode => $def) {
            $regex = $this->buildRegex($shortcode);
			
            $text = preg_replace_callback($regex, function ($matches) use ($shortcode, $def) {
                $mainAttr = $def['main'] ?? null;
				$attrs = $this->parseAttributes($matches, $mainAttr);
                $attrs = $this->validateAndNormalize($attrs, $def['attributes']);
                
				// Verwende die Template-Funktion für den Output
                if (isset($def['template'])) {
                    return $def['template']($attrs);  // Führe die Template-Funktion aus
                }
				
                return ''; // Default
            }, $text);
        }
        return $text;
    }

    // Baue den Regex für einen Shortcode
    private function buildRegex(string $shortcode): string {
        return '/\[' . preg_quote($shortcode) . '(="([^"]*)")?(\s+[^]]*)?\]/i';
    }

    // Verarbeite die Attribute
    private function parseAttributes(array $matches, ?string $mainAttr = null): array {
        $result = [];

        // Hauptwert setzen, wenn vorhanden
        if ($mainAttr && !empty($matches[2])) {
            $result[$mainAttr] = $matches[2];
        }

        // Weitere key="value"-Attribute extrahieren
        if (!empty($matches[3])) {
            preg_match_all('/(\w+)="([^"]*)"/', $matches[3], $attrMatches, PREG_SET_ORDER);
            foreach ($attrMatches as $match) {
                $result[$match[1]] = $match[2];
            }
        }

        return $result;
    }

    // Validierung der Attribute (optional, je nach Definition)
    private function validateAndNormalize(array $attrs, array $definitions): array {
        $result = [];
        foreach ($definitions as $key => $def) {
            if (isset($attrs[$key])) {
                $val = $attrs[$key];
                if ($def['type'] === 'int') {
                    $val = intval($val);
                } elseif ($def['type'] === 'bool') {
                    $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }
                $result[$key] = $val;
            } elseif (!empty($def['required'])) {
                throw new InvalidArgumentException("Missing required attribute '$key'");
            } elseif (isset($def['default'])) {
                $result[$key] = $def['default'];
            }
        }
        return $result;
    }
}
