<?php

    include "../../config/start.php";

    GLOBAL $login, $get, $post;

    if(!@$post->data || !@$post->user_name || !@$post->user_pass){
        headerResponse((Object)[
            "code" => 404,
            "message" => "Parâmetro POST não encontrado"
        ]);
    }

    $post->data = (Object)$post->data;

    $_POST["get_terminal"] = 1;
    $_POST["get_user_access"] = 1;

    $login = User::get((Object)[
        "user_user" => $post->user_name,
        "user_pass" => md5($post->user_pass)
    ]);

    if(!@$login){
        headerResponse((Object)[
            "code" => 404,
            "message" => "Usuário não encontrado."
        ]);
    }

    if($login->user_active == "N"){
        headerResponse((Object)[
            "code" => 417,
            "message" => "Usuário não autorizado."
        ]);
    }

    switch($get->action){

        case "documentCancel":

            if(
                ($post->data->modelo == "OE" && (!@$login->access->oe_cancel || $login->access->oe_cancel == "N"))
                ||
                ($post->data->modelo == "65" && (!@$login->access->nfce_cancel || $login->access->nfce_cancel == "N"))
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Usuário não autorizado."
                ]);
            }

            Json::get((Object)[
                "log_id" => postLog((Object)[
                    "user_id" => $login->user_id,
                    "parent_id" => $post->data->IdDocumento
                ])
            ]);

        break;

    }

?>