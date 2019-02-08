<?php
require_once './abstract.php';
require_once '../app/Mage.php';

class Thai_S3_Shell_Import extends Mage_Shell_Abstract
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
        try {
            if (!$helper->getClient()->doesBucketExist($helper->getBucket())) {
                $errors[] = 'The configured S3 bucket does not exist.';
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
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
        if (Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3 != $helper->getCurrentStorageCode()) {
            echo "\033[1mYou are currently not using S3!\033[0m\n\nYou are not using S3 as your media file storage backend! Reverting from using S3\nto using the local file system backend is not necessary.\n";

            return $this;
        }

        /** @var Thai_S3_Model_Core_File_Storage_S3 $sourceModel */
        $sourceModel = $helper->getStorageModel(Thai_S3_Model_Core_File_Storage::STORAGE_MEDIA_S3);

        /** @var Mage_Core_Model_File_Storage_File $destinationModel */
        $destinationModel = $helper->getStorageModel(Mage_Core_Model_File_Storage::STORAGE_MEDIA_FILE_SYSTEM);

        $offset = 0;
        while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
            foreach ($files as $file) {
                echo sprintf("Importing %s from S3.\n", $file['directory'] . '/' . $file['filename']);
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
    This script allows the developer to re-import all media files from S3 back 
    on your local filesystem via the command line.

    \033[1mNOTE:\033[0m Please make sure to back up your media files before you run this!
    You never know what might happen!

\033[1mSYNOPSIS\033[0m
    php s3_import.php [--dry-run]
                      [-h] [--help]

\033[1mOPTIONS\033[0m
    --dry-run
        This parameter will allow developers to simulate importing media files
        from S3 to your local filesystem without actually downloading anything!


USAGE;
    }
}

$shell = new Thai_S3_Shell_Import();
$shell->run();
