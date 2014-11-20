<?php
class Borderfree_Settings_Block_Adminhtml_Payment
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = '<div id="merchant_allowed_methods_template">';
        $html .= $this->_getRowTemplateHtml($this->_getValue('method'));
        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieve html template for shipping method row
     *
     * @param int $rowIndex
     * @return string
     */
    protected function _getRowTemplateHtml($rowIndex = 0)
    {
        $html = '<select name="' . $this->getElement()->getName() . '" ' . $this->_getDisabled() . '>';
        $html .= '<option value="" style="background:white;">' . $this->__('* Select payment method') . '</option>';
        $html .= '<option value="checkmo" ' . $this->_getSelected('method/' . $rowIndex, "checkmo") . ' style="background:white;">' . $this->__('Borderfree ACH') . '</option>';
        
        foreach(Mage::helper('payment')->getPaymentMethods($this->getRequest()->getParam('store')) as $code => $method) 
        {
        	if(isset($method['active']) && $method['active'] == 1 && isset($method['cctypes']))
        	{
        	$html .= '<option value="' . $this->escapeHtml($code) . '" '
                    . $this->_getSelected('method/' . $rowIndex, $code)
                    . ' style="background:white;">' . $this->escapeHtml($method['title']);
		if(Mage::getModel($method['model'])->canCapturePartial())
		{
		    $html .= ' - ' . Mage::helper('borderfreesettings')->__('(supports partial invoicing )');
                }
		else
		{
		    $html .= ' - ' . Mage::helper('borderfreesettings')->__('(does not support partial invoicing)');
		}
		$html .= '</option>';
    		}
        }

        $html .= '</select>';

        return $html;
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value');
    }

    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value') == $value ? 'selected="selected"' : '';
    }

}
