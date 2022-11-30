<?php

    class Operation
    {
        public $IdOperacao;
        public $StBaixaEstoque;
        public $StAtualizaFinanceiro;

        public function __construct($data)
        {
            $this->IdOperacao = $data->IdOperacao;
            $this->StBaixaEstoque = $data->StBaixaEstoque;
            $this->StAtualizaFinanceiro = $data->StAtualizaFinanceiro;

            $this->IdMensagem1 = @$data->IdMensagem1 ? $data->IdMensagem1 : NULL;
            $this->IdMensagem2 = @$data->IdMensagem2 ? $data->IdMensagem2 : NULL;
            $this->IdMensagem3 = @$data->IdMensagem3 ? $data->IdMensagem3 : NULL;
            $this->IdMensagem4 = @$data->IdMensagem4 ? $data->IdMensagem4 : NULL;

            $this->document = (Object)[
                "CdEspecie" => $data->CdEspecie,
                "CdSerieSubSerie" => $data->CdSerieSubSerie
            ];
        }

        public static function get($params)
        {
            GLOBAL $dafel, $commercial;

            $data = $data = Model::get($commercial, (Object)[
                "tables" => ["Config"],
                "fields" => ["config_value"],
                "filters" => [
                    ["config_category = 'budget'"],
                    ["config_name", "s", "=", ($params->external_type == "D" ? "cf" : "oe") . "_operation_id"],
                ]
            ]);

            if(@$data){

                $data = Model::get($dafel, (Object)[
                    "join" => 1,
                    "tables" => [
                        "Operacao O",
                        "INNER JOIN TipoDocumentoEmpresa TDE ON TDE.IdTipoDocumento = O.IdTipoDocumento AND TDE.CdEmpresa = {$params->company_id}"
                    ],
                    "fields" => [
                        "O.IdOperacao",
                        "O.StBaixaEstoque",
                        "O.StAtualizaFinanceiro",
                        "O.IdMensagem1",
                        "O.IdMensagem2",
                        "O.IdMensagem3",
                        "O.IdMensagem4",
                        "TDE.CdEspecie",
                        "TDE.CdSerieSubSerie"
                    ],
                    "filters" => [["O.IdOperacao", "s", "=", $data->config_value]]
                ]);

                if(@$data){
                    return new Operation($data);
                }
            }

            return NULL;
        }
    }

?>