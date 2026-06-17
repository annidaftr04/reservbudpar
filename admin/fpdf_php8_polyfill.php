<?php
// -----------------------------------------------------------------
// Polyfill FPDF vs PHP 8  :  hapus fatal error magic_quotes_runtime
// -----------------------------------------------------------------
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() { return false; }
}
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($newSetting) { return false; }
}
