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
   <title>Shop</title>
   
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
      
      .shop-container {
         max-width: 1400px;
         margin: 3rem auto;
         padding: 2rem;
         position: relative;
      }
      
      /* Rabbit silhouette decorations */
      .shop-container::before,
      .shop-container::after {
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
      
      .shop-container::before {
         top: 30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .shop-container::after {
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
      
      .products-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
         gap: 3rem;
         position: relative;
      }
      
      .product-card {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .product-card::before {
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
      
      .product-card:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .product-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }
      
      .product-badge {
         position: absolute;
         top: 15px;
         left: 15px;
         background: var(--primary);
         color: white;
         padding: 7px 14px;
         border-radius: 20px;
         font-size: 0.9rem;
         font-weight: 600;
         z-index: 2;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
      }
      
      .product-badge::before {
         content: '🐰';
         margin-right: 5px;
      }
      
      .product-media {
         position: relative;
         height: 260px;
         overflow: hidden;
      }
      
      .product-media::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 0;
         right: 0;
         height: 50px;
         background: linear-gradient(to top, rgba(255,255,255,0.8), transparent);
         z-index: 1;
      }
      
      .product-image {
         width: 100%;
         height: 100%;
         object-fit: cover;
         transition: transform 0.5s ease;
      }
      
      .product-card:hover .product-image {
         transform: scale(1.05);
      }
      
      .product-content {
         padding: 1.8rem;
         position: relative;
         background: linear-gradient(to bottom, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
      }
      
      .product-content::after {
         content: '';
         position: absolute;
         top: 5px;
         right: 5px;
         width: 30px;
         height: 30px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 50 50' fill='%23557A46' opacity='0.3'%3E%3Cpath d='M25,0C11.2,0,0,11.2,0,25s11.2,25,25,25s25-11.2,25-25S38.8,0,25,0z M38,23c-1.7,0-3-1.3-3-3s1.3-3,3-3s3,1.3,3,3 S39.7,23,38,23z M31,10c1.7,0,3,1.3,3,3s-1.3,3-3,3s-3-1.3-3-3S29.3,10,31,10z M19,10c1.7,0,3,1.3,3,3s-1.3,3-3,3s-3-1.3-3-3 S17.3,10,19,10z M12,23c-1.7,0-3-1.3-3-3s1.3-3,3-3s3,1.3,3,3S13.7,23,12,23z M25,40c-6.1,0-11-4.9-11-11s4.9-11,11-11 s11,4.9,11,11S31.1,40,25,40z'/%3E%3C/svg%3E");
         background-size: contain;
         background-repeat: no-repeat;
         opacity: 0.5;
      }
      
      .product-title {
         font-size: 1.4rem;
         color: var(--dark);
         margin-bottom: 0.8rem;
         font-weight: 700;
         line-height: 1.3;
         position: relative;
         display: inline-block;
      }
      
      .product-title::after {
         content: '';
         position: absolute;
         bottom: -5px;
         left: 0;
         width: 40px;
         height: 3px;
         background: var(--accent);
         transition: width 0.3s ease;
      }
      
      .product-card:hover .product-title::after {
         width: 100%;
      }
      
      .product-price {
         display: flex;
         align-items: center;
         margin-bottom: 1.5rem;
         background: rgba(255, 255, 255, 0.6);
         display: inline-block;
         padding: 5px 15px;
         border-radius: 20px;
         border: 1px dashed var(--primary-light);
      }
      
      .current-price {
         font-size: 1.8rem;
         font-weight: 800;
         color: var(--secondary);
      }
      
      .price-currency {
         font-size: 1.2rem;
         margin-right: 5px;
         color: var(--secondary);
      }
      
      .product-actions {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 1.5rem;
      }
      
      .action-btn {
         width: 44px;
         height: 44px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         background: var(--light);
         color: var(--dark);
         border: none;
         cursor: pointer;
         transition: all 0.3s;
         font-size: 1.1rem;
         box-shadow: 0 3px 10px rgba(0,0,0,0.1);
         position: relative;
         overflow: hidden;
      }
      
      .action-btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         right: 0;
         bottom: 0;
         background: var(--primary);
         opacity: 0;
         transition: opacity 0.3s ease;
         z-index: 0;
      }
      
      .action-btn i {
         position: relative;
         z-index: 1;
      }
      
      .action-btn:hover::before {
         opacity: 1;
      }
      
      .action-btn:hover {
         color: white;
         transform: translateY(-3px) scale(1.05);
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
      }
      
      .quantity-selector {
         display: flex;
         align-items: center;
         margin-bottom: 1.5rem;
      }
      
      .quantity-input {
         width: 80px;
         padding: 12px;
         border: 2px solid var(--primary-light);
         border-radius: 8px;
         text-align: center;
         font-size: 1rem;
         margin-right: 10px;
         font-weight: 600;
         background: rgba(255,255,255,0.8);
         transition: all 0.3s ease;
      }
      
      .quantity-input:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(85, 122, 70, 0.3);
         outline: none;
      }
      
      .add-to-cart {
         background: var(--primary);
         color: white;
         border: none;
         padding: 15px 0;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.1rem;
         width: 100%;
         letter-spacing: 0.5px;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         position: relative;
         overflow: hidden;
      }
      
      .add-to-cart::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .add-to-cart:hover::before {
         left: 100%;
      }
      
      .add-to-cart i {
         margin-right: 8px;
      }
      
      .add-to-cart:hover {
         background: var(--dark);
         letter-spacing: 1px;
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
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
      
      @media (max-width: 768px) {
         .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 2rem;
         }
         
         .section-title {
            font-size: 2.2rem;
         }
         
         .section-title::before,
         .section-title::after {
            display: none;
         }
         
         .product-media {
            height: 220px;
         }
         
         .current-price {
            font-size: 1.6rem;
         }
         
         .shop-container::before,
         .shop-container::after {
            width: 120px;
            height: 60px;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="shop-container">
   <!-- Rabbit decorations -->
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   
   <div class="section-header">
      <h1 class="section-title">Premium Meat Selection</h1>
      <p class="section-subtitle">Hand-selected, ethically sourced meats of the highest quality from our forest to your table</p>
   </div>

   <div class="products-grid">
   <?php
     $select_products = $conn->prepare("SELECT * FROM `products`"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="product-card">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      
      <div class="product-media">
         <span class="product-badge">Premium</span>
         <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="<?= $fetch_product['name']; ?>" class="product-image">
      </div>
      
      <div class="product-content">
         <h3 class="product-title"><?= $fetch_product['name']; ?></h3>
         
         <div class="product-price">
            <span class="price-currency">₱</span>
            <span class="current-price"><?= number_format($fetch_product['price'], 2); ?></span>
         </div>
         
         <div class="product-actions">
            <button class="action-btn" type="submit" name="add_to_wishlist" title="Add to Favorites">
               <i class="fas fa-heart"></i>
            </button>
            <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="action-btn" title="Quick View">
               <i class="fas fa-eye"></i>
            </a>
         </div>
         
         <div class="quantity-selector">
            <input type="number" name="qty" class="quantity-input" min="1" max="99" 
                   onkeypress="if(this.value.length == 2) return false;" value="1">
         </div>
         
         <button type="submit" class="add-to-cart" name="add_to_cart">
            <i class="fas fa-shopping-cart"></i> Add to Cart
         </button>
      </div>
   </form>
   <?php
      }
   }else{
      echo '<div class="empty-state">
               <h3 class="empty-message">Our premium selection is coming soon!</h3>
               <p>We\'re preparing the finest meats for you from our forest.</p>
            </div>';
   }
   ?>
   </div>
   
   <div class="forest-footer"></div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>