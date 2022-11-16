<?php

    class UserAccess
    {
        public static function get($params)
        {
            GLOBAL $commercial;

            return self::treeAccess(Model::getList($commercial,(Object)[
                "tables" => ["UserPdv" ],
                "fields" => [
                    "user_pdv_id",
                    "user_pdv_name",
                    "user_pdv_value",
                    "user_pdv_data_type"
                ],
                "filters" => [["user_id", "i", "=", $params->user_id]]
            ]));
        }

        public static function treeAccess($l_access)
        {
            $user_access = json_decode(file_get_contents(PATH_PRODUCTION . "data/pdv.json"));

            $ret = [];
            foreach($user_access as $key => $access){
                $ret[$key] = $access->value;
            }

            foreach($l_access as $access){
                if(!is_null($ret[$access->user_pdv_name])){
                    $ret[$access->user_pdv_name] = $access->user_pdv_value;
                    if( $access->user_pdv_data_type == "float" ){
                        $ret[$access->user_pdv_name] = (float)$access->user_pdv_value;
                    }
                    if( $access->user_pdv_data_type == "int" ){
                        $ret[$access->user_pdv_name] = (int)$access->user_pdv_value;
                    }
                }
            }

            return (Object)$ret;
        }
    }

?>