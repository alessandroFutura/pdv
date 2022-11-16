<?php

    include "../config/start.php";

    GLOBAL $headers, $post, $get;

    $path =  PATH_LOG . "error/" . date("Y/F/d/");
    if( !is_dir($path) ) mkdir($path, 0755, true);

    $file = $get->user_session_value . date("His");
    $post->headers = $headers;
    file_put_contents( "{$path}{$file}.json" , json_encode($post->params) );
    file_put_contents( "{$path}{$file}.html" , $post->response );

?>