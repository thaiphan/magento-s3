<?php

class Thai_S3_Helper_Core_File_Storage_Database extends Mage_Core_Helper_File_Storage_Database
{
    private $useS3 = null;

    /**
     * Check whether we are using the database or S3 for storing images.
     *
     * @return bool
     */
    public function checkDbUsage()
    {
        if (!parent::checkDbUsage()) {
            return $this->checkS3Usage();
        }
        return $this->_useDb;
    }

    /**
     * Check whether we are using S3 for storing images.
     *
     * @return bool
     */
    public function checkS3Usage()
    {
        if (is_null($this->useS3)) {
            $currentStorage = (int) Mage::app()->getConfig()
                ->getNode(Mage_Core_Model_File_Storage::XML_PATH_STORAGE_MEDIA);
            $this->useS3 = $currentStorage == Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3;
        }
        return $this->useS3;
    }

    /**
     * @return Thai_S3_Model_Core_File_Storage_S3|Mage_Core_Model_File_Storage_Database
     */
    public function getStorageDatabaseModel()
    {
        if (is_null($this->_databaseModel)) {
            if ($this->checkS3Usage()) {
                $this->_databaseModel = Mage::getModel('thai_s3/core_file_storage_s3');
            }
        }
        return parent::getStorageDatabaseModel();
    }

    public function saveFileToFilesystem($filename)
    {
        if ($this->checkDbUsage()) {
            $storageModel = $this->getStorageDatabaseModel();
            $file = $storageModel
                ->loadByFilename($this->_removeAbsPathFromFileName($filename));
            if (!$file->getId()) {
                return false;
            }

            return $this->getStorageFileModel()->saveFile($file, true);
        }
        return false;
    }

    /**
     * Removes any forward slashes from the start of the uploaded file name.
     * This addresses a bug where category pages were being saved with duplicate
     * slashes, e.g. catalog/category//tswifty_4.jpg.
     *
     * @param array $result
     * @return string
     */
    public function saveUploadedFile($result = array())
    {
        $file = parent::saveUploadedFile($result);
        $file = ltrim($file, '/');
        return $file;
    }
}
