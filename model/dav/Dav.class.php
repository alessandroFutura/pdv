<?php

    class Dav
    {
        public $StAgendaEntrega;
        public $DsObservacaoPedido;
        public $DsObservacaoDocumento;

        public function __construct($data)
        {
            $this->StAgendaEntrega = "N";
            $this->DsObservacaoPedido = NULL;
            $this->DsObservacaoDocumento = @$data->DsObservacaoDocumento ? $data->DsObservacaoDocumento : NULL;
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["DocumentoAuxVenda"],
                "fields" => [
                    "DsObservacaoDocumento"
                ],
                "filters" => [["IdDocumentoAuxVenda", "s", "=", $params->IdDocumentoAuxVenda]]
            ]);

            if(@$data){
                return new Dav($data);
            }

            return NULL;
        }

        public static function edit($params)
        {
            GLOBAL $dafel;

            Model::update($dafel, (Object)[
                "table" => "DocumentoAuxVenda",
                "fields" => [
                    ["NrCupomFiscal", "s", @$params->NrCupomFiscal ? $params->NrCupomFiscal : NULL],
                    ["StDocumentoAuxVenda", "s", $params->StDocumentoAuxVenda]
                ],
                "filters" => [["IdDocumentoAuxVenda", "s", "=", $params->IdDocumentoAuxVenda]]
            ]);
        }
    }

?>