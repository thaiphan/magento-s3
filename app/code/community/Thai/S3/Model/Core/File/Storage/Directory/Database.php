<?php

class Thai_S3_Model_Core_File_Storage_Directory_Database extends Mage_Core_Model_File_Storage_Directory_Database
{
    /**
     * Return subdirectories
     *
     * @param string $directory
     * @return array
     */
    public function getSubdirectories($directory)
    {
        $directory = Mage::helper('core/file_storage_database')->getMediaRelativePath($directory);

        try {
            return $this->_getResource()->getSubdirectories($directory);
        } catch (Exception $e) {
            return [];
        }
    }
}
