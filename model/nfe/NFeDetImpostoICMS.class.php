<?php

    class NFeDetImpostoICMS
    {
        public $orig;
        public $CSOSN;
        public $pCredSN;
        public $vCredICMSSN;

        public function __construct($data)
        {
            $this->orig = $data->product->TpOrigemProduto;
            $this->CSOSN = $this->_CSOSN($data);
            $this->pCredSN = @$data->pCredSN ? $data->pCredSN : NULL;
            $this->vCredICMSSN = @$data->vCredICMSSN ? $data->vCredICMSSN : NULL;
        }

        public function _CSOSN($data)
        {
            if($data->mod == 65){
                if(@$data->product->CSOSNPDV){
                    return $data->product->CSOSNPDV;
                } else if(@$data->product->CSOSN){
                    return $data->product->CSOSN;
                } else if(@$data->CSOSNEmpresaPDV){
                    return $data->CSOSNEmpresaPDV;
                } else {
                    return $data->CSOSNEmpresa;
                }
            } else {
                return @$data->product->CSOSN ? $data->product->CSOSN : $data->CSOSNEmpresa;
            }
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-det-imposto-icms.xml");

            $data = [
                "101" => "101",
                "102" => "102",
                "400" => "102",
                "500" => "500",
                "900" => "900"
            ];

            $data = $data[$this->CSOSN];
            foreach([101,102,500,900] as $csosn){
                if($data != $csosn){
                    $node = $xml->getElementsByTagName("ICMSSN{$csosn}")->item(0);
                    $node->parentNode->removeChild($node);
                }
            }

            $xml->getElementsByTagName("orig")->item(0)->nodeValue = $this->orig;
            $xml->getElementsByTagName("CSOSN")->item(0)->nodeValue = $this->CSOSN;

            if(@$this->pCredSN && @$this->vCredICMSSN){
                $xml->getElementsByTagName("pCredSN")->item(0)->nodeValue = number_format($this->pCredSN,2,".","");
                $xml->getElementsByTagName("vCredICMSSN")->item(0)->nodeValue = number_format($this->vCredICMSSN,2,".","");
            }

            return $xml;
        }
    }

?>