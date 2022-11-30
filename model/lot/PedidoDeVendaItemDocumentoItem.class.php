<?php

    class PedidoDeVendaItemDocumentoItem
    {
        public static function add($params)
        {
            GLOBAL $dafel;

            Model::insert($dafel, (Object)[
                "table" => "PedidoDeVendaItem_DocumentoItem",
                "fields" => [
                    ["IdPedidoDeVendaItem", "s", $params->IdPedidoDeVendaItem],
                    ["IdDocumentoItem", "s", $params->IdDocumentoItem],
                    ["IdPedidoDeVenda", "s", $params->IdPedidoDeVenda],
                    ["QtAtendida", "s", $params->QtAtendida],
                    ["StConsideraQuantidade", "s", "S"],
                    ["DtAtendimentoPedidoDeVenda", "s", date("Y-m-d H:i:s")]
                ]
            ]);
        }

        public static function del($params)
        {
            GLOBAL $dafel;

            Model::delete($dafel, (Object)[
                "top" => $params->top,
                "table" => "PedidoDeVendaItem_DocumentoItem",
                "filters" => [["IdPedidoDeVenda", "s", "=", $params->IdPedidoDeVenda]]
            ]);
        }
    }

?>