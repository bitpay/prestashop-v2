<?php
require_once __DIR__ . '/../AbstractRestController.php';
class BitpayCheckoutIpnModuleFrontController extends AbstractRestController
{
    protected function processGetRequest()
    {
        // do something then output the result
        $this->ajaxDie(json_encode([
            'success' => true,
            'operation' => 'get',
            'message' => 'You should not be here',
        ]));
    }

    protected function processPostRequest()
    {
        $all_data = json_decode(file_get_contents("php://input"), true);
        $db_prefix = _DB_PREFIX_;

        $data = $all_data['data'];
        $event = $all_data['event'];
        $orderId = $data['orderId'];
        $transaction_id = $data['id'];
        $transaction_status = $event['name'];

        $table_name = '_bitpay_checkout_transactions';
        $order_table = $db_prefix . 'orders';
        $order_history_table = $db_prefix . 'order_history';

        #load BP classess
        $level = 2;
        include dirname(__DIR__, $level) . "/BitPayLib/BPC_Client.php";
        include dirname(__DIR__, $level) . "/BitPayLib/BPC_Configuration.php";
        include dirname(__DIR__, $level) . "/BitPayLib/BPC_Invoice.php";
        include dirname(__DIR__, $level) . "/BitPayLib/BPC_Item.php";

        #BITPAY SPECIFIC INFO
        $env = 'test';
        $bitpay_token = Configuration::get('bitpay_checkout_token_dev');

        if (Configuration::get('bitpay_checkout_endpoint') == 1):
            $env = 'production';
            $bitpay_token = Configuration::get('bitpay_checkout_token_prod');
        endif;

        $config = new BPC_Configuration($bitpay_token, $env);
        $params = new stdClass();

        $params->invoiceID = $transaction_id;

        $item = new BPC_Item($config, $params);

        $invoice = new BPC_Invoice($item); //this creates the invoice with all of the config params
        $orderStatus = json_decode($invoice->BPC_checkInvoiceStatus($transaction_id));

        $bp_sql = "SELECT * FROM $table_name WHERE transaction_id = '$transaction_id'";
  
        #$results = Db::getInstance()->executes($bp_sql);
        $db = Db::getInstance();
        try{
            $results = $db->Execute($bp_sql);

        }catch (Exception $e) {
            die($e);
        }
       
        if (count($results) == 1):
            $d = $results[0];
            switch ($transaction_status) {
                case 'invoice_confirmed': #complete
              
                    if ($orderStatus->data->status == 'confirmed' || $orderStatus->data->status == 'complete'):
                        $current_state = Configuration::get('bitpay_checkout_ipn_map_confirmed');
                        if($current_state == ''){
                            $current_state = 2;
                        }
                        $current_state = (int)$current_state;
                       
                        #update the order and history
                        #$db = Db::getInstance();
                        #$bp_u = "UPDATE $order_table SET current_state = $current_state WHERE id_order = '$orderId'";
                        #$db->Execute($bp_u);
                        
                        $objOrder = new Order($orderId); //order with id=1
                        $history = new OrderHistory();
                        $history->id_order = $orderId;
                        $history->changeIdOrderState($current_state, (int)($objOrder->id)); //order status=3


                        #update the transaction table
                        $bp_t = "UPDATE $table_name SET transaction_status = '$transaction_status' WHERE transaction_id = '$transaction_id' AND order_id = $orderId";
                        $db->Execute($bp_t);

                        #update the history table
                       
                        $bp_h = "INSERT INTO $order_history_table (id_employee,id_order,id_order_state,date_add)
									         VALUES (0,'$orderId',$current_state,NOW())";
                        $db->Execute($bp_h);
                       
                        $email_order = new Order((int)$orderId);
                        $email_customer = new Customer((int)$email_order->id_customer);
                        #print_r($email_customer);
                        
                      
                    endif;
                    break;

                case 'invoice_paidInFull': #pending
                    #update the order and history
                    if ($orderStatus->data->status == 'paid'):
                        $current_state = Configuration::get('bitpay_checkout_ipn_map_paid');
                        if($current_state == ''){
                            $current_state = 3;
                        }
                        $current_state = (int)$current_state;
                        $db = Db::getInstance();
                        $bp_u = "UPDATE $order_table SET current_state = $current_state WHERE id_order = '$orderId'";
                        $db->Execute($bp_u);

                        #update the transaction table
                        $bp_t = "UPDATE $table_name SET transaction_status = '$transaction_status' WHERE transaction_id = '$transaction_id' AND order_id = $orderId";
                        $db->Execute($bp_t);

                        #update the history table
                        $bp_h = "INSERT INTO $order_history_table (id_employee,id_order,id_order_state,date_add)
									        VALUES (0,'$orderId',$current_state,NOW())";
                        $db->Execute($bp_h);
                    endif;
                    break;

                case 'invoice_failedToConfirm':
                    #update the order and history
                    if ($orderStatus->data->status == 'invalid'):
                        $current_state = Configuration::get('bitpay_checkout_ipn_map_failed');
                        if($current_state == ''){
                            $current_state = 8;
                        }
                        $current_state = (int)$current_state;
                        $db = Db::getInstance();
                        $bp_u = "UPDATE $order_table SET current_state = $current_state WHERE id_order = '$orderId'";
                        $db->Execute($bp_u);

                        #update the transaction table
                        $bp_t = "UPDATE $table_name SET transaction_status = '$transaction_status' WHERE transaction_id = '$transaction_id' AND order_id = $orderId";
                        $db->Execute($bp_t);

                        #update the history table
                        $bp_h = "INSERT INTO $order_history_table (id_employee,id_order,id_order_state,date_add)
									         VALUES (0,'$orderId',$current_state,NOW())";
                        $db->Execute($bp_h);
                    endif;
                    break;
                case 'invoice_expired':
                    //delete the previous order
                    #update the order and history
                    if ($orderStatus->data->status == 'expired'):
                        $current_state = Configuration::get('bitpay_checkout_ipn_map');
                        if($current_state == ''){
                            $current_state = 6;
                        }
                        $current_state = (int)$current_state;
                        
                        $db = Db::getInstance();
                        $bp_u = "UPDATE $order_table SET current_state = $current_state WHERE id_order = '$orderId'";
                        $db->Execute($bp_u);

                        #update the transaction table
                        $bp_t = "UPDATE $table_name SET transaction_status = '$transaction_status' WHERE transaction_id = '$transaction_id' AND order_id = $orderId";
                        $db->Execute($bp_t);

                        #update the history table
                        $bp_h = "INSERT INTO $order_history_table (id_employee,id_order,id_order_state,date_add)
									         VALUES (0,'$orderId',$current_state,NOW())";
                        $db->Execute($bp_h);
                    endif;
                    break;

                case 'invoice_refundComplete':
                    if ($orderStatus->data->status == 'refunded'):
                        
                    #update the order and history
                    $current_state = Configuration::get('bitpay_checkout_ipn_map_refunded');
                        if($current_state == ''){
                            $current_state = 7;
                        }
                    $current_state = (int)$current_state;
                    $db = Db::getInstance();
                    $bp_u = "UPDATE $order_table SET current_state = $current_state WHERE id_order = '$orderId'";
                    $db->Execute($bp_u);

                    #update the transaction table
                    $bp_t = "UPDATE $table_name SET transaction_status = '$transaction_status' WHERE transaction_id = '$transaction_id' AND order_id = $orderId";
                    $db->Execute($bp_t);

                    #update the history table
                    $bp_h = "INSERT INTO $order_history_table (id_employee,id_order,id_order_state,date_add)
						         VALUES (0,'$orderId',$current_state,NOW())";
                    $db->Execute($bp_h);
                    endif;

                    break;

            }
        endif;

    }

}
