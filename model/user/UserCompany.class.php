<?php

    class UserCompany
    {
        public static function getList($params)
        {
            GLOBAL $conn, $commercial, $terminal;

            $companies = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.UserCompany UC(NoLock)",
                    "INNER JOIN {$conn->dafel->table}.dbo.EmpresaERP E(NoLock) ON E.CdEmpresa = UC.company_id",
                    "INNER JOIN {$conn->commercial->table}.dbo.Company C(NoLock) ON C.company_id = E.CdEmpresa",
                    "INNER JOIN {$conn->dafel->table}.dbo.UF UF(NoLock) ON UF.IdUF = E.CdUF",
                    "LEFT JOIN {$conn->commercial->table}.dbo.TerminalUser TU(NoLock) ON TU.company_id = C.company_id AND TU.user_id = UC.user_id AND TU.terminal_id = {$terminal->terminal_id}"
                ],
                "fields" => [
                    "C.company_id",
                    "C.company_name",
                    "C.company_color",
                    "C.company_sector_id",
                    "C.company_short_name",
                    "user_company_main=(CASE WHEN TU.terminal_user_id IS NULL THEN 'N' ELSE 'Y' END)",

                    "E.NrCGC",
                    "E.NmEmpresa",
                    "E.CdUF",
                    "E.CdIBGE",
                    "E.TpLogradouro",
                    "E.DsEndereco",
                    "E.NrLogradouro",
                    "E.NmBairro",
                    "E.NmCidade",
                    "E.NrCEP",
                    "E.NrTelefone",
                    "E.NrInscrEstadual",
                    "E.IdContaBancariaCaixa",
                    "E.TpRegimeEspecialTributacao",
                    "CSOSNEmpresa=E.CdSituacaoOperacaoSNSaida",
                    "CSOSNEmpresaPDV=E.CdSituacaoOperacaoSNSaidaPDV",
                    "AlCreditoICMSSN=CAST(E.AlCreditoICMSSN AS FLOAT)",
                    "AlFCP=CAST(UF.AlFCP AS FLOAT)",
                ],
                "filters" => [
                    ["C.company_active = 'Y'"],
                    ["UC.user_company_commercial = 'Y'"],
                    ["ISNULL(UC.user_company_pdv, 'N') = 'Y'"],
                    ["UC.user_id", "i", "=", $params->user_id]
                ],
                "order" => "C.company_id"
            ]);

            $ret = [];
            foreach($companies as $company){
                $ret[] = (Object)[
                    "image" => getImage((Object)[
                        "image_id" => $company->company_id,
                        "image_dir" => "company-compass"
                    ]),
                    "company_id" => (int)$company->company_id,
                    "company_code" => companyCode($company->company_id),
                    "company_name" => $company->company_name,
                    "company_color" => $company->company_color,
                    "company_short_name" => $company->company_short_name,
                    "user_company_main" => $company->user_company_main,
                    "company_sector_id" => @$company->company_sector_id ? $company->company_sector_id : NULL,
                    "external" => (Object)[
                        "NrCGC" => str_replace([".","/","-"], ["","",""], $company->NrCGC),
                        "NmEmpresa" => $company->NmEmpresa,
                        "CdUF" => $company->CdUF,
                        "CdIBGE" => (int)$company->CdIBGE,
                        "TpLogradouro" => $company->TpLogradouro,
                        "DsEndereco" => $company->DsEndereco,
                        "NrLogradouro" => $company->NrLogradouro,
                        "NmBairro" => $company->NmBairro,
                        "NmCidade" => $company->NmCidade,
                        "NrCEP" => $company->NrCEP,
                        "NrTelefone" => $company->NrTelefone,
                        "NrInscrEstadual" => $company->NrInscrEstadual,
                        "TpRegimeEspecialTributacao" => @$company->TpRegimeEspecialTributacao ? $company->TpRegimeEspecialTributacao : NULL,
                        "AlCreditoICMSSN" => @$company->AlCreditoICMSSN ? (float)$company->AlCreditoICMSSN : 0,
                        "CSOSNEmpresa" => @$company->CSOSNEmpresa ? $company->CSOSNEmpresa : NULL,
                        "CSOSNEmpresaPDV" => @$company->CSOSNEmpresaPDV ? $company->CSOSNEmpresaPDV : NULL,
                        "AlFCP" => @$company->AlFCP ? (float)$company->AlFCP : 0,
                        "IdContaBancariaCaixa" => @$company->IdContaBancariaCaixa ? $company->IdContaBancariaCaixa : NULL,
                    ]
                ];
            }

            return $ret;
        }
    }

?>