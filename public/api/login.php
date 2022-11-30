<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $headerStatus, $login;

    if(!@$post->token || !@$post->user_user || !@$post->user_pass){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Parâmetro POST não encontrado"
        ]);
    }

    if(Session::isUser()){
        session_regenerate_id();
    }

    $_POST["get_terminal"] = 1;
    $_POST["get_user_image"] = 1;
    $_POST["get_user_access"] = 1;
    $_POST["get_user_companies"] = 1;
    $_POST["get_user_current_session"] = 1;

    $login = User::get((Object)[
        "user_user" => $post->user_user,
        "user_pass" => md5($post->user_pass)
    ]);

    if(!@$login){
        headerResponse((Object)[
            "code" => 404,
            "message" => "Usuário não encontrado."
        ]);
    }

    if($login->user_active == "N" || $login->access->access == "N"){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Usuário não autorizado."
        ]);
    }

    Terminal::validate((Object)[
        "user_id" => $login->user_id
    ]);

    User::saveSession((Object)[
        "data" => $login,
        "user_id" => $login->user_id,
        "user_session_id" => session_id()
    ]);

    UserSession::add((Object)[
        "user_id" => $login->user_id,
        "user_session_value" => session_id(),
        "user_session_app_version" => @$post->appVersion ? $post->appVersion : NULL,
        "user_session_host_ip" => @$post->hostIP ? $post->hostIP : NULL,
        "user_session_host_name" => @$post->hostName ? $post->hostName : NULL,
        "user_session_platform" => @$post->platform ? $post->platform : NULL
    ]);

    Session::saveSessionUser((Object)[
        "user_id" => $login->user_id
    ]);

    Json::get((Object)[
        "token" => $post->token,
        "user_id" => $login->user_id,
        "user_session_value" => session_id()
    ]);

?>