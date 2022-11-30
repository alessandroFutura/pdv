<?php

    class Documento
    {
        public $IdDocumento;
        public $IdLoteEstoque;
        public $IdOperacao;
        public $IdPessoa;
        public $CdEndereco;
        public $CdEspecie;
        public $CdSerieSubSerie;
        public $NrDocumento;
        public $StDocumentoCancelado;
        public $VlDesconto;
        public $DsObservacao;
        public $AlDesconto;
        public $IdEntidadeOrigem;
        public $NmEntidadeOrigem;
        public $StGeraControleFiscal;
        public $IdPrazo;
        public $StDocumentoImpresso;
        public $StCupomFiscalImpresso;
        public $IdCategoria;
        public $IdMensagem1;
        public $IdMensagem2;
        public $IdMensagem3;
        public $IdMensagem4;
        public $IdSistema;
        public $DtCancelamento;
        public $DtReferenciaPagamento;
        public $DtCadastro;
        public $StNFEletronica;
        public $IdUsuarioAlteracao;
        public $TpDocumento;
        public $TpPagamento;
        public $VlDocumento;
        public $StNFEletronicaOriginal;
        public $CdChaveAcessoNFEletronica;
        public $NrProtocoloNFEletronica;

        public function __construct($data)
        {
            $this->IdDocumento = @$data->IdDocumento ? $data->IdDocumento : NULL;
            $this->IdLoteEstoque = @$data->IdLoteEstoque ? $data->IdLoteEstoque : NULL;
            $this->IdOperacao = @$data->IdOperacao ? $data->IdOperacao : NULL;
            $this->IdPessoa = @$data->IdPessoa ? $data->IdPessoa : NULL;
            $this->CdEndereco = @$data->CdEndereco ? $data->CdEndereco : NULL;
            $this->CdEspecie = @$data->CdEspecie ? $data->CdEspecie : NULL;
            $this->CdSerieSubSerie = @$data->CdSerieSubSerie ? $data->CdSerieSubSerie : NULL;
            $this->NrDocumento = @$data->NrDocumento ? $data->NrDocumento : NULL;
            $this->StDocumentoCancelado = @$data->StDocumentoCancelado ? $data->StDocumentoCancelado : NULL;
            $this->VlDesconto = @$data->VlDesconto ? $data->VlDesconto : NULL;
            $this->DsObservacao = @$data->DsObservacao ? $data->DsObservacao : NULL;
            $this->AlDesconto = @$data->AlDesconto ? $data->AlDesconto : NULL;
            $this->IdEntidadeOrigem = @$data->IdEntidadeOrigem ? $data->IdEntidadeOrigem : NULL;
            $this->NmEntidadeOrigem = @$data->NmEntidadeOrigem ? $data->NmEntidadeOrigem : NULL;
            $this->StGeraControleFiscal = @$data->StGeraControleFiscal ? $data->StGeraControleFiscal : NULL;
            $this->IdPrazo = @$data->IdPrazo ? $data->IdPrazo : NULL;
            $this->StDocumentoImpresso = @$data->StDocumentoImpresso ? $data->StDocumentoImpresso : NULL;
            $this->StCupomFiscalImpresso = @$data->StCupomFiscalImpresso ? $data->StCupomFiscalImpresso : NULL;
            $this->IdCategoria = @$data->IdCategoria ? $data->IdCategoria : NULL;
            $this->IdMensagem1 = @$data->IdMensagem1 ? $data->IdMensagem1 : NULL;
            $this->IdMensagem2 = @$data->IdMensagem2 ? $data->IdMensagem2 : NULL;
            $this->IdMensagem3 = @$data->IdMensagem3 ? $data->IdMensagem3 : NULL;
            $this->IdMensagem4 = @$data->IdMensagem4 ? $data->IdMensagem4 : NULL;
            $this->IdSistema = @$data->IdSistema ? $data->IdSistema : NULL;
            $this->DtCancelamento = @$data->DtCancelamento ? $data->DtCancelamento : NULL;
            $this->DtReferenciaPagamento = @$data->DtReferenciaPagamento ? $data->DtReferenciaPagamento : NULL;
            $this->DtCadastro = @$data->DtCadastro ? $data->DtCadastro : NULL;
            $this->StNFEletronica = @$data->StNFEletronica ? $data->StNFEletronica : NULL;
            $this->IdUsuarioAlteracao = @$data->IdUsuarioAlteracao ? $data->IdUsuarioAlteracao : NULL;
            $this->TpDocumento = @$data->TpDocumento ? $data->TpDocumento : NULL;
            $this->TpPagamento = @$data->TpPagamento ? $data->TpPagamento : NULL;
            $this->VlDocumento = @$data->VlDocumento ? $data->VlDocumento : NULL;
            $this->StNFEletronicaOriginal = @$data->StNFEletronicaOriginal ? $data->StNFEletronicaOriginal : NULL;
            $this->CdChaveAcessoNFEletronica = @$data->CdChaveAcessoNFEletronica ? $data->CdChaveAcessoNFEletronica : NULL;
            $this->NrProtocoloNFEletronica = @$data->NrProtocoloNFEletronica ? $data->NrProtocoloNFEletronica : NULL;

            $this->items = DocumentoItem::getList((Object)[
                 "IdDocumento" => $data->IdDocumento
            ]);

            $this->payments = DocumentoPagamento::getList((Object)[
                "IdDocumento" => $data->IdDocumento
            ]);
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["Documento (NoLock)"],
                "fields" => [
                    "IdDocumento",
                    "IdLoteEstoque",
                    "IdOperacao",
                    "IdPessoa",
                    "CdEndereco=CdEnderecoPrincipal",
                    "CdEspecie",
                    "CdSerieSubSerie",
                    "NrDocumento",
                    "StDocumentoCancelado",
                    "VlDesconto=CAST(VlDesconto AS FLOAT)",
                    "DsObservacao",
                    "AlDesconto=CAST(AlDesconto AS FLOAT)",
                    "IdEntidadeOrigem",
                    "NmEntidadeOrigem",
                    "StGeraControleFiscal",
                    "IdPrazo",
                    "StDocumentoImpresso",
                    "StCupomFiscalImpresso",
                    "IdCategoria",
                    "IdMensagem1",
                    "IdMensagem2",
                    "IdMensagem3",
                    "IdMensagem4",
                    "IdSistema",
                    "DtCancelamento=FORMAT(DtCancelamento, 'yyyy-MM-dd')",
                    "DtReferenciaPagamento=FORMAT(DtReferenciaPagamento, 'yyyy-MM-dd')",
                    "DtCadastro=FORMAT(DtCadastro, 'yyyy-MM-dd')",
                    "StNFEletronica",
                    "IdUsuarioAlteracao",
                    "TpDocumento",
                    "TpPagamento",
                    "VlDocumento=CAST(VlDocumento AS FLOAT)",
                    "StNFEletronicaOriginal",
                    "CdChaveAcessoNFEletronica",
                    "NrProtocoloNFEletronica",
                ],
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            if(@$data){
                return new Documento($data);
            }

            return NULL;
        }
    }

?>