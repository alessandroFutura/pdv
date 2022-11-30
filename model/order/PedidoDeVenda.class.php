<?php

    class PedidoDeVenda
    {
        public static function reopen($params)
        {
            GLOBAL $dafel;

            Model::update($dafel, (Object)[
                "table" => "PedidoDeVenda",
                "fields" => [["StPedidoDeVenda", "s", "L"]],
                "filters" => [["IdPedidoDeVenda", "s", "=", $params->IdPedidoDeVenda]]
            ]);
        }
    }

?>