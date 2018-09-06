<?php

class GettersAndSetters extends Base
{
	function setCreated($value)
	{
		self::set($this, 'created', $value);
		return $this;
	}

	function getCreated($vars = null)
	{
		if (!property_exists($this, 'created')) {
			return null;
		}
		return $this->sanitize('created', $this->created);
	}

	static function getByCreated($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE created='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setData($value)
	{
		self::set($this, 'data', $value);
		return $this;
	}

	function getData($vars = null)
	{
		if (!property_exists($this, 'data')) {
			return null;
		}
		return $this->sanitize('data', $this->data);
	}

	static function getByData($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE data='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setDeleted($value)
	{
		self::set($this, 'deleted', $value);
		return $this;
	}

	function getDeleted($vars = null)
	{
		if (!property_exists($this, 'deleted')) {
			return null;
		}
		return $this->sanitize('deleted', $this->deleted);
	}

	static function getByDeleted($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE deleted='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setEmail($value)
	{
		self::set($this, 'email', $value);
		return $this;
	}

	function getEmail($vars = null)
	{
		if (!property_exists($this, 'email')) {
			return null;
		}
		return $this->sanitize('email', $this->email);
	}

	static function getByEmail($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE email='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setId($value)
	{
		self::set($this, 'id', $value);
		return $this;
	}

	function getId($vars = null)
	{
		if (!property_exists($this, 'id')) {
			return null;
		}
		$value = $this->sanitize('id', $this->id);
		if ($vars) {
			$value = number_format($this->id);
		}
		return $value;
	}

	static function getById($value, $first_only = true)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE id=".round(preg_replace('/[^-.0-9]/', '', $value))."";
		return self::selectAll($sql, $first_only);
	}

	function setMessage($value)
	{
		self::set($this, 'message', $value);
		return $this;
	}

	function getMessage($vars = null)
	{
		if (!property_exists($this, 'message')) {
			return null;
		}
		return $this->sanitize('message', $this->message);
	}

	static function getByMessage($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE message='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setModified($value)
	{
		self::set($this, 'modified', $value);
		return $this;
	}

	function getModified($vars = null)
	{
		if (!property_exists($this, 'modified')) {
			return null;
		}
		return $this->sanitize('modified', $this->modified);
	}

	static function getByModified($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE modified='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setName($value)
	{
		self::set($this, 'name', $value);
		return $this;
	}

	function getName($vars = null)
	{
		if (!property_exists($this, 'name')) {
			return null;
		}
		return $this->sanitize('name', $this->name);
	}

	static function getByName($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE name='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setPassword($value)
	{
		self::set($this, 'password', $value);
		return $this;
	}

	function getPassword($vars = null)
	{
		if (!property_exists($this, 'password')) {
			return null;
		}
		return $this->sanitize('password', $this->password);
	}

	static function getByPassword($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE password='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setSessionId($value)
	{
		self::set($this, 'session_id', $value);
		return $this;
	}

	function getSessionId($vars = null)
	{
		if (!property_exists($this, 'session_id')) {
			return null;
		}
		$value = $this->sanitize('session_id', $this->session_id);
		if ($vars) {
			$value = number_format($this->session_id);
		}
		return $value;
	}

	static function getBySessionId($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE session_id=".round(preg_replace('/[^-.0-9]/', '', $value))."";
		return self::selectAll($sql, $first_only);
	}

	function setSessionKey($value)
	{
		self::set($this, 'session_key', $value);
		return $this;
	}

	function getSessionKey($vars = null)
	{
		if (!property_exists($this, 'session_key')) {
			return null;
		}
		return $this->sanitize('session_key', $this->session_key);
	}

	static function getBySessionKey($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE session_key='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}

	function setUserId($value)
	{
		self::set($this, 'user_id', $value);
		return $this;
	}

	function getUserId($vars = null)
	{
		if (!property_exists($this, 'user_id')) {
			return null;
		}
		$value = $this->sanitize('user_id', $this->user_id);
		if ($vars) {
			$value = number_format($this->user_id);
		}
		return $value;
	}

	static function getByUserId($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE user_id=".round(preg_replace('/[^-.0-9]/', '', $value))."";
		return self::selectAll($sql, $first_only);
	}

	function setUsername($value)
	{
		self::set($this, 'username', $value);
		return $this;
	}

	function getUsername($vars = null)
	{
		if (!property_exists($this, 'username')) {
			return null;
		}
		return $this->sanitize('username', $this->username);
	}

	static function getByUsername($value, $first_only = false)
	{
		if (strlen($value) == 0)
		{
			return [];
		}
		$sql = "SELECT * FROM ".static::TABLE_NAME." WHERE username='".str_replace("'", "''", $value)."'";
		return self::selectAll($sql, $first_only);
	}
}