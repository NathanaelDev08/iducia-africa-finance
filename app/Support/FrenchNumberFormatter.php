<?php

namespace App\Support;

/**
 * Convertit un entier en toutes lettres françaises (ex: 1831548 -> "un million
 * huit cent trente et un mille cinq cent quarante-huit"). Utilisé pour la
 * mention "Arrêté le présent bulletin à la somme de" sur les bulletins de paie.
 */
class FrenchNumberFormatter
{
    private const UNITS = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize',
        'dix-sept', 'dix-huit', 'dix-neuf',
    ];

    private const TENS = [
        2 => 'vingt', 3 => 'trente', 4 => 'quarante', 5 => 'cinquante', 6 => 'soixante',
    ];

    public static function toWords(int $number): string
    {
        if ($number === 0) {
            return 'zéro';
        }

        if ($number < 0) {
            return 'moins ' . self::toWords(-$number);
        }

        $millions = intdiv($number, 1000000);
        $thousands = intdiv($number % 1000000, 1000);
        $rest = $number % 1000;

        $parts = [];

        if ($millions > 0) {
            // "million" est un nom qui s'accorde normalement, indépendamment de la
            // règle d'invariabilité de "mille".
            $parts[] = ($millions === 1 ? 'un' : self::hundredsToWords($millions, true)) . ' million' . ($millions > 1 ? 's' : '');
        }

        if ($thousands > 0) {
            // "mille" est invariable et bloque l'accord du "cent"/"vingt" qui le précède
            // (ex: "deux cent mille", jamais "deux cents mille").
            $parts[] = ($thousands === 1 ? '' : self::hundredsToWords($thousands, false) . ' ') . 'mille';
        }

        if ($rest > 0 || $number === 0) {
            $parts[] = self::hundredsToWords($rest, true);
        }

        return trim(implode(' ', array_filter($parts)));
    }

    private static function hundredsToWords(int $n, bool $allowPlural): string
    {
        $n = $n % 1000;
        $hundreds = intdiv($n, 100);
        $remainder = $n % 100;

        $words = [];

        if ($hundreds > 0) {
            $pluralS = ($allowPlural && $hundreds > 1 && $remainder === 0) ? 's' : '';
            $words[] = ($hundreds === 1 ? 'cent' : self::UNITS[$hundreds] . ' cent') . $pluralS;
        }

        if ($remainder > 0) {
            $words[] = self::tensToWords($remainder);
        }

        return implode(' ', $words);
    }

    private static function tensToWords(int $n): string
    {
        if ($n < 20) {
            return self::UNITS[$n];
        }

        $tensDigit = intdiv($n, 10);
        $unit = $n % 10;

        // 70-79 et 90-99 se construisent en accolant un mot de 10 à 19
        // à "soixante" / "quatre-vingt" (avec "et" uniquement pour soixante et onze).
        if ($tensDigit === 7 || $tensDigit === 9) {
            $base = $tensDigit === 7 ? 'soixante' : 'quatre-vingt';
            if ($unit === 0) {
                return $base . '-dix';
            }
            $teen = self::UNITS[10 + $unit];
            if ($tensDigit === 7 && $unit === 1) {
                return $base . ' et ' . $teen;
            }
            return $base . '-' . $teen;
        }

        if ($unit === 0) {
            return $tensDigit === 8 ? 'quatre-vingts' : self::TENS[$tensDigit];
        }

        if ($tensDigit === 8) {
            return 'quatre-vingt-' . self::UNITS[$unit];
        }

        if ($unit === 1) {
            return self::TENS[$tensDigit] . ' et un';
        }

        return self::TENS[$tensDigit] . '-' . self::UNITS[$unit];
    }
}
