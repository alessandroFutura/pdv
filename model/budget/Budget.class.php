<?php

    class Budget
    {
        public $budget_id;
        public $company_id;
//        public $user_id;
        public $client_id;
        public $seller_id;
//        public $address_code;
        public $term_id;
//        public $external_id;
        public $external_type;
//        public $external_code;
//        public $document_id;
//        public $document_type;
        public $document_code;
//        public $document_canceled;
        public $budget_value;
//        public $budget_aliquot_discount;
        public $budget_value_discount;
//        public $budget_value_addition;
//        public $budget_value_icms;
//        public $budget_value_st;
        public $budget_value_total;
//        public $budget_cost;
//        public $budget_note;
//        public $budget_note_document;
//        public $budget_credit;
//        public $budget_delivery;
//        public $delivery_status;
//        public $truck_size;
//        public $budget_status;
//        public $budget_origin;
//        public $budget_trash;
//        public $budget_online;
//        public $budget_delivery_date;
//        public $budget_date_expiration;
//        public $budget_update;
//        public $budget_date;

        public function __construct($data)
        {
            $this->budget_id = (int)$data->budget_id;
            $this->company_id = (int)$data->company_id;
//            $this->user_id = (int)$data->user_id;
            $this->client_id = $data->client_id;
            $this->seller_id = $data->seller_id;
//            $this->address_code = $data->address_code;
            $this->term_id = @$data->term_id ? $data->term_id : NULL;
//            $this->external_id = @$data->external_id ? $data->external_id : NULL;
            $this->external_type = @$data->external_type ? $data->external_type : NULL;
//            $this->external_code = @$data->external_code ? $data->external_code : NULL;
//            $this->document_id = @$data->document_id ? $data->document_id : NULL;
//            $this->document_type = @$data->document_type ? $data->document_type : NULL;
            $this->document_code = @$data->document_code ? $data->document_code : NULL;
//            $this->document_canceled = $data->document_canceled;
            $this->budget_value = (float)$data->budget_value;
//            $this->budget_aliquot_discount = (float)$data->budget_aliquot_discount;
            $this->budget_value_discount = (float)$data->budget_value_discount;
//            $this->budget_value_addition = (float)$data->budget_value_addition;
//            $this->budget_value_icms = (float)$data->budget_value_icms;
//            $this->budget_value_st = (float)$data->budget_value_st;
            $this->budget_value_total = (float)$data->budget_value_total;
//            $this->budget_cost = (float)$data->budget_cost;
//            $this->budget_note = @$data->budget_note ? $data->budget_note : NULL;
//            $this->budget_note_document = @$data->budget_note_document ? $data->budget_note_document : NULL;
//            $this->budget_credit = $data->budget_credit;
//            $this->budget_delivery = $data->budget_delivery;
//            $this->delivery_status = $data->delivery_status;
//            $this->truck_size = @$data->truck_size ? $data->truck_size : NULL;
//            $this->budget_status = $data->budget_status;
//            $this->budget_origin = $data->budget_origin;
//            $this->budget_trash = $data->budget_trash;
//            $this->budget_online = $data->budget_online;
//            $this->budget_delivery_date = $data->budget_delivery_date;
//            $this->budget_date_expiration = $data->budget_date_expiration;
//            $this->budget_update = @$data->budget_update ? $data->budget_update : NULL;
//            $this->budget_date = $data->budget_date;

//            $this->operation = Operation::get((Object)[
//                "IdOperacao" => "00A00000DD"
//            ]);

            $this->seller = Person::get((Object)[
                "IdPessoa" => $data->seller_id
            ]);

            $this->person = Person::get((Object)[
                "IdPessoa" => $data->client_id
            ]);

            $this->items = BudgetItem::getList((Object)[
                "budget_id" => $data->budget_id,
                "CdEmpresa" => $data->company_id
            ]);

            $this->payments = BudgetPayment::getList((Object)[
                "budget_id" => $data->budget_id,
                "CdEmpresa" => $data->company_id
            ]);

            if(@$data->term_id){
                $this->term = Term::get((Object)[
                    "IdPrazo" => $data->term_id
                ]);
            }
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["Budget"],
                "fields" => [
                    "budget_id",
                    "company_id",
//                    "user_id",
                    "client_id",
                    "seller_id",
//                    "address_code",
                    "term_id",
//                    "external_id",
                    "external_type",
//                    "external_code",
//                    "document_id",
//                    "document_type",
                    "document_code",
//                    "document_canceled",
                    "budget_value=CAST(budget_value AS FLOAT)",
//                    "budget_aliquot_discount=CAST(budget_aliquot_discount AS FLOAT)",
                    "budget_value_discount=CAST(budget_value_discount AS FLOAT)",
//                    "budget_value_addition=CAST(budget_value_addition AS FLOAT)",
//                    "budget_value_icms=CAST(budget_value_icms AS FLOAT)",
//                    "budget_value_st=CAST(budget_value_st AS FLOAT)",
                    "budget_value_total=CAST(budget_value_total AS FLOAT)",
//                    "budget_cost=CAST(budget_cost AS FLOAT)",
//                    "budget_note",
//                    "budget_note_document",
//                    "budget_credit",
//                    "budget_delivery",
//                    "delivery_status",
//                    "truck_size",
//                    "budget_status",
//                    "budget_origin",
//                    "budget_trash",
//                    "budget_online",
//                    "budget_delivery_date=FORMAT(budget_delivery_date,'yyyy-MM-dd')",
//                    "budget_date_expiration=FORMAT(budget_date_expiration,'yyyy-MM-dd')",
//                    "budget_update=FORMAT(budget_update,'yyyy-MM-dd HH:mm:ss')",
//                    "budget_date=FORMAT(budget_date,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    ["budget_trash = 'N'"],
                    ["budget_id", "i", "=", $params->budget_id]
                ]
            ]);

            if(@$data){
                return new Budget($data);
            }

            return NULL;
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $budgets = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.Budget B",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P ON P.IdPessoa = B.client_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P2 ON P2.IdPessoa = B.seller_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Prazo P3 ON P3.IdPrazo = B.term_id",
                ],
                "fields" => [
                    "B.budget_id",
                    "B.external_id",
                    "B.external_code",
                    "B.external_type",
                    "B.document_code",
                    "P.NmPessoa",
                    "P3.DsPrazo",
                    "NmRepresentante=(CASE WHEN LEN(P2.NmCurto) > 0 THEN P2.NmCurto ELSE P2.NmPessoa END)",
                    "budget_value_total=CAST(B.budget_value_total AS FLOAT)",
                    "budget_date=FORMAT(B.budget_date, 'yyyy-MM-dd')"
                ],
                "filters" => [
                    ["B.company_id", "i", "=", $params->company_id],
                    ["B.budget_status", "s", "in", $params->states],
                    ["B.budget_date", "s", "between", ["{$params->reference} 00:00:00", "{$params->reference} 23:23:59"]]
                ]
            ]);

            foreach($budgets as $budget){
                $budget->DsPagamento = "--";
                $budget->DsPrazo = @$budget->DsPrazo ? $budget->DsPrazo : NULL;
                $budget->document_code = @$budget->document_code ? $budget->document_code : NULL;
                $budget->budget_value_total = (float)$budget->budget_value_total;
            }

            return $budgets;
        }
    }

?>