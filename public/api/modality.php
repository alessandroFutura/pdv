<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $login;

    switch($get->action){

        case "edit":

            if(!@$post->budget_id || !@$post->actual || !@$post->selected){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $post->actual = (Object)$post->actual;
            $post->selected = (Object)$post->selected;

            BudgetPayment::edit((Object)[
                "modality_id" => $post->selected->IdFormaPagamento,
                "budget_payment_id" => $post->actual->budget_payment_id,
            ]);

            postLog((Object)[
                "parent_id" => $post->budget_id
            ]);

            Json::get([]);

        break;

        case "getModalityGroup":

            if(!@$post->modality_group_id){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            Json::get(ModalityGroupConfig::getList((Object)[
                "modality_group_id" => $post->modality_group_id
            ]));

        break;

    }

?>