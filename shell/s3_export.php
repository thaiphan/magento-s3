<?php
require_once './abstract.php';

class Thai_S3_Shell_Export extends Mage_Shell_Abstract
{
    protected function _validate()
    {
        $errors = [];

        /** @var Thai_S3_Helper_Data $helper */
        $helper = Mage::helper('thai_s3');
        if (is_null($helper->getAccessKey())) {
            $errors[] = 'You have not provided an AWS access key ID. You can do so using our config script.';
        }
        if (is_null($helper->getSecretKey())) {
            $errors[] = 'You have not provided an AWS secret access key. You can do so using our config script.';
        }
        if (is_null($helper->getBucket())) {
            $errors[] = 'You have not provided an S3 bucket. You can do so using our config script.';
        }
        if (is_null($helper->getRegion())) {
            $errors[] = 'You have not provided an S3 region. You can do so using our config script.';
        }
        if (!$helper->getClient()->isBucketAvailable($helper->getBucket())) {
            $errors[] = 'The AWS credentials you provided did not work. Please review your details and try again. You can do so using our config script.';
        }

        if (empty($errors)) {
            parent::_validate();
        } else {
            foreach ($errors as $error) {
                echo $error . "\n";
                die();
            }
        }
    }

    public function run()
    {
        /** @var Mage_Core_Helper_File_Storage $helper */
        $helper = Mage::helper('core/file_storage');

        // Stop S3 from syncing to itself
        if (Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3 == $helper->getCurrentStorageCode()) {
            echo "\033[1mYou cannot sync from S3 to itself!\033[0m\n\nYou are already using S3 as your media file storage backend! Please revert\nback to using the previous storage backend before trying again.\n";

            return $this;
        }

        /** @var Mage_Core_Model_File_Storage_File|Mage_Core_Model_File_Storage_Database $sourceModel */
        $sourceModel = $helper->getStorageModel();

        /** @var Thai_S3_Model_Core_File_Storage_S3 $destinationModel */
        $destinationModel = $helper->getStorageModel(Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3);

        $offset = 0;
        while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
            foreach ($files as $file) {
                echo sprintf("Uploading %s to S3.\n", $file['directory'] . '/' . $file['filename']);
            }
            if (!$this->getArg('dry-run')) {
                $destinationModel->importFiles($files);
            }
            $offset += count($files);
        }
        unset($files);

        return $this;
    }

    public function usageHelp()
    {
        return <<<USAGE
\033[1mDESCRIPTION\033[0m
    This script allows the developer to export all media files from the current
    storage backend, i.e. file system or database, to S3 via the command line.

    \033[1mNOTE:\033[0m Please make sure to back up your media files before you run this!
    You never know what might happen!

\033[1mSYNOPSIS\033[0m
    php s3_export.php [--dry-run]
                      [-h] [--help]

\033[1mOPTIONS\033[0m
    --dry-run
        This parameter will allow developers to simulate exporting media files
        to S3 without actually uploading anything!


USAGE;
    }
}

$shell = new Thai_S3_Shell_Export();
$shell->run();
