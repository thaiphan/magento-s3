<?php

class Thai_S3_Model_Core_File_Storage extends Mage_Core_Model_File_Storage
{
    const STORAGE_MEDIA_S3 = 2;

    public function getStorageModel($storage = null, $params = [])
    {
        $storageModel = parent::getStorageModel($storage, $params);
        if ($storageModel === false) {
            if (is_null($storage)) {
                $storage = Mage::helper('core/file_storage')->getCurrentStorageCode();
            }
            switch ($storage) {
                case self::STORAGE_MEDIA_S3:
                    /** @var Thai_S3_Model_Core_File_Storage_S3 $storageModel */
                    $storageModel = Mage::getModel('thai_s3/core_file_storage_s3');
                    break;
                default:
                    return false;
            }

            if (isset($params['init']) && $params['init']) {
                $storageModel->init();
            }
        }

        return $storageModel;
    }
}
