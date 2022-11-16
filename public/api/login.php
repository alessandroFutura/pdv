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

    $login->user_current_session = (Object)[
        "user_session_value" => session_id(),
        "user_session_date" => date("Y-m-d H:i:s")
    ];

    User::saveSession((Object)[
        "data" => $login,
        "user_id" => $login->user_id,
        "user_session_id" => session_id()
    ]);

    UserSession::add((Object)[
        "user_id" => $login->user_id,
        "user_session_value" => session_id(),
        "user_session_app_version" => @$post->AppVersion ? $post->AppVersion : NULL,
        "user_session_host_ip" => @$post->HostIP ? $post->HostIP : NULL,
        "user_session_host_name" => @$post->HostName ? $post->HostName : NULL,
        "user_session_platform" => @$post->Platform ? $post->Platform : NULL
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