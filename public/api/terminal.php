<?php

    include "../../config/start.php";

    GLOBAL $post, $get;

    switch($get->action) {

        case "add":

            if(
                !@$post->user_user ||
                !@$post->user_pass ||
                !@$post->terminal_name ||
                !@$post->terminal_token ||
                !@$post->terminal_nickname
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $_POST["get_user_access"] = 1;

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

            if($login->user_active == "N" || $login->access->terminal_add == "N"){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Usuário não autorizado."
                ]);
            }

            $terminal_id = Terminal::add((Object)[
                "user_id" => $login->user_id,
                "terminal_name" => $post->terminal_name,
                "terminal_token" => $post->terminal_token,
                "terminal_nickname" => $post->terminal_nickname
            ]);

            $post->user_pass = "***";

            postLog((Object)[
                "user_id" => $login->user_id,
                "parent_id" => $terminal_id,
                "app_version" => $post->appVersion,
                "host_ip" => $post->hostIP,
                "log_host_name" => $post->hostName,
                "log_platform" => $post->platform
            ]);

            Json::get((Object)[
                "user_id" => $login->user_id,
                "user_name" => $login->user_name,
                "terminal_token" => $post->terminal_token,
                "terminal_id" => $terminal_id,
                "terminal_date" => date("Y-m-d H:i:s")
            ]);

        break;

        case "operation":

            if(
                !@$post->terminal_id ||
                !@$post->terminal_operation_type ||
                !isset($post->terminal_operation_value)
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            TerminalOperation::add((Object)[
                "terminal_id" => $post->terminal_id,
                "terminal_operation_type" => $post->terminal_operation_type,
                "terminal_operation_value" => $post->terminal_operation_value
            ]);

        break;

    }

?>