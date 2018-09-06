<?php

/**
 * build ORM classes
 */
class SledgeMCHammer
{
        /**
         * how do you want to do tabs?
         */
        const TAB = "\t";

        /**
         * how do you want to do EOLs?
         */
        const EOL = "\r\n";

        /**
         * include x tabs
         */
        static function tab($times = 1)
        {
                $tab = self::TAB;
                for ($i=1; $i<$times; $i++)
                {
                        $tab .= self::TAB;
                }

                return $tab;
        }

        /**
         * include end of line
         */
        static function eol($times = 1)
        {
                $eol = self::EOL;
                for ($i=1; $i<$times; $i++)
                {
                        $eol .= self::EOL;
                }

                return $eol;
        }

        /**
         * initialize a database for Sledge
         */
        static function initialize()
        {
                $tables = [
                        'ids' => [
                                'id' => ['bigserial'],
                                'deleted' => ['char(1)'],
                                'created' => ['timestamp without time zone'],
                                'modified' => ['timestamp without time zone'],
                        ],
                        'users' => [
                                'name' => ['varchar(100)', true],
                                'username' => ['varchar(40)', true],
                                'password' => ['varchar(255)'],
                                'email' => ['varchar(255)', true],
                        ],
                        'sessions' => [
                                'user_id' => ['bigint', true],
                                'session_key' => ['varchar(255)', true],
                                'data' => ['text'],
                        ],
                        'flash' => [
                                'user_id' => ['bigint', true],
                                'session_id' => ['bigint', true],
                                'message' => ['text'],
                        ],
                        'attributes' => [
                                'name' => ['varchar(50)', true],
                                'value_decimal' => ['decimal(25,3)'],
                                'value_text' => ['text'],
                        ],
                ];

                foreach ($tables as $table_name => $columns)
                {
                        $indexes = [];
                        $indexes[] = 'id';

                        $column_sql = [];
                        foreach ($columns as $column_name => $column_config)
                        {
                                if (!empty($column_config[0]))
                                {
                                        $column_sql[] = $column_name.' '.$column_config[0];
                                }

                                if (!empty($column_config[1]))
                                {
                                        $indexes[] = $column_name;
                                }
                        }
                        $column_sql = implode(', ', $column_sql);

                        $sql = "CREATE TABLE $table_name ($column_sql)";
                        if ($table_name != 'ids')
                        {
                                $sql .= " INHERITS (ids)";
                        }

                        self::query($sql);

                        foreach ($indexes as $column_name)
                        {
                                $sql = "CREATE INDEX {$table_name}_{$column_name}_index ON $table_name ($column_name)";

                                self::query($sql);
                        }
                }
        }

        /**
         * build the files
         */
        static function build()
        {
                $tables = self::getTables();
                $children = self::getChildren();
                foreach ($tables as $table => $columns)
                {
                        $cc_table = self::camelCase($table);
                        if (!file_exists(SLEDGEMC_PATH.'/models/'.$cc_table.'.php'))
                        {
                                self::saveFile(SLEDGEMC_PATH.'/models/'.$cc_table.'.php', self::getClass($table));
                        }
                        if (!file_exists(SLEDGEMC_PATH.'/models/helpers/'.$cc_table.'Helper.php'))
                        {
                                self::saveFile(SLEDGEMC_PATH.'/models/helpers/'.$cc_table.'Helper.php', self::getHelperClass($table));
                        }
                        self::saveFile(SLEDGEMC_PATH.'/models/base/'.$cc_table.'Base.php', self::getBaseClass($table, $columns, $children));
                }

                $columns = self::getColumns();
                self::saveFile(SLEDGEMC_PATH.'/models/base/GettersAndSetters.php', self::getGettersAndSettersClass($columns));
        }

        /**
         * save a file
         */
        static function saveFile($path, $php)
        {
                file_put_contents($path, $php);
        }

        /**
         * get all tables and columns from the database
         */
        static function getTables()
        {
                $tables = [];
                $sql = "SELECT * FROM information_schema.tables WHERE table_schema='public'";
                $link = self::query($sql);
                while ($row = self::query($sql, $link))
                {
                        $tables[$row['table_name']] = [];
                }
                foreach ($tables as $table => $columns)
                {
                        $sql = "SELECT * FROM information_schema.columns WHERE table_name = '$table'";
                        $link = self::query($sql);
                        while ($row = self::query($sql, $link))
                        {
                                $name = strtolower($row['column_name']);
                                $type = $row['data_type'];
                                $config = (!empty($type_parts) ? str_replace(')', '', array_pop($type_parts)) : 'null');
                                switch ($type)
                                {
                                        case 'smallint':
                                                $config = '5';
                                                break;
                                        case 'integer':
                                                $config = '11';
                                                break;
                                        case 'bigint':
                                                $config = '20';
                                                break;
                                        case 'numeric':
                                                $config = $row['numeric_precision'].', '.$row['numeric_scale'];
                                                break;
                                        case 'text':
                                        case 'bytea':
                                        case 'date':
                                        case 'timestamp without time zone':
                                                $config = '';
                                                break;
                                        case 'character':
                                        case 'character varying':
                                                $config = $row['character_maximum_length'];
                                                break;
                                        default:
                                                die("UNKNOWN COLUMN TYPE '$type' IN ".__FILE__." LINE ".__LINE__."\r\n");
                                }
                                $tables[$table][] = ['name' => $name, 'type' => $type, 'config' => $config];
                        }
                }

                return $tables;
        }

        /**
         * get all column names and types from the database
         */
        static function getColumns()
        {
                $columns = [];
                $sql = "SELECT column_name AS name, data_type AS type
                        FROM information_schema.columns
                        WHERE table_schema = 'public'
                        GROUP BY column_name, data_type
                        ORDER BY column_name, data_type";
                $link = self::query($sql);
                while ($row = self::query($sql, $link))
                {
                        $columns[$row['name']] = array_shift(explode('(', $row['type']));
                }

                return $columns;
        }

        /**
         * get foreign key relationships for children
         */
        static function getChildren()
        {
                $children = [];
                $sql = "SELECT column_name AS name, data_type AS type, table_name
                        FROM information_schema.columns
                        WHERE table_schema = 'public' AND column_name LIKE '%_id'
                        GROUP BY column_name, data_type, table_name
                        ORDER BY column_name";
                $link = self::query($sql);
                while ($row = self::query($sql, $link))
                {
                        $column = $row['name'];
                        $table = $row['table_name'];
                        if ($column != rtrim($table, 's').'_id') {
                                $children[$column][] = $table;
                        }
                }

                return $children;
        }

        /**
         * get the name of the table that a table inherits
         */
        static function getParentTable($table_name)
        {
                $sql = "SELECT parent.relname AS table_name
                        FROM pg_class parent
                        JOIN pg_inherits inherits ON inherits.inhparent = parent.relfilenode
                        JOIN pg_class child ON child.relfilenode = inherits.inhrelid
                        WHERE child.relname = '$table_name'";
                $link = self::query($sql);
                $parent = self::query($sql, $link);
                if ($parent)
                {
                        return $parent['table_name'];
                }
        }

        /**
         * get results from the database
         */
        static function query($sql, $result = null)
        {
                $row = true;
                if (!$result)
                {
                        $link = pg_connect('host='.SLEDGEMC_HOST.' dbname='.SLEDGEMC_NAME.' user='.SLEDGEMC_USER.' password='.SLEDGEMC_PASS);
                        $result = pg_query($link, $sql);

                        return $result;
                }
                if ($result !== true && $result !== false)
                {
                        $row = pg_fetch_assoc($result);
                }

                return $row;
        }

        /**
         * camelcase a string
         */
        static function camelCase($string)
        {
                $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
                $string = ucwords(trim($string));
                $string = lcfirst(str_replace(' ', '', $string));
                $string = ucwords($string);

                return $string;
        }

        /**
         * uncamelcase a string
         */
        static function unCamelCase($string)
        {
                preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
                $strings = $matches[0];
                foreach ($strings as &$match)
                {
                        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
                }

                return implode('_', $strings);
        }

        /**
         * construct php for a wORM class
         */
        static function getClass($table)
        {
                $cc_table = self::camelCase($table);
                $php = '<?php';
                $php .= self::eol(2);
                $php .= 'require_once SLEDGEMC_PATH.\'/models/helpers/'.$cc_table.'Helper.php\';'.self::eol();
                $php .= self::eol();
                $php .= 'class '.$cc_table.' extends '.$cc_table.'Helper'.self::eol();
                $php .= '{';
                $php .= self::eol(2);
                $php .= '}';

                return $php;
        }

        /**
         * construct php for a wORM base class
         */
        static function getBaseClass($table, $columns, $children)
        {
                $parent_table = self::getParentTable($table);
                $parentClass = ($parent_table ? self::camelCase($parent_table) : 'GettersAndSetters');

                $parentClass = 'GettersAndSetters';
                $require = "base/GettersAndSetters.php";
                if ($parent_table)
                {
                        $parentClass = self::camelCase($parent_table);
                        $require = $parentClass.'.php';
                }

                $cc_table = self::camelCase($table);
                $foreign_key = substr($table, 0, -1).'_id';
                $php = '';
                $php .= '<?php';
                $php .= self::eol(2);
                $php .= 'require_once SLEDGEMC_PATH.\'/models/'.$require.'\';'.self::eol();
                $php .= self::eol();
                $php .= 'class '.$cc_table.'Base extends '.$parentClass.''.self::eol();
                $php .= '{'.self::eol();
                $php .= self::tab().'const CLASS_NAME = \''.$cc_table.'\';'.self::eol();
                $php .= self::tab().'const TABLE_NAME = \''.$table.'\';'.self::eol();
                $php .= self::eol();
                foreach ($columns as $column)
                {
                        $php .= self::tab().'var $'.$column['name'].' = null;'.self::eol();
                }
                $php .= self::eol();
                $php .= self::tab().'function getChildren()'.self::eol();
                $php .= self::tab().'{'.self::eol();
                if (empty($children[$foreign_key]))
                {
                        $php .= self::tab(2).'return [];'.self::eol();
                }
                else
                {
                        $php .= self::tab(2).'return ['.self::eol();
                        foreach ($children[$foreign_key] as $foreign_table)
                        {
                                $php .= self::tab(3).'\''.$foreign_table.'\' => \''.$foreign_key.'\','.self::eol();
                        }
                        $php .= self::tab(2).'];'.self::eol();
                }
                $php .= self::tab().'}'.self::eol();
                $php .= self::eol();
                $php .= self::tab().'function toArray()'.self::eol();
                $php .= self::tab().'{'.self::eol();
                $php .= self::tab(2).'return ['.self::eol();
                foreach ($columns as $column)
                {
                        $php .= self::tab(3).'\''.$column['name'].'\' => $this->'.$column['name'].','.self::eol();
                }
                $php .= self::tab(2).'];'.self::eol();
                $php .= self::tab().'}'.self::eol();
                $php .= self::eol();
                $php .= self::tab().'function getColumnType($name)'.self::eol();
                $php .= self::tab().'{'.self::eol();
                $php .= self::tab(2).'$columns = ['.self::eol();
                foreach ($columns as $column)
                {
                        $php .= self::tab(3).'\''.$column['name'].'\' => [\''.$column['type'].'\''.($column['config'] ? ', '.$column['config'] : '').'],'.self::eol();
                }
                $php .= self::tab(2).'];'.self::eol();
                $php .= self::tab(2).'return $columns[$name];'.self::eol();
                $php .= self::tab().'}'.self::eol();
                $php .= '}';

                return $php;
        }

        /**
         * construct php for wORM helper class
         */
        static function getHelperClass($table)
        {
                $cc_table = self::camelCase($table);
                $php = '<?php';
                $php .= self::eol(2);
                $php .= 'require_once SLEDGEMC_PATH.\'/models/base/'.$cc_table.'Base.php\';'.self::eol();
                $php .= self::eol();
                $php .= 'class '.$cc_table.'Helper extends '.$cc_table.'Base'.self::eol();
                $php .= '{';
                $php .= self::eol(2);
                $php .= '}';

                return $php;
        }

        /**
         * build the getters and setters file
         */
        static function getGettersAndSettersClass($columns)
        {
                $php = '<?php';
                $php .= self::eol(2);
                $php .= 'require_once SLEDGEMC_PATH.\'/models/base/Base.php\';'.self::eol();
                $php .= self::eol();
                $php .= 'class GettersAndSetters extends Base'.self::eol();
                $php .= '{';
                foreach ($columns as $column => $type)
                {
                        $php .= self::getSetFunction($column, $type);
                        $php .= self::getGetFunction($column, $type);
                        $php .= self::getGetByFunction($column, $type);
                }
                $php .= '}';

                return $php;
        }

        /**
         * get php for setting a column
         */
        static function getSetFunction($column, $type)
        {
                $php = self::eol();
                $php .= self::tab().'function set'.self::camelCase($column).'($value)'.self::eol();
                $php .= self::tab().'{'.self::eol();
                switch ($type)
                {
                        case 'datetime':
                        case 'timestamp':
                                $php .= self::tab(2).'if (is_numeric($value)) {'.self::eol();
                                $php .= self::tab(3).'$value = $this->smartTime($value, \'Y-m-d H:i:s\');'.self::eol();
                                $php .= self::tab(2).'}'.self::eol();
                                break;
                        case 'date':
                                $php .= self::tab(2).'if (is_numeric($value)) {'.self::eol();
                                $php .= self::tab(3).'$value = $this->smartTime($value, \'Y-m-d\');'.self::eol();
                                $php .= self::tab(2).'}'.self::eol();
                                break;
                }
                $php .= self::tab(2).'self::set($this, \''.$column.'\', $value);'.self::eol();
                $php .= self::tab(2).'return $this;'.self::eol();
                $php .= self::tab().'}'.self::eol();

                return $php;
        }

        /**
         * build php for get column
         */
        static function getGetFunction($column, $type)
        {
                $php = self::eol();
                $php .= self::tab().'function get'.self::camelCase($column).'($vars = null)'.self::eol();
                $php .= self::tab().'{'.self::eol();
                $php .= self::tab(2).'if (!property_exists($this, \''.$column.'\')) {'.self::eol();
                $php .= self::tab(3).'return null;'.self::eol();
                $php .= self::tab(2).'}'.self::eol();
                switch ($type)
                {
                        case 'datetime':
                        case 'timestamp':
                                $php .= self::tab(2).'if ($vars == \'\') {'.self::eol();
                                $php .= self::tab(3).'$vars = \'Y-m-d H:i:s\';'.self::eol();
                                $php .= self::tab(2).'}'.self::eol();
                                $php .= self::tab(2).'return $this->smartTime($this->'.$column.', $vars);'.self::eol();
                                break;
                        case 'date':
                                $php .= self::tab(2).'if ($vars == \'\') {'.self::eol();
                                $php .= self::tab(3).'$vars = \'Y-m-d\';'.self::eol();
                                $php .= self::tab(2).'}'.self::eol();
                                $php .= self::tab(2).'return $this->smartTime($this->'.$column.', $vars);'.self::eol();
                                break;
                        case 'tinyint':
                        case 'smallint':
                        case 'mediumint':
                        case 'int':
                        case 'bigint':
                                $php .= self::tab(2).'$value = $this->sanitize(\''.$column.'\', $this->'.$column.');'.self::eol();
                                $php .= self::tab(2).'if ($vars) {'.self::eol();
                                $php .= self::tab(3).'$value = number_format($this->'.$column.');'.self::eol();
                                $php .= self::tab(2).'}'.self::eol();
                                $php .= self::tab(2).'return $value;'.self::eol();
                                break;
                        default:
                                $php .= self::tab(2).'return $this->sanitize(\''.$column.'\', $this->'.$column.');'.self::eol();
                                break;
                }
                $php .= self::tab().'}'.self::eol();

                return $php;
        }

        /**
         * build php for getByColumn
         */
        static function getGetByFunction($column, $type)
        {
                // getByColumn()
                $first_only = ($column == 'id' ? 'true' : 'false');
                $replace = '".str_replace("\'", "\'\'", $value)."';

                $clean = $replace;
                switch ($type)
                {
                        case 'integer':
                        case 'bigint':
                                $clean = '".round(preg_replace(\'/[^-.0-9]/\', \'\', $value))."';
                                break;
                        case 'numeric':
                                $clean = '".preg_replace(\'/[^-.0-9]/\', \'\', $value)."';
                                break;
                        default:
                                $clean = '\'".str_replace("\'", "\'\'", $value)."\'';
                                break;
                }

                $php = self::eol();
                $php .= self::tab().'static function getBy'.self::camelCase($column).'($value, $first_only = '.$first_only.', $ignore_deleted = true)'.self::eol();
                $php .= self::tab().'{'.self::eol();
                $php .= self::tab(2).'if (strlen($value) == 0)'.self::eol();
                $php .= self::tab(2).'{'.self::eol();
                $php .= self::tab(3).'return [];'.self::eol();
                $php .= self::tab(2).'}'.self::eol();
                $php .= self::tab(2).'$class = get_called_class();'.self::eol();
                $php .= self::tab(2).'$object = new $class();'.self::eol();
                $php .= self::tab(2).'$sql = "SELECT * FROM ".$object->table_config[\'table\']." WHERE '.$column.' = '.$clean.'";'.self::eol();
                $php .= self::tab(2).'return self::selectAll($sql, $first_only, $ignore_deleted);'.self::eol();
                $php .= self::tab().'}'.self::eol();

                return $php;
        }
}
