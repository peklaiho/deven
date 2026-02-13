<?php
namespace PekLaiho\Deven;

class Utils
{
    public static function removeQuotes(string $value): string
    {
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
