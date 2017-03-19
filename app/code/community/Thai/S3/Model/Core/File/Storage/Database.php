<?php

class Thai_S3_Model_Core_File_Storage_Database extends Mage_Core_Model_File_Storage_Database
{
    public function loadByFilename($filePath)
    {
        $storage = Mage::helper('core/file_storage')->getCurrentStorageCode();
        if ($storage == Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3) {
            /** @var Thai_S3_Model_Core_File_Storage_S3 $s3StorageModel */
            $s3StorageModel = Mage::getModel('thai_s3/core_file_storage_s3');
            $s3StorageModel->loadByFilename($filePath);

            if ($s3StorageModel->getData('id')) {
                $this->setData('id', $s3StorageModel->getData('id'));
                $this->setData('filename', $s3StorageModel->getData('filename'));
                $this->setData('content', $s3StorageModel->getData('content'));
            }

            return $this;
        }
        return parent::loadByFilename($filePath);
    }

    /**
     * Return directory listing
     *
     * @param string $directory
     * @return mixed
     */
    public function getDirectoryFiles($directory)
    {
        $directory = Mage::helper('core/file_storage_database')->getMediaRelativePath($directory);

        try {
            return $this->_getResource()->getDirectoryFiles($directory);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getId()
    {
        $storage = Mage::helper('core/file_storage')->getCurrentStorageCode();
        if ($storage == Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3) {
            return $this->getData('id');
        }
        return parent::getId();
    }
}
