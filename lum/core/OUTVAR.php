<?php
namespace Lum\Core;

class OUTVAR {
    public static function dump(mixed $value) : string {
        ob_start();
        var_dump($value);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public static function print(mixed $value) : string {
        ob_start();
        print_r($value);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

}
