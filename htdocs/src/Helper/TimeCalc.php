<?php

namespace App\Helper;


class TimeCalc
{
    static function strTimeToSecond($time)
    {
        list($hour, $minute, $second) = explode(':', $time);
        return self::timeToSecond($hour, $minute, $second);
    }

    static function timeToSecond($hour, $minute, $second)
    {
        return $second + $minute * 60 + $hour * 3600;
    }

    static function normalizeTime($second)
    {
        $hour = floor($second / 3600);
        $second -= $hour * 3600;
        $minute = floor($second / 60);
        $second -= $minute * 60;

        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }

    static function addTime($time1, $time2)
    {
        return self::strTimeToSecond($time1) + self::strTimeToSecond($time2);
    }

    static function removeTime($time1, $time2)
    {
        return self::strTimeToSecond($time1) - self::strTimeToSecond($time2);
    }

    static function diffTime($time1, $time2)
    {
        $sub = self::removeTime($time1, $time2);
        if ($sub < 0) {
            return -1;
        } elseif ($sub > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    static function normalizeTimestamp($time)
    {
        $hour = floor($time / 3600);
        $time = $time % 3600;
        $minute = floor($time / 60);
        $second = $time % 60;

        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }
}