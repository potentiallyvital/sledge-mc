<?php

require_once SLEDGEMC_PATH.'/models/Ids.php';

class SessionsBase extends Ids
{
	const CLASS_NAME = 'Sessions';
	const TABLE_NAME = 'sessions';

	var $id = null;
	var $deleted = null;
	var $created = null;
	var $modified = null;
	var $user_id = null;
	var $session_key = null;
	var $data = null;

	function getChildren()
	{
		return [
			'flash' => 'session_id',
			'session_attributes' => 'session_id',
		];
	}

	function toArray()
	{
		return [
			'id' => $this->id,
			'deleted' => $this->deleted,
			'created' => $this->created,
			'modified' => $this->modified,
			'user_id' => $this->user_id,
			'session_key' => $this->session_key,
			'data' => $this->data,
		];
	}

	function getColumnType($name)
	{
		$columns = [
			'id' => ['bigint', 20],
			'deleted' => ['character', 1],
			'created' => ['timestamp without time zone'],
			'modified' => ['timestamp without time zone'],
			'user_id' => ['bigint', 20],
			'session_key' => ['character varying', 255],
			'data' => ['text'],
		];
		return $columns[$name];
	}
}
