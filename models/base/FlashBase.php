<?php

require_once SLEDGEMC_PATH.'/models/Ids.php';

class FlashBase extends Ids
{
	const CLASS_NAME = 'Flash';
	const TABLE_NAME = 'flash';

	var $id = null;
	var $deleted = null;
	var $created = null;
	var $modified = null;
	var $user_id = null;
	var $session_id = null;
	var $message = null;

	function getChildren()
	{
		return [];
	}

	function toArray()
	{
		return [
			'id' => $this->id,
			'deleted' => $this->deleted,
			'created' => $this->created,
			'modified' => $this->modified,
			'user_id' => $this->user_id,
			'session_id' => $this->session_id,
			'message' => $this->message,
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
			'session_id' => ['bigint', 20],
			'message' => ['text'],
		];
		return $columns[$name];
	}
}
