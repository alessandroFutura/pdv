<?php

    class ModalityGroupConfig
    {
        public static function getList($params)
        {
            GLOBAL $conn, $dafel;

            $modalities = Model::getList($dafel, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->dafel->table}.dbo.FormaPagamento FP",
                    "INNER JOIN {$conn->commercial->table}.dbo.ModalityGroupModality MGM ON MGM.modality_id = FP.IdFormaPagamento"
                ],
                "fields" => [
                    "FP.CdChamada",
                    "FP.IdFormaPagamento",
                    "FP.DsFormaPagamento"
                ],
                "filters" => [
                    ["FP.StAtivo = 'S'"],
                    ["MGM.modality_group_id", "i", "=", $params->modality_group_id]
                ]
            ]);

            foreach($modalities as $modality){
                $modality->image = getImage((Object)[
                    "image_id" => $modality->IdFormaPagamento,
                    "image_dir" => "modality"
                ]);
            }

            return $modalities;
        }
    }

?>