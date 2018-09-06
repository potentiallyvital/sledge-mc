<?php

// this file includes global functions that should have been included in php but arent
// string manipulation, date handlers, input validators, etc

function months($short = false)
{
        $months = [
                0 => 'Month',
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
        ];

        if ($short)
        {
                foreach ($months as $number => $name)
                {
                        $months[$number] = substr($name, 0, 3);
                }
        }

        return $months;
}

function days()
{
        $days = [];
        $days[0] = 'Day';

        for ($day=1; $day<=31; $day++)
        {
                $days[$day] = st($day);
        }

        return $days;
}

function years()
{
        $years = [];
        $years[0] = 'Year';

        for ($year=date('Y'); $year>=date('Y')-100; $year--)
        {
                $years[$year] = $year;
        }

        return $years;
}

function camelCase($string, $uppercase_first = false)
{
        $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
        $string = ucwords(trim($string));
        $string = lcfirst(str_replace(' ', '', $string));
        if ($uppercase_first){
                $string = ucwords($string);
        }
        return $string;
}

function unCamelCase($string)
{
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $strings = $matches[0];
        foreach ($strings as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $strings);
}

function dump($what, $die = true)
{
        $backtrace = debug_backtrace();
        $caller = array_shift($backtrace);

        echo "<div class='left'>";
        echo "<pre>\r\n\r\n";
        echo '"';
        echo trim(print_r($what, true));
        echo '"';
        echo "\r\n\r\n";
        echo 'dump() called in '.$caller['file'].' line num '.$caller['line'];
        backtrace();
        echo "\r\n";
        if ($die){
                die();
        } else {
                echo "</pre>";
                echo "</div>";
        }
}

function vardump($what, $die = true)
{
        $backtrace = debug_backtrace();
        $caller = array_shift($backtrace);

        echo "<pre>";
        var_dump($what);
        echo "\r\n";
        echo 'vardump() called in '.$caller['file'].' line num '.$caller['line'];
        echo "\r\n";
        if ($die){
                die();
        }
}

function saveFile($path, $contents)
{
        //    if (is_file($path)) {
        //        unlink($path);
        //    }
        file_put_contents($path, $contents);
        chmod($path, 0777);
        //    chown($path, 'nwasson');
        //    chgrp($path, 'boun03admin');
}

function fileExtension($path)
{
        $type = explode('.', $path);
        return array_pop($type);
}

function listFiles($directory)
{
        $files = scandir($directory);
        unset($files[0]);
        unset($files[1]);
        sort($files);
        foreach ($files as $key => $name){
                $files[$key] = $directory.'/'.$name;
        }
        return $files;
}

function locateFile($base_directory, $file)
{
        $directories = array();
        $files = listFiles($base_directory);
        foreach ($files as $sub_file){
                if (is_dir($sub_file)){
                        $directories[] = $sub_file;       
                } elseif (stristr($sub_file, '/'.$file)){
                        return $sub_file;
                }
        }
        foreach ($directories as $directory){
                $found = locateFile($directory, $file);
                if ($found){
                        return $found;
                }
        }  
        return false;
}

function _die($message = null)
{
        if ($message){
                echo $message;
        }
        backtrace();
        //$user = user();
        //if ($user){
        //    setElement('money', '$'.number_format($user->getAttribute('money'), 2));
        //}
        die();
}

function absdiff($num1, $num2)
{
        if ($num1 == $num2){
                $diff = 0;
        } elseif ($num1 >= 0 && $num2 >= 0){
                if ($num1 >= $num2){
                        $diff = $num1-$num2;
                } else {
                        $diff = $num2-$num1;
                }
        } elseif ($num1 <= 0 && $num2 <= 0){
                if ($num1 >= $num2){
                        $diff = abs($num2)-abs($num1);
                } else {
                        $diff = abs($num1)-abs($num2);
                }
        } elseif ($num1 >= 0 && $num2 <= 0){
                $diff = $num1+abs($num2);
        } elseif ($num1 <= 0 && $num2 >= 0){
                $diff = abs($num1)+$num2;
        } else {
                die("i cant figure out the absolute difference between $num1 and $num2");
        }
        return $diff;
}

function getTimeAgo($date)
{
        if (!is_numeric($date))
        {
                $date = strtotime($date);
        }

        if ($date == strtotime(''))
        {
                return 'never';
        }

        $now = time();

        $difference = $now-$date;

        $seconds = round($difference);
        $minutes = round($seconds/60);
        $hours = round($minutes/60);
        $days = round($hours/24);
        $weeks = round($days/7);
        $months = round($days/30.5);
        $years = round($months/12);

        if ($years > 1){
                $time = "$years years ago";
        } elseif ($years == 1){
                $time = "a year ago";
        } elseif ($months > 1){
                $time = "$months months ago";
        } elseif ($months == 1){
                $time = "a month ago";
        } elseif ($weeks > 1){
                $time = "$weeks weeks ago";
        } elseif ($weeks == 1){
                $time = "a week ago";
        } elseif ($days > 1){
                $time = "$days days ago";
        } elseif ($days == 1){
                $time = "yesterday";
        } elseif ($hours > 1){
                $time = "$hours hours ago";
        } elseif ($hours == 1){
                $time = "an hour ago";
        } elseif ($minutes > 1){
                $time = "$minutes minutes ago";
        } elseif ($minutes == 1){
                $time = "a minute ago";
        } elseif ($seconds > 1) {
                $time = "$seconds seconds ago";
        } else {
                $time = "just now";
        }

        return $time;
}

function compactString($string)
{
        $new_string = false;
        while ($new_string !== $string){
                if ($new_string !== false){
                        $string = $new_string;
                }
                $new_string = trim(str_replace(array('  ', "\t", "\r\n", "\r", "\n", PHP_EOL), ' ', $string));
                $new_string = str_replace('; ', ';', $new_string);
                $new_string = str_replace(' = ', '=', $new_string);
                $new_string = str_replace('{ ', '{', $new_string);
                        //$new_string = str_replace(' }', '}', $new_string);
                $new_string = str_replace(', ', ',', $new_string);
        }
        return $new_string;
}

function backtrace()
{
        echo "<pre>";
        echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo '</pre>';
}

function smarttime($date, $format = 'Y-m-d H:i:s')
{
        $new_date = str_replace('/', '-', $date);
        $parts = explode('-', $new_date);
        if (count($parts) == 3 && strlen($parts[2]) == 4) {
                $time = strtotime($parts[2].'-'.$parts[0].'-'.$parts[1]);
        } else {
                $time = strtotime($date);
        }
        if ($time && $format) {
                $time = date($format, $time);
        }
        return $time;
}

function email_valid($email)
{
        return true;
        return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function an($string)
{
        $check = strip_tags($string);

        if (substr($check, -1) == 's')
        {
                return 'some '.$string;
        }

        switch (strtolower(substr($check, 0, 1))) {
                case 'a':
                case 'e':
                case 'i':
                case 'o':
                case 'u':
                        return 'an '.$string;
                default:
                        return 'a '.$string;
        }
}

function s($string, $number = null)
{
        $original = strip_tags($string);

        if ($number === null)
        {
                if (substr($original, -1) == 's')
                {
                        $new = $original."'";
                }
                else
                {
                        $new = $original."'s";
                }

                $string = str_replace($original, $new, $string);

                return $string;
        }
        elseif (round($number) == 1)
        {
                return rtrim($string, 's');
        }
        else
        {
                if (substr($original, -2) == 'ey')
                {
                        $new = substr($original, 0, -2);
                        $new .= 'ies';
                }
                elseif (false && substr($original, -1) == 'y')
                {
                        $new = substr($original, 0, -1);
                        $new .= 'ies';
                }
                elseif (substr($original, -1) != 's')
                {
                        $new = $original.'s';
                }
                else
                {
                        $new = $original;
                }

                $string = str_replace($original, $new, $string);

                return $string;
        }
}

function number($number)
{
        switch ($number) {
                case 0:
                        return 'no';
                case 1:
                        return 'one';
                case 2:
                        return 'two';
                case 3:
                        return 'three';
                case 4:
                        return 'four';
                case 5:
                        return 'five';
                case 6:
                        return 'six';
                case 7:
                        return 'seven';
                case 8:
                        return 'eight';
                case 9:
                        return 9;
                default:
                        return number_format($number);
        }
}

function size($bytes, $format = null, $decimals = 2, $number_format = true)
{
        if ($format == null)
        {
                if ($bytes > tb())
                {
                        $format = 't';
                }
                elseif ($bytes > gb())
                {
                        $format = 'g';
                }
                elseif ($bytes > mb())
                {
                        $format = 'm';
                }
                elseif ($bytes > kb())
                {
                        $format = 'k';
                }
                else
                {
                        $format = '';
                }
        }

        switch (strtolower($format))
        {
                case '':
                        $value = $bytes;
                        break;
                case 'k':
                        $value = $bytes/kb();
                        break;
                default:
                case 'm':
                        $value = $bytes/mb();
                        break;
                case 'g':
                        $value = $bytes/gb();
                        break;
                case 't':
                        $value = $bytes/tb();
                        break;
        }

        if ($number_format)
        {
                $value = number_format($value, $decimals);
        }
        elseif ($decimals)
        {
                $value = round($value, $decimals);
        }

        $value .= ' '.strtoupper($format.'B');

        return $value;
}

function kb()
{
        return 1024;
}

function mb()
{
        return 1048576;
}

function gb()
{
        return 1073741824;
}

function tb()
{
        return 1099511627776;
}

function prettySize($bytes, $digits = 2)
{
        $sizes = ['b'=>'B', 'k'=>'KB', 'm'=>'MB', 'g'=>'GB', 't'=>'TB'];
        foreach ($sizes as $size => $label) {
                $size_formatted = size($bytes, $size, 0, false);
                if ($size_formatted < 1000) {
                        return number_format($size_formatted, $digits).' '.$label;
                }
        }
}

function toFunction($string)
{
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
}

function money($amount)
{
        return '$'.number_format($amount*100/100, 2);
}

function spaces($string)
{
        $string = str_replace("\r", "<br />", $string);
        $string = str_replace("  ", "&nbsp;&nbsp;", $string);
        return $string;
}

function line_exec($cmd)
{
        return explode(PHP_EOL, trim(shell_exec($cmd)));
}

function imRunning($times = 0, $process = null)
{
        if (!$process) {
                $process = basename($_SERVER['PHP_SELF']);
        }
        $running = line_exec("ps axf | grep '$process' | grep -v grep | grep -v '/bin/sh'");
        $running_times = count($running);
        if ($times == 0 && $running_times == 1) {
                return false;
        } elseif ($times == 0 && $running_times > 1) {
                return true;
        } else {
                return !($running_times < $times);
        }
}

function numberToWord($number)
{
        $number = preg_replace('/[^0-9]/', '', $number);
        $length = strlen($number);

        $tens = ['ten','twenty','thirty','fourty','fifty','sixty','seventy','eighty','ninety'];
        $thousands = ['hundred','thousand','million','billion','trillion'];
        $small = ['','one','two','three','four','five','six','seven','eight','nine','ten',
                'eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];

        if ($number == 0) {
                return 'zero';
        } elseif ($number < 0) {
                return '';
        } elseif (!empty($small[$number])) {
                return $small[$number];
        } elseif ($number <= 99) {
                $first = substr($number, 0, 1);
                $last = substr($number, -1);
                return $tens[$first].'-'.$small[$last];
        }

        $big = '';
        $word = '';

        if ($length >= 13) {
                $big = substr($number, 0, -12);
                $word = 'trillion';
                $number -= $big*1000000000000;
        } elseif ($length >= 10) {
                $big = substr($number, 0, -9);
                $word = 'billion';
                $number -= $big*1000000000;
        } elseif ($length >= 7) {
                $big = substr($number, 0, -6);
                $word = 'million';
                $number -= $big*1000000;
        } elseif ($length >= 4) {
                $big = substr($number, 0, -3);
                $word = 'thousand';
                $number -= $big*1000;
        } elseif ($length >= 3) {
                $big = substr($number, 0, -2);
                $word = 'hundred';
                $number -= $big*100;
        } else {
                return $number;
        }

        if ($number) {
                return numberToWord($big).' '.$word.' '.numberToWord($number);
        } else {
                return numberToWord($big).' '.$word;
        }
}

function st($number)
{
        switch ($number)
        {
                case 11:
                case 12:
                case 13:
                        return $number.'th';

                default:
                        $last = substr($number, -1);
                        switch ($last)
                        {
                                case 1:
                                        return $number.'st';
                                case 2:
                                        return $number.'nd';
                                case 3:
                                        return $number.'rd';
                                default:
                                        return $number.'th';
                        }
                        break;
        }
}

function only($number)
{
        if ($number == 0)
        {
                return 'none';
        }
        else
        {
                return 'only '.number_format($number);
        }
}

function commas($values)
{
        $last = array_pop($values);
        if ($values)
        {
                $last = 'and '.$last;
        }
        $values[] = $last;
        if (count($values) > 2)
        {
                $values = implode(', ', $values);
        }
        else
        {
                $values = implode(' ', $values);
        }
        return $values;
}
