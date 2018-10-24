<?php

use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

class Thai_S3_Model_Core_File_Storage_S3 extends Mage_Core_Model_File_Storage_Abstract
{
    protected $_eventPrefix = 'thai_s3_core_file_storage_s3';

    protected $bucket;
    
    private $errors = [];

    private $exportMarker;

    protected function _construct()
    {
        $this->_init('thai_s3/core_file_storage_s3');
    }

    public function getIdFieldName()
    {
        return false;
    }

    public function init()
    {
        $this->bucket = $this->getHelper()->getBucket();
        
        return $this;
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return Mage::helper('thai_s3')->__('S3');
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function loadByFilename($filePath)
    {
        try {
            $result = $this->getClient()->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getObjectKey($filePath)
            ]);
            $this->setData('id', $filePath);
            $this->setData('filename', $filePath);
            $this->setData('content', $result['Body']);
        } catch (AwsException $e) {
            Mage::logException($e);
            $this->unsetData();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function clear()
    {
        $result = $this->getClient()->listObjects([
            'Bucket' => $this->bucket,
            'Prefix' => $this->getObjectKey(''),
        ]);

        while ( ! empty($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $this->getClient()->deleteObject(['Bucket' => $this->bucket, 'Key' => $object['Key']]);
            }
            if ($result['IsTruncated']) {
                $result = $this->getClient()->listObjects(['Bucket' => $this->bucket, 'Marker' => $result['NextMarker']]);
            } else {
                break;
            }
        }

        return $this;
    }

    public function exportDirectories($offset = 0, $count = 100)
    {
        return false;
    }

    public function importDirectories(array $dirs = [])
    {
        return $this;
    }

    public function exportFiles($offset = 0, $count = 100)
    {
        if ($offset == 0) {
            $this->exportMarker = NULL;
        }
        $result = $this->getClient()->listObjects([
            'Bucket' => $this->bucket,
            'MaxKeys' => min($count, 1000),
            'Marker' => $this->exportMarker
        ]);

        if ($result['IsTruncated']) {
            $this->exportMarker = $result['NextMarker'];
        }

        $files = [];
        foreach ($result['Contents'] as $object) {
            $_result = $this->getClient()->getObject(['Bucket' => $this->bucket, 'Key' => $object['Key']]);
            $files[] = [
                'filename' => $this->getLocalPath($object['Key']),
                'content' => $_result['Body'],
            ];
        }

        return $files;
    }

    /**
     * Upload the given array of files to S3.
     *
     * @param array $files
     * @return $this
     */
    public function importFiles(array $files = [])
    {
        foreach ($files as $file) {
            try {
                $filePath = $this->getFilePath($file['filename'], $file['directory']);
                $this->getClient()->putObject([
                    'Bucket' => $this->bucket,
                    'Key' => $this->getObjectKey($filePath),
                    'Body' => $file['content'],
                ] + $this->getMetadata($filePath));
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Get the file path of the file.
     *
     * @param string $filePath
     * @param string $prefix
     * @return string
     */
    private function getFilePath($filePath, $prefix = null)
    {
        if (!is_null($prefix)) {
            $filePath = $prefix . '/' . $filePath;
        }
        return $filePath;
    }

    /**
     * Upload the specific file to S3.
     *
     * @param string $filename
     * @return $this
     * @throws AwsException
     * @throws Exception
     */
    public function saveFile($filename)
    {
        $sourcePath = $this->getMediaBaseDirectory() . '/' . $filename;

        $this->getClient()->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getObjectKey($filename),
                'SourceFile' => $sourcePath,
            ] + $this->getMetadata($sourcePath));

        return $this;
    }

    /**
     * An array of the HTTP headers that we intend send to S3 alongside the
     * object.
     *
     * @param string $filename
     * @return array
     * @throws Exception
     */
    public function getMetadata($filename)
    {
        //$mimeType = $this->getClient()->getMimeType($filename);

        $meta = [
            //'ContentType' => $mimeType,
            'ACL' => 'public-read',
        ];

        $headers = Mage::getStoreConfig('thai_s3/general/custom_headers');
        if ($headers) {
            /** @var Mage_Core_Helper_UnserializeArray $unserializeHelper */
            $unserializeHelper = Mage::helper('core/unserializeArray');
            $headers = $unserializeHelper->unserialize($headers);

            foreach ($headers as $header => $value) {
                // TODO - support Metadata?
                $meta[$header] = $value;
            }
        }

        return $meta;
    }

    /**
     * Check whether a file exists in S3
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists($filePath)
    {
        try {
            $this->getClient()->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getObjectKey($filePath)
            ]);
            return TRUE;
        } catch (S3Exception $e) {
            return FALSE;
        }
    }

    public function copyFile($oldFilePath, $newFilePath)
    {
        $this->getClient()->copyObject([
            'Bucket' => $this->bucket,
            'CopySource' => $this->getObjectKey($oldFilePath),
            'Key' => $this->getObjectKey($newFilePath),
        ] + $this->getMetadata($oldFilePath));

        return $this;
    }

    public function renameFile($oldName, $newName)
    {
        $this->copyFile($oldName, $newName);
        $this->deleteFile($oldName);

        return $this;
    }

    public function getSubdirectories($path)
    {
        $prefix = Mage::helper('core/file_storage_database')->getMediaRelativePath($path);
        $prefix = rtrim($prefix, '/') . '/';

        $results = $this->getClient()->getPaginator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->getObjectKey($prefix),
            'Delimiter' => '/'
        ]);

        $subdirectories = [];
        foreach ($results->search('CommonPrefixes[].Prefix') as $item) {
            $subdirectories[] = ['name' => $item];
        }

        return $subdirectories;
    }

    public function getDirectoryFiles($directory)
    {
        $prefix = Mage::helper('core/file_storage_database')->getMediaRelativePath($directory);
        $prefix = rtrim($prefix, '/') . '/';

        $results = $this->getClient()->getPaginator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->getObjectKey($prefix),
            'Delimiter' => '/'
        ]);

        $files = [];
        foreach ($results->search('Contents[].Key') as $key) {
            $_result = $this->getClient()->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
            $files[] = [
                'filename' => $this->getLocalPath($key),
                'content' => $_result['Body'],
            ];
        }

        return $files;
    }

    public function deleteFile($path)
    {
        $this->getClient()->deleteObject(['Bucket' => $this->bucket, 'Key' => $this->getObjectKey($path)]);

        return $this;
    }

    /**
     * @return Thai_S3_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('thai_s3');
    }
    
    protected function getClient()
    {
        return $this->getHelper()->getClient();
    }
    
    protected function getObjectKey($file)
    {
        return $this->getHelper()->getObjectKey($file);
    }

    protected function getLocalPath($file)
    {
        return $this->getHelper()->getLocalPath($file);
    }
}
