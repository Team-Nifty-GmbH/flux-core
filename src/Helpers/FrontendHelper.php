<?php

namespace FluxErp\Helpers;

use DateTime;
use Illuminate\Support\Carbon;

class FrontendHelper
{
    /**
     * This function displays the relative time of a specific
     * CustomDateTime object against the current (now) timestamp.
     *
     * It prints the relative time or date in a very human readable manner.
     *
     * @return string The relative time or date in human readable manner
     */
    public static function relative(string|Carbon $timestamp): string
    {
        // Create the current timestamp
        $now = new DateTime;
        $timestamp = $timestamp instanceof Carbon ? $timestamp->toDateTime() : strtotime($timestamp);

        // Calculate the difference against the current time
        $diff = $timestamp - $now->getTimestamp();

        // Is difference between positive 1-60 secs?
        if ($diff === 0) {
            return __('Now');
        } elseif ($diff > 0 && $diff < 60) {
            return sprintf(__('In %d %s'), $diff, $diff > 1 ? __('seconds') : __('second'));
        } // Is difference between positive 1-60 mins?
        elseif ($diff > 0 && $diff < 60 * 60) {
            $min = $diff / 60;

            return sprintf(__('In %d %s'), $min, $min > 1 ? __('minutes') : __('minute'));
        } // Is difference between positive 1-24 hours ?
        elseif ($diff > 0 && $diff < 60 * 60 * 24) {
            $hours = $diff / (60 * 60);

            return sprintf(__('In %d %s'), $hours, $hours > 1 ? __('hours') : __('hour'));
        } // Is difference between positive 1-31 days?
        elseif ($diff > 0 && $diff < 60 * 60 * 24 * 31) {
            $days = $diff / (60 * 60 * 24);

            return sprintf(__('In %d %s'), $days, $days > 1 ? __('days') : __('day'));
        } // Is difference anything else positive?
        elseif ($diff > 0 && $diff > 0) {
            $months = $diff / (60 * 60 * 24 * 31);

            return sprintf(
                __('In approx. %d %s'), $months, $months > 1 ? __('month') : __('months')
            );
        } // Is difference between negative 1-60 secs?
        elseif ($diff > -60) {
            $sec = abs($diff);

            return sprintf(__('%d %s ago'), $sec, $sec > 1 ? __('seconds') : __('second'));
        } // Is difference between negative 1-60 mins?
        elseif ($diff > -(60 * 60)) {
            $min = abs($diff / 60);

            return sprintf(__('%d %s ago'), $min, $min > 1 ? __('minutes') : __('minute'));
        } // Is difference between negative 1-24 hours?
        elseif ($diff > -(60 * 60 * 24)) {
            $hours = abs($diff / (60 * 60));

            return sprintf(__('%d %s ago'), $hours, $hours > 1 ? __('hours') : __('hour'));
        } // Is difference between negative 1-31 days?
        elseif ($diff > -(60 * 60 * 24 * 31)) {
            $days = abs($diff / (60 * 60 * 24));

            return sprintf(__('%d %s ago'), $days, $days > 1 ? __('days') : __('day'));
        } // Is difference anything else negative?
        elseif ($diff < 0) {
            $months = abs($diff / (60 * 60 * 24 * 31));

            return sprintf(
                __('Approx. %d month%s ago'), $months, $months > 1 ? __('month') : __('months')
            );
        } else {
            return 'now';
        }
    }

    public static function stringToColorCode($str): string
    {
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);

        return '#'.$code;
    }

    public static function getContrastColor($hexColor): string
    {
        // hexColor RGB
        $red1 = hexdec(substr($hexColor, 1, 2));
        $green1 = hexdec(substr($hexColor, 3, 2));
        $blue1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = '#000000';
        $red2BlackColor = hexdec(substr($blackColor, 1, 2));
        $green2BlackColor = hexdec(substr($blackColor, 3, 2));
        $blue2BlackColor = hexdec(substr($blackColor, 5, 2));

        // Calc contrast ratio
        $lightness1 = 0.2126 * pow($red1 / 255, 2.2) +
            0.7152 * pow($green1 / 255, 2.2) +
            0.0722 * pow($blue1 / 255, 2.2);

        $lightness2 = 0.2126 * pow($red2BlackColor / 255, 2.2) +
            0.7152 * pow($green2BlackColor / 255, 2.2) +
            0.0722 * pow($blue2BlackColor / 255, 2.2);

        if ($lightness1 > $lightness2) {
            $contrastRatio = (int) (($lightness1 + 0.05) / ($lightness2 + 0.05));
        } else {
            $contrastRatio = (int) (($lightness2 + 0.05) / ($lightness1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
    }

    public static function stringToTailwindColor(string $string): string
    {
        $num = (string) hexdec(
            substr(
                sha1($string),
                0,
                10
            )
        );
        $num = str_pad($num, 20, '0', STR_PAD_RIGHT);
        $num = substr($num, 5, 10);
        $num = (int) ((int) substr($num, 0, 2) / 4.71);

        $colors = [
            'bg-slate-600',
            'bg-gray-600',
            'bg-zinc-600',
            'bg-neutral-600',
            'bg-stone-600',
            'bg-red-600',
            'bg-orange-600',
            'bg-amber-600',
            'bg-yellow-600',
            'bg-lime-600',
            'bg-green-600',
            'bg-emerald-600',
            'bg-teal-600',
            'bg-cyan-600',
            'bg-sky-600',
            'bg-blue-600',
            'bg-indigo-600',
            'bg-violet-600',
            'bg-purple-600',
            'bg-fuchsia-600',
            'bg-pink-600',
            'bg-rose-600',
        ];

        return $colors[$num];
    }
}
