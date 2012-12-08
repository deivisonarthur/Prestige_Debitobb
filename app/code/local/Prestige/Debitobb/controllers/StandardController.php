<?php

class Prestige_Debitobb_StandardController extends Mage_Core_Controller_Front_Action
{
    
	/**
	 * Retorna o singleton do Debitobb
	 *
	 * @return Prestige_Debitobb_Model_Payment
	 */
	public function getDebitobb()
	{
		return Mage::getSingleton('debitobb/standard');
	}
	
	/**
	 * Retorna o Checkout
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	public function getCheckout()
	{
		return Mage::getSingleton('checkout/session');
	}
	
	/**
	 * Redireciona o cliente ao Banco do Brasil na finalização do pedido
	 *
	 */
	public function redirectAction()
	{
		
		$debito_bb = $this->getDebitobb();
		$session = $this->getCheckout();
		
		$orderIncrementId = $session->getLastRealOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		
		if ($order->getId()) {
			
			// Envia email de confirmação ao cliente
			if(!$order->getEmailSent()) {
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				$order->save();
			}
			
			// Grava ID do pedido na sessÃ£o e exibe formulário de redirecionamento do Banco do Brasil
			Mage::getSingleton("core/session")->setDebitobbOrderId($orderIncrementId);
			
			$html = $debito_bb->createRedirectForm();
			
			$this->getResponse()->setHeader("Content-Type", "text/html; charset=ISO-8859-1", true);
			$this->getResponse()->setBody($html);
		}else {
            $this->_redirect('');
        }
	}
}
