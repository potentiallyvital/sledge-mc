<?php

require_once SLEDGEMC_PATH.'/models/helpers/UsersHelper.php';

class Users extends UsersHelper
{
        /**
         * auto-hash passwords
         */
        function setPassword($password)
        {
                return parent::setPassword($this->getHashedPassword($password));
        }

        /**
         * check user input against hashed password
         */
        function getHashedPassword($password)
        {
                if (empty($this->id))
                {
                        $this->save();
                }

                return md5($this->id.$this->email.$password);
        }

        /**
         * is this user an admin?
         */
        function isAdmin($for = '')
        {
                switch ($this->username)
                {
                        case SLEDGEMC_MASTER_USER:
                                return true;
                        default:
                                if ($this->getAttribute('admin'))
                                {
                                        return true;
                                }
                                return $this->getAttribute('admin_'.$for);
                }
        }
}
