<?php

    class Config
    {
        public static function getList()
        {
            GLOBAL $commercial;

            $configs = Model::getList($commercial,(Object)[
                "tables" => [ "Config" ],
                "fields" => [
                    "config_id",
                    "config_category",
                    "config_name",
                    "config_value"
                ]
            ]);

            $data = [];
            foreach( $configs as $config ){
                if(
                    ($config->config_category == "person" && $config->config_name == "attributes") ||
                    ($config->config_category == "bank" && $config->config_name == "authorized" ) ||
                    ($config->config_category == "credit" && $config->config_name == "authorized_modality_id" ) ||
                    ($config->config_category == "budget" && $config->config_name == "authorized_modality_id" ) ||
                    ($config->config_category == "budget" && $config->config_name == "online_modality_id" ) ||
                    ($config->config_category == "budget" && $config->config_name == "min_margin_exception_groups" ) ||
                    ($config->config_category == "shop" && $config->config_name == "operations" )
                ){
                    $config->config_value = explode(":", $config->config_value);
                }
                $data[$config->config_category][$config->config_name] = $config->config_value;
            }

            $data = (Object)$data;
            foreach($data as $key => $d){
                $data->$key = (Object)$d;
            }

            return $data;
        }

    }

?>