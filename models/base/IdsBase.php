<?php

require_once SLEDGEMC_PATH.'/models/base/GettersAndSetters.php';

class IdsBase extends GettersAndSetters
{
	const CLASS_NAME = 'Ids';
	const TABLE_NAME = 'ids';

	var $id = null;
	var $deleted = null;
	var $created = null;
	var $modified = null;

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
		];
	}

	function getColumnType($name)
	{
		$columns = [
			'id' => ['bigint', 20],
			'deleted' => ['character', 1],
			'created' => ['timestamp without time zone'],
			'modified' => ['timestamp without time zone'],
		];
		return $columns[$name];
	}
}
