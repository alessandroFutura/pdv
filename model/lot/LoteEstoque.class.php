<?php

    class LoteEstoque
    {
        public $StLoteEstoque;
        public $DsMensagemErro;

        public function __construct($data)
        {
            $this->StLoteEstoque = $data->StLoteEstoque;
            $this->DsMensagemErro = @$data->DsMensagemErro ? $data->DsMensagemErro : NULL;
        }

        public static function add($params)
        {
            GLOBAL $dafel, $login;

            $IdLoteEstoque = Model::nextCode($dafel,(Object)[
                "table" => "LoteEstoque",
                "field" => "IdLoteEstoque",
                "increment" => "S",
                "base36encode" => 1
            ]);

            $CdChamada = Model::nextCode($dafel,(Object)[
                "table" => "LoteEstoque",
                "field" => "CdChamada",
                "increment" => "S"
            ]);

            Model::insert($dafel, (Object)[
                "table" => "LoteEstoque",
                "fields" => [
                    ["IdLoteEstoque", "s", $IdLoteEstoque],
                    ["CdEmpresa", "s", $params->CdEmpresa],
                    ["CdEmpresaEstoque", "s", $params->CdEmpresa],
                    ["CdEmpresaFinanceiro", "s", $params->CdEmpresa],
                    ["CdChamada", "s", $CdChamada],
                    ["DsLoteEstoque", "s", $params->DsLoteEstoque],
                    ["DtAbertura", "s", date("Y-m-d")],
                    ["IdUsuario", "s", $login->external_id],
                    ["StLoteEstoque", "s", "A"],
                    ["IdUFEmpresa", "s", "RJ"],
                    ["TpEdicao", "s", "I"],
                    ["StPrioridadeLiberacao", "s", "N"],
                    ["IdUFEmpresaEstoque", "s", "RJ"]
                ]
            ]);

            return $IdLoteEstoque;
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["LoteEstoque"],
                "fields" => [
                    "StLoteEstoque",
                    "DsMensagemErro"
                ],
                "filters" => [["IdLoteEstoque", "s", "=", $params->IdLoteEstoque]]
            ]);

            if(@$data){
                return new LoteEstoque($data);
            }

            return NULL;
        }

        public static function release($params)
        {
            GLOBAL $dafel;

            Model::update($dafel, (Object)[
                "table" => "LoteEstoque",
                "fields" => [["StLoteEstoque", "s", "F"]],
                "filters" => [["IdLoteEstoque", "s", "=", $params->IdLoteEstoque]]
            ]);
        }

        public static function reopen($params)
        {
            GLOBAL $dafel;

            Model::update($dafel, (Object)[
                "table" => "LoteEstoque",
                "fields" => [
                    ["StLoteEstoque", "s", "F"],
                    ["StPrioridadeLiberacao", "s", "U"],
                ],
                "filters" => [["IdLoteEstoque", "s", "=", $params->IdLoteEstoque]]
            ]);
        }
    }

?>