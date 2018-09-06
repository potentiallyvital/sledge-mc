<?php

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 1);

$force_includes = false;

$definitions = [
        'sledgemc_app' => 'Site Name',
        'sledgemc_title' => 'Page Title',
        'sledgemc_host' => 'localhost',
        'sledgemc_user' => 'DB_USER',
        'sledgemc_pass' => 'DB_PASS',
        'sledgemc_name' => 'DB_NAME',
        'sledgemc_path' => '/path/to/public_html',
        'base_url' => 'http://www.your-site.com',
];

foreach ($definitions as $key => $value)
{
        $key = strtoupper($key);
        if (!defined($key))
        {
                define($key, $value);
        }
}

if (empty($skip_auto_include))
{
        $auto_include = [
                SLEDGEMC_PATH.'/libraries/',
                SLEDGEMC_PATH.'/controllers/BaseController.php',
                SLEDGEMC_PATH.'/controllers/Controller.php',
                SLEDGEMC_PATH.'/controllers/',
                SLEDGEMC_PATH.'/models/base/Base.php',
                SLEDGEMC_PATH.'/models/base/GettersAndSetters.php',
                SLEDGEMC_PATH.'/models/base/',
                SLEDGEMC_PATH.'/models/helpers/',
                SLEDGEMC_PATH.'/models/',
        ];

        foreach ($auto_include as $inc_file)
        {
                if (substr($inc_file, -1) == '/')
                {
                        $files = scandir($inc_file);
                        foreach ($files as $inc_file_2)
                        {
                                $file = $inc_file.$inc_file_2;
                                if (is_file($file) && substr($inc_file_2, 0, 1) != '.')
                                {
                                        if ($force_includes)
                                        {
                                                require_once $file;
                                        }
                                        else
                                        {
                                                include_once $file;
                                        }
                                }
                        }
                }
                elseif ($force_includes)
                {
                        require_once $inc_file;
                }
                else
                {
                        include_once $inc_file;
                }
        }
}
