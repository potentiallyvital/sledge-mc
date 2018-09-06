<?php

// this file contains application specific global functions
// used for session handling, url handling, place handling, etc

/**
 * return the session object for the user
 *
 * @return Sessions object
 */
function session()
{
        if (isset($GLOBALS['session']))
        {
                return $GLOBALS['session'];
        }

        $id = session_id();
        $session = Sessions::getBySessionKey($id, true);
        if (!$session)
        {
                $session = new Sessions();
                $session->setSessionKey($id);
                $session->save();
        }

        $GLOBALS['session'] = $session;
        return $session;
}

/**
 * convert a string into a pretty url slug
 * ex:
 * input: 187 Quick Foxes - all jumping & stuff
 * output: 187-quick-foxes-all-jumping-and-stuff
 *
 * @param $string - string (string to convert)
 *
 * @return string
 */
function slugify($string)
{
        $string = strtolower($string);
        $string = str_replace(['&','/'], ' and ', $string);
        $string = str_replace(['[',']'], '-', $string);
        $string = preg_replace('/[^- a-zA-Z0-9]/', '', $string);
        $string = str_replace(' ', '-', $string);
        while (stristr($string, '--'))
        {
                $string = str_replace('--', '-', $string);
        }
        $string = ltrim($string, '-');
        $string = rtrim($string, '-');
        return $string;
}

/**
 * convert a url slug into a pretty string
 *
 * @param $slug - string (url part to convert)
 * @para $ucfirst - boolean (true = capital first letter only, false = cap all words)
 *
 * @return string
 */
function deslugify($string, $ucfirst = false)
{
        $string = str_replace('/', ' | ', $string);
        $string = str_replace(['-','_'], ' ', $string);
        $string = str_replace(' &amp; ', ' & ', $string);
        $string = str_replace(' and ', ' & ', $string);
        if ($ucfirst)
        {
                $string = ucfirst($string);
        }
        else
        {
                $string = ucwords($string);
        }
        return $string;
}

/**
 * convert a table name or string into a class name
 * 
 * @param $string - string to convert
 *
 * @return string
 */
function class_name($string)
{
        return str_replace(' ', '', ucwords(deslugify($string)));
}

/**
 * return the controller object
 *
 * @return Controller object
 */
function controller()
{
        return $GLOBALS['controller'];
}
