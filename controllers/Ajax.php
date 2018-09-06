<?php

/**
 * controller for handling ajax requests
 */
class AjaxController extends Controller
{
        function view($file, $return = false)
        {
                parent::viewOnly($file);
        }
}
