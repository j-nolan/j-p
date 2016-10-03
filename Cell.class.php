<?php
/* Generic class that represents a cell in a matrix. A cell has a value that can
 * be set on construction or left as default (php max value).
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>
<?php

class Cell {
    const MAX_VALUE = PHP_INT_MAX;
    private $value;
    
    public function __construct($value = self::MAX_VALUE) {
        self::setValue($value);
    }
    
    public function setValue($value) {
        if ($value >= 0 && $value <= self::MAX_VALUE) {
            $this->value = $value;
        } else {
            throw new Exception('Invalid numeric value : ' . $value);
        }
    }

    public function getValue() {
        return $this->value;
    }
}

?>