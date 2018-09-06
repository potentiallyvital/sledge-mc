<?php

class MainController extends Controller
{
        /**
         * allow all visitors to view this controller
         */
        function verify()
        {
                return true;
        }

        /**
         * the default method, if no other controller/method is found
         */
        function index($category_slug)
        {
                $this->view('index');
        }
}
