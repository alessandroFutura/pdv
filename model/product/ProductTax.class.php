<?php

    class ProductTax
    {
        public static function get($params)
        {
            GLOBAL $dafel;

            $cfop = Model::get($dafel,(Object)[
                "tables" => ["Produto_Empresa_CFOP (NoLock)"],
                "fields" => ["IdCFOPEquivalente"],
                "filters" => [
                    ["TpOperacao = 'V'"],
                    ["IdUF", "s", "=", $params->IdUFDestino],
                    ["IdProduto", "s", "=", $params->IdProduto],
                    ["CdEmpresa", "i", "=", $params->CdEmpresa]
                ]
            ]);

            if($params->IdUFOrigem == $params->IdUFDestino){
                $prefixCFOP = "5";
                $productCFOP = @$params->CdCFOP ? $params->CdCFOP : NULL;
                $operationCFOP = $params->IdCFOPIntraUF;
            } else {
                $prefixCFOP = "6";
                $productCFOP = @$params->CdCFOPEntreUF ? $params->CdCFOPEntreUF : NULL;
                $operationCFOP = $params->IdCFOPEntreUF;
            }

            if(@$cfop->IdCFOPEquivalente){
                $IdCFOP = "{$prefixCFOP}.{$cfop->IdCFOPEquivalente}";
            } else {
                $IdCFOP = @$productCFOP ? "{$prefixCFOP}.{$productCFOP}" : $operationCFOP;
            }

            $fiscal = Fiscal::get((Object)[
                "IdCFOP" => $IdCFOP,
                "IdUF" => $params->IdUFOrigem,
                "CdEmpresa" => $params->CdEmpresa,
                "IdUFOrigem" => $params->IdUFOrigem,
                "IdUFDestino" => $params->IdUFDestino,
                "IdCalculoICMS" => $params->IdCalculoICMS
            ]);

            $ncm = NULL;
            if(@$fiscal && $params->StCalculaSubstTributariaICMS == "S" && $fiscal->StCalculaSubstTributariaICMS == "S"){
                $ncm = Model::get($dafel,(Object)[
                    "tables" => ["ClassificacaoFiscalItem (NoLock)"],
                    "fields" => [
                        "IdClassificacaoFiscal",
                        "AlLucro=CAST(AlLucro AS FLOAT)",
                        "AlICMSInterna=CAST(AlICMSInterna AS FLOAT)",
                        "AlMVASTInterna=CAST(AlMVASTInterna AS FLOAT)",
                        "AlICMSSTInterna=CAST(AlICMSSTInterna AS FLOAT)"
                    ],
                    "filters" => [
                        ["CdEmpresa", "i", "=", $params->CdEmpresa],
                        ["IdClassificacaoFiscal", "s", "=", $params->IdClassificacaoFiscal]
                    ]
                ]);
            }

            return (Object)[
                "ncm" => $ncm,
                "fiscal" => $fiscal,
                "IdCFOP" => $IdCFOP,
                "messages" => @$fiscal ? $fiscal->messages : [],
                "CST" => @$fiscal ? $fiscal->CdSituacaoTributaria : NULL
            ];
        }
    }

?>