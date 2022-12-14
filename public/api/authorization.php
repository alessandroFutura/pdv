<?php

    include "../../config/start.php";

    GLOBAL $login, $get, $post, $terminal;

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

        case "closeTerminalAuthorization":

            if(!@$login->access->closeTerminal || $login->access->closeTerminal == "N"){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Usuário não autorizado."
                ]);
            }

            postLog((Object)[
                "parent_id" => $terminal->terminal_id
            ]);

            Json::get((Object)[]);

        break;

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

            $log_id = postLog((Object)[
                "user_id" => $login->user_id,
                "parent_id" => $post->data->IdDocumento
            ]);

            Json::get((Object)[
                "log_id" => $log_id
            ]);

        break;

        case "documentPrint":

            if(
                ($post->data->modelo == "OE" && (!@$login->access->oe_print || $login->access->oe_print == "N"))
                ||
                ($post->data->modelo == "65" && (!@$login->access->nfce_print || $login->access->nfce_print == "N"))
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Usuário não autorizado."
                ]);
            }

            $log_id = postLog((Object)[
                "user_id" => $login->user_id,
                "parent_id" => $post->data->IdDocumento
            ]);

            Json::get((Object)[
                "log_id" => $log_id
            ]);

        break;

        case "openDevTools":

            if(!@$login->access->openDevTools || $login->access->openDevTools == "N"){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Usuário não autorizado."
                ]);
            }

            Json::get([]);

        break;

    }

    headerResponse((Object)[
        "code" => 417,
        "message" => "Ação nao localizada."
    ]);

?>