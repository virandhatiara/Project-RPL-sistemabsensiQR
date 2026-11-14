<?php
class SecurityHelper {
    public static function escape($string) {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function e($string) {
    return SecurityHelper::escape($string);
}
