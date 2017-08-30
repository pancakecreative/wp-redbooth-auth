<?php
/**
 * The Redbooth API low-level HTTP Layer.
 *
 * @author Bruno Pedro <bpedro@redbooth.com>
 */
namespace Redbooth;

/**
 * Redbooth base service
 *
 * @package Redbooth
 */
class Base extends OAuth2
{
    /**
     * @access protected
     * @var mixed Full content of last HTTP call response.
     */
    protected $lastResponse = null;

    /**
     * Build a complete endpoint URL.
     *
     * Build an endpoint URL by concatenating the base
     * URL, the API path and the given method.
     *
     * @param string $method The method that you want to call.
     * @return string A complete endpoint URL.
     */
    private function buildEndpointUrl($method)
    {
        return implode('/', array($this->baseUrl,
                                  $this->apiPath,
                                  $method));
    }

    /**
     * Perform a GET request.
     *
     * Perform a GET request to a given API method.
     *
     * @param string $method The method that you want to call.
     * @return object An object representation of the response.
     */
    public function get($method)
    {
        $res = \Httpful\Request::get($this->buildEndpointUrl($method))
            ->addHeaders($this->addAuthorizationHeader())
            ->send();
        $this->throwIfTokenInvalid($res);
        // follow redirect if present
        if ($res->code == 302) {
            $url = $res->meta_data['redirect_url'];
            $res = \Httpful\Request::get($url)->send();
        }
        $this->lastResponse = $res;
        return $res->body;
    }

    /**
     * Perform a POST request.
     *
     * Perform a POST request to a given API method.
     *
     * @param string $method The method that you want to call.
     * @param array $data Data to be POSTed.
     * @return object An object representation of the response.
     */
    public function post($method, $data)
    {
        $res = \Httpful\Request::post($this->buildEndpointUrl($method))
            ->body(json_encode($data))
            ->addHeaders($this->addAuthorizationHeader())
            ->sendsJson()
            ->send();
        $this->lastResponse = $res;
        $this->throwIfTokenInvalid($res);
        return $res->body;
    }

    /**
     * Upload a file.
     *
     * A variation of the post() method that handles a file
     * upload by setting the appropriate MIME type.
     *
     * @param string $method The method that you want to call.
     * @param array $data Data to be POSTed.
     * @param string $filePath The complete path of the file you want to upload.
     * @param string $fileName The name of the file you're uploading. Defaults to 'asset'.
     * @return object An object representation of the response.
     */
    public function postFile($method, $data, $filePath, $fileName = 'asset')
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        $data[$fileName] = '@' . $filePath . ';type=' . $mimeType;
        $res = \Httpful\Request::post($this->buildEndpointUrl($method))
            ->body($data)
            ->addHeaders($this->addAuthorizationHeader())
            ->expectsJson()
            ->sendTypes(\Httpful\Mime::FORM)
            ->addHeader('Accept', 'application/json')
            ->sendsType(\Httpful\Mime::UPLOAD)
            ->send();
        $this->lastResponse = $res;
        $this->throwIfTokenInvalid($res);
        return $res->body;
    }

    /**
     * Get headers from last HTTP call response.
     *
     * Read the previously saved last HTTP call response
     * and return any saved headers.
     *
     * @return array An array of HTTP headers (might be empty).
     */
    public function getLastHeaders()
    {
        if (!empty($this->lastResponse)) {
            return $this->lastResponse->headers->toArray();
        } else {
            return array();
        }
    }
}
