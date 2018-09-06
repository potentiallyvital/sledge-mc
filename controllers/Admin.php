<?php

class AdminController extends Controller
{
        /**
         * check if user has permissions to do admin
         * redirect to login of not already there
         */
        function verify()
        {
                if ($this->data['method'] == 'login')
                {
                        if ($this->user && $this->user->isAdmin())
                        {
                                $this->redirect('admin/index');
                        }
                }
                elseif (!$this->user || !$this->user->isAdmin())
                {
                        $this->redirect('admin/login');
                }

                return true;
        }

        /**
         * login page and processing
         */
        function login()
        {
                if (post())
                {
                        $user = Users::getByEmail(post('email'), true);
                        if ($user)
                        {
                                if ($user->password == $user->getHashedPassword(post('password')))
                                {
                                        $this->session->setUserId($user->id)->save();

                                        $this->redirect('admin');
                                }
                        }
                }

                $this->view('login');
        }

        /**
         * admin dashboard
         */
        function index()
        {
                $this->view('index');
        }
}
