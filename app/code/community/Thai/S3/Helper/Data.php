<?php

class Thai_S3_Helper_Data extends Mage_Core_Helper_Data
{
    private $client = null;

    /**
     * @return Thai_Service_Amazon_S3
     * @throws Zend_Service_Amazon_S3_Exception
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->client = new Thai_Service_Amazon_S3(
                $this->getAccessKey(),
                $this->getSecretKey(),
                $this->getRegion()
            );
            if ($this->getCustomEndpointEnabled()) {
                $this->client->setEndpoint($this->getCustomEndpoint());
            }
        }
        return $this->client;
    }

    /**
     * Get the key used to reference the file in S3.
     *
     * @param string $filePath
     * @return string
     */
    public function getObjectKey($filePath)
    {
        $prefix = $this->getPrefix();
        if ($prefix) {
            $filePath = ltrim($prefix, '/') . '/' . $filePath;
        }
        return $this->getBucket() . '/' . $filePath;
    }

    /**
     * Returns the AWS access key.
     *
     * @return string
     */
    public function getAccessKey()
    {
        return Mage::getStoreConfig('thai_s3/general/access_key');
    }

    /**
     * Returns the AWS secret key.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return Mage::getStoreConfig('thai_s3/general/secret_key');
    }

    /**
     * Returns the AWS region that we're using, e.g. ap-southeast-2.
     *
     * @return string
     */
    public function getRegion()
    {
        return Mage::getStoreConfig('thai_s3/general/region');
    }

    /**
     * Returns the S3 bucket where we want to store all our images.
     *
     * @return string
     */
    public function getBucket()
    {
        return Mage::getStoreConfig('thai_s3/general/bucket');
    }

    /**
     * Returns the string that we want to prepend to all of our S3 object keys.
     *
     * @return string
     */
    public function getPrefix()
    {
        return Mage::getStoreConfig('thai_s3/general/prefix');
    }

    /**
     * @return bool
     */
    public function getCustomEndpointEnabled()
    {
        return (bool)Mage::getStoreConfig('thai_s3/general/custom_endpoint_enabled');
    }

    /**
     * @return string
     */
    public function getCustomEndpoint()
    {
        return Mage::getStoreConfig('thai_s3/general/custom_endpoint');
    }
}
