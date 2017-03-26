<?php

class Thai_S3_Helper_Core_File_Storage extends Mage_Core_Helper_File_Storage
{
    /**
     * Part of hack to disable Mage_DataFlow from uploading CSVs to S3.
     *
     * @return array
     */
    public function getInternalStorageList()
    {
        return $this->_internalStorageList;
    }

    /**
     * Part of hack to disable Mage_DataFlow from uploading CSVs to S3.
     *
     * @param array $internalStorageList
     */
    public function setInternalStorageList(array $internalStorageList)
    {
        $this->_internalStorageList = $internalStorageList;
    }
}
