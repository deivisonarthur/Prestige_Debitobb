<?php

class Prestige_Debitobb_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('prestige/debitobb/form.phtml');
    }
    
}