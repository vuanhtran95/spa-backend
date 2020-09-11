<?php

namespace App\Helper;

class Common
{
    public static function getIds($n)
    {
        return $n['id'];
    }

    public static function calCommissionPercent($attitude, $skill)
    {
        switch ((int )$attitude + (int)$skill) {
            case 10:
                return 1;
            case 9:
                return 0.9;
            case 8:
                return 0.8;
            case 7:
                return 0.7;
            default:
                return 0.5;
        }
    }
}
