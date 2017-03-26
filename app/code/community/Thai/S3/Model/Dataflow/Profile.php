<?php

class Thai_S3_Model_Dataflow_Profile extends Mage_Dataflow_Model_Profile
{
    /**
     * Hack to disable Mage_Dataflow from uploading CSV files to S3.
     */
    protected function _afterSave()
    {
        /** @var Thai_S3_Helper_Core_File_Storage $helper */
        $helper = Mage::helper('core/file_storage');
        $originalInternalStorageList = $helper->getInternalStorageList();

        // Add S3 to the list of file storage backends that are considered
        // "internal". Magento will ignore syncing new file uploads to S3 whilst
        // S3 is considered "internal".
        $helper->setInternalStorageList(array_merge(
            $originalInternalStorageList,
            [Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3]
        ));

        parent::_afterSave();

        // Restore to the original state, i.e. S3 is no longer considered
        // "internal".
        $helper->setInternalStorageList($originalInternalStorageList);
    }
}
