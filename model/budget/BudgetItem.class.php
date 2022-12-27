<?php

    class BudgetItem
    {

        public $budget_item_id;
        public $price_id;
        public $external_id;
        public $budget_item_quantity;
        public $budget_item_value;
        public $budget_item_value_unitary;
        public $budget_item_aliquot_discount;
        public $budget_item_value_discount;
        public $budget_item_value_total;
        public $budget_value_change;
        public $product;

        public function __construct($data)
        {
            $this->budget_item_id = (int)$data->budget_item_id;
            $this->external_id = $data->external_id;
            $this->price_id = $data->price_id;
            $this->budget_item_quantity = (float)$data->budget_item_quantity;
            $this->budget_item_value = (float)$data->budget_item_value;
            $this->budget_item_aliquot_discount = (float)$data->budget_item_aliquot_discount;
            $this->budget_item_value_discount = (float)$data->budget_item_value_discount;
            $this->budget_item_value_unitary = (float)$data->budget_item_value_unitary;
            $this->budget_item_value_total = (float)$data->budget_item_value_total;
            $this->budget_value_change = 0;

            $this->product = (Object)[
                "CST" => $data->CST,
                "IdCFOP" => $data->IdCFOP,
                "IdUnidade" => $data->IdUnidade,
                "IdProduto" => $data->IdProduto,
                "IdClassificacaoFiscal" => $data->IdClassificacaoFiscal,
                "CdSigla" => $data->CdSigla,
                "TpOrigemProduto" => $data->TpOrigemProduto,
                "CdChamada" => $data->CdChamada,
                "NmProduto" => $data->NmProduto,
                "CdClassificacao" => $data->CdClassificacao,
                "CdEAN" => @$data->CdEAN ? $data->CdEAN : NULL,
                "CdCEST" => @$data->CdCEST ? $data->CdCEST : NULL,
                "AlTributoMunicipal" => 0,
                "AlTributoEstadual" => @$data->AlTributoEstadual ? (float)$data->AlTributoEstadual : 0,
                "AlTributoNacional" => @$data->AlTributoNacional ? (float)$data->AlTributoNacional : 0,
                "AlTributoImportado" => @$data->AlTributoImportado ? (float)$data->AlTributoImportado : 0,
                "CSOSN" => @$data->CSOSN ? $data->CSOSN : NULL,
                "CSOSNPDV" => @$data->CSOSNPDV ? $data->CSOSNPDV : NULL,
                "AlFCP" => @$data->AlFCP ? (float)$data->AlFCP : 0,
                "VlFCP" => @$data->VlFCP ? (float)$data->VlFCP : 0,
                "AlICMS" => @$data->AlICMS ? (float)$data->AlICMS : 0,
                "VlICMS" => @$data->VlICMS ? (float)$data->VlICMS : 0,
                "VlBaseICMS" => @$data->VlBaseICMS ? (float)$data->VlBaseICMS : 0,
                "AlRepasseDuplicataDAV" => (float)$data->AlRepasseDuplicataDAV,
                "AlRepasseDuplicataPedido" => (float)$data->AlRepasseDuplicataPedido,
                "IdDocumentoItem" => @$data->IdDocumentoItem ? $data->IdDocumentoItem : NULL,
            ];
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $data = Model::getList($commercial, (Object)[
                "join" => 1,
                "debug" => 0,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetItem BI(NoLock)",
                    "INNER JOIN {$conn->commercial->table}.dbo.BudgetItemTrib BIT(NoLock) ON BIT.budget_item_id = BI.budget_item_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.Produto P(NoLock) ON P.IdProduto = BI.product_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.Produto_Empresa PE(NoLock) ON PE.IdProduto = P.IdProduto AND PE.CdEmpresa = {$params->CdEmpresa}",
                    "INNER JOIN {$conn->dafel->table}.dbo.CodigoProduto CP(NoLock) ON CP.IdProduto = P.IdProduto AND CP.StCodigoPrincipal = 'S'",
                    "INNER JOIN {$conn->dafel->table}.dbo.Unidade U(NoLock) ON U.IdUnidade = P.IdUnidade",
                    "INNER JOIN {$conn->dafel->table}.dbo.ClassificacaoFiscal CF(NoLock) ON CF.IdClassificacaoFiscal = P.IdClassificacaoFiscal",
                    "LEFT JOIN {$conn->dafel->table}.dbo.DocumentoAuxVendaItemRepasse DAVIR (NoLock) ON DAVIR.IdDocumentoAuxVendaItem = BI.external_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Representante_PedidoDeVendaIte RPVI (NoLock) ON RPVI.IdPedidoDeVendaItem = BI.external_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.CEST CEST(NoLock) ON CEST.IdCEST = CF.IdCEST",
                    "LEFT JOIN {$conn->dafel->table}.dbo.ClassificacaoFiscal_UF CFUF(NoLock) ON CFUF.IdClassificacaoFiscal = CF.IdClassificacaoFiscal AND CFUF.IdUF = 'RJ' AND CFUF.DtFimVigenciaTotalTributos >= '" . date("Y-m-d") . "'",
                ],
                "fields" => [
                    "BI.budget_item_id",
                    "BI.price_id",
                    "BI.external_id",
                    "budget_item_quantity=CAST(BI.budget_item_quantity AS FLOAT)",
                    "budget_item_value=CAST(BI.budget_item_value AS FLOAT)",
                    "budget_item_aliquot_discount=CAST(BI.budget_item_aliquot_discount AS FLOAT)",
                    "budget_item_value_discount=CAST(BI.budget_item_value_discount AS FLOAT)",
                    "budget_item_value_unitary=CAST(BI.budget_item_value_unitary AS FLOAT)",
                    "budget_item_value_total=CAST(BI.budget_item_value_total AS FLOAT)",
                    "P.IdProduto",
                    "P.IdUnidade",
                    "P.IdClassificacaoFiscal",
                    "CP.CdChamada",
                    "CF.CdClassificacao",
                    "CSOSN=PE.CdSituacaoOperacaoSN",
                    "CSOSNPDV=PE.CdSituacaoOperacaoSNSaidaPDV",
                    "AlTributoImportado=CAST(CFUF.AlTotalTributosImportadoFederal AS FLOAT)",
                    "AlTributoNacional=CAST(CFUF.AlTotalTributosNacionalFederal AS FLOAT)",
                    "AlTributoEstadual=CAST(CFUF.AlTotalTributosEstadual AS FLOAT)",
                    "CEST.CdCEST",
                    "CdEAN=(SELECT TOP 1 CP2.CdChamada FROM {$conn->dafel->table}.dbo.CodigoProduto CP2 WHERE CP2.IdProduto = P.IdProduto AND CP2.IdTipoCodigoProduto = CONVERT(VARCHAR,(
                        SELECT config_value FROM {$conn->commercial->table}.dbo.Config WHERE config_category = 'product' AND config_name = 'code_ean_id'
                    )))",
                    "P.NmProduto",
                    "P.TpOrigemProduto",
                    "U.CdSigla",
                    "BIT.CST",
                    "BIT.IdCFOP",
                    "AlFCP=CAST(BIT.AlFCP AS FLOAT)",
                    "VlFCP=CAST(BIT.VlICMSFCP AS FLOAT)",
                    "AlICMS=CAST(BIT.AlICMS AS FLOAT)",
                    "VlICMS=CAST(BIT.VlICMS AS FLOAT)",
                    "VlBaseICMS=CAST(BIT.VlBaseFCP AS FLOAT)",
                    "AlRepasseDuplicataDAV=CAST(ISNULL(DAVIR.AlRepasseDuplicata,0) AS FLOAT)",
                    "AlRepasseDuplicataPedido=CAST(ISNULL(RPVI.AlComissaoDuplicata,0) AS FLOAT)",
                    "IdDocumentoItem=(SELECT TOP 1 IdDocumentoItem FROM {$conn->dafel->table}.dbo.PedidoDeVendaItem_DocumentoItem WHERE IdPedidoDeVendaItem = BI.external_id ORDER BY IdDocumentoItem DESC)"
                ],
                "filters" => [["BI.budget_id", "i", "=", $params->budget_id]],
                "order" => "BI.budget_item_id"
            ]);

            $ret = [];
            foreach($data as $item){
                $ret[] = new BudgetItem($item);
            }

            return $ret;
        }
    }

?>