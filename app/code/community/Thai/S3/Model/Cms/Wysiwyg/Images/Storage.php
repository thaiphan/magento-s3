<?php

class Thai_S3_Model_Cms_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    private $s3Helper = null;

    public function getDirsCollection($path)
    {
        if ($this->getS3Helper()->checkS3Usage()) {
            /** @var Thai_S3_Model_Core_File_Storage_S3 $storageModel */
            $storageModel = $this->getS3Helper()->getStorageDatabaseModel();
            $subdirectories = $storageModel->getSubdirectories($path);

            foreach ($subdirectories as $directory) {
                $fullPath = rtrim($path, '/') . '/' . $directory['name'];
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
            }
        }
        return parent::getDirsCollection($path);
    }

    public function getFilesCollection($path, $type = null)
    {
        if ($this->getS3Helper()->checkS3Usage()) {
            /** @var Thai_S3_Model_Core_File_Storage_S3 $storageModel */
            $storageModel = $this->getS3Helper()->getStorageDatabaseModel();
            $files = $storageModel->getDirectoryFiles($path);

            /** @var Mage_Core_Model_File_Storage_File $fileStorageModel */
            $fileStorageModel = Mage::getModel('core/file_storage_file');
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }
        return parent::getFilesCollection($path, $type);
    }

    public function resizeFile($source, $keepRatio = true)
    {
        if ($dest = parent::resizeFile($source, $keepRatio)) {
            if ($this->getS3Helper()->checkS3Usage()) {
                /** @var Thai_S3_Model_Core_File_Storage_S3 $storageModel */
                $storageModel = $this->getS3Helper()->getStorageDatabaseModel();

                $filePath = ltrim(str_replace(Mage::getConfig()->getOptions()->getMediaDir(), '', $dest), DS);

                $storageModel->saveFile($filePath);
            }
        }
        return $dest;
    }

    public function getThumbsPath($filePath = false)
    {
        $mediaRootDir = Mage::getConfig()->getOptions()->getMediaDir();
        $thumbnailDir = $this->getThumbnailRoot();

        if ($filePath && strpos($filePath, $mediaRootDir) === 0) {
            $thumbnailDir .= DS . ltrim(dirname(substr($filePath, strlen($mediaRootDir))), DS);
        }

        return $thumbnailDir;
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
