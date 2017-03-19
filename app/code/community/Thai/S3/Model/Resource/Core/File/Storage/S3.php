<?php

class Thai_S3_Model_Resource_Core_File_Storage_S3
{
    private $helper = null;

    public function deleteFolder($folderName = '')
    {
        $folderName = rtrim($folderName, '/');
        if (!strlen($folderName)) {
            return;
        }

        $folderName .= '/';

        $objects = $this->getHelper()->getClient()->getObjectsByBucket($this->getHelper()->getBucket(), [
            'prefix' => $folderName
        ]);

        foreach ($objects as $object) {
            $this->getHelper()->getClient()->removeObject($this->getHelper()->getObjectKey($object));
        }
    }

    /**
     * @return Thai_S3_Helper_Data
     */
    protected function getHelper()
    {
        if (is_null($this->helper)) {
            $this->helper = Mage::helper('thai_s3');
        }
        return $this->helper;
    }
}
