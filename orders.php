<?php
session_start();
include 'components/connect.php';

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

// Handle reorder action
if(isset($_POST['reorder']) && isset($_POST['order_id'])){
   $order_id = $_POST['order_id'];

   // Fetch the order details
   $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ?");
   $select_order->execute([$order_id, $user_id]);

   if($select_order->rowCount() > 0){
      $order = $select_order->fetch(PDO::FETCH_ASSOC);

      // Parse the products from the order
      $products = explode(', ', $order['total_products']);

      // Add each product to the cart
      foreach($products as $product){
         $product_details = explode('(', $product);
         $name = trim($product_details[0]);
         $qty = isset($product_details[1]) ? (int)trim($product_details[1], '()x') : 1;

         // Fetch product details from the products table (including the image and price)
         $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
         $select_product->execute([$name]);

         if($select_product->rowCount() > 0){
            $product_info = $select_product->fetch(PDO::FETCH_ASSOC);
            
            // Check if product already exists in cart
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ?");
            $select_cart->execute([$user_id, $product_info['id']]);
            
            if($select_cart->rowCount() > 0){
               $update_cart = $conn->prepare("UPDATE `cart` SET quantity = (quantity + ?) WHERE user_id = ? AND pid = ?");
               $update_cart->execute([$qty, $user_id, $product_info['id']]);
               $message[] = 'Product quantity updated in cart!';
            }else{
               $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
               $insert_cart->execute([$user_id, $product_info['id'], $product_info['name'], $product_info['price'], $qty, $product_info['image_01']]);
               $message[] = 'Product added to cart!';
            }
         } else {
            $message[] = "Product not found: " . $name;
         }
      }

      // Redirect to cart page
      header('location: cart.php');
      exit();
   }
}

// Handle rating redirect
if(isset($_POST['rate']) && isset($_POST['order_id'])){
   $order_id = $_POST['order_id'];
   // Redirect to rating page with order ID
   header('location: rate_order.php?order_id='.$order_id);
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
   
   <style>
      :root {
         --primary: #557A46;         /* Forest green */
         --primary-light: #7D9D6A;   /* Light green */
         --secondary: #8B5A2B;       /* Earthy brown */
         --dark: #2C3E2D;            /* Dark forest green */
         --light: #F6F7EB;           /* Light cream */
         --accent: #E6C2AC;          /* Soft rabbit brown */
         --text: #2B2D2C;            /* Almost black */
      }
      
      body {
         font-family: 'Poppins', sans-serif;
         background-image: url('https://images.unsplash.com/photo-1418065460487-3e41a6c84dc5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
         background-size: cover;
         background-attachment: fixed;
         background-position: center;
         position: relative;
      }
      
      body::before {
         content: '';
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(255, 255, 255, 0.85);
         z-index: -1;
      }
      
      .orders-container {
         max-width: 1400px;
         margin: 3rem auto;
         padding: 2rem;
         position: relative;
      }
      
      /* Rabbit silhouette decorations */
      .orders-container::before,
      .orders-container::after {
         content: '';
         position: absolute;
         width: 180px;
         height: 80px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 45' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M95,20c-2.6,0-5.2,2.1-7.1,4.3c-0.5-6.4-5.7-11.1-11.9-10.5C74,5.4,67.5,0,59.5,0c-8.9,0-16.3,7.4-16.3,16.3 c0,1.6,0.3,3.1,0.7,4.5c-3.3-1.4-6.9-2.2-10.7-2.2c-4.9,0-9.5,1.3-13.4,3.5C17.7,10.1,9.4,1.8,0,1.1v12c4.2,0.5,7.7,3.7,8.8,7.9 c-1.6,2.8-2.6,6.1-2.6,9.5c0,10.7,8.7,19.4,19.4,19.4c4.4,0,8.5-1.5,11.8-4c3.3,2.5,7.3,4,11.8,4c10.7,0,19.4-8.7,19.4-19.4 c0-3.9-1.1-7.5-3.1-10.5c1.3,0.5,2.8,0.8,4.3,0.8c7,0,10.3-5.6,10.3-5.6s3.9,3.8,8.1,3.8c4.8,0,8.8-5.2,8.8-5.2S99.8,20,95,20z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: contain;
         pointer-events: none;
         opacity: 0.5;
      }
      
      .orders-container::before {
         top: 30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .orders-container::after {
         bottom: 40px;
         left: 30px;
         transform: rotate(-5deg) scaleX(-1);
      }
      
      .section-header {
         text-align: center;
         margin-bottom: 4rem;
         position: relative;
      }
      
      .section-header::after {
         content: '';
         position: absolute;
         bottom: -20px;
         left: 50%;
         transform: translateX(-50%);
         width: 150px;
         height: 3px;
         background: linear-gradient(90deg, transparent, var(--primary), transparent);
      }
      
      .section-title {
         font-size: 2.8rem;
         color: var(--dark);
         font-weight: 800;
         margin-bottom: 1.5rem;
         letter-spacing: -0.5px;
         position: relative;
         display: inline-block;
      }
      
      .section-title::before,
      .section-title::after {
         content: '🌿';
         position: absolute;
         top: 50%;
         transform: translateY(-50%);
         font-size: 1.8rem;
      }
      
      .section-title::before {
         left: -40px;
      }
      
      .section-title::after {
         right: -40px;
      }
      
      .section-subtitle {
         font-size: 1.2rem;
         color: var(--text);
         max-width: 700px;
         margin: 0 auto;
         line-height: 1.6;
      }
      
      .orders-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(550px, 1fr));
         gap: 3rem;
         position: relative;
      }
      
      .order-card {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .order-card::before {
         content: '';
         position: absolute;
         top: -5px;
         right: -5px;
         bottom: -5px;
         left: -5px;
         border: 2px dashed var(--primary-light);
         border-radius: 20px;
         opacity: 0;
         transition: all 0.4s ease;
         z-index: -1;
      }
      
      .order-card:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .order-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }
      
      .order-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 1.5rem 2rem;
         background: var(--primary-light);
         border-bottom: 1px solid rgba(0,0,0,0.05);
         background-image: linear-gradient(to right, rgba(125, 157, 106, 0.8), rgba(85, 122, 70, 0.8));
         color: white;
      }
      
      .order-id {
         font-weight: 600;
         font-size: 1.2rem;
         position: relative;
         padding-left: 24px;
      }
      
      .order-id::before {
         content: '🗂️';
         position: absolute;
         left: 0;
         top: 50%;
         transform: translateY(-50%);
      }
      
      .order-date {
         color: rgba(255, 255, 255, 0.85);
         font-size: 1rem;
      }
      
      .order-body {
         padding: 2rem;
         background: linear-gradient(to bottom, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
      }
      
      .order-row {
         display: flex;
         margin-bottom: 1.5rem;
         position: relative;
      }
      
      .order-label {
         width: 150px;
         font-weight: 500;
         color: var(--dark);
         opacity: 0.7;
      }
      
      .order-value {
         flex: 1;
         color: var(--text);
         font-weight: 500;
         line-height: 1.6;
      }
      
      .order-status {
         display: inline-block;
         padding: 0.4rem 1.2rem;
         border-radius: 30px;
         font-size: 0.95rem;
         font-weight: 600;
         box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      }
      
      .status-pending {
         background: #fff3bf;
         color: #b38600;
         border: 1px dashed #ffc107;
      }
      
      .status-completed {
         background: #d3f9d8;
         color: #2b7a39;
         border: 1px dashed #4cc9a0;
      }
      
      .status-cancelled {
         background: #ffe3e3;
         color: #c92a2a;
         border: 1px dashed #ff6b6b;
      }
      
      .order-footer {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 1.5rem 2rem;
         border-top: 1px solid rgba(85, 122, 70, 0.2);
         background: rgba(255,255,255,0.5);
      }
      
      .order-total {
         font-weight: 700;
         color: var(--secondary);
         font-size: 1.3rem;
         position: relative;
         padding-left: 22px;
      }
      
      .order-total::before {
         content: '₱';
         position: absolute;
         left: 0;
         top: 0;
         font-size: 1rem;
      }
      
      .order-actions {
         display: flex;
         justify-content: flex-end;
         gap: 10px;
      }
      
      .btn-reorder, .btn-rate {
         background: var(--primary);
         color: white;
         border: none;
         padding: 0.8rem 1.8rem;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.05rem;
         letter-spacing: 0.5px;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         position: relative;
         overflow: hidden;
      }
      
      .btn-reorder::before, .btn-rate::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .btn-reorder:hover::before, .btn-rate:hover::before {
         left: 100%;
      }
      
      .btn-reorder i, .btn-rate i {
         margin-right: 8px;
      }
      
      .btn-reorder:hover, .btn-rate:hover {
         background: var(--dark);
         letter-spacing: 1px;
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-rate {
         background: var(--secondary);
         box-shadow: 0 5px 15px rgba(139, 90, 43, 0.3);
      }
      
      .btn-rate:hover {
         box-shadow: 0 8px 20px rgba(139, 90, 43, 0.4);
      }
      
      .empty-state {
         text-align: center;
         grid-column: 1/-1;
         padding: 5rem;
         background: rgba(255,255,255,0.8);
         border-radius: 16px;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         border: 1px dashed var(--primary);
         position: relative;
      }
      
      .empty-state::before,
      .empty-state::after {
         content: '';
         position: absolute;
         width: 60px;
         height: 60px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M50,10c-22.1,0-40,17.9-40,40s17.9,40,40,40s40-17.9,40-40S72.1,10,50,10z M65,25c2.8,0,5,2.2,5,5s-2.2,5-5,5 s-5-2.2-5-5S62.2,25,65,25z M35,25c2.8,0,5,2.2,5,5s-2.2,5-5,5s-5-2.2-5-5S32.2,25,35,25z M70,65H30c0-11,9-20,20-20 S70,54,70,65z'/%3E%3C/svg%3E");
         background-size: contain;
         background-repeat: no-repeat;
         opacity: 0.5;
      }
      
      .empty-state::before {
         top: 20px;
         right: 30px;
      }
      
      .empty-state::after {
         bottom: 20px;
         left: 30px;
         transform: rotate(180deg);
      }
      
      .empty-message {
         font-size: 1.5rem;
         color: var(--dark);
         margin-bottom: 2rem;
         position: relative;
         display: inline-block;
      }
      
      .empty-message::after {
         content: '';
         position: absolute;
         bottom: -10px;
         left: 50%;
         transform: translateX(-50%);
         width: 100px;
         height: 3px;
         background: var(--primary-light);
      }
      
      .empty-icon {
         font-size: 3.5rem;
         color: var(--primary-light);
         margin-bottom: 1.5rem;
      }
      
      .empty-text {
         font-size: 1.1rem;
         color: var(--text);
         margin-bottom: 2rem;
         line-height: 1.6;
      }
      
      .btn-shop {
         background: var(--primary);
         color: white;
         border: none;
         padding: 1rem 2.2rem;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.1rem;
         display: inline-flex;
         align-items: center;
         justify-content: center;
         text-decoration: none;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
      }
      
      .btn-shop:hover {
         background: var(--dark);
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-shop i {
         margin-right: 10px;
      }
      
      /* Forest floor footer decoration */
      .forest-footer {
         height: 50px;
         width: 100%;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M0,50 C150,20 300,80 450,50 C600,20 750,80 900,50 C1050,20 1200,80 1350,50 L1440,50 L1440,100 L0,100 Z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: cover;
         margin-top: 3rem;
      }
      
      /* Animation for rabbit elements */
      @keyframes hop {
         0% { transform: translateY(0); }
         50% { transform: translateY(-10px); }
         100% { transform: translateY(0); }
      }
      
      .rabbit-decoration {
         position: absolute;
         width: 40px;
         height: 40px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' fill='%23557A46' opacity='0.4'%3E%3Cpath d='M70,25c0-5.5-4.5-10-10-10s-10,4.5-10,10c0,0.7,0.1,1.4,0.2,2C42.8,28.9,37,35.5,37,43.3V55c0,5,4,9,9,9h8 c5,0,9-4,9-9V43.3c0-3.8-1.5-7.2-3.9-9.8c1.8-1.8,3-4.3,3-7.1c0-0.1,0-0.3,0-0.4C67.7,27.3,70,30.4,70,34v11c0,2.2,1.8,4,4,4 s4-1.8,4-4V34C78,29.6,74.4,25.9,70,25z M50,25c0-2.8,2.2-5,5-5s5,2.2,5,5s-2.2,5-5,5S50,27.8,50,25z M58,55c0,2.2-1.8,4-4,4h-8 c-2.2,0-4-1.8-4-4V43.3c0-5.5,4.5-10,10-10S58,37.8,58,43.3V55z'/%3E%3C/svg%3E");
         background-size: contain;
         background-repeat: no-repeat;
         animation: hop 3s ease-in-out infinite;
      }
      
      .rabbit-decoration:nth-of-type(1) {
         top: 10%;
         left: 5%;
         animation-delay: 0s;
      }
      
      .rabbit-decoration:nth-of-type(2) {
         top: 20%;
         right: 8%;
         animation-delay: 0.5s;
      }
      
      .rabbit-decoration:nth-of-type(3) {
         bottom: 15%;
         right: 15%;
         animation-delay: 1s;
      }
      
      .rabbit-decoration:nth-of-type(4) {
         bottom: 25%;
         left: 10%;
         animation-delay: 1.5s;
      }
      
      @media (max-width: 1200px) {
         .orders-grid {
            grid-template-columns: 1fr;
         }
      }
      
      @media (max-width: 768px) {
         .orders-container {
            padding: 1.5rem;
            margin: 1rem;
         }
         
         .section-title {
            font-size: 2.2rem;
         }
         
         .section-title::before,
         .section-title::after {
            display: none;
         }
         
         .order-row {
            flex-direction: column;
         }
         
         .order-label {
            width: 100%;
            margin-bottom: 0.5rem;
         }
         
         .order-footer {
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
         }
         
         .order-actions {
            width: 100%;
            flex-direction: column;
            gap: 10px;
         }
         
         .order-actions button {
            width: 100%;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders-container">
   <!-- Rabbit decorations -->
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   
   <div class="section-header">
      <h1 class="section-title">My Orders</h1>
      <p class="section-subtitle">View and track all your premium meat orders from forest to table</p>
   </div>

   <div class="orders-grid">
      <?php
         if($user_id == ''){
            echo '<div class="empty-state">
                     <div class="empty-icon">
                        <i class="fas fa-user-lock"></i>
                     </div>
                     <h3 class="empty-message">Please log in first</h3>
                     <p class="empty-text">You need to be logged in to view your order history.</p>
                     <a href="user_login.php" class="btn-shop"><i class="fas fa-sign-in-alt"></i> Login to Your Account</a>
                  </div>';
         } else {
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
            $select_orders->execute([$user_id]);
            
            if($select_orders->rowCount() > 0) {
               while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  $status_class = '';
                  if($fetch_orders['payment_status'] == 'completed') {
                     $status_class = 'status-completed';
                  } elseif($fetch_orders['payment_status'] == 'cancelled') {
                     $status_class = 'status-cancelled';
                  } else {
                     $status_class = 'status-pending';
                  }
      ?>
      <div class="order-card">
         <div class="order-header">
            <span class="order-id">Order #<?= $fetch_orders['id']; ?></span>
            <span class="order-date"><?= date('F j, Y, g:i a', strtotime($fetch_orders['placed_on'])); ?></span>
         </div>
         <div class="order-body">
            <div class="order-row">
               <span class="order-label">Customer Name</span>
               <span class="order-value"><?= $fetch_orders['name']; ?></span>
            </div>
            <div class="order-row">
               <span class="order-label">Contact Information</span>
               <span class="order-value">
                  <div><?= $fetch_orders['email']; ?></div>
                  <div><?= $fetch_orders['number']; ?></div>
               </span>
            </div>
            <div class="order-row">
               <span class="order-label">Shipping Address</span>
               <span class="order-value"><?= nl2br($fetch_orders['address']); ?></span>
            </div>
            <div class="order-row">
               <span class="order-label">Payment Method</span>
               <span class="order-value"><?= ucfirst($fetch_orders['method']); ?></span>
            </div>
            <div class="order-row">
               <span class="order-label">Order Status</span>
               <span class="order-value"><span class="order-status <?= $status_class; ?>"><?= ucfirst($fetch_orders['payment_status']); ?></span></span>
            </div>
            <div class="order-row">
               <span class="order-label">Items Ordered</span>
               <span class="order-value"><?= $fetch_orders['total_products']; ?></span>
            </div>
         </div>
         <div class="order-footer">
            <div class="order-total"><?= number_format($fetch_orders['total_price'], 2); ?></div>
            <div class="order-actions">
               <form method="post" style="margin-right: 10px;">
                  <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                  <button type="submit" name="rate" class="btn-rate">
                     <i class="fas fa-star"></i> Give Rating
                  </button>
               </form>
               <form method="post">
                  <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                  <button type="submit" name="reorder" class="btn-reorder">
                     <i class="fas fa-redo-alt"></i> Reorder Items
                  </button>
               </form>
            </div>
         </div>
      </div>
      <?php
               }
            } else {
               echo '<div class="empty-state">
                        <div class="empty-icon">
                           <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="empty-message">No orders yet</h3>
                        <p class="empty-text">You haven\'t placed any orders yet.<br>Browse our premium selection and place your first order today!</p>
                        <a href="shop.php" class="btn-shop"><i class="fas fa-shopping-basket"></i> Browse Premium Meats</a>
                     </div>';
            }
         }
      ?>
   </div>
   
   <div class="forest-footer"></div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>