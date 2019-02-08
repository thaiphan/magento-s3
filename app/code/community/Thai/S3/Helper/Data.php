<?php

class Thai_S3_Helper_Data extends Mage_Core_Helper_Data
{
    private $client = null;

    /**
     * @return \Aws\S3\S3Client
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region' => $this->getRegion(),
                'endpoint' => $this->getCustomEndpoint() ?: NULL,
                'credentials' => [
                    'key' => $this->getAccessKey(),
                    'secret' => $this->getSecretKey(),
                ],
            ]);
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
        return $filePath;
    }

    /**
     * Get the local path for the remote key (strip prefix)
     *
     * @param string $remoteKey
     * @return null|string|string[]
     */
    public function getLocalPath($remoteKey)
    {
        $prefix = $this->getPrefix();
        if ($prefix) {
            $remoteKey = preg_replace('/^'.preg_quote($prefix).'/', '', $remoteKey, 1);
        }
        return $remoteKey;
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
        $region = Mage::getStoreConfig('thai_s3/general/region');
        if ($region == '_custom_') {
            return Mage::getStoreConfig('thai_s3/general/custom_region');
        }
        return $region;
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
     * @return string
     */
    public function getCustomEndpoint()
    {
        return Mage::getStoreConfig('thai_s3/general/custom_endpoint');
    }
}
