<?php

class Thai_S3_Model_System_Config_Backend_Headers extends Mage_Core_Model_Config_Data
{
    private $coreHelper = null;

    private $unserializeHelper = null;

    /**
     * @throws Exception
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->getUnserializeHelper()->unserialize($value);
        $value = $this->_encodeArrayFieldValue($value);
        $this->setValue($value);
    }

    protected function _beforeSave()
    {
        $value = $this->getValue();
        $value = $this->_decodeArrayFieldValue($value);
        $value = serialize($value);
        $this->setValue($value);
    }

    protected function _encodeArrayFieldValue(array $value)
    {
        $result = array();
        foreach ($value as $header => $headerValue) {
            $id = $this->getCoreHelper()->uniqHash('_');
            $result[$id] = array(
                'header' => $header,
                'value' => $headerValue,
            );
        }
        return $result;
    }

    protected function _decodeArrayFieldValue(array $value)
    {
        $result = array();
        unset($value['__empty']);
        foreach ($value as $row) {
            $header = $row['header'];
            $value = $row['value'];
            $result[$header] = $value;
        }
        return $result;
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        if (is_null($this->coreHelper)) {
            $this->coreHelper = Mage::helper('core');
        }
        return $this->coreHelper;
    }

    /**
     * @return Mage_Core_Helper_UnserializeArray
     */
    protected function getUnserializeHelper()
    {
        if (is_null($this->unserializeHelper)) {
            $this->unserializeHelper = Mage::helper('core/unserializeArray');
        }
        return $this->unserializeHelper;
    }
}
