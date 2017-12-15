<?php

class Aplazame_Aplazame_Http_ZendClient implements Aplazame_Sdk_Http_ClientInterface
{
    public function send(Aplazame_Sdk_Http_RequestInterface $request)
    {
        $rawHeaders = array();
        foreach ($request->getHeaders() as $header => $value) {
            $rawHeaders[] = sprintf('%s:%s', $header, implode(', ', $value));
        }

        $client = new Zend_Http_Client($request->getUri());
        $client->setHeaders($rawHeaders);
        $client->setMethod($request->getMethod());

        $body = $request->getBody();
        if (!empty($body)) {
            $client->setRawData($body);
        }

        try {
            $zendResponse = $client->request();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $responseBody = $zendResponse->getBody();

        $response = new Aplazame_Sdk_Http_Response(
            $zendResponse->getStatus(),
            $responseBody
        );

        return $response;
    }
}
