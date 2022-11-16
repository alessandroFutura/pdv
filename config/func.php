<?php

    function loadClass()
    {
        spl_autoload_register(function( $className ){
            if(file_exists( PATH_CLASS . "{$className}.class.php")){
                require_once PATH_CLASS . "{$className}.class.php";
                return;
            }
            if(file_exists( PATH_MODEL . "{$className}.class.php")){
                require_once PATH_MODEL . "{$className}.class.php";
                return;
            }
            $models = array_filter(glob(PATH_MODEL . '*'), 'is_dir');
            foreach($models as $model){
                if(file_exists("{$model}/{$className}.class.php")){
                    require_once "{$model}/{$className}.class.php";
                    return;
                }
            }
        });
    }

    function headerResponse($params)
    {
        $httpStatus = [
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Not Found",
            409 => "Conflict",
            417 => "Expectation Fail",
            420 => "Process Failed",
        ];

        header("HTTP/1.0 {$params->code} {$httpStatus[$params->code]}");
        Json::get((Object)[
            "message" => @$params->message ? $params->message : NULL
        ]);
    }

    function getImage( $params )
    {
        $uri = @$params->uri ? $params->uri : URI_PRODUCTION_FILES;
        $file = "{$params->image_dir}/{$params->image_id}";
        $path = (@$params->path ? $params->path : PATH_PRODUCTION_FILES) . "{$file}";

        $rand = rand(1000,9999);
        $types = ["jpg","jpeg","png","gif","webp","ico"];

        $ret = NULL;
        foreach($types as $type){
            if( file_exists( "{$path}.{$type}" )){
                $ret = "{$uri}{$file}.{$type}" . (@$params->rand ? "?{$rand}" : "");
            }
        }

        return @$ret ? $ret : (URI_PRODUCTION . "images/empty.png");
    }

    function postLog($params)
    {
        GLOBAL $commercial, $post, $headers;

        $log_id = (int)Model::insert($commercial,(Object)[
            "table" => "[Log]",
            "fields" => [
                ["user_id", "s", $params->user_id],
                ["log_script", "s", $params->script],
                ["log_action", "s", $params->action],
                ["log_system_version", "s", VERSION],
                ["log_parent_id", "s", $params->parent_id],
                ["log_app_version", "s", $params->app_version],
                ["log_host_ip", "s", $params->host_ip],
                ["log_host_name", "s", $params->host_name],
                ["log_platform", "s", $params->platform],
                ["log_origin", "s", "P"],
                ["log_date", "s", date("Y-m-d H:i:s") ],
            ]
        ]);

        if(!@$params->postIgnore){
            $pathLog =  PATH_LOG . "post/" . date("Y/F/d") . "/{$params->script}/{$params->action}/";
            if(!is_dir($pathLog)) mkdir($pathLog, 0755, true);
            file_put_contents("{$pathLog}{$log_id}.json" , json_encode((Object)[
                "post" => $post,
                "headers" => $headers
            ]));
        }

        return $log_id;
    }

    function removeSpace($string)
    {
        return rtrim($string," ");
    }

?>