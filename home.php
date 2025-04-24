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
   <title>Home</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

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
      
      /* Rabbit silhouette decorations */
      .rabbit-decoration {
         position: absolute;
         width: 40px;
         height: 40px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' fill='%23557A46' opacity='0.4'%3E%3Cpath d='M70,25c0-5.5-4.5-10-10-10s-10,4.5-10,10c0,0.7,0.1,1.4,0.2,2C42.8,28.9,37,35.5,37,43.3V55c0,5,4,9,9,9h8 c5,0,9-4,9-9V43.3c0-3.8-1.5-7.2-3.9-9.8c1.8-1.8,3-4.3,3-7.1c0-0.1,0-0.3,0-0.4C67.7,27.3,70,30.4,70,34v11c0,2.2,1.8,4,4,4 s4-1.8,4-4V34C78,29.6,74.4,25.9,70,25z M50,25c0-2.8,2.2-5,5-5s5,2.2,5,5s-2.2,5-5,5S50,27.8,50,25z M58,55c0,2.2-1.8,4-4,4h-8 c-2.2,0-4-1.8-4-4V43.3c0-5.5,4.5-10,10-10S58,37.8,58,43.3V55z'/%3E%3C/svg%3E");
         background-size: contain;
         background-repeat: no-repeat;
         z-index: 10;
         animation: hop 3s ease-in-out infinite;
      }
      
      @keyframes hop {
         0% { transform: translateY(0); }
         50% { transform: translateY(-10px); }
         100% { transform: translateY(0); }
      }
      
      .rabbit-decoration:nth-child(1) {
         top: 10%;
         left: 5%;
         animation-delay: 0s;
      }
      
      .rabbit-decoration:nth-child(2) {
         top: 20%;
         right: 8%;
         animation-delay: 0.5s;
      }
      
      .rabbit-decoration:nth-child(3) {
         bottom: 15%;
         right: 15%;
         animation-delay: 1s;
      }
      
      .rabbit-decoration:nth-child(4) {
         bottom: 25%;
         left: 10%;
         animation-delay: 1.5s;
      }

      /* Forest Theme for Home Slider */
      .home-bg {
          background: linear-gradient(rgba(44, 62, 45, 0.7), rgba(44, 62, 45, 0.9)), url('images/forest-bg.jpg');
          background-size: cover;
          background-position: center;
          padding: 3rem 0;
          position: relative;
      }
      
      .home-bg::before,
      .home-bg::after {
          content: '';
          position: absolute;
          width: 180px;
          height: 80px;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 45' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M95,20c-2.6,0-5.2,2.1-7.1,4.3c-0.5-6.4-5.7-11.1-11.9-10.5C74,5.4,67.5,0,59.5,0c-8.9,0-16.3,7.4-16.3,16.3 c0,1.6,0.3,3.1,0.7,4.5c-3.3-1.4-6.9-2.2-10.7-2.2c-4.9,0-9.5,1.3-13.4,3.5C17.7,10.1,9.4,1.8,0,1.1v12c4.2,0.5,7.7,3.7,8.8,7.9 c-1.6,2.8-2.6,6.1-2.6,9.5c0,10.7,8.7,19.4,19.4,19.4c4.4,0,8.5-1.5,11.8-4c3.3,2.5,7.3,4,11.8,4c10.7,0,19.4-8.7,19.4-19.4 c0-3.9-1.1-7.5-3.1-10.5c1.3,0.5,2.8,0.8,4.3,0.8c7,0,10.3-5.6,10.3-5.6s3.9,3.8,8.1,3.8c4.8,0,8.8-5.2,8.8-5.2S99.8,20,95,20z'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-size: contain;
          pointer-events: none;
          opacity: 0.5;
          z-index: 1;
      }
      
      .home-bg::before {
          top: 30px;
          right: 50px;
          transform: rotate(10deg);
      }
      
      .home-bg::after {
          bottom: 40px;
          left: 30px;
          transform: rotate(-5deg) scaleX(-1);
      }

      .home {
          position: relative;
          max-width: 1200px;
          margin: 0 auto;
      }

      .home .swiper-slide {
          border-radius: 15px;
          overflow: hidden;
          box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
          position: relative;
          border: 3px solid rgba(125, 157, 106, 0.6);
          transition: transform 0.3s ease;
      }
      
      .home .swiper-slide:hover {
          transform: translateY(-5px);
      }

      .home .swiper-slide::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: linear-gradient(to right, rgba(44, 62, 45, 0.7), rgba(44, 62, 45, 0.3));
          z-index: 1;
      }

      .home .swiper-slide .image {
          height: 450px;
          overflow: hidden;
      }

      .home .swiper-slide .image img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          transition: transform 0.7s ease;
      }

      .home .swiper-slide:hover .image img {
          transform: scale(1.05);
      }

      .home .swiper-slide .content {
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          padding: 2rem;
          z-index: 2;
          background: linear-gradient(to top, rgba(44, 62, 45, 0.9), rgba(44, 62, 45, 0));
      }

      .home .swiper-slide .content span {
          font-size: 1.3rem;
          color: var(--accent);
          font-weight: 500;
          display: block;
          margin-bottom: 0.5rem;
          text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
          letter-spacing: 1px;
          position: relative;
          padding-left: 25px;
      }

      .home .swiper-slide .content span::before {
          content: '🍃';
          position: absolute;
          left: 0;
          top: 50%;
          transform: translateY(-50%);
      }

      .home .swiper-slide .content h3 {
          font-size: 2.2rem;
          color: var(--light);
          font-weight: 700;
          text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
          margin-bottom: 1.5rem;
          line-height: 1.3;
      }

      .home .swiper-slide .content .btn {
          background: var(--primary);
          color: var(--light);
          padding: 12px 30px;
          border-radius: 30px;
          border: 2px solid var(--primary-light);
          font-weight: 600;
          font-size: 1.1rem;
          transition: all 0.3s ease;
          display: inline-block;
          text-transform: uppercase;
          letter-spacing: 1px;
          position: relative;
          overflow: hidden;
          z-index: 1;
      }

      .home .swiper-slide .content .btn::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
          transition: 0.5s;
          z-index: -1;
      }

      .home .swiper-slide .content .btn:hover {
          background: var(--dark);
          border-color: var(--light);
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
      }

      .home .swiper-slide .content .btn:hover::before {
          left: 100%;
      }

      .home .swiper-pagination-bullet {
          width: 12px;
          height: 12px;
          background: var(--primary-light);
          opacity: 0.7;
          transition: all 0.3s ease;
      }

      .home .swiper-pagination-bullet-active {
          opacity: 1;
          width: 30px;
          border-radius: 10px;
          background: var(--light);
      }

      /* Categories Section */
      .category {
         padding: 4rem 0;
         position: relative;
         max-width: 1200px;
         margin: 0 auto;
      }
      
      .heading {
         font-size: 2.5rem;
         text-align: center;
         margin-bottom: 2.5rem;
         color: var(--dark);
         position: relative;
         padding-bottom: 10px;
         font-weight: 800;
      }
      
      .heading::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 100px;
         height: 3px;
         background: var(--primary);
      }
      
      .heading::before {
         content: '🌿';
         position: absolute;
         bottom: -5px;
         left: calc(50% - 110px);
         font-size: 1.5rem;
      }
      
      .category .swiper-slide {
         background: white;
         border-radius: 15px;
         overflow: hidden;
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
         transition: all 0.3s ease;
         text-align: center;
         position: relative;
         border: 1px solid rgba(125, 157, 106, 0.3);
      }
      
      .category .swiper-slide::after {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: linear-gradient(to bottom, rgba(85, 122, 70, 0), rgba(85, 122, 70, 0.1));
         pointer-events: none;
      }
      
      .category .swiper-slide:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 25px rgba(85, 122, 70, 0.3);
      }
      
      .category .swiper-slide img {
         height: 150px;
         width: 150px;
         object-fit: contain;
         padding: 1.5rem;
         transition: transform 0.5s ease;
      }
      
      .category .swiper-slide:hover img {
         transform: scale(1.05);
      }
      
      .category .swiper-slide h3 {
         padding: 1rem;
         font-size: 1.2rem;
         color: var(--dark);
         font-weight: 600;
         background: rgba(246, 247, 235, 0.8);
         border-top: 2px dashed rgba(125, 157, 106, 0.3);
      }
      
      /* Products Section */
      .home-products {
         padding: 3rem 0;
         max-width: 1200px;
         margin: 0 auto;
         position: relative;
      }
      
      .home-products .swiper-slide {
         background: white;
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(0,0,0,0.05);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(125, 157, 106, 0.3);
      }
      
      .home-products .swiper-slide::before {
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
      
      .home-products .swiper-slide:hover::before {
         opacity: 1;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }

      .home-products .swiper-slide:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }

      .home-products .swiper-slide img {
         width: 100%;
         height: 220px;
         object-fit: cover;
         transition: transform 0.5s ease;
      }

      .home-products .swiper-slide:hover img {
         transform: scale(1.05);
      }

      .home-products .swiper-slide .name {
         font-size: 1.4rem;
         color: var(--dark);
         margin: 1rem 1.5rem 0.5rem;
         font-weight: 700;
         line-height: 1.3;
         text-align: left;
         position: relative;
         padding-bottom: 5px;
      }
      
      .home-products .swiper-slide .name::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 0;
         width: 40px;
         height: 3px;
         background: var(--accent);
         transition: width 0.3s ease;
      }
      
      .home-products .swiper-slide:hover .name::after {
         width: 100%;
      }

      .home-products .swiper-slide .flex {
         display: flex;
         align-items: center;
         justify-content: space-between;
         margin: 0 1.5rem 1.5rem;
      }

      .home-products .swiper-slide .price {
         font-size: 1.8rem;
         font-weight: 800;
         color: var(--secondary);
         margin: 0 1.5rem 1rem;
         text-align: left;
         display: flex;
         align-items: center;
      }

      .home-products .swiper-slide .price span {
         font-size: 1.2rem;
         color: var(--secondary);
         margin-right: 5px;
      }

      .home-products .swiper-slide .qty {
         width: 80px;
         padding: 12px;
         border: 2px solid var(--primary-light);
         border-radius: 8px;
         text-align: center;
         font-size: 1rem;
         font-weight: 600;
         transition: all 0.3s ease;
         background: rgba(246, 247, 235, 0.5);
      }
      
      .home-products .swiper-slide .qty:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(85, 122, 70, 0.3);
         outline: none;
      }

      .home-products .swiper-slide .btn {
         background: var(--primary);
         color: white;
         border: none;
         padding: 15px 0;
         border-radius: 0 0 16px 16px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.1rem;
         width: 100%;
         letter-spacing: 0.5px;
         position: relative;
         overflow: hidden;
      }
      
      .home-products .swiper-slide .btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .home-products .swiper-slide .btn:hover::before {
         left: 100%;
      }

      .home-products .swiper-slide .btn:hover {
         background: var(--dark);
         letter-spacing: 1px;
      }

      .home-products .swiper-slide .fa-heart,
      .home-products .swiper-slide .fa-eye {
         position: absolute;
         top: 15px;
         width: 42px;
         height: 42px;
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
         z-index: 2;
         box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      }

      .home-products .swiper-slide .fa-heart:hover,
      .home-products .swiper-slide .fa-eye:hover {
         background: var(--primary);
         color: white;
         transform: scale(1.1);
      }

      .home-products .swiper-slide .fa-heart {
         right: 15px;
      }

      .home-products .swiper-slide .fa-eye {
         right: 65px;
      }
      
      /* Forest floor footer decoration */
      .forest-footer {
         height: 50px;
         width: 100%;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M0,50 C150,20 300,80 450,50 C600,20 750,80 900,50 C1050,20 1200,80 1350,50 L1440,50 L1440,100 L0,100 Z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: cover;
         margin-top: 1rem;
         margin-bottom: 3rem;
      }
      
      .empty {
         text-align: center;
         padding: 3rem;
         background: rgba(255,255,255,0.8);
         border-radius: 16px;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         border: 1px dashed var(--primary);
         font-size: 1.5rem;
         color: var(--dark);
         margin: 2rem 0;
      }

      @media (max-width: 991px) {
          .home .swiper-slide .image {
              height: 400px;
          }
          
          .home .swiper-slide .content h3 {
              font-size: 1.8rem;
          }
          
          .heading {
             font-size: 2.2rem;
          }
      }

      @media (max-width: 768px) {
          .home .swiper-slide .image {
              height: 350px;
          }
          
          .home .swiper-slide .content {
              padding: 1.5rem;
          }
          
          .home .swiper-slide .content h3 {
              font-size: 1.5rem;
          }
          
          .home-bg::before,
          .home-bg::after {
              width: 120px;
              height: 50px;
          }
          
          .home-products .swiper-slide img {
             height: 180px;
          }
          
          .home-products .swiper-slide .name {
             font-size: 1.2rem;
          }
          
          .home-products .swiper-slide .price {
             font-size: 1.5rem;
          }
          
          .rabbit-decoration {
             width: 30px;
             height: 30px;
          }
      }

      @media (max-width: 576px) {
          .home .swiper-slide .image {
              height: 300px;
          }
          
          .home .swiper-slide .content h3 {
              font-size: 1.3rem;
              margin-bottom: 1rem;
          }
          
          .home .swiper-slide .content span {
              font-size: 1rem;
          }
          
          .home .swiper-slide .content .btn {
              padding: 10px 20px;
              font-size: 0.9rem;
          }
          
          .heading {
             font-size: 1.8rem;
          }
          
          .rabbit-decoration:nth-child(3),
          .rabbit-decoration:nth-child(4) {
             display: none;
          }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- Rabbit decorations -->
<div class="rabbit-decoration"></div>
<div class="rabbit-decoration"></div>
<div class="rabbit-decoration"></div>
<div class="rabbit-decoration"></div>

<div class="home-bg">

<section class="home">

   <div class="swiper home-slider">
   
   <div class="swiper-wrapper">

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/h1.webp" alt="">
         </div>
         <div class="content">
            <span>Alive Rabbits</span>
            <h3>Hop into Happiness – Healthy Bunnies Await!</h3>
            <a href="shop.php" class="btn">order now</a>
         </div>
      </div>

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/h2.jpg" alt="">
         </div>
         <div class="content">
            <span>Raw Meats</span>
            <h3>Fresh, Fierce, and Farm-Raised – Raw Meat You Can Trust!</h3>
            <a href="shop.php" class="btn">order now</a>
         </div>
      </div>

      <div class="swiper-slide slide">
         <div class="image">
            <img src="images/h3.jpg" alt="">
         </div>
         <div class="content">
            <span>Stud Services</span>
            <h3>Top Studs, Strong Bloodlines – Breed with the Best!</h3>
            <a href="shop.php" class="btn">order now</a>
         </div>
      </div>

   </div>

      <div class="swiper-pagination"></div>

   </div>

</section>

</div>

<section class="category">

   <h1 class="heading">Shop by Category</h1>

   <div class="swiper category-slider">

   <div class="swiper-wrapper">

   <a href="category.php?category=raw meat" class="swiper-slide slide">
      <img src="images/c1.png" alt="">
      <h3>Raw Meat</h3>
   </a>

   <a href="category.php?category=processed meat" class="swiper-slide slide">
      <img src="images/c2.png" alt="">
      <h3>Processed Meat</h3>
   </a>

   <a href="category.php?category=cooked meat" class="swiper-slide slide">
      <img src="images/c3.png" alt="">
      <h3>Cooked Meat</h3>
   </a>

   <a href="category.php?category=alive rabbit" class="swiper-slide slide">
      <img src="images/c4.png" alt="">
      <h3>Alive Rabbit</h3>
   </a>

   <a href="category.php?category=rabbit needs" class="swiper-slide slide">
      <img src="images/c6.png" alt="">
      <h3>Rabbit's Needs</h3>
   </a>

   <a href="category.php?category=breeding" class="swiper-slide slide">
      <img src="images/c5.png" alt="">
      <h3>Stud Services</h3>
   </a>

   <a href="category.php?category=processed meat" class="swiper-slide slide">
      <img src="images/c2.png" alt="">
      <h3>Processed Meat</h3>
   </a>

   <a href="category.php?category=cooked meat" class="swiper-slide slide">
      <img src="images/c3.png" alt="">
      <h3>Cooked Meat</h3>
   </a>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>


<section class="home-products">

   <h1 class="heading">latest products</h1>

   <div class="swiper products-slider">

   <div class="swiper-wrapper">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="swiper-slide slide">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_product['name']; ?></div>
      <div class="flex">
         <div class="price"><span>Php </span><?= $fetch_product['price']; ?><span></span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>


<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<script src="js/script.js"></script>

<script>
var swiper = new Swiper(".home-slider", {
   loop: true,
   spaceBetween: 20,
   grabCursor: true, // Enables grab effect
   mousewheel: true, // Allows mouse wheel scrolling
   effect: "fade", // Added fade effect for forest theme
   fadeEffect: {
     crossFade: true
   },
   autoplay: {
     delay: 5000,
     disableOnInteraction: false,
   },
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
});

var swiper = new Swiper(".category-slider", {
   loop: true,
   spaceBetween: 20,
   grabCursor: true,
   mousewheel: true,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
   breakpoints: {
      0: {
         slidesPerView: 2,
      },
      650: {
         slidesPerView: 3,
      },
      768: {
         slidesPerView: 4,
      },
      1024: {
         slidesPerView: 5,
      },
   },
});

var swiper = new Swiper(".products-slider", {
   loop: true,
   spaceBetween: 20,
   grabCursor: true,
   mousewheel: true,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
   breakpoints: {
      550: {
         slidesPerView: 2,
      },
      768: {
         slidesPerView: 2,
      },
      1024: {
         slidesPerView: 3,
      },
   },
});
</script>

</body>
</html>