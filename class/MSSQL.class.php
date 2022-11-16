<?php

    class MSSQL
    {
        private $host;
        private $user;
        private $pass;
        private $table;

        private $conn;

        private $tables;
        private $fields;
        private $params;
        private $columns;
        private $ps;
        private $values;
        private $filters;
        private $group;
        private $query;
        private $order;
        private $distinct;
        private $top;
        private $p;

        private $response;
        private $dataTypes = [ "i" => "INT", "d" => "FLOAT", "s" => "VARCHAR(MAX)" ];
        private $type;

        public $last_id;

        public function __construct( $conn )
        {
            $this->host = $conn->host;
            $this->user = $conn->user;
            $this->pass = base64_decode($conn->pass);
            $this->table = $conn->table;
        }

        private function initParams()
        {
            $this->fields = [];
            $this->values = [];
            $this->params = [];
            $this->columns = [];
            $this->ps = [];
            $this->filters = "";
            $this->query = "";
            $this->group = "";
            $this->distinct = null;
            $this->order = "";
            $this->top = "";
            $this->p = 0;

            $this->response = NULL;
            $this->last_id = NULL;
        }

        private function openConn()
        {
            try
            {
                $this->conn = new COM ("ADODB.Connection");
                $this->conn->open( "PROVIDER=SQLOLEDB;SERVER={$this->host};UID={$this->user};PWD={$this->pass};DATABASE={$this->table}" );
				$this->conn->CommandTimeout = 120;
            }
            Catch( Exception $e )
            {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Erro ao conectar no banco de dados."
                ]);
            }
        }

        private function closeConn()
        {
            $this->conn->Close();
        }

        public function callSelect( $params )
        {
            $this->initParams();

            $this->tables = str_replace( "'", "''", implode( ( @$params->join ? " " : "," ), $params->tables ));
            $this->fields = @$params->fields ? str_replace( "'", "''", implode( ",", $params->fields )) : "*";

            if( @$params->filters ) $this->setFilters( $params->filters );
            if( @$params->group ) $this->group = $params->group;
            if( @$params->order ) $this->setOrder( $params->order );
            if( @$params->top ) $this->setTop( $params->top );
            if( @$params->distinct ) $this->distinct = 1;

            $this->query = "exec sp_executesql N'
                SELECT " . ( @$this->distinct ? "DISTINCT " : "" ) . ( @$this->top ? ( @$this->distinct ? " " : "" ) . "TOP {$this->top}" : "" ) . "
                    {$this->fields}
                FROM
                    {$this->tables}
                " . ( @$this->filters ? "WHERE {$this->filters}" : "" ) . "
                " . ( @$this->group ? "GROUP BY " . str_replace( "'", "''", $this->group ) : "" ) . "
                " . ( @$params->having ? "HAVING " . str_replace( "'", "''", $params->having ) : "" ) . "
                " . ( @$this->order ? "ORDER BY {$this->order}" : "" ) . "'
                " . ( @$this->params ? ",N'" . implode( ",", $this->params ) . "'," . implode( ",", $this->values ) : "" ) . "
            ";

            if( @$params->debug ) $this->debug($params);

            $this->type = "select";
            $this->callQuery();

            if( @$params->fields ) {
                $ret = [];
                foreach( $this->response as $response ){
                    $row = [];
                    foreach( $response as $key => $data ){
                        $object = explode( "=", $params->fields[$key] );
                        $object = str_replace( [" "], [""], $object[0] );
                        $object = explode( ".", $object );
                        $object = @$object[1] ? $object[1] : $object[0];
                        $row[$object] = $data;
                    }
                    $ret[] = (Object)$row;
                }
                $this->response = $ret;
            }

            return $this->response;
        }

        public function callInsert( $params )
        {
            $this->initParams();

            $this->setFields( $params->fields );

            $this->query = "exec sp_executesql N'
                INSERT INTO {$params->table}(
                    " . implode( ",", $this->columns ) . "
                ) VALUES (
                    " . implode( ",", $this->ps ) . "
                )',N'" . implode( ",", $this->params ) . "'," . implode( ",", $this->values ) . "
            ";

            if( @$params->debug ) $this->debug($params);

            $this->type = "insert";
            $this->callQuery();
        }

        public function callUpdate( $params )
        {
            $this->initParams();

            $this->setFields( $params->fields );
            $this->setFilters( $params->filters );

            if( !@$params->filters || !@$this->filters ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não será possivel realizar o update sem filtros."
                ]);
            }

            $this->query = "exec sp_executesql N'
                    UPDATE TOP(" . ( @$params->top ? (int)$params->top : 1 ) . ") {$params->table}
                    SET {$this->fields}
                    WHERE {$this->filters}
                    ',N'" . implode( ",", $this->params ) . "'," . implode( ",", $this->values ) . "
                ";

            if( @$params->debug ) $this->debug($params);

            $this->type = "update";
            $this->callQuery();
        }

        public function callDelete( $params )
        {
            $this->initParams();

            $this->setFilters( $params->filters );

            if( !@$params->filters || !@$this->filters ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não será possivel realizar o update sem filtros."
                ]);
            }

            $this->query = "exec sp_executesql N'
                    DELETE TOP(" . ( @$params->top ? $params->top : "1" ) . ")
                    FROM {$params->table}
                    WHERE {$this->filters}
                    ',N'" . implode( ",", $this->params ) . "'," . implode( ",", $this->values ) . "
                ";

            if( @$params->debug ) $this->debug($params);

            $this->type = "delete";
            $this->callQuery();
        }

        public function nextCode( $params )
        {
            $this->query = "exec sp_GetNextCode '{$params->table}','{$params->field}','{$params->increment}'";

            $this->type = "procedure";
            $this->callQuery();

            return @$this->response[0][0] ? $this->response[0][0] : NULL;
        }
		
		public function exec( $params )
        {
            $this->query = "exec {$params->procedure} '{$params->params1}'";

            $this->type = "procedure";
            $this->callQuery();
        }

        private function setFields( $fields )
        {
            $ret = [];
            foreach( $fields as $field ){
                if( empty($field[1]) ){
                    $ret[] = "{$field[0]}";
                } else if( is_null($field[2]) ){
                    $ret[] = "{$field[0]}=NULL";
                } else {
                    $this->p++;
                    $this->ps[] = "@p{$this->p}";
                    $this->params[] = "@p{$this->p} {$this->dataTypes[$field[1]]}";
                    $this->columns[] = $field[0];
                    if( $field[1] == "s" ){
                        $field[2] = str_replace("'","''",utf8_decode($field[2]));
                        $field[2] = "'{$field[2]}'";
                    }
                    $this->values[] = $field[2];
                    $ret[] = "{$field[0]}=@p{$this->p}";
                }
            }
            $this->fields = str_replace( "'", "''", implode( ", ", $ret ));
        }

        private function setFilters( $filters )
        {
            $ret = [];
            foreach( $filters as $key => $filter ){
                if( is_array($filter[0]) )
                {
                    $clauses = [];
                    foreach( $filter as $sub_filter ){
                        if( @$sub_filter[0] ) {
                            if (!@$sub_filter[1]) {
                                $clauses[] = $sub_filter[0];
                            } elseif (isset($sub_filter[3])) {
                                $sub_filter[3] = str_replace("'","''",$sub_filter[3]);
                                if ($sub_filter[2] == "in") {
                                    $clauses[] = $this->filterIn($sub_filter);
                                } elseif ($sub_filter[2] == "between") {
                                    $clauses[] = $this->filterBetween($sub_filter);
                                } else {
                                    $clauses[] = $this->filterDefault($sub_filter);
                                }
                            }
                        }
                    }
                    $ret[] = "(" . implode( " OR ", $clauses ). ")";
                }
                else if( @$filter[0] )
                {
                    if (!@$filter[1]) {
                        $ret[] = $filter[0];
                    } elseif (isset($filter[3])) {
                        $filter[3] = str_replace("'","''",$filter[3]);
                        if ($filter[2] == "in" || $filter[2] == "not in") {
                            $ret[] = $this->filterIn($filter);
                        } elseif ($filter[2] == "between") {
                            $ret[] = $this->filterBetween($filter);
                        } else {
                            $ret[] = $this->filterDefault($filter);
                        }
                    }
                }
            }
            $this->filters = str_replace( "'", "''", implode( " AND ", $ret ));
        }

        private function filterDefault( $filter )
        {
            $this->p++;
            $this->params[] = "@p{$this->p} {$this->dataTypes[$filter[1]]}";
            $this->values[] = $filter[1] == "s" ? "'{$filter[3]}'" : $filter[3];
            return "{$filter[0]} {$filter[2]} @p{$this->p}";
        }

        private function filterBetween( $filter )
        {
            $this->p++;
            $p1 = $this->p;
            $this->p++;
            $p2 = $this->p;

            $this->params[] = "@p{$p1} {$this->dataTypes[$filter[1]]}";
            $this->params[] = "@p{$p2} {$this->dataTypes[$filter[1]]}";
            $this->values[] = $filter[1] == "s" ? "'{$filter[3][0]}'" : $filter[3][0];
            $this->values[] = $filter[1] == "s" ? "'{$filter[3][1]}'" : $filter[3][1];

            return "{$filter[0]} BETWEEN @p{$p1} AND @p{$p2}";
        }

        private function filterIn( $filter )
        {
            $q = [];

            foreach ($filter[3] as $value) {
                $this->p++;
                $this->params[] = "@p{$this->p} {$this->dataTypes[$filter[1]]}";
                $this->values[] = $filter[1] == "s" ? "'{$value}'" : $value;
                $q[] = "@p{$this->p}";
            }

            return "{$filter[0]} {$filter[2]}(" . implode(",", $q) . ")";
        }

        private function debug( $params )
        {
            echo "TABLES";
            var_dump($this->tables);
            echo "FIELDS";
            var_dump($this->fields);
            echo "FILTERS";
            var_dump($this->filters);
            echo "PS";
            var_dump($this->ps);
            echo "COLUMNS";
            var_dump($this->columns);
            echo "GROUP";
            var_dump($this->group);
            echo "ORDER";
            var_dump($this->order);
            echo "QUERY<br/>";
            echo "{$this->query}<br/><br/>";
            echo "PARAMS";
            var_dump($params);
        }

        private function last_id()
        {
            $rs = $this->conn->execute("select @@identity as last_id");
            $ret = $rs->Fields(0);

            return $ret->value;
        }

        private function callQuery()
        {
            $this->openConn();
            $ret = Array();

            try
            {
                $rs = $this->conn->execute($this->query);
            }
            Catch( Exception $e )
            {
                GLOBAL $post;

                $pathLog = PATH_LOG . "sql/" . date("Y/F/d") . "/";
                if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);
                $date = date("YmdHis");

                ob_start();
                var_dump($e);
                file_put_contents( "{$pathLog}{$date}.html", ob_get_clean() );
                file_put_contents( "{$pathLog}{$date}.json", json_encode($post) );
                file_put_contents( "{$pathLog}{$date}.sql", $this->query );

                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Erro ao realizar a consulta no banco de dados."
                ]);
            }

            if( $this->type == "insert" ){
                $this->last_id = self::last_id();
            }

            $num_columns = $rs->Fields->Count();

            if( $num_columns > 0 )
            {
                $fld = [];

                for ($i=0; $i < $num_columns; $i++)
                {
                    $fld[$i] = $rs->Fields($i);
                }

                $k=0;
                while (!$rs->EOF)
                {
                    $ret[$k] = [];
                    for ($i=0; $i < $num_columns; $i++)
                    {
                        $ret[$k][$i] = utf8_encode(removeSpace($fld[$i]->value));
                    }
                    $rs->MoveNext(); $k++;
                }
                $rs->Close();
            }

            $this->closeConn();

            $rs = null;
            $conn = null;

            $this->response = $ret;
        }

        private function setOrder( $order )
        {
            $this->order = $order;
        }

        private function setTop( $top )
        {
            $this->top = $top;
        }

        public function connTest( $params )
        {
            try
            {
                $conn = new COM ("ADODB.Connection");
                $conn->open("PROVIDER=SQLOLEDB;ConnectionTimeout=10;SERVER={$params["host"]};UID={$params["user"]};PWD={$params["pass"]};DATABASE={$params["table"]}");
                $conn->Close();
            }
            Catch( Exception $e )
            {
                return false;
            }

            return true;
        }
    }

?>