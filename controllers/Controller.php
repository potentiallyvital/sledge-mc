<?php

/**
 * customized controller for this app
 */
class Controller extends BaseController
{
        /**
         * verify this user can see this controller
         */
        function verify()
        {
                return true;
        }

        /**
         * set the current user
         */
        function setUser()
        {
                $this->user = null;
                $this->user_id = null;

                if (!empty($this->session) && !empty($this->session->user_id))
                {
                        $this->user = Users::getById($this->session->user_id);
                        if (!empty($this->user))
                        {
                                $this->user_id = $this->session->user_id;
                        }
                }
        }

        /**
         * return the current user
         */
        function getUser()
        {
                if (empty($this->user) && !empty($this->user_id))
                {
                        $this->user = Users::getById($this->user_id);
                }

                return $this->user;
        }

        /**
         * set the current session
         */
        function setSession()
        {
                $this->session_id = session_id();

                $session = Sessions::getBySessionKey($this->session_id, true);
                if (!$session)
                {
                        $session = new Sessions();
                        $session->setSessionKey($this->session_id);
                }

                $session->setModified(date('Y-m-d H:i:s'));
                $session->save();

                $this->session = $session;
        }

        /**
         * get the current session
         */
        function getSession()
        {
                if (empty($this->session) && !empty($this->session_id))
                {
                        $this->session = Sessions::getById($this->session_id);
                }

                return $this->session;
        }

        /**
         * set base data used in all controllers
         */
        function setData()
        {
                $this->data['title'] = SLEDGEMC_TITLE;
        }

        /**
         * flash a message to the user
         */
        function flash($message, $class = 'info')
        {
                $flash = new Flash();
                $flash->setUserId($this->user_id);
                $flash->setSessionId($this->session_id);
                $flash->save();
        }

        /**
         * get all flash messages for the user
         */
        function getFlash()
        {
                $flashes = [];

                if ($this->session_id)
                {
                        $session = Flash::getBySessionId($this->session_id);
                        foreach ($session as $flash)
                        {
                                $flashes[$flash->id] = $flash;
                        }
                }

                if ($this->user_id)
                {
                        $user = Flash::getByUserId($this->user_id);
                        foreach ($user as $flash)
                        {
                                $flashes[$flash->id] = $flash;
                        }
                }

                return $flashes;
        }

        /**
         * show flash messages to the user
         */
        function doFlash()
        {
                $flashes = $this->getFlash();

                if ($flashes)
                {
                        $html = '<div class="flash">';
                        foreach ($flashes as $flash)
                        {
                                $html .= '<div class="message">'.$message.'</div>';
                        }
                        $html .= '</div>';

                        echo $html;

                        $flash->delete();
                }
        }
}
