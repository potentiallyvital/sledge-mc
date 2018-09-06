<?php

class CronController extends Controller
{
        /**
         * allow all visitors to view this controller
         */
        function verify()
        {
                return true;
        }

        function index($method)
        {
                Cron::doCron($method);

                die("DONE");
        }
}
