<?php
require_once './abstract.php';

class Thai_S3_Shell_Disable extends Mage_Shell_Abstract
{
    /**
     * Update configuration to tell Magento that we are no longer using S3 in
     * favour of the local file system as the file storage backend.
     */
    public function run()
    {
        if ($this->getArg('y') || $this->getArg('yes')) {
            echo "Updating configuration to use the local filesystem.\n";

            Mage::getConfig()->saveConfig(
                'system/media_storage_configuration/media_storage',
                Mage_Core_Model_File_Storage::STORAGE_MEDIA_FILE_SYSTEM
            );
            Mage::app()->getConfig()->reinit();
        } else {
            echo $this->usageHelp();
        }

        return $this;
    }

    public function usageHelp()
    {
        return <<<USAGE
\033[1mDESCRIPTION\033[0m
    This script will set Magento to use the local filesystem (as opposed to S3)
    as the default file storage backend.

    \033[1mNOTE:\033[0m Please make sure to back up your media files before you run this!
    You never know what might happen!

\033[1mSYNOPSIS\033[0m
    php s3_disable.php [-y] [--yes]
                       [-h] [--help]

\033[1mOPTIONS\033[0m
    -y, --yes
        This parameter will confirm that you want to revert to using the local
        filesystem as your storage backend.


USAGE;
    }
}

$shell = new Thai_S3_Shell_Disable();
$shell->run();
