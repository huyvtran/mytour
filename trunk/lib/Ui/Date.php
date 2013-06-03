<?php

function short_time( $t ) {
    $a = explode(':', $t);
    array_pop($a);
    return implode(':', $a);

}

function is_date( $str ) {
    $matches = null;
    if ( preg_match("#^(\d{1,2})\/(\d{1,2})\/(\d{4})$#is", $str, $matches) ) {
        $d = (int) $matches[1];
        $d = ($d < 10 ? "0$d" : $d);
        $m = (int) $matches[2];
        $m = ($m < 10 ? "0$m" : $m);
        $y = (int) $matches[3];
        $date = "$y-$m-$d";
        return date('Y-m-d', strtotime($date)) == $date;
    }
    return false;

}

/*
  Convert H:m => H:m PM/AM
 */

function time_label( $str ) {
    return strtoupper(date("g:i a", strtotime("2011/01/01 $str")));

}

function date_state( $date ) {
    $date = date('d-m-Y', strtotime($date));
    if ( $date == date('d-m-Y', time()) ) {
        return "hôm nay";
    } else {
        return $date;
    }

}

/*
  Convert a date from dd/mm/yyyy => yyyy-mm-dd
 */

function change_date_format( $date ) {
    if ( preg_match("#^(\d{1,2})\D+(\d{1,2})\D+(\d{4})$#is", $date, $matches) ) {
        $d = (int) $matches[1];
        $d = ($d < 10 ? "0$d" : $d);
        $m = (int) $matches[2];
        $m = ($m < 10 ? "0$m" : $m);

        $y = (int) $matches[3];
        return "$y-$m-$d";
    }
    return $date;

}

function change_datetime_format( $date ) {
    if ( preg_match("#^(\d{1,2})\D+(\d{1,2})\D+(\d{4}) (\d{1,2})\D+(\d{1,2})\D+(\d{1,2})$#is", $date, $matches) ) {
        $d = (int) $matches[1];
        $d = ($d < 10 ? "0$d" : $d);

        $m = (int) $matches[2];
        $m = ($m < 10 ? "0$m" : $m);

        $y = (int) $matches[3];

        $H = (int) $matches[4];
        $H = ($H < 10 ? "0$H" : $H);

        $i = (int) $matches[5];
        $i = ($i < 10 ? "0$i" : $i);

        $s = (int) $matches[6];
        $s = ($s < 10 ? "0$s" : $s);

        return "$y-$m-$d $H:$i:$s";
    }
    return $date;

}

/* date for mysql */

function mysql_date( $time = NULL ) {
    return $time == NULL ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $time);

}

/**
 *
 * Cal number of day in a month
 * @param $month
 * @param $year
 */
function days_in_month( $month, $year ) {
    // calculate number of days in a month
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);

}

/**
 *
 * Display a date in normal form
 * @param $format
 * @param $date
 */
function show_date() {
    if ( func_num_args() == 0 ) {
        $format = DATE_FORMAT;
        $date = date('Y-m-d', time());
    } else
    if ( func_num_args() == 1 ) {
        $format = DATE_FORMAT;
        $date = func_get_arg(0);
    } else {
        $format = func_get_arg(0);
        $date = func_get_arg(1);
    }

    if ( strtotime($date) == 0 || $date == '0000-00-00' ) {
        return '';
    }
    return date($format, strtotime($date));

}

/**
 * @param Y-m-d $date1
 * @param Y-m-d $date2
 * @return Integer
 *
 * Return number days from $date1 to $date2
 */
function date_diff_day( $date1, $date2 = null ) {
    $util_time = $date2 ? strtotime($date2) : time();
    return ceil(($util_time - strtotime($date1)) / (60 * 60 * 24));

}

/**
 * Cal diff time
 */
function diff_time( $t1, $t2 ) {
    return strtotime($t2) - strtotime($t1);

}

/**
 * @param Y-m-d $date1
 * @param Y-m-d $date2
 * @return Integer
 *
 * Return number days from $date1 to $date2
 */
function date_cal_day( $date1, $date2 = null ) {
    if ( $date2 == null )
        return 1;
    $util_time = strtotime($date2);
    return ceil(($util_time - strtotime($date1)) / (60 * 60 * 24));

}

/**
 * Convert a duration string to mysql time
 *
 * @param String $str
 * @param Aray $config
 * @return H:i:s
 */
function duration_to_time( $str, $config = array() ) {
    $st = array(
        h => 60,
        d => 24 * 60,
        m => 1,
        mo => 30 * 24 * 60
    );

    $time = 0;
    $a = explode(' ', $str);
    foreach ( $a as $i ) {
        if ( preg_match("#^(\d+)(m|mo|d|m|h)$#is", trim($i), $m) ) {
            $time += ((int) $m[1]) * ((int) $st[$m[2]]);
        }
    }

    $h = str_pad(floor($time / 60), 2, 0, STR_PAD_LEFT);
    $m = str_pad($time % 60, 2, 0, STR_PAD_LEFT);

    return "$h:$m:00";

}

?>