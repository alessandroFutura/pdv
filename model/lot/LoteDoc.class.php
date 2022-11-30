<?php

    class LoteDoc
    {
        public static function add($params)
        {
            GLOBAL $dafel, $login;

            if(!@$params->IdDocumento){
                $params->IdDocumento = Model::nextCode($dafel, (Object)[
                    "table" => "Documento",
                    "field" => "IdDocumento",
                    "increment" => "S",
                    "base36encode" => 1
                ]);
            }

            Model::insert($dafel, (Object)[
                "table" => "LoteDoc",
                "fields" => [
                    ["IdDocumento", "s", $params->IdDocumento],
                    ["IdLoteEstoque", "s", $params->IdLoteEstoque],
                    ["IdOperacao", "s", $params->IdOperacao],
                    ["IdPessoa", "s", $params->IdPessoa],
                    ["CdEnderecoPrincipal", "s", $params->CdEndereco],
                    ["DtEmissao", "s", date("Y-m-d")],
                    ["DtReferencia", "s", date("Y-m-d")],
                    ["CdEspecie", "s", $params->CdEspecie],
                    ["CdSerieSubSerie", "s", $params->CdSerieSubSerie],
                    ["NrDocumento", "s", $params->NrDocumento],
                    ["StDocumentoCancelado", "s", @$params->StDocumentoCancelado ? $params->StDocumentoCancelado : "N"],
                    ["AlAcrescimo", "s", 0],
                    ["VlDesconto", "d", $params->VlDesconto],
                    ["VlFrete", "d", 0],
                    ["VlSeguro", "s", 0],
                    ["VlOutrasDespesas", "s", 0],
                    ["TpEdicao", "s", @$params->TpEdicao ? $params->TpEdicao : "I"],
                    ["TpAcrescimo", "s", "V"],
                    ["DsObservacao", "s", @$params->DsObservacao ? $params->DsObservacao : NULL],
                    ["AlDesconto", "d", $params->AlDesconto],
                    ["CdEnderecoEntrega", "s", $params->CdEndereco],
                    ["TpDesconto", "s", "V"],
                    ["CdEnderecoCobranca", "s", $params->CdEndereco],
                    ["IdUsuario", "s", $login->external_id],
                    ["IdEntidadeOrigem", "s", @$params->IdEntidadeOrigem ? $params->IdEntidadeOrigem : NULL],
                    ["NmEntidadeOrigem", "s", $params->NmEntidadeOrigem],
                    ["StGeraControleFiscal", "s", $params->StGeraControleFiscal],
                    ["VlComplementoICMS", "s", 0],
                    ["VlComplementoIPI", "s", 0],
                    ["TpFretePorConta", "s", @$params->TpFretePorConta ? $params->TpFretePorConta : NULL],
                    ["DsVolumes", "s", @$params->DsVolumes ? $params->DsVolumes : NULL],
                    ["DsEspecie", "s", $params->CdEspecie],
                    ["IdPrazo", "s", $params->IdPrazo],
                    ["DsPesoLiquido", "s", @$params->DsPesoLiquido ? $params->DsPesoLiquido : NULL],
                    ["StDocumentoImpresso", "s", @$params->StDocumentoImpresso ? $params->StDocumentoImpresso : NULL],
                    ["VlACT", "d", 0],
                    ["StCupomFiscalImpresso", "s", @$params->StCupomFiscalImpresso ? $params->StCupomFiscalImpresso : NULL],
                    ["IdMensagem1", "s", @$params->IdMensagem1 ? $params->IdMensagem1 : NULL],
                    ["IdMensagem2", "s", @$params->IdMensagem2 ? $params->IdMensagem2 : NULL],
                    ["IdMensagem3", "s", @$params->IdMensagem3 ? $params->IdMensagem3 : NULL],
                    ["IdMensagem4", "s", @$params->IdMensagem4 ? $params->IdMensagem4 : NULL],
                    ["IdCategoria", "s", @$params->IdCategoria ? $params->IdCategoria : NULL],
                    ["IdSistema", "s", $params->IdSistema],
                    ["DtCancelamento", "s", @$params->DtCancelamento ? $params->DtCancelamento : NULL],
                    ["AlICMSFrete", "s", 0],
                    ["VlICMSFrete", "s", 0],
                    ["StPrecisaAutorizacao", "s", "N"],
                    ["StAutorizacaoConcedida", "s", "N"],
                    ["DtReferenciaPagamento", "s", @$params->DtReferenciaPagamento ? $params->DtReferenciaPagamento : NULL],
                    ["StDocumentoLiberado", "s", "S"],
                    ["AlBaseINSS", "s", 0],
                    ["VlComplementoPIS", "s", 0],
                    ["VlComplementoCOFINS", "s", 0],
                    ["VlPISFrete", "s", 0],
                    ["AlPISFrete", "s", 0],
                    ["VlCOFINSFrete", "s", 0],
                    ["AlCOFINSFrete", "s", 0],
                    ["StNFEletronica", "s", $params->StNFEletronica],
                    ["DtCadastro", "s", @$params->DtCadastro ? $params->DtCadastro : NULL],
                    ["StNFEletronicaOriginal", "s", @$params->StNFEletronicaOriginal ? $params->StNFEletronicaOriginal : NULL],
                    ["CdChaveAcessoNFEletronica", "s", @$params->CdChaveAcessoNFEletronica ? $params->CdChaveAcessoNFEletronica : NULL],
                    ["IdUsuarioAlteracao", "s", $params->IdUsuarioAlteracao],
                    ["TpDocumento", "s", $params->TpDocumento],
                    ["NrProtocoloNFEletronica", "s", @$params->NrProtocoloNFEletronica ? $params->NrProtocoloNFEletronica : NULL],
                    ["VlDespesaAduaneira", "s", 0],
                    ["DtSaida", "s", date("Y-m-d H:i:s")],
                    ["TpPagamento", "s", @$params->TpPagamento ? $params->TpPagamento : NULL],
                    ["VlAntiDumping", "s", 0],
                    ["TpEntrega", "s", "N"],
                    ["VlAFRMM", "s", 0],
                    ["StNFEspecifica", "s", "N"],
                    ["TpIndAtendimentoPresencial", "s", 1],
                    ["VlPISRetido", "s", 0],
                    ["VlCOFINSRetido", "s", 0],
                    ["VlCSLLRetido", "s", 0],
                    ["StPrestacaoContas", "s", "N"],
                    ["VlDocumento", "d", $params->VlDocumento],
                    ["AlJurosParcelamento", "s", 0],
                    ["VlJurosParcelamento", "s", 0],
                    ["VlDescontoRegraComercializacao", "s", 0],
                    ["VlComplementoICMSST", "s", 0],
                    ["TpEnderecoPrestacaoServico", "s", 0],
                    ["StRetiradaEstabelecimento", "s", 0],
                    ["DtHoraEmissao", "s", date("Y-m-d H:i:s")],
                    ["VlTotalICMS", "s", 0],
                    ["VlTotalICMSSubstTributaria", "s", 0],
                    ["VlTotalIPI", "s", 0],
                    ["VlTotalPIS", "s", 0],
                    ["VlTotalCOFINS", "s", 0],
                    ["VlTotalItem", "s", 0],
                    ["VlTotalPagamento", "s", 0],
                    ["VlTotalFCP", "s", 0],
                    ["VlTotalFCPSubstTributaria", "s", 0],
                    ["VlTotalFCPSubstTributariaRetido", "s", 0],
                    ["VlTotalDesoneracaoICMS", "s", 0],
                    ["VlICMSSTFrete", "s", 0],
                    ["AlICMSSTFrete", "s", 0],
                    ["TpCalculoOutrasRetencoes", "s", 0],
                    ["VlOutrasRetencoes", "s", 0],
                    ["AlOutrasRetencoes", "s", 0]
                ]
            ]);

            return $params->IdDocumento;
        }

        public static function del($params)
        {
            GLOBAL $dafel;

            $items = Model::get($dafel, (Object)[
                "tables" => ["DocumentoItem"],
                "fields" => ["items=COUNT(*)"],
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            $payments = Model::get($dafel, (Object)[
                "tables" => ["DocumentoPagamento"],
                "fields" => ["payments=COUNT(*)"],
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            Model::delete($dafel, (Object)[
                "table" => "LoteDoc",
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            LoteDocItem::del((Object)[
                "top" => sizeof($items->items),
                "IdDocumento" => $params->IdDocumento
            ]);
            LoteDocPagamento::del((Object)[
                "top" => sizeof($payments->payments),
                "IdDocumento" => $params->IdDocumento
            ]);
            LoteDocItemRepasse::del((Object)[
                "top" => sizeof($items->items),
                "IdDocumento" => $params->IdDocumento
            ]);
        }
    }

?>