<?php

class Thai_Service_Amazon_S3 extends Zend_Service_Amazon_S3
{
    /**
     * Make a request to Amazon S3
     *
     * @param  string $method    Request method
     * @param  string $path        Path to requested object
     * @param  array  $params    Request parameters
     * @param  array  $headers    HTTP headers
     * @param  string|resource $data        Request data
     * @return Zend_Http_Response
     */
    public function _makeRequest($method, $path='', $params=null, $headers=array(), $data=null)
    {
        $retry_count = 0;

        if (!is_array($headers)) {
            $headers = array($headers);
        }

        $headers['Date'] = gmdate(DATE_RFC1123, time());

        if(is_resource($data) && $method != 'PUT') {
            /**
             * @see Zend_Service_Amazon_S3_Exception
             */
            #require_once 'Zend/Service/Amazon/S3/Exception.php';
            throw new Zend_Service_Amazon_S3_Exception("Only PUT request supports stream data");
        }

        // build the end point out
        $parts = explode('/', $path, 2);
        $endpoint = clone($this->_endpoint);
        if ($parts[0]) {
            // prepend bucket name to the hostname
            $endpoint->setHost($parts[0].'.'.$endpoint->getHost());
        }
        if (!empty($parts[1])) {
            // ZF-10218, ZF-10122
            $pathparts = explode('?',$parts[1]);
            $endpath = $pathparts[0];
            $endpoint->setPath('/'.$endpath);
            
        }
        else {
            $endpoint->setPath('/');
            if ($parts[0]) {
                $path = $parts[0].'/';
            }
        }
        self::addSignature($method, $path, $headers);

        $client = self::getHttpClient();

        $client->resetParameters(true);
        $client->setUri($endpoint);
        $client->setAuth(false);
        // Work around buglet in HTTP client - it doesn't clean headers
        // Remove when ZHC is fixed
        /*
        $client->setHeaders(array('Content-MD5'              => null,
                                  'Content-Encoding'         => null,
                                  'Expect'                   => null,
                                  'Range'                    => null,
                                  'x-amz-acl'                => null,
                                  'x-amz-copy-source'        => null,
                                  'x-amz-metadata-directive' => null));
        */
        $client->setHeaders($headers);

        if (is_array($params)) {
            foreach ($params as $name=>$value) {
                $client->setParameterGet($name, $value);
            }
         }

         if (($method == 'PUT') && ($data !== null)) {
             if (!isset($headers['Content-type'])) {
                 $headers['Content-type'] = self::getMimeType($path);
             }
             $client->setRawData($data, $headers['Content-type']);
         } 
         do {
            $retry = false;

            $response = $client->request($method);
            $response_code = $response->getStatus();

            // Some 5xx errors are expected, so retry automatically
            if ($response_code >= 500 && $response_code < 600 && $retry_count <= 5) {
                $retry = true;
                $retry_count++;
                sleep($retry_count / 4 * $retry_count);
            }
            else if ($response_code == 307) {
                // Need to redirect, new S3 endpoint given
                // This should never happen as Zend_Http_Client will redirect automatically
            }
            else if ($response_code == 100) {
                // echo 'OK to Continue';
            }
        } while ($retry);

        return $response;
    }
}
