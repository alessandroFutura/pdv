<?php

    class NFeTransp
    {
        public $modFrete;
        public $qVol;
        public $pesoL;
        public $pesoB;

        public function __construct($data)
        {
            $this->modFrete = 9;
            $this->qVol = 0;
            $this->pesoL = 0;
            $this->pesoB = 0;
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-transp.xml");

            $xml->getElementsByTagName("modFrete")[0]->nodeValue = $this->modFrete;

            if($this->modFrete == 9){
                $node = $xml->getElementsByTagName("vol")[0];
                $node->parentNode->removeChild($node);
            }

            return $xml;
        }
    }

?>