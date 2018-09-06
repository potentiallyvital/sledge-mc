<?php

/**
 * base class for database objects
 * all database classes will extend this class
 * do basic functions like save, delete, set, etc
 */
class Base extends Controller
{
        protected $table_config = [];
        protected $attribute_config = [];

        protected $modified_columns = [];
        protected $attributes = [];

        /**
         * constructor function
         * @param   $params mixed   - int or array of values
         *                          - if int, return self::getById($id)
         *                          - if array, save new object with values
         */
        function __construct($params = null)
        {
                parent::__construct($params);

                if (!empty($params))
                {
                        if (is_numeric($params))
                        {
                                $this->id = $params;
                                $this->load();
                        }
                        elseif (is_array($params))
                        {
                                foreach ($params as $key => $value)
                                {
                                        self::set($this, $key, $value);
                                }
                                $this->save();
                        }
                }

                $this->table_config['table'] = static::TABLE_NAME;
                $this->table_config['key'] = substr(static::TABLE_NAME, 0, -1).'_id';
                $this->table_config['setId'] = 'set'.substr(str_replace(' ', '', ucwords(str_replace('_', ' ', static::TABLE_NAME))), 0, -1).'Id';
                $this->table_config['getById'] = 'getBy'.substr(str_replace(' ', '', ucwords(str_replace('_', ' ', static::TABLE_NAME))), 0, -1).'Id';
                $this->attribute_config['table'] = substr(static::TABLE_NAME, 0, -1).'_attributes';
                $this->attribute_config['class'] = substr(static::CLASS_NAME, 0, -1).'Attributes';
        }

        /**
         * sanitize a value before saving
         */
        function sanitize($column, $value)
        {
                if ($value === null)
                {
                        return $value;
                }

                $type = $this->getColumnType($column);
                switch ($type[0])
                {
                        case 'tinyint':
                        case 'smallint':
                        case 'mediumint':
                        case 'int':
                        case 'integer':
                        case 'bigint':
                        case 'numeric':
                                $sanitized = preg_replace('/[^-.0-9]/', '', $value);
                                if (strlen($sanitized) == 0)
                                {
                                        $sanitized = 0;
                                }
                                break;
                        case 'date':
                        case 'datetime':
                        case 'timestamp':
                        case 'timestamp without time zone':
                                $sanitized = preg_replace('/[^-0-9 :]/', '', $value);
                                break;
/*
                        case 'character':
                        case 'character varying':
                                $sanitized = preg_replace('/[^-_,.a-zA-Z0-9 ~!@#$%^&*()+=|]:;/', '', $value);
                                break;
*/
                        case 'enum':
                                $sanitized = (in_array($value, $type[1]) ? $value : null);
                                break;
                        case 'blob':
                        case 'text':
                                $sanitized = $value;
                                break;
                        default:
                                $sanitized = $value;
                                break;
                }

                if (!empty($type[1]) && is_numeric($type[1]) && $type[1] > 0)
                {
                        if (substr($sanitized, 0, 1) == '-')
                        {
                                $type[1]++;
                        }
                        $sanitized = substr($sanitized, 0, $type[1]);
                }

                return $sanitized;
        }

        /**
         * whether to allow HTML being saved in this field
         */
        function allowHtml($field = string)
        {
                return false;
        }

        /**
         * called just before saving an objects data
         */
        function beforeSave()
        {
                if (array_key_exists('slug', $this->toArray()) && empty($this->slug))
                {
                        $this->setSlug(slugify($this->name));
                }
        }

        /**
         * called just after saving an objects data
         */
        function afterSave()
        {
        }

        /**
         * called just before inserting a new record
         */
        function beforeCreate()
        {
                if (empty($this->created))
                {
                        $this->setCreated(date('Y-m-d H:i:s'));
                }
                if (array_key_exists('deleted', $this->toArray()) && empty($this->deleted))
                {
                        $this->setDeleted('n');
                }
                if (array_key_exists('read', $this->toArray()) && empty($this->read))
                {
                        $this->setRead('n');
                }
        }

        /**
         * called just after inserting a new record
         */
        function afterCreate()
        {
        }

        /**
         * called just before deleting a record
         */
        function beforeDelete()
        {
        }

        /**
         * called just after deleting a record
         */
        function afterDelete()
        {
        }

        /**
         * delete all of a records children
         * called just after afterDelete
         */
        function deleteChildren()
        {
                foreach ($this->getChildren() as $child_table => $foreign_key)
                {
                        $childClass = str_replace(' ', '', ucwords(str_replace('_', ' ', $child_table)));

                        $sql = "SELECT * FROM $child_table WHERE $foreign_key = $this->id";
                        $children = $childClass::selectAll($sql);
                        foreach ($children as $child)
                        {
                                $child->delete();
                        }
                }
        }

        /**
         * delete a record from the database
         * set to deleted and do not delete if column exists
         */
        function delete($hard = false)
        {
                if ($this->id !== null)
                {
                        $this->beforeDelete();

                        if (!$hard && array_key_exists('deleted', $this->toArray()))
                        {
                                $this->setDeleted('y')->save();
                        }
                        else
                        {
                                $this->hardDelete();
                        }

                        $this->afterDelete();
                        $this->deleteChildren();
                }
        }

        /**
         * remove a record from the database
         */
        function hardDelete()
        {
                $sql = "DELETE FROM ".$this->table_config['table']." WHERE id = $this->id";
                $this->execute($sql);
        }

        /**
         * save updated values to the database if
         * necessary. do not save if values have not
         * changed, unless $force is true
         */
        function save($force = false)
        {
                $id = $this->getUseId();
                $new = (!is_numeric($id) ? true : false);

                if ($new)
                {
                        $this->beforeCreate();
                        $this->insertNew($id);
                        $this->beforeSave();
                        $this->doSave();
                        $this->afterCreate();
                        $this->afterSave();
                }
                else
                {
                        $result = $this->beforeSave();
                        if (!$force && $result === false)
                        {
                                return $this;
                        }
                        if ($force || $this->isModified())
                        {
                                $this->doSave();
                                $this->afterSave();
                        }
                }

                return $this;
        }

        /**
         * check if i've been updated
         */
        function isModified()
        {
                return $this->modified_columns;
        }

        /**
         * get the id to use for saving
         * if someone wants to change an id
         * let me do it
         */
        function getUseId()
        {
                if (isset($this->original_id))
                {
                        if ($this->original_id != $this->id)
                        {
                                $sql = "SELECT * FROM ".$this->table_config['table']." WHERE id = $this->id";
                                $results = self::selectAll($sql);
                                if (!empty($results))
                                {
                                        $this->setId($this->original_id);

                                        throw new Exception('that id is already taken');
                                }

                                return $this->original_id;
                        }
                }
                return $this->id;
        }

        /**
         * insert a new record into the database
         */
        function insertNew($id = null)
        {
                $date = date('Y-m-d H:i:s');
                if ($id)
                {
                        $sql = "INSERT INTO ".$this->table_config['table']." (id) VALUES ($id) RETURNING id";
                }
                else
                {
                        $sql = "INSERT INTO ".$this->table_config['table']." DEFAULT VALUES RETURNING id";
                }
                $link = pg_connect('host='.SLEDGEMC_HOST.' dbname='.SLEDGEMC_NAME.' user='.SLEDGEMC_USER.' password='.SLEDGEMC_PASS);
                $result = pg_fetch_array(pg_query($link, $sql));
                $id = $result['id'];
                $this->setId($id);
        }

        /**
         * get sql for update statement
         */
        function getColumnSql($modified_only = false)
        {
                $columns = [];
                $values = $this->toArray();
                foreach ($values as $key => $value)
                {
                        if ($modified_only && !in_array($key, $this->modified_columns))
                        {
                                continue;
                        }
                        $column = $this->getColumnType($key);
                        switch ($column[0])
                        {
                                case 'integer':
                                case 'bigint':
                                case 'smallint':
                                        $clean = preg_replace('/[^-0-9]/', '', $value);
                                        if (strlen($clean) == 0)
                                        {
                                                $clean = 0;
                                        }
                                        break;
                                case 'numeric':
                                        $clean = round($value, $column[2]);
                                        break;
                                case 'timestamp':
                                case 'timestamp without time zone':
                                        if ($value)
                                        {
                                                $clean = "'".date('Y-m-d H:i:s', strtotime($value))."'";
                                        }
                                        else
                                        {
                                                $clean = 'NULL';
                                        }
                                        break;
                                case 'character':
                                case 'character varying':
                                case 'text':
                                default:
                                        if (empty($this->html_fields) || !in_array($key, $this->html_fields))
                                        {
                                                $value = htmlspecialchars($value);
                                        }
                                        $clean = "'".str_replace("'", "''", $value)."'";
                                        break;
                        }
                        $columns[] = "$key=$clean";
                }
                return implode(',',$columns);
        }

        /**
         * save values to the database
         */
        function doSave()
        {
                $this->setModified(date('Y-m-d H:i:s'));

                $columns = $this->getColumnSql(true);

                if ($columns)
                {
                        $sql = "UPDATE ".$this->table_config['table']." SET $columns WHERE id = $this->id";
                        $this->execute($sql);
                }

                $this->modified_columns = [];
        }

        /**
         * execute a database query
         */
        function execute($sql)
        {
                $link = pg_connect('host='.SLEDGEMC_HOST.' dbname='.SLEDGEMC_NAME.' user='.SLEDGEMC_USER.' password='.SLEDGEMC_PASS);
                return pg_query($link, $sql);
        }

        /**
         * retrieve objects from the database
         * usage:
         *   $link = Users::stream($sql);
         *   while ($user = Users::stream($link)) {
         *      $user->doSomething();
         *   }
         */     
        static function stream($sql, $result = null)
        {
                if (!$result)
                {
                        $link = pg_connect('host='.SLEDGEMC_HOST.' dbname='.SLEDGEMC_NAME.' user='.SLEDGEMC_USER.' password='.SLEDGEMC_PASS);
                        $result = pg_query($link, $sql);
                        return $result;
                }
                if ($result !== true && $result !== false)
                {
                        $result = pg_fetch_assoc($result);
                        if ($result)
                        {
                                $class = static::CLASS_NAME;
                                $object = new $class();
                                foreach ($result as $key => $value)
                                {
                                        $object->$key = $value;
                                }
                                return $object;
                        }
                }
        }

        /**
         * return the first object from the database matching $sql
         */
        static function selectOne($sql = '')
        {
                return self::selectAll($sql, true);
        }

        /**
         * return all objects of the database matching $sql
         * if $sql is not specified, all objects in the table will
         * be returned
         */
        static function selectAll($sql = '', $first_only = false, $include_deleted = false)
        {
                if (!$sql)
                {
                        $sql = "SELECT * FROM ".static::TABLE_NAME;
                }

                if ($first_only && $first_only !== true)
                {
                        $sql .= " ORDER BY $first_only";
                }

                $objects = [];
                $link = self::stream($sql);
                while ($object = self::stream($sql, $link))
                {
                        if (!$include_deleted && $object->deleted == 'y')
                        {
                                continue;
                        }
                        if ($first_only === true)
                        {
                                return $object;
                        }
                        $objects[] = $object;
                }
                return $objects;
        }

        /**
         * truncate a table
         * if $quick a simple TRUNCATE {table] will be called
         * if not, each row will use the classes to delete data
         * children will also be deleted
         */
        static function truncate($quick = false)
        {
                if (!$quick)
                {
                        $sql = "SELECT * FROM ".static::TABLE_NAME;
                        $link = self::stream($sql);
                        while ($object = self::stream($sql, $link))
                        {
                                $object->delete();
                        }
                }

                $sql = "TRUNCATE ".static::TABLE_NAME;
                $this->execute($sql);
        }

        /**
         * load up values for a record from the database
         */
        function load()
        {
                $sql = "SELECT * FROM ".$this->table_config['table']." WHERE id = $this->id";
                $results = self::selectOne($sql);
                if ($results)
                {
                        $values = get_object_vars($results);
                        foreach ($values as $key => $value)
                        {
                                $this->$key = $value;
                        }
                }
        }

        /**
         * base setter function
         */
        static function set($object, $column, $value)
        {
                if (!property_exists($object, $column))
                {
                        return;
                }
                $value = $object->sanitize($column, $value);
                if ($object->$column !== $value)
                {
                        $object->modified_columns[$column] = $column;
                        $object->$column = $value;
                }
                return $object;
        }

        /**
         * match this object values from another
         */
        function cloneFrom($object, $include_id = false)
        {
                $array = $object->toArray();
                if (!$include_id)
                {
                        unset($array['id']);
                }
                foreach ($array as $key => $value)
                {
                        $set = 'set'.ucwords(slugify($key));
                        $this->$set($value);
                }
        }

        /**
         * match this object values to another
         */
        function cloneTo($object, $include_id = false)
        {
                $array = $this->toArray();
                if (!$include_id)
                {
                        unset($array['id']);
                }
                foreach ($array as $key => $value)
                {
                        $object->$key = $value;
                }
        }

        /**
         * get the class name of this table
         * ex. $class = $anyObject->getClass();
         */
        function getClass()
        {
                return static::CLASS_NAME;
        }

        /**
         * check if a column has been modified
         */
        function hasModified($column)
        {
                return in_array($column, $this->modified_columns);
        }

        /**
         * row attribute handling
         */
        function getDefaultAttributes()
        {
                return [];
        }
        function increment($attribute, $by = 1)
        {
                if ($by != 0)
                {
                        $this->setAttribute($attribute, round($this->getAttribute($attribute)+$by, 3));
                }
        }
        function decrement($attribute, $by = 1)
        {
                if ($by != 0)
                {
                        $this->setAttribute($attribute, round($this->getAttribute($attribute)-$by, 3));
                }
        }
        function removeAttribute($name)
        {
                $class = $this->attribute_class;

                $attribute = $class::selectOne("SELECT * FROM ".$this->attribute_config['table']." WHERE ".$this->table_config['key']." = $this->id AND name = '$name'");
                if ($attribute)
                {
                        $attribute->hardDelete();

                        unset($this->attributes[$name]);
                }
        }
        function hasAttribute($name)
        {
                $this->getAttributes();

                return isset($this->attributes[$name]);
        }
        function getAttribute($name)
        {
                $this->getAttributes();

                return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
        }
        function getArrayAttribute($name)
        {        
                $value = $this->getAttribute($name);

                if ($value)
                {
                        return json_decode($value, true);
                }
                else
                {
                        return [];
                }
        }
        function setAttribute($name, $value)
        {
                $class = $this->attribute_class;
                $setKey = $this->set_table_key;

                $sql = "SELECT * FROM ".$this->attribute_config['table']." WHERE ".$this->table_config['key']." = $this->id AND name = '$name'";

                $attribute = $class::selectOne($sql);
                if (!$attribute)
                {
                        $attribute = new $class();
                        $attribute->setName($name);
                        $attribute->$setKey($this->id);
                }
                if (is_numeric($value))
                {
                        $attribute->setValueDecimal($value);
                        $attribute->setValueText('');
                }
                else
                {
                        $attribute->setValueText($value);
                        $attribute->setValueDecimal(0);
                }

                $this->attributes[$name] = $value;
        }
        function setArrayAttribute($name, $value)
        {
                $value = json_encode($value ?: []);

                $this->setAttribute($name, $value);
        }
        function getAttributes()
        {
                if (empty($this->attributes))
                {
                        $class = $this->attribute_class;
                        $getBy = $this->get_by_table_key;

                        $this->attributes = [];

                        $attributes = $class::$getBy($this->id, false, 'name');
                        foreach ($attributes as $attribute)
                        {
                                switch ($attribute->name)
                                {
                                        case 'text-only-field':
                                                $this->attributes[$attribute->name] = $attribute->value_text;
                                                break;
                                        default:
                                                $this->attributes[$attribute->name] = ($attribute->value_text ?: $attribute->value_decimal ?: 0);
                                                break;
                                }
                        }
                }

                return $this->attributes;
        }
        function defaultAttributes($attributes = [])
        {
                foreach ($attributes as $name => $value)
                {
                        $this->setAttribute($name, $value);
                }
        }
        static function getByAttribute($name, $value = null, $first_only = false)
        {
                $class = get_class($this);
                $object = new $class();

                $sql = "SELECT me.*
                        FROM ".$object->table_config['key']." me
                        JOIN ".$object->attribute_config['table']." attr ON attr.".$object->table_config['key']." = me.id
                        WHERE attr.name = '$name'";
                if ($value)
                {
                        if (is_numeric($value))
                        {
                                $sql .= " AND (attr.value_decimal = $value OR attr.value_text = '$value')";
                        }
                        else
                        {
                                $sql .= " AND attr.value_text = '$value'";
                        }
                }
                if ($first_only)
                {
                        return self::selectOne($sql);
                }
                else
                {
                        return self::selectAll($sql);
                }
        }

        static function getIdNameArray($name_column = 'name')
        {
                $array = [];
                $objects = self::selectAll(null, $name_column);
                foreach ($objects as $object)
                {
                        $array[$object->id] = $object->$name_column;
                }
                return $array;
                
        }
}
