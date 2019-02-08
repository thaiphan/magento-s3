<?php
require_once './abstract.php';

class Thai_S3_Shell_Config extends Mage_Shell_Abstract
{
    protected function _validate()
    {
        if (empty($this->getArg('h')) && empty($this->getArg('help')) && empty($this->getArg('list'))) {
            $errors = [];
            if ($this->getArg('region')) {
                /** @var Thai_S3_Helper_S3 $helper */
                $helper = Mage::helper('thai_s3/s3');
                if (!$helper->isValidRegion($this->getArg('region'))) {
                    $errors[] = sprintf('The region "%s" is invalid.', $this->getArg('region'));
                }
            }
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo $error . "\n";
                }

                echo "\nusage: php s3_config.php [options]\n\n";
                echo "    --list                         list current AWS credentials\n";
                echo "    --access-keyid <access-key-id> a valid AWS access key ID\n";
                echo "    --secret-key <secret-key>      a valid AWS secret access key\n";
                echo "    --bucket <bucket>              an S3 bucket name\n";
                echo "    --region <region>              an S3 region, e.g. us-east-1\n";
                echo "    --prefix <prefix>              a prefix to prepend to all your files\n";
                echo "    -h, --help\n\n";
                die();
            }

            parent::_validate();
        }
    }

    public function run()
    {
        if ($this->getArg('test')) {
            /** @var Thai_S3_Helper_Data $helper */
            $helper = Mage::helper('thai_s3');
            try {
                if (!$helper->getClient()->doesBucketExist($helper->getBucket())) {
                    throw new Exception("The configured S3 bucket does not exist.");
                }
                echo "The configuration is valid.\n";
                exit(0);
            } catch (Exception $e) {
                echo "ERROR: {$e->getMessage()}\n";
                exit(1);
            }
        }
        if (empty($this->getArg('list'))) {
            $updatedCredentials = false;
            if (!empty($this->getArg('access-key-id'))) {
                Mage::getConfig()->saveConfig('thai_s3/general/access_key', $this->getArg('access-key-id'));
                $updatedCredentials = true;
            }
            if (!empty($this->getArg('secret-key'))) {
                Mage::getConfig()->saveConfig('thai_s3/general/secret_key', $this->getArg('secret-key'));
                $updatedCredentials = true;
            }
            if (!empty($this->getArg('bucket'))) {
                Mage::getConfig()->saveConfig('thai_s3/general/bucket', $this->getArg('bucket'));
                $updatedCredentials = true;
            }
            if (!empty($this->getArg('region'))) {
                Mage::getConfig()->saveConfig('thai_s3/general/region', $this->getArg('region'));
                $updatedCredentials = true;
            }
            if (!empty($this->getArg('prefix'))) {
                Mage::getConfig()->saveConfig('thai_s3/general/prefix', $this->getArg('prefix'));
                $updatedCredentials = true;
            }

            if ($updatedCredentials) {
                echo "You have successfully updated your S3 credentials.\n";

                // Refresh the config cache
                Mage::app()->getConfig()->reinit();
            } else {
                echo $this->usageHelp();
            }
        } else {
            /** @var Thai_S3_Helper_Data $helper */
            $helper = Mage::helper('thai_s3');
            echo 'Here are your AWS credentials.';
            if ($this->getArg('access-key-id') || $this->getArg('secret-key') || $this->getArg('bucket') || $this->getArg('region') || $this->getArg('prefix')) {
                echo " \033[1mNo configuration setting was updated.\033[0m";
            }
            echo "\n\n";

            echo sprintf("Access Key ID:     %s\n", $helper->getAccessKey());
            echo sprintf("Secret Access Key: %s\n", $helper->getSecretKey());
            echo sprintf("Bucket:            %s\n", $helper->getBucket());
            echo sprintf("Region:            %s\n", $helper->getRegion());
            echo sprintf("Prefix:            %s\n", $helper->getPrefix());
        }

        return $this;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
\033[1mDESCRIPTION\033[0m
    Allows the developer to configure which S3 bucket they want to use with
    their Magento installation.

\033[1mSYNOPSIS\033[0m
    php s3_config.php [--list]
                      [--test]
                      [--access-key-id <access-key-id>]
                      [--secret-key <secret-key>]
                      [--bucket <bucket>]
                      [--region <region>]
                      [--prefix <prefix>]
                      [-h] [--help]

\033[1mOPTIONS\033[0m
    --list
        Lists whatever credentials for S3 you have provided for Magento.

        \033[1mNOTE:\033[0m Using this option will cause the script to ignore the other options.

    --test
        Test the credentials provided and confirm that the bucket exists.

        \033[1mNOTE:\033[0m Using this option will cause the script to ignore the other options.

    --access-key-id <access-key-id>
        You must provide a valid AWS access key ID. You can generate access keys
        using the AWS IAM (https://console.aws.amazon.com/iam/home).

    --secret-key <secret-key>
        You must also provide the secret access key that corresponds to the
        access key ID that you have just generated.

    --bucket <bucket>
        You must provide a valid S3 bucket name that you want media files to be
        uploaded to.

    --region <region>
        You must provide a valid S3 region. As 2016-03-17, S3 has the following
        regions:

        us-east-1
        us-west-1
        us-west-2
        eu-west-1
        eu-central-1
        ap-southeast-1
        ap-southeast-2
        ap-northeast-1
        ap-northeast-2
        sa-east-1

        You can review all valid S3 regions via the AWS documentation
        (http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region).

    --prefix <prefix>
        You can optionally provide a prefix that you want to preprend to all of
        your objects prior to uploading to S3.


USAGE;
    }
}

$shell = new Thai_S3_Shell_Config();
$shell->run();
