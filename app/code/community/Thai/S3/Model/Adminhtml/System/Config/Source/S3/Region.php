<?php

class Thai_S3_Model_Adminhtml_System_Config_Source_S3_Region
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Thai_S3_Helper_S3 $helper */
        $helper = Mage::helper('thai_s3/s3');
        return $helper->getRegions();
    }
}
