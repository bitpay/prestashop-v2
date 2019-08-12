<?php
require_once __DIR__ . '/../AbstractRestController.php';
class BitpayCheckoutCartfixModuleFrontController extends AbstractRestController
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
       
        extract($_POST);
        if (isset($bpaction) && $bpaction == 's'):
            #this will delete the tempcart
            $db_prefix = _DB_PREFIX_;
            $cart_table = $db_prefix . 'part';
            $cart_sql = "DELETE FROM $cart_table WHERE id_customer = $cid";
            $db = Db::getInstance();
            $db->Execute($cart_sql);
        exit();

        elseif (isset($bpaction) && $bpaction == 'd'):
            $db_prefix = _DB_PREFIX_;
            $bitpay_table_name = '_bitpay_checkout_transactions';
            $order_table = $db_prefix . 'orders';
            $order_history_table = $db_prefix . 'order_history';

            $bp_sql = "SELECT * FROM $bitpay_table_name WHERE order_id = $orderid AND transaction_id = '$invoiceID' AND customer_key = '$customerKey'";
            $results = Db::getInstance()->executes($bp_sql);
            if (count($results) == 1):
                $this->restoreBitPayCart($orderid);
                $this->deleteBitPayOrder($orderid, $invoiceID, $bitpay_table_name, $order_table, $order_history_table);
                exit();
            endif;
        exit();
        endif;
        

    }

    protected function getCartInfo($orderid)
    {
        $db_prefix = _DB_PREFIX_;
        $order_table = $db_prefix.'orders';
        $bp_sql = "SELECT * FROM $order_table WHERE id_order = $orderid LIMIT 1";
        $results = Db::getInstance()->executes($bp_sql);
        if (count($results) == 1):
            return $results[0]['id_cart'];
        endif;
    }
    protected function restoreBitPayCart($orderId){
        $id_cart = $this->getCartInfo($orderId);
        error_log(print_r($this->context->cart,true));

            $oldCart = new Cart($id_cart);
            $duplication = $oldCart->duplicate();
            $this->context->cart = new Cart($id_cart);
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $context = $this->context;
            $context->cart = $duplication['cart'];
            CartRule::autoAddToCart($context);
            $this->context->cookie->write();

    }
    protected function deleteBitPayOrder($orderid, $invoiceID, $bitpay_table_name, $order_table, $order_history_table)
    {
       
        #delete this order
        $bp_d = "DELETE FROM $bitpay_table_name WHERE transaction_id = '$invoiceID'";
        $db = Db::getInstance();
        $db->Execute($bp_d);
        #delete from the order table

        $bp_d = "DELETE FROM $order_table WHERE id_order = '$orderid'";
        $db = Db::getInstance();
        $db->Execute($bp_d);

        #delete from the history table
        $bp_d = "DELETE FROM $order_history_table WHERE id_order = '$orderid'";
        $db = Db::getInstance();
        $db->Execute($bp_d);
        return true;
        

    }
    
}
