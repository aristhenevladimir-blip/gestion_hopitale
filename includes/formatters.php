<?php
function escape_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8"); }

function format_date_fr($value) {
    if (!$value) return "—";
    $t = strtotime((string) $value);
    return $t ? date("d/m/Y", $t) : (string) $value;
}

function format_datetime_fr($value) {
    if (!$value) return "—";
    $t = strtotime((string) $value);
    return $t ? date("d/m/Y H:i", $t) : (string) $value;
}

function text_length($value) {
    $text = (string) $value;
    return function_exists("mb_strlen") ? mb_strlen($text, "UTF-8") : strlen($text);
}

function shorten_text($value, $maxLength = 40) {
    $text = trim((string) $value);
    if ($text === "") return "—";
    if (text_length($text) > $maxLength) {
        return (function_exists("mb_substr") ? mb_substr($text, 0, $maxLength - 1, "UTF-8") : substr($text, 0, $maxLength - 1)) . "…";
    }
    return $text;
}
