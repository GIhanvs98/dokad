
<?php

if (!function_exists('ietfLangTag')) {
    function ietfLangTag($locale) {
        // Assuming the locale is already in a valid IETF format
        return str_replace('_', '-', $locale);
    }
}
//created ny gihanvs
