<?php
class Borderfree_Settings_Block_Adminhtml_Shipping_State
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
        $html .= '<option value="">' . $this->__('Select Ship-To State') . '</option>';

        foreach ($this->state_list as $stateCode => $state) 
        {
            $html .= '<option value="' . $stateCode . '" '
                    . $this->_getSelected($stateCode)
                    . ' style="background:white;">' . $state . '</option>';
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

    protected function _getSelected($value)
    {
        return $this->getElement()->getData('value') == $value ? 'selected="selected"' : '';
    }

    private $state_list = array('AL'=>"Alabama",
    		'AK'=>"Alaska",
    		'AZ'=>"Arizona",
    		'AR'=>"Arkansas",
    		'CA'=>"California",
    		'CO'=>"Colorado",
    		'CT'=>"Connecticut",
    		'DE'=>"Delaware",
    		'DC'=>"District of Coloumbia",
    		'FL'=>"Flordia",
    		'GA'=>"Georgia",
    		'HI'=>"Hawaii",
    		'ID'=>"Idaho",
    		'IL'=>"Illinois",
    		'IN'=>"Indiana",
    		'IA'=>"Iowa",
    		'KS'=>"Kansas",
    		'KY'=>"Kentucky",
    		'LA'=>"Louisiana",
    		'ME'=>"Maine",
    		'MD'=>"Maryland",
    		'MA'=>"Massachusetts",
    		'MI'=>"Michigan",
    		'MN'=>"Minnesota",
    		'MS'=>"Mississippi",
    		'MO'=>"Missouri",
    		'MT'=>"Montana",
    		'NE'=>"Nebraska",
    		'NV'=>"Nevada",
    		'NH'=>"New Hampshire",
    		'NJ'=>"New Jersey",
    		'NM'=>"New Mexico",
    		'NY'=>"New York",
    		'NC'=>"North Carolina",
    		'ND'=>"North Dakota",
    		'OH'=>"Ohio",
    		'OK'=>"Oklahoma",
    		'OR'=>"Oregon",
    		'PA'=>"Pennsylvania",
    		'RI'=>"Rhode Island",
    		'SC'=>"South Carolina",
    		'SD'=>"South Dakota",
    		'TN'=>"Tennessee",
    		'TX'=>"Texas",
    		'UT'=>"Utah",
    		'VT'=>"Vermont",
    		'VA'=>"Virginia",
    		'WA'=>"Washington",
    		'WV'=>"West Virginia",
    		'WI'=>"Wisconson",
    		'WY'=>"Wyoming");
    
}
