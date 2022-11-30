<?php

    class DocumentoComplemento
    {
        public static function add($params)
        {
            GLOBAL $dafel;

            Model::insert($dafel, (Object)[
                "table" => "DocumentoComplemento",
                "fields" => [
                    ["IdDocumento", "s", $params->IdDocumento],
                    ["IdMotivoExclusaoTitulo", "s", $params->IdMotivoExclusaoTitulo]
                ]
            ]);
        }
    }

?>