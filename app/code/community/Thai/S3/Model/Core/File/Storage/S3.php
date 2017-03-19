<?php

class Thai_S3_Model_Core_File_Storage_S3 extends Mage_Core_Model_File_Storage_Abstract
{
    protected $_eventPrefix = 'thai_s3_core_file_storage_s3';

    private $helper = null;

    private $errors = [];

    private $objects = [];

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
        return $this;
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return Mage::helper('thai_s3')->__('S3');
    }

    public function loadByFilename($filePath)
    {
        $object = $this->getHelper()->getClient()->getObject($this->getHelper()->getObjectKey($filePath));
        if ($object) {
            $this->setData('id', $filePath);
            $this->setData('filename', $filePath);
            $this->setData('content', $object);
        } else {
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
        $this->getHelper()->getClient()->cleanBucket($this->getHelper()->getBucket());
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
        $files = [];

        if (empty($this->objects)) {
            $this->objects = $this->getHelper()->getClient()->getObjectsByBucket($this->getHelper()->getBucket(), [
                'max-keys' => $count
            ]);
        } else {
            $this->objects = $this->getHelper()->getClient()->getObjectsByBucket($this->getHelper()->getBucket(), [
                'marker' => $this->objects[count($this->objects) - 1],
                'max-keys' => $count
            ]);
        }

        if (empty($this->objects)) {
            return false;
        }

        foreach ($this->objects as $object) {
            if (substr($object, -1) != '/') {
                $files[] = [
                    'filename' => $object,
                    'content' => $this->getHelper()->getClient()->getObject($this->getHelper()->getObjectKey($object))
                ];
            }
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
                $objectKey = $this->getHelper()->getObjectKey($file['filename'], $file['directory']);
                $content = $file['content'];
                $meta = [
                    Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ
                ];
                $this->getHelper()->getClient()->putObject($objectKey, $content, $meta);
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Upload the specific file to S3.
     *
     * @param string $filename
     * @return $this
     */
    public function saveFile($filename)
    {
        $sourcePath = $this->getMediaBaseDirectory() . '/' . $filename;
        $destinationPath = $this->getHelper()->getObjectKey($filename);

        $this->getHelper()->getClient()->putFile($sourcePath, $destinationPath, $this->getMetadata());

        return $this;
    }

    /**
     * An array of the HTTP headers that we intend send to S3 alongside the
     * object.
     *
     * @return array
     */
    public function getMetadata()
    {
        return [
            Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ
        ];
    }

    /**
     * Check whether a file exists in S3
     *
     * @param string $filePath
     * @return bool
     */
    public function fileExists($filePath)
    {
        return $this->getHelper()->getClient()->isObjectAvailable($this->getHelper()->getObjectKey($filePath));
    }

    public function copyFile($oldFilePath, $newFilePath)
    {
        $oldFilePath = $this->getHelper()->getObjectKey($oldFilePath);
        $newFilePath = $this->getHelper()->getObjectKey($newFilePath);

        $mimeType = $this->getHelper()->getClient()->getMimeType($oldFilePath);
        $meta = [
            Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $mimeType,
            Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ
        ];

        $this->getHelper()->getClient()->copyObject($oldFilePath, $newFilePath, $meta);

        return $this;
    }

    public function renameFile($oldName, $newName)
    {
        $oldName = $this->getHelper()->getObjectKey($oldName);
        $newName = $this->getHelper()->getObjectKey($newName);

        $mimeType = $this->getHelper()->getClient()->getMimeType($oldName);
        $meta = [
            Zend_Service_Amazon_S3::S3_CONTENT_TYPE_HEADER => $mimeType,
            Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ
        ];
        $this->getHelper()->getClient()->moveObject($oldName, $newName, $meta);

        return $this;
    }

    public function getSubdirectories($path)
    {
        $subdirectories = [];

        $prefix = Mage::helper('core/file_storage_database')->getMediaRelativePath($path);
        $prefix = rtrim($prefix, '/') . '/';

        $objectsAndPrefixes = $this->getHelper()->getClient()->getObjectsAndPrefixesByBucket($this->getBucket(), [
            'prefix' => $prefix,
            'delimiter' => '/'
        ]);

        if (isset($objectsAndPrefixes['prefixes'])) {
            foreach ($objectsAndPrefixes['prefixes'] as $subdirectory) {
                $subdirectories[] = [
                    'name' => substr($subdirectory, strlen($prefix))
                ];
            }
        }

        return $subdirectories;
    }

    public function getDirectoryFiles($directory)
    {
        $files = [];

        $prefix = Mage::helper('core/file_storage_database')->getMediaRelativePath($directory);
        $prefix = rtrim($prefix, '/') . '/';

        $objectsAndPrefixes = $this->getHelper()->getClient()->getObjectsAndPrefixesByBucket($this->getBucket(), [
            'prefix' => $prefix,
            'delimiter' => '/'
        ]);

        if (isset($objectsAndPrefixes['objects'])) {
            foreach ($objectsAndPrefixes['objects'] as $object) {
                if ($object != $prefix) {
                    $files[] = [
                        'filename' => $object,
                        'content' => $this->getHelper()->getClient()->getObject($this->getHelper()->getObjectKey($object))
                    ];
                }
            }
        }

        return $files;
    }

    public function deleteFile($path)
    {
        $path = $this->getHelper()->getObjectKey($path);

        $this->getHelper()->getClient()->removeObject($path);

        return $this;
    }

    /**
     * @return Thai_S3_Helper_Data
     */
    protected function getHelper()
    {
        if (is_null($this->helper)) {
            $this->helper = Mage::helper('thai_s3');
        }
        return $this->helper;
    }
}
