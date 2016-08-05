<?php

/**
 * Created by PhpStorm.
 * User: rmukherjee
 * Date: 7/29/2016
 * Time: 1:28 PM
 */
class OrderStatusWebServiceClient
{
    const VERSION = "1.0.0";
    const VENDOR_NUMBER = 61125;
    const QUERY_TYPE = 4;
    const ORDER_STATUS_TYPE = 1;
    const ORDER_STATUS_DETAIL = 2;
    const XML_FILE_TYPE = ".xml";

    public function getOrderStatus($wsdl, $id, $credential, $queryType, $timeStamp, $output = false, $outputDir = null)
    {
        try {
            $client = new SoapClient($wsdl, array('trace' => 1));
            $request = (object)array(
                "wsVersion" => self::VERSION,
                "id" => $id,
                "password" => $credential,
                "queryType" => is_null($queryType) ? self::QUERY_TYPE : $queryType,
                "referenceNumber" => "",
                "statusTimeStamp" => $timeStamp
            );
            $status = $client->getOrderStatusDetails($request);
            if ($output) {
                $this->outputXml($client->__getLastResponse(), $outputDir, self::ORDER_STATUS_DETAIL);
            } else {
                return $status;
            }
        } catch (exception $exception) {
            echo "The exception is {$exception->getMessage()}";
        }
    }

    public function getOrderStatusTypes($wsdl, $id, $credential, $output = false, $outputDir = null)
    {
        try {
            $client = new SoapClient($wsdl, array('trace' => 1));
            $request = (object)array(
                "wsVersion" => self::VERSION,
                "id" => $id,
                "password" => $credential,
            );
            $statusTypes = $client->getOrderStatusTypes($request);
            if ($output) {
                $this->outputXml($client->__getLastResponse(), $outputDir, self::ORDER_STATUS_TYPE);
            } else {
                return $statusTypes;
            }
        } catch (exception $exception) {
            echo "The exception is {$exception->getMessage()}";
        }
    }

    public function outputXml($response, $outputDir, $fileType)
    {
        $doc = new DOMDocument();
        $doc->loadXML($response);
        if ($fileType == self::ORDER_STATUS_TYPE) {
            $fileName = "OrderStatusType";
        } else {
            $fileName = "OrderStatusDetail_" . strtotime('now');
        }
        if($doc->save($outputDir . $fileName . self::XML_FILE_TYPE)) {
            return true;
        }
        else{
            return false;
        }
    }

}