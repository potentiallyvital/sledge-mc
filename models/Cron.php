<?php

class Cron
{
        static function doCron($method)
        {
                // start log

                self::$method();

                // end log
        }

        static function runHourly()
        {
                $methods = [
                        'update_products',
                ];

                foreach ($methods as $method)
                {
                        try
                        {
                                self::doCron($method);
                        }
                        catch (Exception $e)
                        {

                        }
                }

                die("DONE");
        }

        static function update_products()
        {
                $traxia = new Traxia();
                $traxia->updateProducts();
        }
}
