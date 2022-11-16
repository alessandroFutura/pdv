<?php

    class UserCompany
    {
        public static function getList($params)
        {
            GLOBAL $commercial, $terminal;

            $companies = Model::getList($commercial,(Object)[
                "join" => 1,
                "tables" => [
                    "UserCompany UC(NoLock)",
                    "INNER JOIN Company C(NoLock) ON(UC.company_id = C.company_id)",
                    "INNER JOIN TerminalCompany TC(NoLock) ON TC.company_id = C.company_id AND TC.terminal_id = {$terminal->terminal_id}",
                    "LEFT JOIN TerminalUser TU(NoLock) ON TU.company_id = C.company_id AND TU.user_id = UC.user_id AND TU.terminal_id = {$terminal->terminal_id}"
                ],
                "fields" => [
                    "C.company_id",
                    "C.company_name",
                    "C.company_color",
                    "C.company_short_name",
                    "user_company_main=(CASE WHEN TU.terminal_user_id IS NULL THEN 'N' ELSE 'Y' END)",
                ],
                "filters" => [
                    ["C.company_active = 'Y'"],
                    ["UC.user_company_commercial = 'Y'"],
                    ["ISNULL(UC.user_company_pdv, 'N') = 'Y'"],
                    ["UC.user_id", "i", "=", $params->user_id]
                ],
                "order" => "C.company_id"
            ]);

            foreach($companies as $company){
                $company->image = getImage((Object)[
                    "image_id" => $company->company_id,
                    "image_dir" => "company-compass"
                ]);
                $company->company_id = (int)$company->company_id;
                $company->company_code = substr("0{$company->company_id}", -2);
                $company->company_name = @$company->company_short_name ? $company->company_short_name : $company->company_name;
            }

            return $companies;
        }
    }

?>