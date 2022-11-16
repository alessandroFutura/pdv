<?php

    class Fiscal
    {
        public $CdSituacaoTributaria;
        public $StCalculaSubstTributariaICMS;
        public $AlICMS;
        public $AlFCP;
        public $AlReducaoBaseICMS;
        public $DsMensagemCdSituacaoTributaria;
        public $messages;

        public function __construct($data)
        {
            $this->CdSituacaoTributaria = @$data->CdSituacaoTributaria ? $data->CdSituacaoTributaria : NULL;
            $this->StCalculaSubstTributariaICMS = $data->StCalculaSubstTributariaICMS;
            $this->AlICMS = @$data->AlICMS ? (float)$data->AlICMS : 0;
            $this->AlFCP = @$data->AlFCP ? (float)$data->AlFCP : 0;
            $this->AlReducaoBaseICMS = @$data->AlReducaoBaseICMS ? (float)$data->AlReducaoBaseICMS : 0;
            $this->DsMensagemCdSituacaoTributaria = @$data->DsMensagemCdSituacaoTributaria ? $data->DsMensagemCdSituacaoTributaria : NULL;

            $this->messages = [];
            if(@$data->DsMensagemCdSituacaoTributaria){
                $messages = explode(";", $data->DsMensagemCdSituacaoTributaria);
                foreach($messages as $message){
                    if(@$message){
                        $message = explode("|",$message);
                        if(@$message[1]){
                            $this->messages[] = $message[1];
                        }
                    }
                }
                $this->messages = array_unique($this->messages);
            }
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["CalculoICMS_UF (NoLock)"],
                "fields" => [
                    "CdSituacaoTributaria",
                    "StCalculaSubstTributariaICMS",
                    "AlICMS=CAST(AlICMS AS FLOAT)",
                    "AlFCP=CAST(AlFCP AS FLOAT)",
                    "AlReducaoBaseICMS=CAST(AlReducaoBaseICMS AS FLOAT)",
                    "DsMensagemCdSituacaoTributaria",
                ],
                "filters" => [
                    [
                        ["CdEmpresa IS NULL"],
                        ["CdEmpresa", "i", "=", $params->CdEmpresa]
                    ],
                    ["IdCFOP", "s", "=", $params->IdCFOP],
                    ["IdUF", "s", "=", $params->IdUFOrigem],
                    ["IdUFDestino", "s", "=", $params->IdUFDestino],
                    ["IdCalculoICMS", "s", "=", $params->IdCalculoICMS],
                ],
                "order" => "CdEmpresa DESC"
            ]);

            if(@$data){
                return new Fiscal($data);
            }

            return NULL;
        }

    }

?>