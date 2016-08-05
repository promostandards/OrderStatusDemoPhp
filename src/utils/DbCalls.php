<?php

/**
 * Created by PhpStorm.
 * User: rmukherjee
 * Date: 7/29/2016
 * Time: 1:29 PM
 */
class DbCalls
{

    /**
     * @param $conn mysqli
     * @return array
     */
    public function getActiveVendors($conn)
    {
        $sql = "select * from  order_status.order_status_vendor where active = 1";
        $result = $conn->query($sql);
        if ($result === true) {
            echo "query failed\n";
        } else {
            echo "query successful\n";
        }
        if ($result->num_rows == 0) {
            echo "No data found";
        }
        $clients = array();
        while ($row = $result->fetch_assoc()) {
            $client = (object)array(
                "vendor_id" => $row['order_status_vendor_id'],
                "vendor_number" => $row['vendor_number'],
                "wsdl" => $row['wsdl_end_point'],
                "id" => $row['user_id'],
                "credentials" => $row['credentials']
            );
            $clients[] = $client;
        }
        return $clients;
    }

    public function handleOrderTypes($statusId, $statusName, $conn)
    {
        if (!$this->isStatusTypeInDb($conn, $statusId)) {
            $this->insertStatusType($conn, $statusId, $statusName);
        } else {
            echo "Order type data is already present status id {$statusId}....skipping\n";
        }
    }

    public function isStatusTypeInDb($conn, $statusId)
    {
        $sql = "select count(*) as countStatus from order_status.order_status_types where order_status_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $statusId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            while (mysqli_stmt_fetch($stmt)) {
                return $count > 0;
            }
        } else {
            echo "stmt failed " . __FILE__ . "  " . __LINE__;
        }
    }

    public function isOrderDetailInDb($conn, $poNumber, $factoryOrderNumber, $vendorId)
    {
        $sql = "select count(*) as countOrderDetail from order_status.order_status_detail where po_number = ? and factory_order_number = ? and order_status_vendor_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sii", $poNumber, $factoryOrderNumber, $vendorId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            while (mysqli_stmt_fetch($stmt)) {
                return $count > 0;
            }
        } else {
            echo "stmt failed " . __FILE__ . "  " . __LINE__;
        }
    }


    public function insertStatusType($conn, $statusId, $statusName)
    {
        $sql = "insert into order_status.order_status_types (order_status_id, order_status_name) values (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $bind = mysqli_stmt_bind_param($stmt, "is", $statusId, $statusName);
        $exec = mysqli_stmt_execute($stmt);
        if ($exec) {
            echo "Inserted data in order_status_type STATUS : {$statusId}  and NAME : {$statusName} \n";
        } else {
            echo "Insert failed \n";
        }
    }

    public function insertOrderStatusDetail($conn, $poNumber, $factoryOrderNumber, $expectedShipDate, $expectedDeliveryDate, $additionalExplanation, $responseRequired, $validTimeStamp, $orderStatusTypeId, $orderStatusVendorId)
    {
        $sql = "insert into order_status.order_status_detail (
                `po_number`,
                `factory_order_number`,
                `expected_ship_date_utc`,
                `expected_delivery_date_utc`,
                `additional_explanation`,
                `response_required`,
                `valid_time_stamp_utc`,
                `order_status_types_id`,
                `order_status_vendor_id`) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $bind = mysqli_stmt_bind_param($stmt, "sssssisii",
            $poNumber,
            $factoryOrderNumber,
            $expectedShipDate,
            $expectedDeliveryDate,
            $additionalExplanation,
            $responseRequired,
            $validTimeStamp,
            $orderStatusTypeId,
            $orderStatusVendorId
        );
        $exec = mysqli_stmt_execute($stmt);
        if ($exec) {
            echo "Inserted data in order_status_type detail PO {$poNumber} and factory order {$factoryOrderNumber} \n";
        } else {
            return false;
        }
    }

    public function updateOrderStatusDetail($conn, $poNumber, $factoryOrderNumber, $expectedShipDate, $expectedDeliveryDate, $additionalExplanation, $responseRequired, $validTimeStamp, $orderStatusTypeId, $vendorId)
    {
        $detailId = $this->getOrderDetailId($conn, $poNumber, $factoryOrderNumber, $vendorId);
        $sql = "update order_status.order_status_detail set
                `expected_ship_date_utc` = ? ,
                `expected_delivery_date_utc` = ?,
                `additional_explanation` = ?,
                `response_required` = ?,
                `valid_time_stamp_utc` = ?,
                `order_status_types_id` = ? where order_status_detail_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        $bind = mysqli_stmt_bind_param($stmt, "sssisii",
            $expectedShipDate,
            $expectedDeliveryDate,
            $additionalExplanation,
            $responseRequired,
            $validTimeStamp,
            $orderStatusTypeId,
            $detailId
        );
        $exec = mysqli_stmt_execute($stmt);
        if ($exec) {
            echo "Updated data in order_status_type detail PO {$poNumber} and factory order {$factoryOrderNumber} \n";
        } else {
            return false;
        }
    }

    public function getStatusTypeId($statusId, $conn)
    {
        $sql = "select order_status_type_id as typeId from  order_status.order_status_types where order_status_id = ? ";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $statusId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $typeId);
            while (mysqli_stmt_fetch($stmt)) {
                return $typeId;
            }
        } else {
            echo "stmt failed " . __FILE__ . "  " . __LINE__;
        }
    }

    public function getOrderDetailId($conn, $poNumber, $factoryOrderNumber, $vendorId)
    {
        $sql = "select order_status_detail_id as detailId from order_status.order_status_detail where po_number = ? and factory_order_number = ? and order_status_vendor_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sii", $poNumber, $factoryOrderNumber, $vendorId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $detailId);
            while (mysqli_stmt_fetch($stmt)) {
                return $detailId;
            }
        } else {
            echo "stmt failed " . __FILE__ . "  " . __LINE__;
        }
    }

    public function insertOrderStatusRespondTo($conn, $csrName, $csrEmail, $csrPhone, $orderStatusDetailId)
    {
        $sql = "insert into order_status.order_status_respond_to (
                `csr_name`,
                `csr_email`,
                `csr_phone`,
                `order_status_detail_id`
                ) values (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $bind = mysqli_stmt_bind_param($stmt, "sssi",
            $csrName,
            $csrEmail,
            $csrPhone,
            $orderStatusDetailId
        );
        $exec = mysqli_stmt_execute($stmt);
        if ($exec) {
            return true;
        } else {
            return false;
        }
    }

}