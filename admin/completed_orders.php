<?php
include '../components/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Completed Orders</title>
   <link rel="stylesheet" href="../css/admin_style.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
   <style>
      :root {
         --primary: #4361ee;
         --primary-light: #3f37c9;
         --secondary: #3a0ca3;
         --light: #f8f9fa;
         --dark: #212529;
         --success: #4cc9f0;
         --border-radius: 12px;
         --box-shadow: 0 8px 22px rgba(0,0,0,0.1);
      }
      
      body {
         font-family: 'Poppins', sans-serif;
         background-color: #f5f7fb;
         color: var(--dark);
         font-size: 130%; /* Increased base font size by 30% */
      }
      
      .orders {
         padding: 2rem;
         max-width: 1400px;
         margin: 0 auto;
      }
      
      .heading {
         font-size: 2.86rem; /* Increased by 30% */
         font-weight: 600;
         color: var(--secondary);
         margin-bottom: 1.95rem; /* Increased by 30% */
         position: relative;
         padding-bottom: 0.65rem; /* Increased by 30% */
      }
      
      .heading:after {
         content: '';
         position: absolute;
         left: 0;
         bottom: 0;
         width: 78px; /* Increased by 30% */
         height: 5.2px; /* Increased by 30% */
         background: linear-gradient(90deg, var(--primary), var(--success));
         border-radius: 2.6px; /* Increased by 30% */
      }
      
      .container {
         background: white;
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
         padding: 1.95rem; /* Increased by 30% */
         overflow-x: auto;
      }
      
      .orders-table {
         width: 100%;
         border-collapse: separate;
         border-spacing: 0 1.3rem; /* Added vertical spacing between rows */
         min-width: 800px;
      }
      
      .orders-table thead th {
         background-color: var(--primary);
         color: white;
         font-weight: 500;
         padding: 1.3rem; /* Increased by 30% */
         position: sticky;
         top: 0;
         text-transform: uppercase;
         letter-spacing: 0.65px; /* Increased by 30% */
         font-size: 1.105rem; /* Increased by 30% */
      }
      
      .orders-table th:first-child {
         border-top-left-radius: var(--border-radius);
      }
      
      .orders-table th:last-child {
         border-top-right-radius: var(--border-radius);
      }
      
      .orders-table td {
         padding: 1.3rem; /* Increased by 30% */
         border-bottom: 1px solid #e9ecef;
         vertical-align: top;
         background: white;
      }
      
      /* Add margin between rows using box-shadow */
      .orders-table tbody tr {
         box-shadow: 0 0 0 1rem #f5f7fb; /* Creates visual margin */
         border-radius: 8px;
      }
      
      .orders-table tbody tr:hover {
         background-color: rgba(67, 97, 238, 0.05);
      }
      
      .customer-info {
         display: flex;
         flex-direction: column;
         gap: 0.325rem; /* Increased by 30% */
      }
      
      .customer-name {
         font-weight: 600;
         color: var(--secondary);
      }
      
      .product-details {
         margin: 0;
         padding: 0;
      }
      
      .product-details li {
         list-style-type: none;
         padding: 0.975rem 0; /* Increased by 30% */
         border-bottom: 1px solid rgba(0,0,0,0.05);
         display: flex;
         flex-direction: column;
         gap: 0.325rem; /* Increased by 30% */
      }
      
      .product-details li:last-child {
         border-bottom: none;
         padding-bottom: 0;
      }
      
      .product-name {
         font-weight: 500;
         color: var(--primary-light);
      }
      
      .product-price {
         font-size: 1.17rem; /* Increased by 30% */
         color: #6c757d;
      }
      
      .total-price {
         font-weight: 600;
         color: var(--primary);
         font-size: 1.3rem; /* Increased by 30% */
      }
      
      .empty {
         text-align: center;
         padding: 3.9rem; /* Increased by 30% */
         background: white;
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
      }
      
      .empty-icon {
         font-size: 3.9rem; /* Increased by 30% */
         color: #adb5bd;
         margin-bottom: 1.3rem; /* Increased by 30% */
      }
      
      .empty-text {
         font-size: 1.43rem; /* Increased by 30% */
         color: #6c757d;
      }
      
      .status-badge {
         display: inline-block;
         padding: 0.455rem 0.975rem; /* Increased by 30% */
         border-radius: 65px; /* Increased by 30% */
         font-size: 0.975rem; /* Increased by 30% */
         font-weight: 500;
         text-transform: uppercase;
         letter-spacing: 0.65px; /* Increased by 30% */
      }
      
      .status-completed {
         background-color: rgba(76, 201, 240, 0.1);
         color: var(--success);
      }
      
      @media (max-width: 768px) {
         body {
            font-size: 110%; /* Slightly smaller increase for mobile */
         }
         
         .orders {
            padding: 1.3rem; /* Increased by 30% */
         }
         
         .heading {
            font-size: 2.34rem; /* Increased by 30% */
         }
         
         .container {
            padding: 1.3rem; /* Increased by 30% */
         }
         
         .orders-table td, 
         .orders-table th {
            padding: 0.975rem; /* Increased by 30% */
         }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">
   <h1 class="heading">Completed Orders</h1>
   <div class="container">
      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'completed' ORDER BY placed_on DESC");
         $select_orders->execute();

         if($select_orders->rowCount() > 0){
            echo '<table class="orders-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Order Date</th>';
            echo '<th>Customer</th>';
            echo '<th>Contact Info</th>';
            echo '<th>Address</th>';
            echo '<th>Payment</th>';
            echo '<th>Products</th>';
            echo '<th>Total</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
               $product_names = explode(',', $fetch_orders['total_products']);
               $products_list = '<ul class="product-details">';
               
               foreach($product_names as $product_name) {
                  $product_name = trim($product_name);
                  $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
                  $select_product->execute([$product_name]);

                  if($select_product->rowCount() > 0){
                     while($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)){
                        $products_list .= '<li>';
                        $products_list .= '<span class="product-name">'.$fetch_product['name'].'</span>';
                        $products_list .= '<span class="product-desc">'.$fetch_product['details'].'</span>';
                        $products_list .= '<span class="product-price">₱'.$fetch_product['price'].'</span>';
                        $products_list .= '</li>';
                     }
                  }
               }
               $products_list .= '</ul>';

               echo '<tr>';
               echo '<td>'.date('M j, Y g:i A', strtotime($fetch_orders['placed_on'])).'</td>';
               echo '<td><div class="customer-info"><span class="customer-name">'.$fetch_orders['name'].'</span></div></td>';
               echo '<td><div class="customer-info"><span>'.$fetch_orders['email'].'</span><span>'.$fetch_orders['number'].'</span></div></td>';
               echo '<td>'.$fetch_orders['address'].'</td>';
               echo '<td><span class="status-badge status-completed">'.$fetch_orders['method'].'</span></td>';
               echo '<td>'.$products_list.'</td>';
               echo '<td class="total-price">₱'.$fetch_orders['total_price'].'</td>';
               echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
         } else { 
            echo '<div class="empty">';
            echo '<div class="empty-icon">📦</div>';
            echo '<p class="empty-text">No completed orders found</p>';
            echo '</div>';
         }
      ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>