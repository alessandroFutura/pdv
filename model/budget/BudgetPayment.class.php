<?php

    class BudgetPayment
    {
        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $payments = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetPayment BP",
                    "INNER JOIN {$conn->dafel->table}.dbo.FormaPagamento FP ON FP.IdFormaPagamento = BP.modality_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.NaturezaLancamento NL ON NL.IdNaturezaLancamento = FP.IdNaturezaLancamento",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Banco B ON B.IdBanco = BP.bank_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Agencia A ON A.IdAgencia = BP.agency_id"
                ],
                "fields" => [
                    "BP.bank_id",
                    "BP.agency_id",
                    "BP.modality_id",
                    "BP.agency_code",
                    "BP.check_number",
                    "BP.budget_payment_id",
                    "budget_payment_value=CAST(BP.budget_payment_value AS FLOAT)",
                    "BP.budget_payment_entry",
                    "BP.budget_payment_credit",
                    "BP.budget_payment_installment",
                    "budget_payment_deadline=FORMAT(BP.budget_payment_deadline, 'yyyy-MM-dd')",
                    "FP.IdFormaPagamento",
                    "FP.TpFormaPagamento",
                    "FP.DsFormaPagamento",
                    "FP.IdNaturezaLancamento",
                    "NL.IdTipoBaixa",
                    "B.IdBanco",
                    "B.NmBanco",
                    "A.IdAgencia",
                    "A.NrAgencia",
                    "A.NmAgencia",
                    "NrParcelas=(SELECT COUNT(*) FROM {$conn->dafel->table}.dbo.FormaPagamentoItem WHERE IdFormaPagamento = FP.IdFormaPagamento AND CdEmpresa = {$params->CdEmpresa})"
                ],
                "filters" => [["BP.budget_id", "i", "=", $params->budget_id]]
            ]);

            $ret = [];
            foreach($payments as $payment){
                $image = getImage((Object)[
                    "image_id" => $payment->modality_id,
                    "image_dir" => "modality"
                ]);
                if(!@$image){
                    $image = getImage((Object)[
                        "image_id" => $payment->TpFormaPagamento,
                        "image_dir" => "modality/type"
                    ]);
                }
                $payment->NrParcelas = (int)$payment->NrParcelas;
                if($payment->NrParcelas == 0 && $payment->TpFormaPagamento != "A"){
                    $payment->NrParcelas = 1;
                }
                $ret[] = (Object)[
                    "image" => $image,
                    "modality_id" => $payment->modality_id,
                    "modality_type" => $payment->TpFormaPagamento,
                    "budget_payment_id" => (int)$payment->budget_payment_id,
                    "budget_payment_value" => (float)$payment->budget_payment_value,
                    "budget_payment_entry" => $payment->budget_payment_entry,
                    "budget_payment_credit" => $payment->budget_payment_credit,
                    "budget_payment_deadline" => $payment->budget_payment_deadline,
                    "budget_payment_installment" => (int)$payment->budget_payment_installment,
                    "bank_id" => @$payment->bank_id ? $payment->bank_id : NULL,
                    "agency_id" => @$payment->agency_id ? $payment->agency_id : NULL,
                    "agency_code" => @$payment->NrAgencia ? $payment->NrAgencia : NULL,
                    "check_number" => @$payment->check_number ? $payment->check_number : NULL,
                    "modality" => (Object)[
                        "NrParcelas" => (int)$payment->NrParcelas,
                        "IdFormaPagamento" => $payment->IdFormaPagamento,
                        "TpFormaPagamento" => $payment->TpFormaPagamento,
                        "DsFormaPagamento" => $payment->DsFormaPagamento,
                        "IdNaturezaLancamento" => $payment->IdNaturezaLancamento,
                        "IdTipoBaixa" => $payment->IdTipoBaixa,
                        "StEntrada" => $payment->TpFormaPagamento == "D" ? "S" : "N",
                        "NrDias1aParcela" => $payment->TpFormaPagamento == "A" ? 30 : 1
                    ],
                    "bank" => (Object)[
                        "IdBanco" => @$payment->IdBanco ? $payment->IdBanco : NULL,
                        "NmBanco" => @$payment->NmBanco ? $payment->NmBanco : NULL,
                    ],
                    "agency" => (Object)[
                        "IdAgencia" => @$payment->IdAgencia ? $payment->IdAgencia : NULL,
                        "NrAgencia" => @$payment->NrAgencia ? $payment->NrAgencia : NULL,
                        "NmAgencia" => @$payment->NmAgencia ? $payment->NmAgencia : NULL,
                    ]
                ];
            }

            return $ret;
        }
    }

?>