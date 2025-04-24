<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quick View</title>
   
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
      
      .quick-view-container {
         max-width: 1400px;
         margin: 3rem auto;
         padding: 2rem;
         position: relative;
      }
      
      /* Rabbit silhouette decorations */
      .quick-view-container::before,
      .quick-view-container::after {
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
      
      .quick-view-container::before {
         top: 30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .quick-view-container::after {
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
      
      /* Modern Product Card Styling */
      .product-card {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 20px;
         overflow: hidden;
         box-shadow: 0 20px 60px rgba(85, 122, 70, 0.12);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
         max-width: 1200px;
         margin: 0 auto;
      }
      
      .product-card::before {
         content: '';
         position: absolute;
         top: -5px;
         right: -5px;
         bottom: -5px;
         left: -5px;
         border: 2px dashed var(--primary-light);
         border-radius: 25px;
         opacity: 0;
         transition: all 0.4s ease;
         z-index: -1;
      }
      
      .product-card:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .product-row {
         display: grid;
         grid-template-columns: 1fr 1.5fr;
         min-height: 600px;
      }
      
      .product-images {
         padding: 2.5rem;
         background: linear-gradient(135deg, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
         display: flex;
         flex-direction: column;
         position: relative;
         border-right: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .main-image-container {
         flex: 1;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 1.5rem;
         position: relative;
      }
      
      .main-image {
         height: 100%;
         width: 100%;
         display: flex;
         align-items: center;
         justify-content: center;
         overflow: hidden;
         border-radius: 15px;
         box-shadow: 0 8px 25px rgba(0,0,0,0.08);
         background: white;
         position: relative;
      }
      
      .main-image img {
         max-height: 100%;
         max-width: 100%;
         object-fit: contain;
         transition: transform 0.5s ease;
      }
      
      .main-image:hover img {
         transform: scale(1.05);
      }
      
      .thumbnail-container {
         display: flex;
         justify-content: center;
         margin-top: 1.5rem;
      }
      
      .thumbnail-images {
         display: flex;
         gap: 1.2rem;
         justify-content: center;
         overflow-x: auto;
         padding: 0.5rem;
         max-width: 100%;
         scrollbar-width: none; /* Firefox */
      }
      
      .thumbnail-images::-webkit-scrollbar {
         display: none; /* Chrome, Safari, Edge */
      }
      
      .thumbnail {
         width: 90px;
         height: 90px;
         border-radius: 12px;
         overflow: hidden;
         cursor: pointer;
         border: 2px solid transparent;
         transition: all 0.3s ease;
         box-shadow: 0 4px 12px rgba(0,0,0,0.05);
         background: white;
         display: flex;
         align-items: center;
         justify-content: center;
         flex-shrink: 0;
      }
      
      .thumbnail img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
      
      .thumbnail:hover {
         border-color: var(--primary);
         transform: translateY(-5px);
      }
      
      .thumbnail.active {
         border-color: var(--primary);
         transform: translateY(-5px);
         box-shadow: 0 8px 15px rgba(85, 122, 70, 0.25);
      }
      
      .product-content {
         display: flex;
         flex-direction: column;
         padding: 3rem;
         background: white;
         position: relative;
      }
      
      .product-header {
         margin-bottom: 2rem;
      }
      
      .product-badge {
         display: inline-block;
         padding: 0.4rem 1rem;
         background: rgba(85, 122, 70, 0.1);
         color: var(--primary);
         font-weight: 600;
         font-size: 0.9rem;
         border-radius: 30px;
         margin-bottom: 1.2rem;
      }
      
      .product-name {
         font-size: 2.8rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 1.2rem;
         line-height: 1.2;
      }
      
      .product-price-row {
         display: flex;
         align-items: center;
         margin-bottom: 2rem;
      }
      
      .product-price {
         font-size: 2.2rem;
         font-weight: 700;
         color: var(--secondary);
         position: relative;
         padding-left: 1.5rem;
      }
      
      .product-price::before {
         content: '₱';
         position: absolute;
         left: 0;
         top: 0.2rem;
         font-size: 1.4rem;
      }
      
      .product-description {
         margin-bottom: 2.5rem;
         font-size: 1.1rem;
         line-height: 1.9;
         color: var(--text);
         flex-grow: 1;
      }
      
      .product-description p {
         margin-bottom: 1.2rem;
      }
      
      .product-features {
         margin-bottom: 2.5rem;
      }
      
      .feature-title {
         font-weight: 600;
         font-size: 1.2rem;
         color: var(--dark);
         margin-bottom: 1rem;
         display: flex;
         align-items: center;
      }
      
      .feature-title i {
         margin-right: 0.8rem;
         color: var(--primary);
      }
      
      .feature-list {
         list-style: none;
         padding: 0;
         margin: 0;
      }
      
      .feature-item {
         display: flex;
         align-items: center;
         margin-bottom: 0.8rem;
         font-size: 1.05rem;
      }
      
      .feature-item::before {
         content: '•';
         color: var(--primary);
         font-weight: bold;
         margin-right: 0.8rem;
      }
      
      .purchase-section {
         border-top: 1px solid rgba(85, 122, 70, 0.15);
         padding-top: 2rem;
         margin-top: auto;
      }
      
      .quantity-label {
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 1rem;
         display: block;
      }
      
      .quantity-control {
         display: flex;
         align-items: center;
         border: 2px solid rgba(85, 122, 70, 0.2);
         border-radius: 10px;
         width: fit-content;
         overflow: hidden;
         margin-bottom: 2rem;
      }
      
      .qty-btn {
         width: 45px;
         height: 45px;
         background: transparent;
         border: none;
         font-size: 1.2rem;
         font-weight: bold;
         color: var(--primary);
         cursor: pointer;
         transition: all 0.2s;
      }
      
      .qty-btn:hover {
         background: rgba(85, 122, 70, 0.08);
      }
      
      .qty {
         width: 60px;
         height: 45px;
         text-align: center;
         font-weight: 600;
         font-size: 1.1rem;
         color: var(--dark);
         border: none;
         background: transparent;
         border-left: 1px solid rgba(85, 122, 70, 0.2);
         border-right: 1px solid rgba(85, 122, 70, 0.2);
      }
      
      .action-buttons {
         display: flex;
         gap: 1.2rem;
      }
      
      .btn-cart {
         flex: 1.5;
         height: 55px;
         background: var(--primary);
         color: white;
         border: none;
         padding: 0 2rem;
         border-radius: 12px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 600;
         font-size: 1.1rem;
         letter-spacing: 0.5px;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
      }
      
      .btn-wishlist {
         width: 55px;
         height: 55px;
         background: var(--light);
         color: var(--primary);
         border: 2px solid var(--primary);
         border-radius: 12px;
         cursor: pointer;
         transition: all 0.3s;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.3rem;
      }
      
      .btn-cart:hover {
         background: var(--dark);
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-wishlist:hover {
         background: var(--accent);
         border-color: var(--secondary);
         transform: translateY(-3px);
         color: var(--secondary);
      }
      
      .btn-cart i {
         margin-right: 10px;
         font-size: 1.2rem;
      }
      
      /* Empty state styling */
      .empty-state {
         text-align: center;
         padding: 5rem;
         background: rgba(255,255,255,0.8);
         border-radius: 16px;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         border: 1px dashed var(--primary);
         position: relative;
         max-width: 800px;
         margin: 0 auto;
      }
      
      .empty-icon {
         font-size: 3.5rem;
         color: var(--primary-light);
         margin-bottom: 1.5rem;
      }
      
      .empty-message {
         font-size: 1.5rem;
         color: var(--dark);
         margin-bottom: 2rem;
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
      
      /* Responsive Styles */
      @media (max-width: 1200px) {
         .product-row {
            grid-template-columns: 1fr;
         }
         
         .product-images {
            border-right: none;
            border-bottom: 1px solid rgba(85, 122, 70, 0.1);
         }
         
         .main-image-container {
            height: 350px;
         }
      }
      
      @media (max-width: 768px) {
         .quick-view-container {
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
         
         .product-content {
            padding: 2rem;
         }
         
         .product-name {
            font-size: 2.2rem;
         }
         
         .main-image-container {
            height: 300px;
            padding: 1rem;
         }
         
         .thumbnail {
            width: 70px;
            height: 70px;
         }
         
         .action-buttons {
            flex-direction: column;
         }
         
         .btn-wishlist {
            width: 100%;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="quick-view-container">
   <!-- Rabbit decorations -->
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>

   <div class="section-header">
      <h1 class="section-title">Premium Selection</h1>
      <p class="section-subtitle">Quality meat sourced from the finest free-range farms</p>
   </div>

   <?php
     $pid = $_GET['pid'];
     $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?"); 
     $select_products->execute([$pid]);
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="product-card">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      
      <div class="product-row">
         <div class="product-images">
            <div class="main-image-container">
               <div class="main-image">
                  <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= $fetch_product['name']; ?>" id="main-product-image">
               </div>
            </div>
            <div class="thumbnail-container">
               <div class="thumbnail-images">
                  <div class="thumbnail active" onclick="changeImage('<?= $fetch_product['image_01']; ?>', this)">
                     <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="thumbnail">
                  </div>
                  <?php if(!empty($fetch_product['image_02'])): ?>
                  <div class="thumbnail" onclick="changeImage('<?= $fetch_product['image_02']; ?>', this)">
                     <img src="uploaded_img/<?= $fetch_product['image_02']; ?>" alt="thumbnail">
                  </div>
                  <?php endif; ?>
                  <?php if(!empty($fetch_product['image_03'])): ?>
                  <div class="thumbnail" onclick="changeImage('<?= $fetch_product['image_03']; ?>', this)">
                     <img src="uploaded_img/<?= $fetch_product['image_03']; ?>" alt="thumbnail">
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
         
         <div class="product-content">
            <div class="product-header">
               <span class="product-badge">Premium</span>
               <h2 class="product-name"><?= $fetch_product['name']; ?></h2>
               <div class="product-price-row">
                  <div class="product-price"><?= $fetch_product['price']; ?></div>
               </div>
            </div>
            
            <div class="product-description">
               <?= $fetch_product['details']; ?>
            </div>
            
            <div class="product-features">
               <h3 class="feature-title"><i class="fas fa-check-circle"></i> Product Features</h3>
               <ul class="feature-list">
                  <li class="feature-item">Farm-raised for superior quality</li>
                  <li class="feature-item">100% natural, no hormones or additives</li>
                  <li class="feature-item">Premium cut selection by master butchers</li>
                  <li class="feature-item">Vacuum-sealed for ultimate freshness</li>
               </ul>
            </div>
            
            <div class="purchase-section">
               <label class="quantity-label">Quantity:</label>
               <div class="quantity-control">
                  <button type="button" class="qty-btn" onclick="decrementQty()">-</button>
                  <input type="number" name="qty" id="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                  <button type="button" class="qty-btn" onclick="incrementQty()">+</button>
               </div>
               
               <div class="action-buttons">
                  <button type="submit" name="add_to_cart" class="btn-cart">
                     <i class="fas fa-shopping-cart"></i> Add to Cart
                  </button>
                  <button type="submit" name="add_to_wishlist" class="btn-wishlist">
                     <i class="fas fa-heart"></i>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </form>
   <?php
      }
   }else{
      echo '<div class="empty-state">
               <div class="empty-icon">
                  <i class="fas fa-search"></i>
               </div>
               <h3 class="empty-message">Product Not Found</h3>
               <p class="empty-text">Sorry, we couldn\'t find the product you\'re looking for.<br>Try browsing our collection for similar items.</p>
               <a href="shop.php" class="btn-shop"><i class="fas fa-shopping-basket"></i> Browse Premium Meats</a>
            </div>';
   }
   ?>
   
   <div class="forest-footer"></div>
</section>

<?php include 'components/footer.php'; ?>

<script>
   function changeImage(imageSrc, thumbnail) {
      // Update main image
      document.getElementById('main-product-image').src = 'uploaded_img/' + imageSrc;
      
      // Update active thumbnail state
      const thumbnails = document.querySelectorAll('.thumbnail');
      thumbnails.forEach(thumb => {
         thumb.classList.remove('active');
      });
      
      thumbnail.classList.add('active');
   }
   
   function incrementQty() {
      const qtyInput = document.getElementById('qty');
      const currentQty = parseInt(qtyInput.value);
      if (currentQty < 99) {
         qtyInput.value = currentQty + 1;
      }
   }
   
   function decrementQty() {
      const qtyInput = document.getElementById('qty');
      const currentQty = parseInt(qtyInput.value);
      if (currentQty > 1) {
         qtyInput.value = currentQty - 1;
      }
   }
</script>

<script src="js/script.js"></script>

</body>
</html>