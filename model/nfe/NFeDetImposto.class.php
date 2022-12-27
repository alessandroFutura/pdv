<?php

    class NFeDetImposto
    {
        public $ICMS;
        public $PIS;
        public $COFINS;
        public $vFedTrib;
        public $vEstTrib;
        public $vMunTrib;
        public $vTotTrib;

        public function __construct($data)
        {
            $this->ICMS = new NFeDetImpostoICMS($data);
//            $this->PIS = $data->mod == 55 ? new NFeDetImpostoPIS($data->operation == "V" ? "99" : "01") : NULL;
//            $this->COFINS = $data->mod == 55 ? new NFeDetImpostoCOFINS($data->operation == "V" ? "99" : "01") : NULL;

            $this->vFedTrib = $this->_vFedTrib($data);
            $this->vEstTrib = $this->_vEstTrib($data);
            $this->vMunTrib = $this->_vMunTrib($data);
            $this->vTotTrib = $data->TpOperacao == "V" ? ($this->vFedTrib + $this->vEstTrib + $this->vMunTrib) : 0;
        }

        public function _vMunTrib($data)
        {
            $vMunTrib = 0;
            if($data->TpOperacao == "V"){
                $vMunTrib = $data->budget_item_value_total * $data->product->AlTributoMunicipal / 100;
            }
            return $vMunTrib;
        }

        public function _vEstTrib($data)
        {
            $vEstTrib = 0;
            if($data->TpOperacao == "V"){
                $vEstTrib = $data->budget_item_value_total * $data->product->AlTributoEstadual / 100;
            }
            return $vEstTrib;
        }

        public function _vFedTrib($data)
        {
            $vFedTrib = 0;
            if($data->TpOperacao == "V"){
                if(in_array($data->product->TpOrigemProduto, ["1","2","6","7"])){
                    $AlTributo = $data->product->AlTributoImportado;
                } else {
                    $AlTributo = $data->product->AlTributoNacional;
                }
                $vFedTrib = $data->budget_item_value_total * $AlTributo / 100;
            }
            return $vFedTrib;
        }


        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-det-imposto.xml");

            $xml->getElementsByTagName("vTotTrib")->item(0)->nodeValue = number_format($this->vTotTrib,2,".","");

            $ICMS = $this->ICMS->xml();
            $xml->getElementsByTagName("imposto")->item(0)->appendChild(
                $xml->importNode($ICMS->getElementsByTagName("ICMS")->item(0),TRUE)
            );

            if(@$this->PIS){
                $PIS = $this->PIS->xml();
                $xml->getElementsByTagName("imposto")->item(0)->appendChild(
                    $xml->importNode($PIS->getElementsByTagName("PIS")->item(0),TRUE)
                );
            }

            if(@$this->COFINS){
                $COFINS = $this->COFINS->xml();
                $xml->getElementsByTagName("imposto")->item(0)->appendChild(
                    $xml->importNode($COFINS->getElementsByTagName("COFINS")->item(0),TRUE)
                );
            }

            return $xml;
        }
    }

?>