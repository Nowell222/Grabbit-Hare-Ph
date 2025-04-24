<?php
// Start session and include connection FIRST
session_start();
include 'components/connect.php';

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

// Check if order_id is set and valid
if(!isset($_GET['order_id'])) {
   header('location:orders.php');
   exit();
}

$order_id = $_GET['order_id'];

// Verify the order belongs to the current user
$check_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ?");
$check_order->execute([$order_id, $user_id]);

if($check_order->rowCount() == 0) {
   header('location:orders.php');
   exit();
}

$order = $check_order->fetch(PDO::FETCH_ASSOC);

// Get products from this order
$products = explode(', ', $order['total_products']);
$product_names = [];

foreach($products as $product) {
   $product_details = explode('(', $product);
   $name = trim($product_details[0]);
   if(!in_array($name, $product_names)) {
      $product_names[] = $name;
   }
}

// Process rating submission
if(isset($_POST['submit_rating'])) {
   $rating = $_POST['rating'];
   $product_id = $_POST['product_id'];
   $review = $_POST['review'];
   
   // Check if user has already rated this product from this order
   $check_previous = $conn->prepare("SELECT * FROM `ratings` WHERE user_id = ? AND product_id = ? AND order_id = ?");
   $check_previous->execute([$user_id, $product_id, $order_id]);
   
   if($check_previous->rowCount() > 0) {
      // Update existing rating
      $update_rating = $conn->prepare("UPDATE `ratings` SET rating = ?, review = ?, date = NOW() WHERE user_id = ? AND product_id = ? AND order_id = ?");
      $update_rating->execute([$rating, $review, $user_id, $product_id, $order_id]);
      $message[] = 'Rating updated successfully!';
   } else {
      // Insert new rating
      $insert_rating = $conn->prepare("INSERT INTO `ratings`(user_id, product_id, order_id, rating, review, date) VALUES(?,?,?,?,?,NOW())");
      $insert_rating->execute([$user_id, $product_id, $order_id, $rating, $review]);
      $message[] = 'Rating submitted successfully!';
   }
   
   // Update product average rating
   $get_ratings = $conn->prepare("SELECT AVG(rating) as avg_rating FROM `ratings` WHERE product_id = ?");
   $get_ratings->execute([$product_id]);
   $avg = $get_ratings->fetch(PDO::FETCH_ASSOC);
   
   $update_product = $conn->prepare("UPDATE `products` SET rating = ? WHERE id = ?");
   $update_product->execute([$avg['avg_rating'], $product_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Rate Your Order</title>
   
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
      
      .rating-container {
         max-width: 900px;
         margin: 3rem auto;
         padding: 2rem;
         position: relative;
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
         font-size: 2.5rem;
         color: var(--dark);
         font-weight: 800;
         margin-bottom: 1.5rem;
         letter-spacing: -0.5px;
         position: relative;
         display: inline-block;
      }
      
      .section-title::before,
      .section-title::after {
         content: '⭐';
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
      
      .order-info {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         padding: 1.5rem;
         margin-bottom: 2rem;
         box-shadow: 0 8px 20px rgba(85, 122, 70, 0.15);
         border: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .order-info h3 {
         font-size: 1.3rem;
         color: var(--dark);
         margin-bottom: 1rem;
         display: flex;
         align-items: center;
      }
      
      .order-info h3 i {
         margin-right: 10px;
         color: var(--primary);
      }
      
      .order-details {
         display: flex;
         flex-wrap: wrap;
         gap: 1.5rem;
      }
      
      .order-detail {
         flex: 1;
         min-width: 200px;
      }
      
      .detail-label {
         font-size: 0.9rem;
         color: var(--text);
         opacity: 0.7;
         margin-bottom: 0.3rem;
      }
      
      .detail-value {
         font-size: 1.1rem;
         color: var(--dark);
         font-weight: 600;
      }
      
      .products-to-rate {
         margin-top: 2rem;
      }
      
      .product-item {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         padding: 2rem;
         margin-bottom: 2rem;
         box-shadow: 0 8px 20px rgba(85, 122, 70, 0.15);
         border: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .product-header {
         display: flex;
         align-items: center;
         margin-bottom: 1.5rem;
      }
      
      .product-image {
         width: 100px;
         height: 100px;
         border-radius: 12px;
         overflow: hidden;
         margin-right: 1.5rem;
         border: 2px solid var(--primary-light);
         padding: 5px;
      }
      
      .product-image img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         border-radius: 8px;
      }
      
      .product-name {
         font-size: 1.5rem;
         color: var(--dark);
         font-weight: 700;
         flex: 1;
      }
      
      .star-rating {
         margin-bottom: 1.5rem;
      }
      
      .rating-stars {
         display: flex;
         align-items: center;
         margin-bottom: 1rem;
      }
      
      .rating-stars input {
         display: none;
      }
      
      .rating-stars label {
         cursor: pointer;
         font-size: 2rem;
         color: #ddd;
         padding: 0 5px;
         transition: all 0.2s ease;
      }
      
      .rating-stars label:hover,
      .rating-stars label:hover ~ label,
      .rating-stars input:checked ~ label {
         color: #FFD700;
         text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
         transform: scale(1.1);
      }
      
      .rating-text {
         font-size: 1.1rem;
         color: var(--dark);
         font-weight: 500;
      }
      
      .review-form textarea {
         width: 100%;
         height: 120px;
         padding: 1rem;
         border: 1px solid rgba(85, 122, 70, 0.3);
         border-radius: 10px;
         font-family: 'Poppins', sans-serif;
         resize: none;
         margin-bottom: 1.5rem;
         transition: all 0.3s ease;
      }
      
      .review-form textarea:focus {
         outline: none;
         border-color: var(--primary);
         box-shadow: 0 0 0 2px rgba(85, 122, 70, 0.2);
      }
      
      .btn-submit {
         background: var(--primary);
         color: white;
         border: none;
         padding: 0.8rem 2rem;
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
      }
      
      .btn-submit:hover {
         background: var(--dark);
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-submit i {
         margin-right: 8px;
      }
      
      .rating-success {
         display: flex;
         align-items: center;
         padding: 1rem;
         background: #d3f9d8;
         color: #2b7a39;
         border-radius: 10px;
         margin-bottom: 1.5rem;
      }
      
      .rating-success i {
         font-size: 1.5rem;
         margin-right: 10px;
      }
      
      .back-to-orders {
         display: flex;
         justify-content: center;
         margin-top: 3rem;
      }
      
      .btn-back {
         background: var(--secondary);
         color: white;
         border: none;
         padding: 0.8rem 2rem;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.05rem;
         letter-spacing: 0.5px;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(139, 90, 43, 0.3);
         text-decoration: none;
      }
      
      .btn-back:hover {
         background: var(--dark);
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-back i {
         margin-right: 8px;
      }
      
      .forest-footer {
         height: 50px;
         width: 100%;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M0,50 C150,20 300,80 450,50 C600,20 750,80 900,50 C1050,20 1200,80 1350,50 L1440,50 L1440,100 L0,100 Z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: cover;
         margin-top: 3rem;
      }
      
      /* Animation for rating stars */
      @keyframes pulse {
         0% { transform: scale(1); }
         50% { transform: scale(1.05); }
         100% { transform: scale(1); }
      }
      
      .animate-star {
         animation: pulse 1s ease-in-out infinite;
      }
      
      @media (max-width: 768px) {
         .rating-container {
            padding: 1.5rem;
            margin: 1rem;
         }
         
         .section-title {
            font-size: 2rem;
         }
         
         .section-title::before,
         .section-title::after {
            display: none;
         }
         
         .product-header {
            flex-direction: column;
            text-align: center;
         }
         
         .product-image {
            margin-right: 0;
            margin-bottom: 1rem;
         }
         
         .rating-stars label {
            font-size: 1.8rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="rating-container">
   <div class="section-header">
      <h1 class="section-title">Rate Your Order</h1>
      <p class="section-subtitle">Share your experience with our premium products</p>
   </div>

   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<div class="rating-success"><i class="fas fa-check-circle"></i> <span>'.$msg.'</span></div>';
      }
   }
   ?>

   <div class="order-info">
      <h3><i class="fas fa-clipboard-list"></i> Order Information</h3>
      <div class="order-details">
         <div class="order-detail">
            <div class="detail-label">Order ID</div>
            <div class="detail-value">#<?= $order['id']; ?></div>
         </div>
         <div class="order-detail">
            <div class="detail-label">Order Date</div>
            <div class="detail-value"><?= date('F j, Y', strtotime($order['placed_on'])); ?></div>
         </div>
         <div class="order-detail">
            <div class="detail-label">Order Status</div>
            <div class="detail-value"><?= ucfirst($order['payment_status']); ?></div>
         </div>
         <div class="order-detail">
            <div class="detail-label">Total Amount</div>
            <div class="detail-value">₱<?= number_format($order['total_price'], 2); ?></div>
         </div>
      </div>
   </div>

   <div class="products-to-rate">
      <?php
      // Get all products from this order for rating
      foreach($product_names as $product_name) {
         // Get product details from database
         $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ? LIMIT 1");
         $select_product->execute([$product_name]);
         
         if($select_product->rowCount() > 0) {
            $product = $select_product->fetch(PDO::FETCH_ASSOC);
            $product_id = $product['id'];
            
            // Check if user has already rated this product from this order
            $check_rating = $conn->prepare("SELECT * FROM `ratings` WHERE user_id = ? AND product_id = ? AND order_id = ?");
            $check_rating->execute([$user_id, $product_id, $order_id]);
            
            $existing_rating = false;
            $rating_value = 0;
            $review_text = '';
            
            if($check_rating->rowCount() > 0) {
               $existing_rating = true;
               $rating_data = $check_rating->fetch(PDO::FETCH_ASSOC);
               $rating_value = $rating_data['rating'];
               $review_text = $rating_data['review'];
            }
      ?>
      <div class="product-item">
         <div class="product-header">
            <div class="product-image">
               <img src="uploaded_img/<?= $product['image_01']; ?>" alt="<?= $product['name']; ?>">
            </div>
            <h3 class="product-name"><?= $product['name']; ?></h3>
         </div>
         
         <form action="" method="post" class="review-form">
            <input type="hidden" name="product_id" value="<?= $product_id; ?>">
            
            <div class="star-rating">
               <div class="rating-stars">
                  <input type="radio" name="rating" id="star5-<?= $product_id; ?>" value="5" <?= ($rating_value == 5) ? 'checked' : ''; ?>>
                  <label for="star5-<?= $product_id; ?>" class="<?= ($existing_rating && $rating_value == 5) ? 'animate-star' : ''; ?>">★</label>
                  
                  <input type="radio" name="rating" id="star4-<?= $product_id; ?>" value="4" <?= ($rating_value == 4) ? 'checked' : ''; ?>>
                  <label for="star4-<?= $product_id; ?>" class="<?= ($existing_rating && $rating_value == 4) ? 'animate-star' : ''; ?>">★</label>
                  
                  <input type="radio" name="rating" id="star3-<?= $product_id; ?>" value="3" <?= ($rating_value == 3) ? 'checked' : ''; ?>>
                  <label for="star3-<?= $product_id; ?>" class="<?= ($existing_rating && $rating_value == 3) ? 'animate-star' : ''; ?>">★</label>
                  
                  <input type="radio" name="rating" id="star2-<?= $product_id; ?>" value="2" <?= ($rating_value == 2) ? 'checked' : ''; ?>>
                  <label for="star2-<?= $product_id; ?>" class="<?= ($existing_rating && $rating_value == 2) ? 'animate-star' : ''; ?>">★</label>
                  
                  <input type="radio" name="rating" id="star1-<?= $product_id; ?>" value="1" <?= ($rating_value == 1) ? 'checked' : ''; ?>>
                  <label for="star1-<?= $product_id; ?>" class="<?= ($existing_rating && $rating_value == 1) ? 'animate-star' : ''; ?>">★</label>
               </div>
               
               <div class="rating-text">
                  <?php if($existing_rating): ?>
                     You've rated this product <?= $rating_value ?> out of 5 stars. Update your rating below.
                  <?php else: ?>
                     How would you rate this product?
                  <?php endif; ?>
               </div>
            </div>
            
            <textarea name="review" placeholder="Share your experience with this product... What did you like? What could be improved?"><?= $review_text; ?></textarea>
            
            <button type="submit" name="submit_rating" class="btn-submit">
               <i class="fas fa-paper-plane"></i> <?= $existing_rating ? 'Update Rating' : 'Submit Rating'; ?>
            </button>
         </form>
      </div>
      <?php
         }
      }
      ?>
   </div>
   
   <div class="back-to-orders">
      <a href="orders.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Orders</a>
   </div>
   
   <div class="forest-footer"></div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
   // JavaScript to enhance the rating experience
   document.addEventListener('DOMContentLoaded', function() {
      // Add visual feedback when selecting stars
      const ratingLabels = document.querySelectorAll('.rating-stars label');
      ratingLabels.forEach(label => {
         label.addEventListener('click', function() {
            // Get the product container this rating belongs to
            const productItem = this.closest('.product-item');
            const ratingText = productItem.querySelector('.rating-text');
            const starValue = this.previousElementSibling.value;
            
            // Update the rating text
            ratingText.textContent = `You selected ${starValue} out of 5 stars`;
            
            // Remove animation from all stars
            productItem.querySelectorAll('.rating-stars label').forEach(star => {
               star.classList.remove('animate-star');
            });
            
            // Add animation to selected star
            this.classList.add('animate-star');
         });
      });
   });
</script>

</body>
</html>