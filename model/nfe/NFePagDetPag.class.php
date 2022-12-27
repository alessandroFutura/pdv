<?php

    class NFePagDetPag
    {
        public $tPag;
        public $xPag;
        public $vPag;

        public function __construct($data)
        {
            $this->tPag = $this->_tPag($data);
            $this->xPag = $data->external->DsFormaPagamento;
            $this->vPag = $data->budget_payment_value;
        }

        public function _tPag($data)
        {
            // Novas formas de pagamento para o campo tPag:
            // 01 - Dinheiro
            // 02 - Cheque
            // 03 - Cartão de Crédito
            // 04 - Cartão de Débito
            // 05 - Crédito Loja
            // 10 - Vale Alimentação
            // 11 - Vale Refeição
            // 12 - Vale Presente
            // 13 - Vale Combustível
            // 14 - Duplicata Mercantil
            // 15 - Boleto Bancário
            // 90 - Sem Pagamento
            // 99 - Outros

            switch($data->external->TpFormaPagamento){
                case "D": return "01"; break;
                case "A": return $data->external->TpCartao == "0" ? "03" : "04"; break;
                default: return NULL; break;
            }

            // Novos valores definidos para o campo tBand (card):
            // 01 - Visa
            // 02 - Mastercard
            // 03 - American Express
            // 04 - Sorocred
            // 05 - Diners Club
            // 06 - Elo
            // 07 - Hipercard
            // 08 - Aura
            // 09 - Cabal
            // 99 - Outros

            //return sizeof($data->payments) == 1 ? $data->payments[0]->TpFormaPagamento : 90;
        }

        public function _vPag($data)
        {
            $vPag = 0;
            if($data->TpOperacao != "D"){
                $vPag = $data->budget_value_total + (@$data->budget_value_change ? $data->budget_value_change : 0);
            }
            return $vPag;
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-pag-detPag.xml");

            $xml->getElementsByTagName("tPag")->item(0)->nodeValue = $this->tPag;
            $xml->getElementsByTagName("vPag")->item(0)->nodeValue = number_format($this->vPag,2,".","");

            if($this->tPag != "03" && $this->tPag != "04"){
                $node = $xml->getElementsByTagName("card")->item(0);
                $node->parentNode->removeChild($node);
            }

            if($this->tPag == "99"){
                $xml->getElementsByTagName("xPag")->item(0)->nodeValue = $this->xPag;
            } else {
                $node = $xml->getElementsByTagName("xPag")->item(0);
                $node->parentNode->removeChild($node);
            }

            return $xml;
        }
    }

?>