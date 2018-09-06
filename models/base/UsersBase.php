<?php

require_once SLEDGEMC_PATH.'/models/base/GettersAndSetters.php';

class UsersBase extends GettersAndSetters
{
	const CLASS_NAME = 'Users';
	const TABLE_NAME = 'users';

	var $id = null;
	var $deleted = null;
	var $created = null;
	var $modified = null;
	var $name = null;
	var $username = null;
	var $password = null;
	var $email = null;

	function getChildren()
	{
		return [
			'flash' => 'user_id',
			'sessions' => 'user_id',
		];
	}

	function toArray()
	{
		return [
			'id' => $this->id,
			'deleted' => $this->deleted,
			'created' => $this->created,
			'modified' => $this->modified,
			'name' => $this->name,
			'username' => $this->username,
			'password' => $this->password,
			'email' => $this->email,
		];
	}

	function getColumnType($name)
	{
		$columns = [
			'id' => ['bigint', 20],
			'deleted' => ['character', 1],
			'created' => ['timestamp without time zone'],
			'modified' => ['timestamp without time zone'],
			'name' => ['character varying', 100],
			'username' => ['character varying', 40],
			'password' => ['character varying', 255],
			'email' => ['character varying', 255],
		];
		return $columns[$name];
	}
}
