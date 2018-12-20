<?php
namespace App\Utils;

class TemporaryUtils {
    public static function getDomcekCounter() {
        $homepage = file_get_contents('http://www.old.domcek.org/');
        preg_match('/prihl&aacute;sen&yacute;ch:&nbsp;&nbsp;<strong>[0-9]+/i', $homepage, $matches);
        preg_match('/[0-9]+/', $matches[0],$counter);
        return $counter[0];
    }
}