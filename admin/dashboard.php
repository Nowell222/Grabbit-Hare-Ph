<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location: admin_login.php');
    exit();
}

// Fetch admin profile
$fetch_profile = [];
$select_admin = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_admin->execute([$admin_id]);
if ($select_admin->rowCount() > 0) {
    $fetch_profile = $select_admin->fetch(PDO::FETCH_ASSOC);
}

// Data aggregation
$total_data = [
    'pending' => 0, 'completed' => 0, 'orders' => 0,
    'products' => 0, 'users' => 0, 'admins' => 0, 'messages' => 0
];

$stmt_map = [
    'pending' => "SELECT SUM(total_price) AS total FROM `orders` WHERE payment_status = 'pending'",
    'completed' => "SELECT SUM(total_price) AS total FROM `orders` WHERE payment_status = 'completed'",
    'orders' => "SELECT COUNT(*) AS count FROM `orders`",
    'products' => "SELECT COUNT(*) AS count FROM `products`",
    'users' => "SELECT COUNT(*) AS count FROM `users`",
    'admins' => "SELECT COUNT(*) AS count FROM `admins`",
    'messages' => "SELECT COUNT(*) AS count FROM `messages`"
];

foreach ($stmt_map as $key => $sql) {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_data[$key] = $result['total'] ?? $result['count'] ?? 0;
}

// Get recent messages
$recent_msgs = $conn->prepare("SELECT name, message FROM `messages` ORDER BY id DESC LIMIT 3");
$recent_msgs->execute();
$messages = $recent_msgs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>
   <style>
      :root {
         --bg-color: #f4f4f4;
         --text-color: #333;
         --box-bg: #fff;
         --primary: #4CAF50;
         --secondary: #555;
         --hover: #45a049;
      }

      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }

      body {
         background-color: var(--bg-color);
         color: var(--text-color);
         padding: 20px;
      }

      h1.heading {
         text-align: center;
         margin-bottom: 20px;
         font-size: 2rem;
      }

      .dashboard .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 20px;
      }

      .dashboard .box {
         background: var(--box-bg);
         padding: 20px;
         border-radius: 12px;
         box-shadow: 0 2px 6px rgba(0,0,0,0.1);
         transition: transform 0.3s;
      }

      .dashboard .box:hover {
         transform: translateY(-5px);
      }

      .dashboard .box h3 {
         font-size: 1.6rem;
         margin-bottom: 10px;
         color: var(--primary);
      }

      .dashboard .box p {
         font-size: 1rem;
         color: var(--secondary);
         margin-bottom: 10px;
      }

      .btn {
         display: inline-block;
         padding: 8px 16px;
         background-color: var(--primary);
         color: #fff;
         border: none;
         border-radius: 6px;
         text-decoration: none;
         transition: background-color 0.3s;
      }

      .btn:hover {
         background-color: var(--hover);
      }

      @media (prefers-color-scheme: dark) {
         :root {
            --bg-color: #181818;
            --text-color: #f0f0f0;
            --box-bg: #242424;
            --secondary: #bbb;
         }
      }
   </style>
</head>
<body>

<header style="text-align:center; margin-bottom:20px;">
   <h2>Welcome, <?= htmlspecialchars($fetch_profile['name'] ?? 'Admin'); ?></h2>
</header>

<section class="dashboard">
   <h1 class="heading">Admin Dashboard</h1>

   <div class="box-container">
      <div class="box">
         <h3>Php <?= number_format($total_data['pending'], 2); ?></h3>
         <p>Total Pending Orders</p>
         <a href="pending_orders.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3>Php <?= number_format($total_data['completed'], 2); ?></h3>
         <p>Completed Orders</p>
         <a href="completed_orders.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3><?= $total_data['orders']; ?></h3>
         <p>Orders Placed</p>
         <a href="placed_orders.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3><?= $total_data['products']; ?></h3>
         <p>Products Available</p>
         <a href="products.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3><?= $total_data['users']; ?></h3>
         <p>Registered Users</p>
         <a href="users_accounts.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3><?= $total_data['admins']; ?></h3>
         <p>Admin Accounts</p>
         <a href="admin_accounts.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3><?= $total_data['messages']; ?></h3>
         <p>Messages Received</p>
         <a href="messages.php" class="btn">View</a>
      </div>

      <div class="box">
         <h3>Quick Actions</h3>
         <a href="products.php" class="btn">Add Product</a>
         <a href="messages.php" class="btn">Check Messages</a>
      </div>

      <div class="box">
         <h3>Recent Messages</h3>
         <?php foreach ($messages as $msg): ?>
            <p><strong><?= htmlspecialchars($msg['name']); ?>:</strong> <?= htmlspecialchars(substr($msg['message'], 0, 50)); ?>...</p>
         <?php endforeach; ?>
         <a href="messages.php" class="btn">See All</a>
      </div>
   </div>
</section>

<script>
   // Example of basic interactivity
   console.log("Admin Dashboard loaded successfully.");
</script>

</body>
</html>
