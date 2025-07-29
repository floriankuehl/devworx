<?php

namespace Cascade\Runtime;

class Context
{
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Liefert den Wert zum angegebenen Schlüssel (inkl. verschachtelter Pfade).
     * Beispiel: 'user.name' liefert $data['user']['name'].
     */
    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $this->data;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } elseif( is_object($value) ){
				if( property_exists($value,$segment) ){
					$value = $value->$segment;
				} elseif( method_exists($value,$segment) ){
					$value = call_user_method($segment,$value);
				} else
					return $default;
            } else
				return $default;
		}

        return $value;
    }

    /**
     * Setzt einen Wert im Kontext.
     */
    public function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $data =& $this->data;

        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        $data[array_shift($segments)] = $value;
    }
	
	/**
     * Setzt alle Werte eines Arrays
     */
    public function setAll(?array $values=null): void
    {
		if( $values === null ) return;
        foreach( $values as $key => $value )
			$this->set($key,$value);
    }

    /**
     * Gibt die gesamte Kontext-Datenstruktur zurück.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
