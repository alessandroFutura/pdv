<?php

    class PedidoDeVenda
    {
        public $StAgendaEntrega;
        public $DsObservacaoPedido;
        public $DsObservacaoDocumento;

        public function __construct($data)
        {
            $this->StAgendaEntrega = $data->StAgendaEntrega;
            $this->DsObservacaoPedido = @$data->DsObservacaoPedido ? $data->DsObservacaoPedido : NULL;
            $this->DsObservacaoDocumento = @$data->DsObservacaoDocumento ? $data->DsObservacaoDocumento : NULL;
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["PedidoDeVenda"],
                "fields" => [
                    "StAgendaEntrega",
                    "DsObservacaoPedido",
                    "DsObservacaoDocumento"
                ],
                "filters" => [["IdPedidoDeVenda", "s", "=", $params->IdPedidoDeVenda]]
            ]);

            if(@$data){
                return new PedidoDeVenda($data);
            }

            return NULL;
        }

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