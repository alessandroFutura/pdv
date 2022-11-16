<?php

    class Model
    {
        public static function insert( $conn, $params )
        {
            $conn->callInsert( $params );

            return @$conn->last_id ? $conn->last_id : NULL;
        }

        public static function update( $conn, $params )
        {
            $conn->callUpdate( $params );
        }

        public static function delete( $conn, $params )
        {
            $conn->callDelete( $params );
        }

        public static function get( $conn, $params )
        {
            $data = $conn->callSelect( $params );

            if( @$data[0] ) {
                if( @$params->class ) {
                    return new $params->class( $data[0], @$params->gets ? $params->gets : NULL );
                } else {
                    return $data[0];
                }
            }

            return NULL;
        }

        public static function getList( $conn, $params )
        {
            $l_data = $conn->callSelect( $params );

            $ret = [];

            if( @$params->class ) {
                foreach ($l_data as $data) {
                    $ret[] = new $params->class( $data, @$params->gets ? $params->gets : NULL );
                }
            } else {
                $ret = $l_data;
            }

            return $ret;
        }

        public static function nextCode( $conn, $params )
        {
            do {

                $data = $conn->nextCode($params);

                if( @$params->base36encode ){
                    $data = base36encode($data);
                }

                if( @$params->table ){

                    $test = self::get( $conn, (Object)[
                        "tables" => [$params->table],
                        "fields" => [$params->field],
                        "filters" => [[$params->field, "s", "=", @$params->base36encode ? $data : ( strlen($data) > 6 ? $data : substr("00000{$data}",-6))]]
                    ]);

                }

            } while( @$test );

            return $data;
        }

        public static function nextCode2( $conn, $params )
        {
            $code = Model::get($conn,(Object)[
                "class" => "Code",
                "tables" => [ "code" ],
                "filters" => [
                    [ "site_id", "i", "=", $params->site_id ],
                    [ "code_name", "s", "=", $params->code_name ]
                ]
            ]);

            Model::update( $conn,(Object)[
                "table" => "code",
                "fields" => [[ "code_value", "i", $code->code_value + 1 ]],
                "filters" => [[ "code_id", "i", "=", $code->code_id ]]
            ]);

            return substr("00000{$code->code_value}", -6);
        }

    }

?>