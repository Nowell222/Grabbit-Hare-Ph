<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

if (isset($_POST['update_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];

    // Debugging: Ensure the correct data is received
    // echo "Order ID: " . $order_id; 
    // echo "Payment Status: " . $payment_status;

    if (!empty($order_id) && !empty($payment_status)) {
        try {
            // Prepare SQL query
            $update_order = $conn->prepare("UPDATE `orders` SET payment_status = :payment_status WHERE id = :order_id");
            $update_order->bindParam(':payment_status', $payment_status);
            $update_order->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            // Execute the query
            $update_order->execute();

            // Check if the update was successful
            if ($update_order->rowCount() > 0) {
                $_SESSION['message'] = "Payment status updated successfully!";
            } else {
                $_SESSION['message'] = "No changes made to payment status. It may already be updated.";
            }
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage(), 3, "../error_log.txt");
            $_SESSION['message'] = "Error updating payment status: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "Invalid input. Please try again.";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

header('location: pending_orders.php');
exit();
?>
