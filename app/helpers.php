<?php
if (!function_exists('normalize')) {
    function normalize($string)
    {
        $words = explode(' ', $string);
        foreach ($words as $word) {
            if (empty($word) || str_contains(' ', $word)) {
                continue;
            }

            $newWords[] = trim($word);
        }
        $newString = implode(' ', $newWords);

        setlocale(LC_CTYPE, 'nl_BE.utf8');
        $response = preg_replace('/[0-9\@\.\;\"]+/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $newString));

        return $response;
    }
}
