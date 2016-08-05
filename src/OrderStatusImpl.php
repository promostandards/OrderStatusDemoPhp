<?php
/**
 * Created by PhpStorm.
 * User: rmukherjee
 * Date: 7/29/2016
 * Time: 1:04 PM
 */

include_once('utils/MysqlConnector.php');
include_once('utils/DbCalls.php');
include('utils/config/mysqlConfig.php');
include('utils/config/paramConfig.php');
include('utils/OrderStatusWebServiceClient.php');


/**
 * Main function below
 */
$mysqlConnector = new MysqlConnector($username, $password, $servername, $dbname);
$dbCalls = new DbCalls();
$connection = $mysqlConnector->getMysqlConnection();
$clients = $dbCalls->getActiveVendors($connection);
$soapClient = new OrderStatusWebServiceClient();
$results = $xmlOutput ? $soapClient->getOrderStatusTypes($clients[0]->wsdl, $clients[0]->id, $clients[0]->credentials, true, $xmlOutputDir) : $soapClient->getOrderStatusTypes($clients[0]->wsdl, $clients[0]->id, $clients[0]->credentials);
if (!$xmlOutput) {
    foreach ($results->StatusArray->Status as $status) {
        $dbCalls->handleOrderTypes($status->id, $status->name, $connection);
    }
}
foreach ($clients as $client) {
    if ($params['vendor_id'] == $client->vendor_number) {
        $queryType = $params['querytype'];
        $timeStamp = $params['timestamp'];
    }
    $statuses = $xmlOutput ? $soapClient->getOrderStatus($client->wsdl, $client->id, $client->credentials, $queryType, $timeStamp, true, $xmlOutputDir) : $soapClient->getOrderStatus($client->wsdl, $client->id, $client->credentials, $queryType, $timeStamp);
    if (count($statuses) > 0 && !$xmlOutput) {
        foreach ($statuses->OrderStatusArray->OrderStatus as $status) {
            $detailStatus = $status->OrderStatusDetailArray->OrderStatusDetail;
            $responseRequired = $detailStatus->responseRequired == 1 ? 1 : 0;
            $statusTypeId = $dbCalls->getStatusTypeId($detailStatus->statusID, $connection);
            if (!$dbCalls->isOrderDetailInDb($connection, $status->purchaseOrderNumber, $detailStatus->factoryOrderNumber, $client->vendor_id)) {
                $dbCalls->insertOrderStatusDetail($connection, $status->purchaseOrderNumber, $detailStatus->factoryOrderNumber, $detailStatus->expectedShipDate, $detailStatus->expectedDeliveryDate, $detailStatus->additionalExplanation, $responseRequired, $detailStatus->validTimestamp, $statusTypeId, $client->vendor_id);
                $idInserted = $connection->insert_id;
                if ($responseRequired == 1) {
                    $dbCalls->insertOrderStatusRespondTo($connection, $detailStatus->ResponseToArray->RespondTo->name, $detailStatus->ResponseToArray->RespondTo->emailAddress, $detailStatus->ResponseToArray->RespondTo->phone, $idInserted);
                }
            } else {
                $dbCalls->updateOrderStatusDetail($connection, $status->purchaseOrderNumber, $detailStatus->factoryOrderNumber, $detailStatus->expectedShipDate, $detailStatus->expectedDeliveryDate, $detailStatus->additionalExplanation, $responseRequired, $detailStatus->validTimestamp, $statusTypeId, $client->vendor_id);
            }
        }
        $connection->close();
    }
}
//$mysqlConnector->getMysqlConnection();