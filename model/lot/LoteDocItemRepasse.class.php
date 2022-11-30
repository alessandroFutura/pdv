<?php

    class LoteDocItemRepasse
    {
        public static function add($params)
        {
            GLOBAL $dafel;

            Model::insert($dafel, (Object)[
                "table" => "LoteDocItemRepasse",
                "fields" => [
                    ["IdDocumentoItem", "s", $params->IdDocumentoItem],
                    ["IdPessoa", "s", $params->IdPessoa],
                    ["VlBaseRepasse", "s", $params->VlBaseRepasse],
                    ["AlRepasse", "s", 0],
                    ["TpEdicao", "s", isset($params->TpEdicao) ? $params->TpEdicao : "I"],
                    ["AlRepasseDuplicata", "s", $params->AlRepasseDuplicata],
                    ["IdCategoria", "s", "0000000004"]
                ]
            ]);
        }

        public static function del($params)
        {
            GLOBAL $dafel;

            Model::delete($dafel, (Object)[
                "top" => $params->top,
                "table" => "LoteDocItemRepasse",
                "filters" => [
                    ["1", "i", "=", 1],
                    ["IdDocumentoItem IN(SELECT IdDocumentoItem FROM LoteDocItem WHERE IdDocumento = '{$params->IdDocumento}')"]
                ]
            ]);
        }
    }

?>