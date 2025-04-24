<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

// Fetch all ratings from the database (modified query)
$select_reviews = $conn->prepare("SELECT r.*, u.name as user_name, u.image as user_image, p.name as product_name, p.image_01 as product_image 
                              FROM ratings r 
                              JOIN users u ON r.user_id = u.id 
                              JOIN products p ON r.product_id = p.id 
                              ORDER BY r.date DESC");
$select_reviews->execute();
$reviews = $select_reviews->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Us</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
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
      
      .about-container {
         max-width: 1400px;
         margin: 3rem auto;
         padding: 2rem;
         position: relative;
      }
      
      /* Rabbit silhouette decorations */
      .about-container::before,
      .about-container::after {
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
      
      .about-container::before {
         top: 30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .about-container::after {
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
      
      /* About section styling */
      .about-row {
         display: flex;
         align-items: center;
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
         margin-bottom: 4rem;
      }
      
      .about-row::before {
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
      
      .about-row:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .about-image {
         flex: 1;
         padding: 2rem;
         display: flex;
         justify-content: center;
         align-items: center;
         background: linear-gradient(to right, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
      }
      
      .about-image img {
         max-width: 100%;
         height: auto;
         border-radius: 10px;
         box-shadow: 0 10px 20px rgba(0,0,0,0.1);
         transition: transform 0.3s ease;
      }
      
      .about-image img:hover {
         transform: scale(1.05);
      }
      
      .about-content {
         flex: 1;
         padding: 3rem;
         background: linear-gradient(to left, rgba(255, 255, 255, 0.8), rgba(246, 247, 235, 0.4));
      }
      
      .about-title {
         font-size: 2.2rem;
         color: var(--dark);
         margin-bottom: 1.5rem;
         position: relative;
         padding-bottom: 15px;
      }
      
      .about-title::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 0;
         width: 80px;
         height: 3px;
         background: var(--primary);
      }
      
      .about-text {
         font-size: 1.05rem;
         line-height: 1.8;
         color: var(--text);
         margin-bottom: 1.5rem;
      }
      
      .about-features {
         margin: 2rem 0;
      }
      
      .feature-item {
         display: flex;
         align-items: center;
         margin-bottom: 1rem;
      }
      
      .feature-icon {
         width: 40px;
         height: 40px;
         background: var(--primary-light);
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         margin-right: 15px;
         color: white;
         font-size: 1.2rem;
         box-shadow: 0 4px 8px rgba(85, 122, 70, 0.2);
      }
      
      .feature-text {
         flex: 1;
         font-size: 1rem;
         font-weight: 500;
      }
      
      .about-btn {
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
         display: inline-flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         text-decoration: none;
      }
      
      .about-btn:hover {
         background: var(--dark);
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .about-btn i {
         margin-right: 8px;
      }
      
      /* Reviews styling */
      .reviews-container {
         max-width: 1400px;
         margin: 5rem auto;
         padding: 2rem;
         position: relative;
      }
      
      .reviews-grid {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
         gap: 2.5rem;
         margin-top: 3rem;
      }
      
      .review-card {
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
         display: flex;
         flex-direction: column;
      }
      
      .review-card::before {
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
      
      .review-card:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .review-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }
      
      .review-header {
         display: flex;
         flex-direction: column;
         align-items: center;
         padding: 2rem;
         background: linear-gradient(to bottom, rgba(125, 157, 106, 0.4), rgba(246, 247, 235, 0.8));
         position: relative;
      }
      
      .review-header::after {
         content: '"';
         position: absolute;
         top: 10px;
         right: 20px;
         font-size: 5rem;
         font-family: Georgia, serif;
         color: rgba(85, 122, 70, 0.2);
         line-height: 0;
      }
      
      .reviewer-img {
         width: 120px;
         height: 120px;
         border-radius: 50%;
         object-fit: cover;
         border: 5px solid white;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
         margin-bottom: 1rem;
      }
      
      .reviewer-name {
         font-size: 1.4rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 0.5rem;
      }
      
      .reviewer-location {
         font-size: 0.95rem;
         color: var(--text);
         opacity: 0.8;
      }
      
      .review-body {
         padding: 2rem;
         flex: 1;
         background: linear-gradient(to bottom, rgba(246, 247, 235, 0.6), rgba(255, 255, 255, 0.9));
      }
      
      .review-text {
         font-size: 1rem;
         line-height: 1.7;
         color: var(--text);
         font-style: italic;
         margin-bottom: 1.5rem;
      }
      
      .review-stars {
         display: flex;
         margin-bottom: 1rem;
      }
      
      .review-stars i {
         color: #FFB100;
         margin-right: 3px;
         font-size: 1.2rem;
      }
      
      .review-date {
         font-size: 0.9rem;
         color: var(--text);
         opacity: 0.7;
         text-align: right;
      }
      
      .review-product {
         display: flex;
         align-items: center;
         margin-top: 1.5rem;
         padding-top: 1.5rem;
         border-top: 1px dashed rgba(85, 122, 70, 0.3);
      }
      
      .product-thumb {
         width: 60px;
         height: 60px;
         border-radius: 10px;
         object-fit: cover;
         margin-right: 15px;
         border: 2px solid white;
         box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      }
      
      .product-info {
         flex: 1;
      }
      
      .product-name {
         font-size: 0.95rem;
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 3px;
      }
      
      .product-desc {
         font-size: 0.85rem;
         color: var(--text);
         opacity: 0.8;
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
      
      /* Media queries */
      @media (max-width: 1200px) {
         .about-row {
            flex-direction: column;
         }
         
         .about-image, .about-content {
            width: 100%;
         }
      }
      
      @media (max-width: 768px) {
         .about-container, .reviews-container {
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
         
         .reviews-grid {
            grid-template-columns: 1fr;
         }
         
         .about-row {
            flex-direction: column;
         }
         
         .about-image {
            order: 1;
            padding: 1.5rem;
         }
         
         .about-content {
            order: 2;
            padding: 1.5rem;
         }
      }

      /* No reviews message styling */
      .no-reviews-message {
         text-align: center;
         padding: 3rem;
         background: rgba(255, 255, 255, 0.8);
         border-radius: 16px;
         border: 1px dashed var(--primary-light);
         margin: 2rem 0;
      }
      
      .no-reviews-message i {
         font-size: 3rem;
         color: var(--primary-light);
         margin-bottom: 1rem;
      }
      
      .no-reviews-message h3 {
         font-size: 1.8rem;
         color: var(--dark);
         margin-bottom: 1rem;
      }
      
      .no-reviews-message p {
         font-size: 1.1rem;
         color: var(--text);
         max-width: 500px;
         margin: 0 auto;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="about-container">
   <!-- Rabbit decorations -->
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   
   <div class="section-header">
      <h1 class="section-title">About Us</h1>
      <p class="section-subtitle">Learn about our passion for sustainable, premium quality meat products</p>
   </div>

   <div class="about-row">
      <div class="about-image">
         <img src="images/c1.png" alt="Premium Meats Farm">
      </div>
      <div class="about-content">
         <h2 class="about-title">Why Choose Grabbit Hare PH?</h2>
         <p class="about-text">At Grabbit Hare PH, we’re passionate about bringing you the freshest, most responsibly sourced rabbit products in the Philippines. Our goal is simple: to provide high-quality rabbit meat, live rabbits, ready-to-eat dishes, and premium feeds that support both healthy living and sustainable farming.</p>
         
         <p class="about-text">We partner with trusted local farmers who raise their rabbits with care in clean, spacious environments. Each rabbit is nurtured with natural feed and plenty of room to hop—resulting in happier animals and better-quality meat you can feel good about.</p>
         
         <div class="about-features">
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-leaf"></i>
               </div>
               <div class="feature-text">100% Organic, Locally-Sourced Meat</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-heart"></i>
               </div>
               <div class="feature-text">Humanely Raised With Love & Care</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-award"></i>
               </div>
               <div class="feature-text">Premium Quality & Exceptional Taste</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-truck"></i>
               </div>
               <div class="feature-text">Farm-Fresh Delivery To Your Door</div>
            </div>
         </div>
         
         <a href="contact.php" class="about-btn"><i class="fas fa-envelope"></i> Contact Us</a>
      </div>
   </div>
   
   <div class="forest-footer"></div>
</section>

<section class="reviews-container">
   <div class="section-header">
      <h1 class="section-title">Client Reviews</h1>
      <p class="section-subtitle">Hear what our happy customers have to say about their Premium Meats experience</p>
   </div>

   <div class="reviews-grid">
      <?php 
      if($select_reviews->rowCount() > 0){ 
         foreach($reviews as $review){ 
            // Format the date
            $review_date = date('F j, Y', strtotime($review['date']));
            
         // Get user profile image or use placeholder
$user_image = !empty($review['user_image']) ? 'uploaded_img/'.$review['user_image'] : 'images/user.png';
            
            // Get product image
            $product_image_path = !empty($review['product_image']) ? 'uploaded_img/'.$review['product_image'] : '/api/placeholder/200/200';
      ?>
      <div class="review-card">
         <div class="review-header">
            <img src="<?= $user_image; ?>" alt="<?= htmlspecialchars($review['user_name']); ?>" class="reviewer-img">
            <h3 class="reviewer-name"><?= htmlspecialchars($review['user_name']); ?></h3>
            <p class="reviewer-location">Valued Customer</p>
         </div>
         <div class="review-body">
            <div class="review-stars">
               <?php 
               // Display star ratings based on actual rating
               for($i = 1; $i <= 5; $i++){
                  if($i <= $review['rating']){
                     echo '<i class="fas fa-star"></i>';
                  } elseif($i - 0.5 <= $review['rating']){
                     echo '<i class="fas fa-star-half-alt"></i>';
                  } else {
                     echo '<i class="far fa-star"></i>';
                  }
               }
               ?>
            </div>
            <p class="review-text"><?= htmlspecialchars($review['review']); ?></p>
            <div class="review-date"><?= $review_date; ?></div>
            
            <div class="review-product">
               <img src="<?= $product_image_path; ?>" alt="<?= htmlspecialchars($review['product_name']); ?>" class="product-thumb">
               <div class="product-info">
                  <p class="product-name"><?= htmlspecialchars($review['product_name']); ?></p>
                  <p class="product-desc">Verified Purchase</p>
               </div>
            </div>
         </div>
      </div>
      <?php 
         }
      } else { 
      ?>
      <div class="no-reviews-message" style="grid-column: 1 / -1;">
         <i class="far fa-comment-dots"></i>
         <h3>No Reviews Yet</h3>
         <p>Be the first to share your experience with our premium meat products!</p>
         <a href="shop.php" class="about-btn" style="margin-top: 1.5rem;">
            <i class="fas fa-shopping-basket"></i> Shop Now
         </a>
      </div>
      <?php } ?>
   </div>
   
   <div class="forest-footer"></div>
</section>

<section class="about-container">
   <div class="section-header">
      <h1 class="section-title">Or Story</h1>
      <p class="section-subtitle">From humble beginnings to becoming the Philippines' trusted source for premium meat</p>
   </div>

   <div class="about-row">
      <div class="about-content">
         <h2 class="about-title">Rooted in Family, Raised with Care</h2>
         <p class="about-text">Grabbit Hare PH began as a family dream rooted in the farmlands of Batangas. What started as a small backyard rabbitry has grown into a trusted network of farms across Luzon, united by a shared passion for ethical farming and animal care.
         </p>
         
         <p class="about-text">Our founder, Christian James Aguila, was inspired by his grandmother’s way of raising rabbits with patience, love, and respect. That same tradition lives on in every product we offer—raising rabbits the right way, just like grandma taught us.</p>
         
         <div class="about-features">
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-history"></i>
               </div>
               <div class="feature-text">Three Generations of Farming Excellence</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-users"></i>
               </div>
               <div class="feature-text">Supporting Local Farming Communities</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-seedling"></i>
               </div>
               <div class="feature-text">Sustainability at Our Core</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-star"></i>
               </div>
               <div class="feature-text">Committed to Excellence in Every Cut</div>
            </div>
         </div>
         
         <a href="shop.php" class="about-btn"><i class="fas fa-shopping-basket"></i> Discover Our Products</a>
      </div>
      <div class="about-image">
         <img src="images/c1.png" alt="Premium Meats Family">
      </div>
   </div>
</section>

<section class="about-container">
   <div class="section-header">
      <h1 class="section-title">Our Process</h1>
      <p class="section-subtitle">How we ensure the highest quality from farm to table</p>
   </div>

   <div class="about-row">
      <div class="about-image">
         <img src="images/c1.png" alt="Our Process">
      </div>
      <div class="about-content">
         <h2 class="about-title">From Our Farm to Your Table</h2>
         <p class="about-text">We take pride in every step of the journey—from farm to freezer to your home. Our rabbits are raised in stress-free settings with no antibiotics, hormones, or GMOs, and they’re fed only with quality, all-natural feeds.
         </p>
         
         <p class="about-text">Each product is prepared in small batches and processed in our modern facility with the highest standards of hygiene and humane handling. We vacuum-seal and flash-freeze our meats to lock in freshness, then deliver them straight to your door—ensuring every order arrives safe, clean, and delicious.</p>
         
         <div class="about-features">
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-check-circle"></i>
               </div>
               <div class="feature-text">Natural Feed & No Antibiotics</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-hand-holding-heart"></i>
               </div>
               <div class="feature-text">Humane Processing Standards</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-snowflake"></i>
               </div>
               <div class="feature-text">Flash-Frozen to Preserve Quality</div>
            </div>
            
            <div class="feature-item">
               <div class="feature-icon">
                  <i class="fas fa-home"></i>
               </div>
               <div class="feature-text">Door-to-Door Delivery Service</div>
            </div>
         </div>
         
         <a href="contact.php" class="about-btn"><i class="fas fa-question-circle"></i> Learn More in Our FAQ</a>
      </div>
   </div>
   
   <div class="forest-footer"></div>
</section>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>