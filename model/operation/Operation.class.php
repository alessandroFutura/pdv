<?php

    class Operation
    {
        public $IdOperacao;
        public $CdChamada;
        public $NmOperacao;
        public $TpOperacao;
        public $IdCFOPIntraUF;
        public $IdCFOPEntreUF;
        public $StCalculaICMS;
        public $StBaixaEstoque;
        public $StBaseReduzidaICMS;
        public $StAtualizaFinanceiro;
        public $CDSituacaoTributariaPIS;
        public $CDSituacaoTributariaCOFINS;
        public $StCalculaSubstTributariaICMS;

        public function __construct($data)
        {
            $this->IdOperacao = $data->IdOperacao;
            $this->CdChamada = $data->CdChamada;
            $this->NmOperacao = $data->NmOperacao;
            $this->TpOperacao = $data->TpOperacao;
            $this->IdCFOPIntraUF = $data->IdCFOPIntraUF;
            $this->IdCFOPEntreUF = $data->IdCFOPEntreUF;
            $this->StCalculaICMS = $data->StCalculaICMS;
            $this->StBaixaEstoque = $data->StBaixaEstoque;
            $this->StBaseReduzidaICMS = $data->StBaseReduzidaICMS;
            $this->StAtualizaFinanceiro = $data->StAtualizaFinanceiro;
            $this->CDSituacaoTributariaPIS = $data->CDSituacaoTributariaPIS;
            $this->CDSituacaoTributariaCOFINS = $data->CDSituacaoTributariaCOFINS;
            $this->StCalculaSubstTributariaICMS = $data->StCalculaSubstTributariaICMS;
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["Operacao"],
                "fields" => [
                    "IdOperacao",
                    "CdChamada",
                    "NmOperacao",
                    "TpOperacao",
                    "IdCFOPIntraUF",
                    "IdCFOPEntreUF",
                    "StCalculaICMS",
                    "StBaixaEstoque",
                    "StBaseReduzidaICMS",
                    "StAtualizaFinanceiro",
                    "CDSituacaoTributariaPIS",
                    "CDSituacaoTributariaCOFINS",
                    "StCalculaSubstTributariaICMS"
                ],
                "filters" => [["IdOperacao", "s", "=", $params->IdOperacao]]
            ]);

            if(@$data){
                return new Operation($data);
            }
        }

        public static function getOeOperation()
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["Config"],
                "fields" => ["config_value"],
                "filters" => [
                    ["config_category = 'budget'"],
                    ["config_name = 'oe_operation_id'"]
                ]
            ]);

            if(@$data){
                return Operation::get((Object)[
                    "IdOperacao" => $data->config_value
                ]);
            }

            return NULL;
        }

        public static function getCupomOperation($params)
        {
            GLOBAL $dafel, $commercial;

            $config = Model::get($commercial, (Object)[
                "tables" => ["Config"],
                "fields" => ["config_value"],
                "filters" => [
                    ["config_category = 'budget'"],
                    ["config_name = 'oe_operation_id'"]
                ]
            ]);

            if(@$config){
                $data = Model::get($dafel, (Object)[
                    "tables" => ["Operacao"],
                    "fields" => [
                        "IdOperacao",
                        "CdChamada",
                        "NmOperacao",
                        "TpOperacao",
                        "IdCFOPIntraUF",
                        "IdCFOPEntreUF",
                        "StCalculaICMS",
                        "StBaixaEstoque",
                        "StBaseReduzidaICMS",
                        "StAtualizaFinanceiro",
                        "CDSituacaoTributariaPIS",
                        "CDSituacaoTributariaCOFINS",
                        "StCalculaSubstTributariaICMS"
                    ],
                    "filters" => [["IdOperacao", "s", "=", $config->config_value]]
                ]);

                if(@$data){
                    return new Operation($data);
                }
            }

            return NULL;
        }
    }

?>