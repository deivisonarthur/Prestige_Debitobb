<?php

class Prestige_Debitobb_Model_Standard extends Mage_Payment_Model_Method_Abstract {

	protected $_code = 'debitobb';
	protected $_formBlockType = 'debitobb/form';
// 	protected $_infoBlockType = 'debito_bb/debito_bb_info';
	protected $_canUseInternal = true;
	protected $_canUseForMultishipping = false;
	protected $_tpPagamento = 3;
	protected $_order = null;
	
	/**
	 * Get checkout session namespace
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}
	
	/**
	 *  Retorna pedido
	 *
	 *  @return	  Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
		if ($this->_order == null) {
			$orderIncrementId = $this->getCheckout()->getLastRealOrderId();
			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		}
		return $this->_order;
	}	
	
	/**
	 * getCheckoutFormFields
	 *
	 * Gera os campos para o formulário de redirecionamento ao Banco do Brasil
	 *
	 * @return array
	 */
	public function getCheckoutFormFields()
	{
		$order = $this->getOrder();
		
		
		// Utiliza endereço de cobrança caso produto seja virtual/para download
		$address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
		
                $idconv = $this->getConfigData('idconv', $order->getStoreId());
                $idconvc = $this->getConfigData('idconvc', $order->getStoreId());
                $url_retorno = $this->getConfigData('url_retorno', $order->getStoreId());
                $url_informa = $this->getConfigData('url_informa', $order->getStoreId());
	
		// Monta os dados para o formulário
		$fields = array(
				'idConv'		=> $idconv,
				'refTran'		=> $this->_geraRefTran($idconvc,$order->getRealOrderId()),
				'valor'			=> number_format($order->getGrandTotal(),2,'',''),
				'dtVenc'		=> date('dmY'),
				'tpPagamento'		=> $this->_tpPagamento,
				'urlRetorno'		=> $url_retorno,
				'urlInforma'		=> $url_informa,
				'nome'      		=> $address->getFirstname() . ' ' . $address->getLastname()
		);
		
// 		echo '<pre>';print_r($fields);exit;
	
	
		return $fields;
	}
	
	public function createRedirectForm()
	{
		$form = new Varien_Data_Form();
		$form->setAction($this->getBancoUrl())
		->setId('debitobb_checkout')
		->setName('pagamento')
		->setMethod('POST')
		->setUseContainer(true);
	
		$fields = $this->getCheckoutFormFields();
		foreach ($fields as $field => $value) {
			$form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
		}
	
		$submit_script = 'document.getElementById(\'debitobb_checkout\').submit();';
	
		$html  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$html .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="pt-BR">';
		$html .= '<head>';
		$html .= '<meta http-equiv="Content-Language" content="pt-br" />';
		$html .= '<meta name="language" content="pt-br" />';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
		$html .= '<style type="text/css">';
		$html .= '* { font-family: Arial; font-size: 16px; line-height: 34px; text-align: center; color: #222222; }';
		$html .= 'small, a, a:link:visited:active, a:hover { font-size: 13px; line-height: normal; font-style: italic; }';
		$html .= 'a, a:link:visited:active { font-weight: bold; text-decoration: none; }';
		$html .= 'a:hover { font-weight: bold; text-decoration: underline; color: #555555; }';
		$html .= '</style>';
		$html .= '</head>';
		$html .= '<body onload="' . $submit_script . '">';
		$html .= 'Você será redirecionado ao <strong>Banco do Brasil</strong> em alguns instantes.<br />';
		$html .= '<small>Se a página não carregar, <a href="#" onclick="' . $submit_script . ' return false;">clique aqui</a>.</small>';
		$html .= $form->toHtml();
		$html .= '</body></html>';
	
		return utf8_decode($html);
	
	}
	
	public function getBancoUrl()	{
		return 'https://www16.bancodobrasil.com.br/site/mpag/';
	}
	
	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl($this->getCode().'/standard/redirect', array('_secure' => true));
	}
        
        protected function _geraRefTran($idconvc,$nrOrder){
            $count_idconv = strlen($idconvc);
            $count_nrOrder = strlen($nrOrder);
            
            $refTran = $idconvc;
            
            for ($i = 0; $i < (($count_idconv+$count_nrOrder) - 17); $i++){
                $refTran.= '0';
            }
            
            $refTran.= $nrOrder;
            
            return $refTran;
        }
	
}