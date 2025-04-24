<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>All Orders</title>
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      .orders-container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 20px;
      }
      
      .orders-header {
         margin-bottom: 30px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }
      
      .orders-title {
         font-size: 2rem;
         color: #333;
         font-weight: 600;
      }
      
      .orders-table {
         width: 100%;
         border-collapse: collapse;
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
         background: #fff;
         border-radius: 8px;
         overflow: hidden;
      }
      
      .orders-table thead {
         background-color: #f8f9fa;
      }
      
      .orders-table th {
         padding: 16px;
         text-align: left;
         font-weight: 600;
         color: #495057;
         border-bottom: 2px solid #e9ecef;
      }
      
      .orders-table td {
         padding: 16px;
         border-bottom: 1px solid #e9ecef;
      }
      
      .orders-table tr:hover {
         background-color: #f8f9fa;
      }
      
      .delete-btn {
         display: inline-block;
         padding: 8px 16px;
         background-color: #dc3545;
         color: white;
         border-radius: 4px;
         text-decoration: none;
         font-weight: 500;
         transition: background-color 0.2s;
      }
      
      .delete-btn:hover {
         background-color: #c82333;
      }
      
      .empty-message {
         text-align: center;
         padding: 40px;
         font-size: 1.2rem;
         color: #6c757d;
      }
      
      @media (max-width: 768px) {
         .orders-table {
            display: block;
            overflow-x: auto;
         }
         
         .orders-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
         }
      }
   </style>
</head>
<body>

<?php
include '../components/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

include '../components/admin_header.php';
?>

<div class="orders-container">
   <div class="orders-header">
      <h1 class="orders-title">All Orders</h1>
   </div>

   <?php
   $select_orders = $conn->prepare("SELECT * FROM `orders` ORDER BY placed_on DESC");
   $select_orders->execute();
   if($select_orders->rowCount() > 0){
   ?>
   
   <table class="orders-table">
      <thead>
         <tr>
            <th>Order ID</th>
            <th>Date Placed</th>
            <th>Customer Name</th>
            <th>Total Price</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){ ?>
         <tr>
            <td>#<?= $fetch_orders['id']; ?></td>
            <td><?= $fetch_orders['placed_on']; ?></td>
            <td><?= $fetch_orders['name']; ?></td>
            <td>Php <?= $fetch_orders['total_price']; ?></td>
            <td>
               <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   
   <?php } else { ?>
      <div class="empty-message">
         <p>No orders placed yet!</p>
      </div>
   <?php } ?>
</div>

<script src="../js/admin_script.js"></script>
</body>
</html>