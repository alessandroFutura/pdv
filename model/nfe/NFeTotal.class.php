<?php

    class NFeTotal
    {
        public $vBC;
        public $vICMS;
        public $vICMSDeson;
        public $vFCP;
        public $vBCST;
        public $vST;
        public $vFCPST;
        public $vFCPSTRet;
        public $vProd;
        public $vFrete;
        public $vSeg;
        public $vDesc;
        public $vII;
        public $vIPI;
        public $vIPIDevol;
        public $vPIS;
        public $vCOFINS;
        public $vOutro;
        public $vNF;
        public $vTotTrib;
        public $vCredICMSSN;

        public function __construct($data)
        {
            $this->vBC = 0;
            $this->vICMS = 0;
            $this->vICMSDeson = 0;
            $this->vFCP = 0;
            $this->vBCST = 0;
            $this->vST = 0;
            $this->vFCPST = 0;
            $this->vFCPSTRet = 0;
            $this->vProd = $data->budget_value;
            $this->vFrete = 0;
            $this->vSeg = 0;
            $this->vDesc = $data->budget_value_discount;
            $this->vII = 0;
            $this->vIPI = 0;
            $this->vIPIDevol = 0;
            $this->vPIS = 0;
            $this->vCOFINS = 0;
            $this->vOutro = 0;
            $this->vNF = $data->budget_value_total;

            $this->vFedTrib = 0;
            $this->vEstTrib = 0;
            $this->vMunTrib = 0;
            $this->vTotTrib = 0;
            $this->vCredICMSSN = 0;
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-total.xml");

            $xml->getElementsByTagName("vBC")[0]->nodeValue = number_format($this->vBC,2,".","");
            $xml->getElementsByTagName("vICMS")[0]->nodeValue = number_format($this->vICMS,2,".","");
            $xml->getElementsByTagName("vICMSDeson")[0]->nodeValue = number_format($this->vICMSDeson,2,".","");
            $xml->getElementsByTagName("vFCP")[0]->nodeValue = number_format($this->vFCP,2,".","");
            $xml->getElementsByTagName("vBCST")[0]->nodeValue = number_format($this->vBCST,2,".","");
            $xml->getElementsByTagName("vST")[0]->nodeValue = number_format($this->vST,2,".","");
            $xml->getElementsByTagName("vFCPST")[0]->nodeValue = number_format($this->vFCPST,2,".","");
            $xml->getElementsByTagName("vFCPSTRet")[0]->nodeValue = number_format($this->vFCPSTRet,2,".","");
            $xml->getElementsByTagName("vProd")[0]->nodeValue = number_format($this->vProd,2,".","");
            $xml->getElementsByTagName("vFrete")[0]->nodeValue = number_format($this->vFrete,2,".","");
            $xml->getElementsByTagName("vSeg")[0]->nodeValue = number_format($this->vSeg,2,".","");
            $xml->getElementsByTagName("vDesc")[0]->nodeValue = number_format($this->vDesc,2,".","");
            $xml->getElementsByTagName("vII")[0]->nodeValue = number_format($this->vII,2,".","");
            $xml->getElementsByTagName("vIPI")[0]->nodeValue = number_format($this->vIPI,2,".","");
            $xml->getElementsByTagName("vIPIDevol")[0]->nodeValue = number_format($this->vIPIDevol,2,".","");
            $xml->getElementsByTagName("vPIS")[0]->nodeValue = number_format($this->vPIS,2,".","");
            $xml->getElementsByTagName("vCOFINS")[0]->nodeValue = number_format($this->vCOFINS,2,".","");
            $xml->getElementsByTagName("vOutro")[0]->nodeValue = number_format($this->vOutro,2,".","");
            $xml->getElementsByTagName("vNF")[0]->nodeValue = number_format($this->vNF,2,".","");
            $xml->getElementsByTagName("vTotTrib")[0]->nodeValue = number_format($this->vTotTrib,2,".","");

            return $xml;
        }
    }    

?>