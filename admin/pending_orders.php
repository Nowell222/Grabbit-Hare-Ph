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
   <title>Pending Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      :root {
         --main-color: #4361ee;
         --hover-color: #3a56d4;
         --light-bg: #f5f7fa;
         --dark-text: #2b2d42;
         --light-text: #8d99ae;
         --success-color: #4cc9f0;
         --border-radius: 8px;
         --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      }
      
      .orders {
         padding: 2rem;
         max-width: 100%;
         margin: 0 auto;
         overflow-x: auto;
         font-size: 1.3em; /* Increased base font size by 30% */
      }
      
      .heading {
         text-align: center;
         margin-bottom: 2rem;
         font-size: 2.86rem; /* 30% larger than 2.2rem */
         color: var(--dark-text);
         position: relative;
         padding-bottom: 1rem;
      }
      
      .heading:after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 130px; /* Slightly larger to match heading */
         height: 4px;
         background: var(--main-color);
         border-radius: 2px;
      }
      
      .orders-table {
         width: 100%;
         border-collapse: collapse;
         background: white;
         box-shadow: var(--box-shadow);
         border-radius: var(--border-radius);
         overflow: hidden;
         font-size: inherit; /* Inherits from .orders */
      }
      
      .orders-table thead {
         background: var(--main-color);
         color: white;
      }
      
      .orders-table th {
         padding: 1.56rem 1.3rem; /* Increased by 30% */
         text-align: left;
         font-weight: 600;
         text-transform: uppercase;
         font-size: 1.105rem; /* 30% larger than 0.85rem */
         letter-spacing: 0.5px;
      }
      
      .orders-table td {
         padding: 1.3rem; /* Increased by 30% */
         border-bottom: 1px solid #f0f0f0;
         color: var(--dark-text);
         vertical-align: middle;
      }
      
      .orders-table tr:last-child td {
         border-bottom: none;
      }
      
      .orders-table tr:hover {
         background: var(--light-bg);
      }
      
      .status-pending {
         background: #fff3cd;
         color: #856404;
         padding: 0.52rem 1.04rem; /* Increased by 30% */
         border-radius: 20px;
         font-size: 1.105rem; /* 30% larger */
         font-weight: 500;
         display: inline-block;
      }
      
      .status-completed {
         background: #d4edda;
         color: #155724;
         padding: 0.52rem 1.04rem; /* Increased by 30% */
         border-radius: 20px;
         font-size: 1.105rem; /* 30% larger */
         font-weight: 500;
         display: inline-block;
      }
      
      .action-form {
         display: flex;
         gap: 0.65rem; /* Increased by 30% */
         align-items: center;
      }
      
      .select {
         padding: 0.65rem 1.3rem; /* Increased by 30% */
         border-radius: var(--border-radius);
         border: 1px solid #ddd;
         font-size: 1.17rem; /* 30% larger than 0.9rem */
         background: white;
         min-width: 156px; /* Increased by 30% */
      }
      
      .option-btn {
         padding: 0.5rem 0.8rem; /* Made smaller than original */
         border-radius: var(--border-radius);
         background: var(--main-color);
         color: white;
         font-size: 1.05rem; /* Slightly larger than select */
         font-weight: 500;
         border: none;
         cursor: pointer;
         transition: background 0.3s ease;
         white-space: nowrap;
         height: fit-content;
      }
      
      .option-btn:hover {
         background: var(--hover-color);
      }
      
      .empty {
         text-align: center;
         padding: 3.9rem; /* Increased by 30% */
         background: white;
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
         font-size: 1.56rem; /* 30% larger */
         color: var(--light-text);
         margin-top: 2.6rem; /* Increased by 30% */
      }
      
      .message {
         position: fixed;
         top: 20px;
         right: 20px;
         padding: 1.3rem 2.6rem; /* Increased by 30% */
         background: var(--success-color);
         color: white;
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
         z-index: 1000;
         animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
         font-size: 1.3rem; /* Increased by 30% */
      }
      
      @keyframes slideIn {
         from { transform: translateX(100%); opacity: 0; }
         to { transform: translateX(0); opacity: 1; }
      }
      
      @keyframes fadeOut {
         to { opacity: 0; }
      }
      
      @media (max-width: 768px) {
         .orders {
            padding: 1.3rem; /* Increased by 30% */
            font-size: 1.1em; /* Slightly less increase on mobile */
         }
         
         .heading {
            font-size: 2.34rem; /* 30% larger than 1.8rem */
         }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<?php
// Display session message
if (isset($_SESSION['message'])) {
    echo '<div class="message">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}
?>

<section class="orders">
<h1 class="heading">Pending Orders</h1>

<?php
$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = 'pending' ORDER BY placed_on DESC");
$select_orders->execute();

if($select_orders->rowCount() > 0){
?>
<table class="orders-table">
   <thead>
      <tr>
         <th>Order ID</th>
         <th>Date</th>
         <th>Customer</th>
         <th>Contact</th>
         <th>Amount</th>
         <th>Status</th>
         <th>Actions</th>
      </tr>
   </thead>
   <tbody>
   <?php
   while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
      <tr>
         <td>#<?= $fetch_orders['id']; ?></td>
         <td><?= date('M d, Y h:i A', strtotime($fetch_orders['placed_on'])); ?></td>
         <td>
            <strong><?= $fetch_orders['name']; ?></strong><br>
            <small><?= $fetch_orders['email']; ?></small>
         </td>
         <td><?= $fetch_orders['number']; ?></td>
         <td>Php <?= number_format($fetch_orders['total_price'], 2); ?></td>
         <td><span class="status-pending">Pending</span></td>
         <td>
            <form action="update_payment.php" method="post" class="action-form">
               <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
               <select name="payment_status" class="select">
                  <option value="pending" selected disabled>Pending</option>
                  <option value="completed">Completed</option>
               </select>
               <button type="submit" class="option-btn" name="update_payment">
                  <i class="fas fa-save"></i> Update
               </button>
            </form>
         </td>
      </tr>
   <?php } ?>
   </tbody>
</table>
<?php } else { echo '<p class="empty">No pending orders found!</p>'; } ?>
</section>

<script src="../js/admin_script.js"></script>
<script>
   // Auto-hide message after 3 seconds
   setTimeout(() => {
      const message = document.querySelector('.message');
      if(message) {
         message.style.display = 'none';
      }
   }, 3000);
</script>
</body>
</html>