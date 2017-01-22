<?php

class Coret_View_Helper_Formatuj extends Zend_View_Helper_Abstract
{

    static public function kwote($kwota)
    {
        if ($kwota) {
            $kwota = substr($kwota, 0, -2) . '.' . substr($kwota, -2);
            return number_format($kwota, 2, ',', ' ');
        } else {
            return 'brak';
        }
    }

    static public function date($data, $format = null)
    {
        if (!$data) {
            return;
        }
        if (!$format) {
            $format = '%F %H:%M:%S';
        }

//            return date('Y-m-d H:i:s', strtotime($data));
        return date($format, strtotime($data));
    }

    static public function shortDate($data)
    {
        return self::date($data, 'Y.m.d');
    }

    static public function nrb($nrb)
    {
        if ($nrb) {
            return substr($nrb, 0, 2) . '&nbsp;' . substr($nrb, 2, 4) . '&nbsp;' . substr($nrb, 6, 4) . '&nbsp;' . substr($nrb, 10, 4) . '&nbsp;' . substr($nrb, 14, 4) . '&nbsp;' . substr($nrb, 18, 4) . '&nbsp;' . substr($nrb, 22, 4);
        } else {
            return 'brak';
        }
    }

    static public function bool($bool)
    {
        if ($bool) {
            return 'TAK';
        } else {
            return 'NIE';
        }
    }

    static function varchar($varchar)
    {
        if (empty($varchar)) {
            return;
        }

        return substr(strip_tags($varchar), 0, 100);
    }

    static function number($number)
    {
        return $number;
    }
}
