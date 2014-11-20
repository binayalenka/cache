<?php
class Borderfree_Settings_Block_Adminhtml_Shipping_Merchant extends Mage_Adminhtml_Block_System_Config_Form_Field
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
        $html .= '<option value="">' . $this->__('* Select shipping method') . '</option>';

        foreach ($this->getShippingMethods() as $carrierCode => $carrier) {
            $html .= '<optgroup label="' . $this->escapeHtml($carrier['title'])
                . '" style="border-top:solid 1px black; margin-top:3px;">';

            foreach ($carrier['methods'] as $methodCode => $method) {
                $code = $carrierCode . '_' . $methodCode;
                $html .= '<option value="' . $this->escapeHtml($code) . '" '
                    . $this->_getSelected('method/' . $rowIndex, $code)
                    . ' style="background:white;">' . $this->escapeHtml($method['title']) . '</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';

        return $html;
    }

    protected function getShippingMethods()
    {
        if (!$this->hasData('shipping_methods')) {
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $storeId = null;
            if (!is_null($website)) {
                $storeId = Mage::getModel('core/website')
                    ->load($website, 'code')
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
            } elseif (!is_null($store)) {
                $storeId = Mage::getModel('core/store')
                    ->load($store, 'code')
                    ->getId();
            }

            $methods = array();
            $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers($storeId);
            foreach ($carriers as $carrierCode=>$carrierModel) {
                if (!$carrierModel->isActive()) {
                    continue;
                }
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
                $methods[$carrierCode] = array(
                    'title'   => $carrierTitle,
                    'methods' => array(),
                );
                foreach ($carrierMethods as $methodCode=>$methodTitle) {
                    $methods[$carrierCode]['methods'][$methodTitle] = array(
                        'title' => '[' . $carrierCode . '] ' . $methodTitle,
                    );
                }
            }
            $this->setData('shipping_methods', $methods);
        }
        return $this->getData('shipping_methods');
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
