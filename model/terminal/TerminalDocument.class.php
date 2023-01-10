<?php

    class TerminalDocument
    {
        public $terminal_document_id;
        public $terminal_id;
        public $user_id;
        public $budget_id;
        public $serie;
        public $versao;
        public $modelo;
        public $idLote;
        public $cNF;
        public $nNF;
        public $chNFe;
        public $tpAmb;
        public $verAplic;
        public $dhRecbto;
        public $nProt;
        public $digVal;
        public $cStat;
        public $xMotivo;
        public $IdLoteEstoque;
        public $IdDocumento;
        public $CdStatus;
        public $StCancelado;
        public $terminal_document_date;
        public $vFedTrib;
        public $vEstTrib;
        public $vMunTrib;
        public $vlPago;
        public $vlTroco;
        public $vlCobrado;

        public function __construct($data)
        {
            $this->terminal_document_id = (int)$data->terminal_document_id;
            $this->terminal_id = (int)$data->terminal_id;
            $this->user_id = (int)$data->user_id;
            $this->budget_id = (int)$data->budget_id;

            $this->tpAmb = @$data->tpAmb ? (int)$data->tpAmb : NULL;
            $this->serie = @$data->serie ? (int)$data->serie : NULL;
            $this->versao = (float)$data->versao;
            $this->modelo = $data->modelo;
            $this->idLote = @$data->idLote ? $data->idLote : NULL;
            $this->cNF = @$data->cNF ? $data->cNF : NULL;
            $this->nNF = $data->nNF;
            $this->NrDocumento = $this->modelo == "65" ? substr("00000000{$data->nNF}",-9) : $data->nNF;
            $this->chNFe = @$data->chNFe ? $data->chNFe : NULL;
            $this->verAplic = $data->verAplic;

            $this->cStat = @$data->cStat ? (int)$data->cStat : NULL;
            $this->dhRecbto = @$data->dhRecbto ? $data->dhRecbto : NULL;
            $this->nProt = @$data->nProt ? $data->nProt : NULL;
            $this->digVal = @$data->digVal ? $data->digVal : NULL;
            $this->xMotivo = @$data->xMotivo ? $data->xMotivo : NULL;

            $this->IdLoteEstoque = @$data->IdLoteEstoque ? $data->IdLoteEstoque : NULL;
            $this->IdDocumento = @$data->IdDocumento ? $data->IdDocumento : NULL;
            $this->CdStatus = @$data->CdStatus ? (int)$data->CdStatus : NULL;
            $this->StCancelado = @$data->StCancelado ? $data->StCancelado : "N";

            $this->terminal_document_date = $data->terminal_document_date;

            $this->vFedTrib = (float)$data->vFedTrib;
            $this->vEstTrib = (float)$data->vEstTrib;
            $this->vMunTrib = (float)$data->vMunTrib;
            $this->vlPago = (float)$data->vlPago;
            $this->vlTroco = (float)$data->vlTroco;
            $this->vlCobrado = (float)$data->vlCobrado;

            $date = new DateTime($this->dhRecbto);
            $this->qrCodePath = "{$date->format("Y/F/d")}/{$this->chNFe}.png";
        }

        public static function add($params)
        {
            GLOBAL $commercial, $terminal, $login;

            return (int)Model::insert($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [
                    ["terminal_id", "i", $terminal->terminal_id],
                    ["user_id", "i", $login->user_id],
                    ["budget_id", "i", $params->budget_id],
                    ["tpAmb", "i", $params->tpAmb],
                    ["serie", "i", $params->serie],
                    ["versao", "d", $params->versao],
                    ["modelo", "s", $params->modelo],
                    ["idLote", "s", $params->idLote],
                    ["cNF", "s", $params->cNF],
                    ["nNF", "s", $params->nNF],
                    ["chNFe", "s", $params->chNFe],
                    ["verAplic", "s", VERSION],
                    ["CdStatus", "s", 1],
                    ["terminal_document_date", "s", date("Y-m-d H:i:s")],
                ]
            ]);
        }

        public static function cancel($params)
        {
            GLOBAL $commercial, $login;

            $data = Model::get($commercial, (Object)[
                "tables" => ["[Log]"],
                "fields" => ["user_id"],
                "filters" => [
                    ["log_origin = 'P'"],
                    ["log_script = 'authorization'"],
                    ["log_action = 'documentCancel'"],
                    ["log_parent_id", "s", "=", $params->IdDocumento]
                ],
                "order" => "log_id DESC"
            ]);

            Model::update($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [
                    ["CdStatus", "s", 904],
                    ["StCancelado", "s", "S"],
                    ["DtCancelamento", "s", date("Y-m-d H:i:s")],
                    ["IdUsuarioCancelamento", "s", $login->user_id],
                    ["IdUsuarioAutorizacaoCancelamento", "s", $data->user_id],
                    ["terminal_document_update", "s", date("Y-m-d H:i:s")]
                ],
                "filters" => [["budget_id", "i", "=", $params->budget_id]]
            ]);
        }

        public static function edit($params)
        {
            GLOBAL $commercial;

            Model::update($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [
                    ["dhRecbto", "s", $params->dhRecbto],
                    ["nProt", "s", $params->nProt],
                    ["cStat", "s", $params->cStat],
                    ["xMotivo", "s", $params->xMotivo],
                    ["digVal", "s", $params->digVal],
                    ["vFedTrib", "d", $params->vFedTrib],
                    ["vEstTrib", "d", $params->vEstTrib],
                    ["vMunTrib", "d", $params->vMunTrib],
                    ["vlPago", "d", $params->vlPago],
                    ["vlTroco", "d", $params->vlTroco],
                    ["vlCobrado", "d", $params->vlCobrado],
                    ["CdStatus", "s", $params->CdStatus],
                    ["terminal_document_update", "s", date("Y-m-d H:i:s")],
                ],
                "filters" => [["terminal_document_id", "i", "=", $params->terminal_document_id]]
            ]);
        }

        public static function editDocument($params)
        {
            GLOBAL $commercial;

            Model::update($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [
                    ["IdDocumento", "s",$params->IdDocumento],
                    ["CdStatus", "s",$params->CdStatus]
                ],
                "filters" => [["terminal_document_id", "i", "=", $params->terminal_document_id]]
            ]);
        }

        public static function editLote($params)
        {
            GLOBAL $commercial;

            Model::update($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [
                    ["IdLoteEstoque", "s",$params->IdLoteEstoque],
                    ["CdStatus", "s",$params->CdStatus]
                ],
                "filters" => [["terminal_document_id", "i", "=", $params->terminal_document_id]]
            ]);
        }

        public static function editStatus($params)
        {
            GLOBAL $commercial;

            Model::update($commercial, (Object)[
                "table" => "TerminalDocument",
                "fields" => [["CdStatus", "s",$params->CdStatus]],
                "filters" => [["terminal_document_id", "i", "=", $params->terminal_document_id]]
            ]);
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["TerminalDocument"],
                "fields" => [
                    "terminal_document_id",
                    "terminal_id",
                    "user_id",
                    "budget_id",
                    "cStat",
                    "tpAmb",
                    "serie",
                    "versao=CAST(versao AS FLOAT)",
                    "modelo",
                    "idLote",
                    "cNF",
                    "nNF",
                    "chNFe",
                    "verAplic",
                    "dhRecbto",
                    "nProt",
                    "digVal",
                    "xMotivo",
                    "IdLoteEstoque",
                    "IdDocumento",
                    "CdStatus",
                    "StCancelado",
                    "vFedTrib=CAST(vFedTrib AS FLOAT)",
                    "vEstTrib=CAST(vEstTrib AS FLOAT)",
                    "vMunTrib=CAST(vMunTrib AS FLOAT)",
                    "vlPago=CAST(vlPago AS FLOAT)",
                    "vlTroco=CAST(vlTroco AS FLOAT)",
                    "vlCobrado=CAST(vlCobrado AS FLOAT)",
                    "terminal_document_date=FORMAT(terminal_document_date, 'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [["budget_id", "i", "=", @$params->budget_id ? $params->budget_id : NULL]]
            ]);

            if(@$data){
                return new TerminalDocument($data);
            }

            return NULL;
        }

        public static function getNrSequencial($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["TipoDocumentoEmpresa"],
                "fields" => ["NrSequencial"],
                "filters" => [
                    ["CdEmpresa", "i", "=", $params->CdEmpresa],
                    ["IdTipoDocumento", "s", "=", $params->IdTipoDocumento],
                ]
            ]);

            if(@$data){
                $data->NrSequencial = (int)$data->NrSequencial + 1;

                Model::update($dafel, (Object)[
                    "table" => "TipoDocumentoEmpresa",
                    "fields" => [["NrSequencial", "i", $data->NrSequencial]],
                    "filters" => [
                        ["CdEmpresa", "i", "=", $params->CdEmpresa],
                        ["IdTipoDocumento", "s", "=", $params->IdTipoDocumento],
                    ]
                ]);
            }

            return @$data ? $data->NrSequencial : NULL;
        }
    }

?>