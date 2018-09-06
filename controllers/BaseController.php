<?php

/**
 * base controller class for sledgeMC
 */
class BaseController
{
        /**
         * current users object
         */
        var $user = null;

        /**
         * current user id
         */
        var $user_id = null;

        /**
         * current session object
         */
        var $session = null;

        /**
         * current session id
         */
        var $session_id = null;

        /**
         * flag indicating if header was already shown
         */
        var $header = null;

        /**
         * data to be passed to the view
         */
        var $data = [];

        /**
         * multiple views to display inside header and footer
         */
        var $views = [];

        /**
         * names of any javascript files that need to be included
         */
        var $js = [];

        /**
         * names of any css files that need to be included
         */
        var $css = [];

        /**
         * base constructor
         */
        function __construct($params = [])
        {

        }

        /**
         * handle incoming http request
         * use url args to determine controller, method, and method values
         */
        function initialize()
        {
                // get the URL args
                $args = $_SERVER['REQUEST_URI'];
                $args = explode('?', $args);
                $args = array_shift($args);
                $args = explode('/', $args);
                array_shift($args);
                for ($i=0; $i<=10; $i++)
                {
                        if (!isset($args[$i]) or !$args[$i])
                        {
                                $args[$i] = false;
                        }
                }
                if (stristr($args[0], '?'))
                {
                        $args[0] = explode('?', $args[0]);
                        $args[0] = $args[0][0];
                }
                foreach ($args as $key => $value)
                {
                        $args[$key] = urldecode($value);
                }

                // figure out the controller and method
                $controller = $args[0];
                $controller = str_replace('-', ' ', $controller);
                $controller = strtolower($controller);
                $controller = ucwords($controller);
                $controller = str_replace(' ', '', $controller);
                $controller_path = SLEDGEMC_PATH.'/controllers/'.$controller.'.php';
                $controller .= 'Controller';
                $method = $args[1] ?: $args[0];

                // default to main controller if not found
                if (!is_file($controller_path))
                {
                        $args['controller'] = 'main';
                        $method = $args[0];
                        $controller_path = SLEDGEMC_PATH.'/controllers/Main.php';
                        $controller = 'MainController';
                }
                else
                {
                        $args['controller'] = array_shift($args);
                }
                $args['method'] = ($args[0] ?: 'index');

                // do controller stuff
                $controller = new $controller();
                $controller->data = $args;
                $controller->method($method);
        }

        /**
         * do things before executing a method
         */
        function beforeMethod($method)
        {

        }

        /**
         * do things after executing a method
         */
        function afterMethod($method)
        {

        }

        /**
         * attempt to call a method
         * if method not found, use index
         */
        function method($method = null)
        {
                // set controller global
                $GLOBALS['controller'] = $this;

                // set app data
                $this->setSession();
                $this->setUser();
                $this->setData();

                // validate user
                if (!$this->verify())
                {
                        $this->redirect('index');
                }

                // do controller method
                $this->beforeMethod($method);
                $method = str_replace('-', '_', $method);
                if (method_exists($this, $method))
                {
                        $this->$method($this->data[1], $this->data[2], $this->data[3], $this->data[4], $this->data[5]);
                }
                else
                {
                        $this->index($this->data[0], $this->data[1], $this->data[2], $this->data[3], $this->data[4], $this->data[5]);
                }
                $this->afterMethod($method);
        }

        /**
         * default method for the controller
         */
        function index()
        {
                $this->view('index');
        }

        /**
         * redirect to another url in this app
         */
        function redirect($url = null)
        {
                header('Location: '.BASE_URL.($url && $url != 'index' ? '/'.$url : ''));
                exit;
        }

        /**
         * refresh the page
         */
        function refresh()
        {
                header('Location: '.BASE_URL.$_SERVER['REQUEST_URI']);
                exit;
        }

        /**
         * add views to display
         */
        function addView($view)
        {
                $this->views[] = $view;
        }

        /**
         * load a view page
         * include the header if not included yet
         * include the footer by default
         * render the content first so the nav is loaded with updated values (if modifed in a view)
         */
        function view($view = 'index')
        {
                // get the view html
                ob_start();
                if ($view)
                {
                        $this->viewOnly($view);
                }
                else
                {
                        foreach ($this->views as $view)
                        {
                                $this->viewOnly($view);
                        }
                }
                $page = ob_get_contents();
                ob_end_clean();

                // render header
                $this->viewHeader();

                // render view
                echo $page;

                // render footer
                $this->viewFooter();
        }

        /**
         * include the header
         */
        function viewHeader($header = true)
        {
                // only render the header once
                if (!$this->header)
                {
                        // set variables for header
                        $user = $this->user;
                        $session = $this->session;
                        $data = $this->data;
                        extract($data);

                        // render the header
                        $js = $this->js;
                        $css = $this->css;
                        $header = ($header === true ? 'nav' : $header);
                        require SLEDGEMC_PATH.'/views/'.$header.'.php';
                        $this->header = true;
                }
        }

        /**
         * include only a view, no header/footer
         */
        function viewOnly($view, $return = false)
        {
                // set variables for view
                $user = $this->user;
                $session = $this->session;
                $data = $this->data;
                extract($data);

                // if the view directory is specified, use the whole path
                // otherwise, assume the view directory matches the controller name (MainController assumes views/main)
                if (stristr($view, '/'))
                {
                        $parts = explode('/', $view);
                        $controller = array_shift($parts);
                        $view = implode('/', $parts);
                }
                else
                {
                        $controller = strtolower(str_replace('Controller', '', get_class($this)));
                }

                // start output buffer if we are returning the view
                if ($return)
                {
                        ob_start();
                }

                // include the view or throw an error
                if (file_exists(SLEDGEMC_PATH.'/views/'.$controller.'/'.$view.'.php'))
                {
                        require SLEDGEMC_PATH.'/views/'.$controller.'/'.$view.'.php';
                }
                elseif ($return)
                {
                        return "<pre>Error: /views/$controller/$view.php not found :(</pre>";
                }
                else
                {
                        echo "<pre>Error: /views/$controller/$view.php not found :(</pre>";
                        return;
                }

                // return output buffer if necessary
                if ($return)
                {
                        $contents = ob_get_contents();
                        ob_end_clean();
                        return $contents;
                }
        }

        /**
         * show a footer
         */
        function viewFooter($footer = true)
        {
                // set footer variables
                $user = $this->user;
                $session = $this->session;
                $data = $this->data;
                extract($data);

                // render footer
                $footer = ($footer == true ? 'footer' : $footer);
                require SLEDGEMC_PATH.'/views/'.$footer.'.php';
        }
}
