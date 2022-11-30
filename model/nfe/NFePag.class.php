<?php

    class NFePag
    {
        public $vTroco;
        public $detPag;

        public function __construct($data)
        {
            $this->detPag = [];
            $this->vTroco = @$data->budget_value_change ? $data->budget_value_change : 0;

            foreach($data->payments as $payment){
                $detPag = new NFePagDetPag($payment);
                if($detPag->tPag == "01"){
                    $detPag->vPag += $this->vTroco;
                }
                $this->detPag[] = $detPag;
            }
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-pag.xml");

            $xml->getElementsByTagName("vTroco")[0]->nodeValue = number_format($this->vTroco,2,".","");

            foreach($this->detPag as $detPag){
                $pag = $detPag->xml();
                $xml->getElementsByTagName("pag")[0]->insertBefore(
                    $xml->importNode($pag->getElementsByTagName("detPag")[0],TRUE),
                    $xml->getElementsByTagName("vTroco")[0]
                );
            }

            return $xml;
        }
    }

?>