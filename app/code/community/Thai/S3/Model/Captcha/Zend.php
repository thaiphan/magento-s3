<?php

class Thai_S3_Model_Captcha_Zend extends Mage_Captcha_Model_Zend
{
    private $s3Helper = null;

    /**
     * Upload the generated CAPTCHA to S3.
     *
     * @param string $id
     * @param string $word
     */
    protected function _generateImage($id, $word)
    {
        parent::_generateImage($id, $word);

        if ($this->getS3Helper()->checkS3Usage()) {
            $this->getS3Helper()->saveFile($this->getImgDir() . $this->getId() . $this->getSuffix());
        }
    }

    /**
     * @return Thai_S3_Helper_Core_File_Storage_Database
     */
    protected function getS3Helper()
    {
        if (is_null($this->s3Helper)) {
            $this->s3Helper = Mage::helper('thai_s3/core_file_storage_database');
        }
        return $this->s3Helper;
    }
}
