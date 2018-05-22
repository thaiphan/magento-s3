<?php

class Thai_S3_Block_Adminhtml_Form_Field_Headers extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('header', array(
            'label' => 'Header',
            'style' => 'width:120px',
        ));
        $this->addColumn('value', array(
            'label' => 'Value',
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = 'Add Custom Header';
    }
}
