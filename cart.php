<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
   $_SESSION['message'] = 'Item removed from cart';
   header('location:cart.php');
   exit();
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   $_SESSION['message'] = 'All items removed from cart';
   header('location:cart.php');
   exit();
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $_SESSION['message'] = 'Cart quantity updated';
   header('location:cart.php');
   exit();
}

// Display messages if any
if(isset($_SESSION['message'])){
   $message[] = $_SESSION['message'];
   unset($_SESSION['message']);
}

// Function to format price with commas
function formatPrice($price) {
    return number_format($price, 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      <style>
      :root {
         --primary: #557A46;         /* Forest green */
         --primary-light: #7D9D6A;   /* Light green */
         --secondary: #8B5A2B;       /* Earthy brown */
         --dark: #2C3E2D;            /* Dark forest green */
         --light: #F6F7EB;           /* Light cream */
         --accent: #E6C2AC;          /* Soft rabbit brown */
         --text: #2B2D2C;            /* Almost black */
         --success: #4D7C50;         /* Forest success green */
         --warning: #D7A249;         /* Autumn gold */
         --danger: #A44A3F;          /* Rustic red */
         --gray: #9B9B8B;            /* Neutral gray */
         --border-radius: 12px;
         --box-shadow: 0 8px 20px rgba(85, 122, 70, 0.15);
         --transition: all 0.3s ease;
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
         max-width: 1200px;
         margin: 3rem auto;
         padding: 2rem 2rem 3rem;
         background: rgba(255, 255, 255, 0.9);
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
         gap: 2rem;
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
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
         top: -30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .orders-container::after {
         bottom: -30px;
         left: 30px;
         transform: rotate(-5deg) scaleX(-1);
      }

      .orders-header {
         grid-column: 1 / -1;
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 2rem;
         padding-bottom: 1.5rem;
         border-bottom: 2px dashed var(--primary-light);
         position: relative;
      }
      
      .orders-header::after {
         content: '';
         position: absolute;
         bottom: -12px;
         left: 50%;
         transform: translateX(-50%);
         width: 150px;
         height: 3px;
         background: linear-gradient(90deg, transparent, var(--primary), transparent);
      }

      .orders-title {
         font-size: 2.5rem;
         font-weight: 700;
         color: var(--dark);
         margin: 0;
         position: relative;
         display: inline-block;
      }
      
      .orders-title::before,
      .orders-title::after {
         content: '🌿';
         position: absolute;
         top: 50%;
         transform: translateY(-50%);
         font-size: 1.8rem;
      }
      
      .orders-title::before {
         left: -40px;
      }
      
      .orders-title::after {
         right: -40px;
      }

      .order-card {
         background: white;
         border-radius: var(--border-radius);
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         overflow: hidden;
         transition: var(--transition);
         border: 1px solid rgba(85, 122, 70, 0.1);
         width: 100%;
         position: relative;
      }
      
      .order-card::before {
         content: '';
         position: absolute;
         top: -5px;
         right: -5px;
         bottom: -5px;
         left: -5px;
         border: 2px dashed var(--primary-light);
         border-radius: 16px;
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
         transform: translateY(-8px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }

      .order-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 1.5rem 2rem;
         background: var(--light);
         border-bottom: 1px solid rgba(85, 122, 70, 0.1);

         /* Forest pattern in header */
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='20' opacity='0.1' viewBox='0 0 100 20' fill='%23557A46'%3E%3Cpath d='M20,10 Q25,0 30,10 T40,10 T50,10 T60,10 T70,10 T80,10 T90,10 T100,10 V20 H0 V10 Q5,0 10,10 T20,10'/%3E%3C/svg%3E");
         background-repeat: repeat-x;
         background-position: bottom;
         background-size: 100px 20px;
      }

      .order-id {
         font-weight: 600;
         color: var(--primary);
         font-size: 1.1rem;
         display: flex;
         align-items: center;
      }
      
      .order-id::before {
         content: '🐰';
         margin-right: 8px;
         font-size: 1.2rem;
      }

      .order-date {
         color: var(--gray);
         font-size: 1rem;
      }

      .order-body {
         padding: 2rem;
         background-color: rgba(246, 247, 235, 0.3);
         position: relative;
      }
      
      .order-body::after {
         content: '';
         position: absolute;
         bottom: 10px;
         right: 10px;
         width: 80px;
         height: 80px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 50 50' opacity='0.07' fill='%23557A46'%3E%3Cpath d='M25,10c-2.6,0-5.2,2.1-7.1,4.3c-0.5-6.4-5.7-11.1-11.9-10.5C5,0.4,2.5,0,0,0v12c4.2,0.5,7.7,3.7,8.8,7.9c-1.6,2.8-2.6,6.1-2.6,9.5 c0,10.7,8.7,19.4,19.4,19.4c10.7,0,19.4-8.7,19.4-19.4C45,18.7,36.3,10,25.6,10C25.4,10,25.2,10,25,10z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: contain;
         pointer-events: none;
      }

      .order-row {
         display: flex;
         margin-bottom: 1.2rem;
         font-size: 1.05rem;
         position: relative;
      }
      
      .order-row:not(:last-child)::after {
         content: '';
         position: absolute;
         bottom: -8px;
         left: 0;
         right: 0;
         height: 1px;
         background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
         opacity: 0.3;
      }

      .order-label {
         width: 150px;
         font-weight: 500;
         color: var(--secondary);
         position: relative;
      }
      
      .order-label::after {
         content: '→';
         position: absolute;
         right: 15px;
         color: var(--primary-light);
      }

      .order-value {
         flex: 1;
         color: var(--dark);
         line-height: 1.6;
      }

      .order-status {
         display: inline-block;
         padding: 0.4rem 1rem;
         border-radius: 20px;
         font-size: 0.95rem;
         font-weight: 500;
         box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      }

      .status-pending {
         background: rgba(215, 162, 73, 0.2);
         color: var(--warning);
         border: 1px solid rgba(215, 162, 73, 0.3);
      }

      .status-completed {
         background: rgba(77, 124, 80, 0.2);
         color: var(--success);
         border: 1px solid rgba(77, 124, 80, 0.3);
      }

      .status-cancelled {
         background: rgba(164, 74, 63, 0.2);
         color: var(--danger);
         border: 1px solid rgba(164, 74, 63, 0.3);
      }

      .order-footer {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 1.5rem 2rem;
         border-top: 1px solid rgba(85, 122, 70, 0.1);
         background: #f6f7eb;
         
         /* Forest floor pattern */
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='20' opacity='0.1' viewBox='0 0 100 20' fill='%23557A46'%3E%3Cpath d='M0,15 Q10,10 20,15 T40,15 T60,15 T80,15 T100,15 V20 H0 Z'/%3E%3C/svg%3E");
         background-repeat: repeat-x;
         background-position: bottom;
         background-size: 100px 20px;
      }

      .order-total {
         font-weight: 700;
         color: var(--secondary);
         font-size: 1.2rem;
         position: relative;
         padding-left: 28px;
      }
      
      .order-total::before {
         content: '🥕';
         position: absolute;
         left: 0;
         font-size: 1.2rem;
      }

      .order-actions {
         display: flex;
         justify-content: flex-end;
      }

      .order-actions .btn {
         padding: 0.8rem 1.5rem;
         border-radius: 25px;
         font-size: 1rem;
         font-weight: 500;
         cursor: pointer;
         transition: var(--transition);
         border: none;
         outline: none;
      }

      .btn-primary {
         background: var(--primary);
         color: white;
         border: none;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         position: relative;
         overflow: hidden;
      }
      
      .btn-primary::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .btn-primary:hover::before {
         left: 100%;
      }

      .btn-primary:hover {
         background: var(--dark);
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }

      .empty-state {
         text-align: center;
         padding: 4rem 2rem;
         background: rgba(255, 255, 255, 0.8);
         border-radius: var(--border-radius);
         box-shadow: var(--box-shadow);
         margin: 2rem 0;
         grid-column: 1 / -1;
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

      .empty-icon {
         font-size: 3rem;
         color: var(--primary-light);
         margin-bottom: 1.5rem;
      }

      .btn-lg {
         padding: 1rem 2rem;
         font-size: 1.1rem;
         border-radius: 30px;
      }
      
      .empty-text {
         color: var(--text);
         font-size: 1.1rem;
         margin-bottom: 2rem;
      }
      
      /* Forest floor footer decoration */
      .forest-footer {
         height: 50px;
         width: 100%;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' fill='%23557A46' opacity='0.2'%3E%3Cpath d='M0,50 C150,20 300,80 450,50 C600,20 750,80 900,50 C1050,20 1200,80 1350,50 L1440,50 L1440,100 L0,100 Z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: cover;
         margin-top: -2rem;
         position: relative;
         z-index: -1;
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
         .orders-container {
            grid-template-columns: 1fr;
         }
      }

      @media (max-width: 768px) {
         .orders-container {
            padding: 1.5rem;
            margin: 1rem;
         }
         
         .order-row {
            flex-direction: column;
         }
         
         .order-label {
            width: 100%;
            margin-bottom: 0.5rem;
         }
         
         .order-label::after {
            display: none;
         }
         
         .order-footer {
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
         }
         
         .order-actions {
            width: 100%;
         }
         
         .order-actions .btn {
            width: 100%;
            text-align: center;
         }
         
         .orders-title::before,
         .orders-title::after {
            display: none;
         }
      }
  
      :root {
         --primary-color: #FF6B35;
         --primary-light: #ff8f68;
         --primary-dark: #e05a2b;
         --secondary-color: #2EC4B6;
         --text-color: #333333;
         --text-light: #666666;
         --white: #FFFFFF;
         --light-bg: #F7F7F7;
         --gray-bg: #EEEEEE;
         --border-color: #E0E0E0;
         --danger: #FF4136;
         --success: #3D9970;
         --font-primary: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }

      * {
         font-family: var(--font-primary);
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         outline: none;
         border: none;
         text-decoration: none;
      }

      body {
         background-color: var(--light-bg);
      }

      .container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 20px;
      }

      .products.shopping-cart {
         padding: 30px 0;
      }

      .heading {
         font-size: 28px;
         color: var(--text-color);
         text-transform: capitalize;
         margin-bottom: 35px;
         text-align: center;
         font-weight: 600;
         position: relative;
      }

      .heading:after {
         content: '';
         position: absolute;
         bottom: -10px;
         left: 50%;
         transform: translateX(-50%);
         width: 60px;
         height: 4px;
         background-color: var(--primary-color);
         border-radius: 2px;
      }

      .message {
         background-color: #e8f5e9;
         color: #2e7d32;
         padding: 12px 20px;
         margin-bottom: 20px;
         border-radius: 8px;
         display: flex;
         align-items: center;
         justify-content: space-between;
         animation: fadeIn 0.5s ease;
      }

      @keyframes fadeIn {
         from { opacity: 0; transform: translateY(-10px); }
         to { opacity: 1; transform: translateY(0); }
      }

      .message i {
         cursor: pointer;
         font-size: 16px;
         color: #555;
      }

      .message i:hover {
         color: var(--danger);
      }

      .cart-container {
         display: flex;
         flex-direction: column;
         gap: 20px;
      }

      @media (min-width: 992px) {
         .cart-container {
            flex-direction: row;
         }

         .cart-items {
            flex: 3;
         }

         .cart-summary-container {
            flex: 1;
         }
      }

      .cart-items {
         width: 100%;
         margin-bottom: 20px;
      }

      /* Table styling for the cart items */
      .cart-table {
         width: 100%;
         border-collapse: collapse;
         background-color: var(--white);
         border-radius: 8px;
         overflow: hidden;
         box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }

      .cart-table thead {
         background-color: var(--gray-bg);
      }

      .cart-table th {
         padding: 15px;
         text-align: left;
         color: var(--text-color);
         font-weight: 600;
         font-size: 14px;
      }

      .cart-table td {
         padding: 15px;
         vertical-align: middle;
         border-top: 1px solid var(--border-color);
      }

      /* Product checkbox styling */
      .product-checkbox {
         width: 18px;
         height: 18px;
         cursor: pointer;
         accent-color: var(--primary-color);
      }

      /* Product image styling */
      .product-image {
         width: 70px;
         height: 70px;
         object-fit: cover;
         border-radius: 6px;
      }

      /* Product details styling */
      .product-content {
         display: flex;
         flex-direction: column;
         gap: 5px;
      }

      .product-name {
         font-size: 15px;
         color: var(--text-color);
         font-weight: 600;
         max-width: 100%;
         overflow: hidden;
         text-overflow: ellipsis;
         display: -webkit-box;
         -webkit-line-clamp: 2;
         -webkit-box-orient: vertical;
         line-height: 1.3;
      }

      .quick-view-link {
         color: var(--text-light);
         font-size: 13px;
         display: flex;
         align-items: center;
         gap: 5px;
         width: fit-content;
      }

      .quick-view-link:hover {
         color: var(--primary-color);
      }

      /* Product price styling */
      .product-price {
         font-size: 15px;
         color: var(--text-color);
         font-weight: 500;
      }

      /* Quantity controls */
      .quantity-wrapper {
         display: flex;
         align-items: center;
         gap: 10px;
      }

      .qty-controls {
         display: flex;
         align-items: center;
         border: 1px solid var(--border-color);
         border-radius: 6px;
         overflow: hidden;
         height: 32px;
         width: 90px;
      }

      .qty {
         width: 50px;
         height: 30px;
         border: none;
         text-align: center;
         font-size: 14px;
         color: var(--text-color);
         -moz-appearance: textfield;
         background-color: var(--white);
         padding: 0;
      }

      .qty::-webkit-outer-spin-button,
      .qty::-webkit-inner-spin-button {
         -webkit-appearance: none;
         margin: 0;
      }

      .update-btn {
         width: 32px;
         height: 32px;
         background-color: var(--light-bg);
         color: var(--text-color);
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.3s ease;
         border: none;
         font-size: 12px;
      }

      .update-btn:hover {
         background-color: var(--primary-light);
         color: var(--white);
      }

      /* Subtotal styling */
      .sub-total {
         font-size: 15px;
         color: var(--primary-color);
         font-weight: 600;
      }

      /* Product actions styling */
      .product-actions {
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .delete-btn {
         padding: 6px 10px;
         background-color: #fff2f0;
         color: var(--danger);
         border-radius: 6px;
         font-size: 13px;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         gap: 5px;
      }

      .delete-btn i {
         font-size: 12px;
      }

      .delete-btn:hover {
         background-color: var(--danger);
         color: var(--white);
      }

      .empty {
         text-align: center;
         padding: 40px 0;
         color: var(--text-light);
         font-size: 16px;
         line-height: 1.6;
      }

      .empty i {
         font-size: 60px;
         color: var(--gray-bg);
         margin-bottom: 20px;
         display: block;
      }

      .empty a {
         display: inline-block;
         margin-top: 20px;
         background-color: var(--primary-color);
         color: var(--white);
         padding: 10px 24px;
         border-radius: 6px;
         font-weight: 500;
         transition: all 0.3s ease;
      }

      .empty a:hover {
         background-color: var(--primary-dark);
      }

      .cart-summary-container {
         width: 100%;
      }

      .cart-summary {
         background-color: var(--white);
         border-radius: 10px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
         padding: 25px;
      }

      .summary-title {
         font-size: 20px;
         color: var(--text-color);
         font-weight: 600;
         margin-bottom: 20px;
         padding-bottom: 15px;
         border-bottom: 1px solid var(--border-color);
      }

      .summary-item {
         display: flex;
         justify-content: space-between;
         margin-bottom: 15px;
         color: var(--text-light);
         font-size: 15px;
      }

      .summary-item.total {
         font-size: 18px;
         font-weight: 600;
         color: var(--text-color);
         padding-top: 15px;
         margin-top: 15px;
         border-top: 1px solid var(--border-color);
      }

      .summary-item.total .price {
         color: var(--primary-color);
      }

      .cart-buttons {
         display: grid;
         grid-template-columns: 1fr;
         gap: 10px;
         margin-top: 20px;
      }

      .btn, .option-btn, .delete-all-btn {
         padding: 12px 20px;
         border-radius: 8px;
         font-size: 16px;
         font-weight: 500;
         cursor: pointer;
         text-align: center;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
      }

      .btn {
         background-color: var(--primary-color);   
         color: var(--white);
      }

      .btn:hover {
         background-color: var(--primary-dark);
      }

      .btn.disabled {
         opacity: 0.5;
         cursor: not-allowed;
      }

      .option-btn {
         background-color: var(--white);
         color: var(--text-color);
         border: 1px solid var(--border-color);
      }

      .option-btn:hover {
         background-color: var(--light-bg);
      }

      .delete-all-btn {
         background-color: #fff2f0;
         color: var(--danger);
      }

      .delete-all-btn:hover {
         background-color: var(--danger);
         color: var(--white);
      }

      .overlay {
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0,0,0,0.5);
         z-index: 999;
         display: none;
         backdrop-filter: blur(3px);
      }

      .confirmation-dialog {
         position: fixed;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         background-color: var(--white);
         padding: 25px;
         border-radius: 12px;
         box-shadow: 0 10px 30px rgba(0,0,0,0.1);
         z-index: 1000;
         display: none;
         width: 90%;
         max-width: 350px;
         text-align: center;
      }

      .confirmation-dialog p {
         font-size: 18px;
         color: var(--text-color);
         margin-bottom: 25px;
      }

      .confirmation-buttons {
         display: flex;
         justify-content: center;
         gap: 15px;
      }

      .confirmation-buttons button {
         padding: 10px 25px;
         border-radius: 6px;
         font-size: 15px;
         font-weight: 500;
         cursor: pointer;
         transition: all 0.3s ease;
      }

      .confirm-btn {
         background-color: var(--danger);
         color: var(--white);
      }

      .confirm-btn:hover {
         background-color: #d63326;
      }

      .cancel-btn {
         background-color: var(--gray-bg);
         color: var(--text-color);
      }

      .cancel-btn:hover {
         background-color: #ddd;
      }

      /* Animation for items */
      @keyframes slideIn {
         from { opacity: 0; transform: translateY(20px); }
         to { opacity: 1; transform: translateY(0); }
      }

      .cart-table tbody tr {
         animation: slideIn 0.4s ease forwards;
      }

      .cart-table tbody tr:nth-child(2) { animation-delay: 0.1s; }
      .cart-table tbody tr:nth-child(3) { animation-delay: 0.2s; }
      .cart-table tbody tr:nth-child(4) { animation-delay: 0.3s; }
      .cart-table tbody tr:nth-child(5) { animation-delay: 0.4s; }

      /* Responsive adjustments */
      @media (max-width: 992px) {
         .cart-table th, .cart-table td {
            padding: 12px 10px;
         }
         
         .product-image {
            width: 60px;
            height: 60px;
         }
      }

      @media (max-width: 768px) {
         .cart-table {
            display: block;
         }
         
         .cart-table thead {
            display: none;
         }
         
         .cart-table tbody, .cart-table tr, .cart-table td {
            display: block;
            width: 100%;
         }
         
         .cart-table tr {
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding: 10px;
            position: relative;
         }
         
         .cart-table td {
            text-align: right;
            padding: 8px 10px;
            border: none;
            position: relative;
         }
         
         .cart-table td:before {
            content: attr(data-label);
            float: left;
            font-weight: 600;
            color: var(--text-color);
         }
         
         .cart-table td:first-child {
            padding-top: 15px;
         }
         
         .cart-table td:last-child {
            padding-bottom: 15px;
         }
         
         .product-image {
            width: 80px;
            height: 80px;
            margin: 0 auto;
         }
         
         .product-actions {
            position: absolute;
            top: 10px;
            right: 10px;
         }
         
         .quantity-wrapper {
            justify-content: flex-end;
         }
      }

      @media (max-width: 480px) {
         .cart-table td {
            font-size: 14px;
            padding: 6px 10px;
         }
         
         .product-image {
            width: 60px;
            height: 60px;
         }
         
         .product-name {
            font-size: 14px;
         }
         
         .delete-btn {
            padding: 5px 8px;
            font-size: 12px;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="container">
   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '
         <div class="message">
            <span>'.$msg.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <div class="overlay" id="overlay"></div>
   <div class="confirmation-dialog" id="confirmationDialog">
      <p>Do you really want to remove this item?</p>
      <div class="confirmation-buttons">
         <button class="cancel-btn" id="cancelDelete">Cancel</button>
         <button class="confirm-btn" id="confirmDelete">Remove</button>
      </div>
   </div>

   <!-- New dialog for clear cart confirmation -->
   <div class="confirmation-dialog" id="clearCartDialog">
      <p>Are you sure you want to clear your cart?</p>
      <div class="confirmation-buttons">
         <button class="cancel-btn" id="cancelClearCart">Cancel</button>
         <button class="confirm-btn" id="confirmClearCart">Clear Cart</button>
      </div>
   </div>

   <section class="products shopping-cart">
      <h3 class="heading">Your Order</h3>

      <div class="cart-container">
         <div class="cart-items">
            <!-- Using a table for clear column layout -->
            <?php
               $grand_total = 0;
               $total_items = 0;
               $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
               $select_cart->execute([$user_id]);
               if($select_cart->rowCount() > 0){
            ?>
            <table class="cart-table">
               <thead>
                  <tr>
                     <th width="5%"></th>
                     <th width="15%">Image</th>
                     <th width="25%">Item</th>
                     <th width="15%">Price</th>
                     <th width="20%">Quantity</th>
                     <th width="15%">Subtotal</th>
                     <th width="5%"></th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                     while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                        $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                        $grand_total += $sub_total;
                        $total_items += $fetch_cart['quantity'];
                  ?>
                  <tr>
                     <td data-label="">
                        <form method="post" id="item_form_<?= $fetch_cart['id']; ?>">
                           <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                           <input type="checkbox" name="selected_items[]" value="<?= $fetch_cart['id']; ?>" class="product-checkbox" checked>
                        </form>
                     </td>
                     <td data-label="Image">
                        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="<?= $fetch_cart['name']; ?>" class="product-image">
                     </td>
                     <td data-label="Item">
                        <div class="product-content">
                           <h4 class="product-name"><?= $fetch_cart['name']; ?></h4>
                           <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="quick-view-link">
                              <i class="fas fa-eye"></i> Quick view
                           </a>
                        </div>
                     </td>
                     <td data-label="Price" class="product-price">₱<?= formatPrice($fetch_cart['price']); ?></td>
                     <td data-label="Quantity">
                        <div class="quantity-wrapper">
                           <div class="qty-controls">
                              <input form="item_form_<?= $fetch_cart['id']; ?>" type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="<?= $fetch_cart['quantity']; ?>">
                              <button form="item_form_<?= $fetch_cart['id']; ?>" type="submit" class="update-btn fas fa-edit" name="update_qty" title="Update quantity"></button>
                           </div>
                        </div>
                     </td>
                     <td data-label="Subtotal" class="sub-total">₱<?= formatPrice($sub_total); ?></td>
                     <td data-label="">
                        <div class="product-actions">
                           <button type="button" class="delete-btn delete-item-btn" data-cart-id="<?= $fetch_cart['id']; ?>">
                              <i class="fas fa-trash"></i>
                           </button>
                        </div>
                     </td>
                  </tr>
                  <?php
                     }
                  ?>
               </tbody>
            </table>
            <?php
               } else {
                  echo '
                  <div class="empty">
                     <i class="fas fa-shopping-cart"></i>
                     Your cart is empty
                     <p>Add some delicious items to your cart!</p>
                     <a href="shop.php"><i class="fas fa-utensils"></i> Browse Menu</a>
                  </div>
                  ';
               }
            ?>
         </div>

         <?php if($select_cart->rowCount() > 0): ?>
         <div class="cart-summary-container">
            <div class="cart-summary">
               <h3 class="summary-title">Order Summary</h3>
               
               <div class="summary-item">
                  <span>Subtotal (<?= $total_items; ?> items)</span>
                  <span>₱<?= formatPrice($grand_total); ?></span>
               </div>
               
               <div class="summary-item">
                  <span>Delivery Fee</span>
                  <span>₱<?= formatPrice(50); ?></span>
               </div>
               
               <div class="summary-item">
                  <span>Tax</span>
                  <span>₱<?= formatPrice($grand_total * 0.001); ?></span>
               </div>
               
               <div class="summary-item total">
                  <span>Total</span>
                  <span class="price">₱<?= formatPrice($grand_total + 50 + ($grand_total * 0.001)); ?></span>
               </div>
               
               <div class="cart-buttons">
                  <a href="#" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" id="checkoutBtn">
                     <i class="fas fa-check-circle"></i> Proceed to Checkout
                  </a>
                  
                  <a href="shop.php" class="option-btn">
                     <i class="fas fa-utensils"></i> Continue Ordering
                  </a>
                  
                  <a href="#" class="delete-all-btn <?= ($grand_total > 1)?'':'disabled'; ?>" id="clearCartBtn">
                     <i class="fas fa-trash"></i> Clear Cart
                  </a>
               </div>
            </div>
         </div>
         <?php endif; ?>
      </div>
   </section>
</div>

<script>
// Delete confirmation functionality
document.addEventListener('DOMContentLoaded', function() {
   let currentDeleteBtn = null;
   const overlay = document.getElementById('overlay');
   const confirmationDialog = document.getElementById('confirmationDialog');
   const clearCartDialog = document.getElementById('clearCartDialog');
   const cancelDeleteBtn = document.getElementById('cancelDelete');
   const confirmDeleteBtn = document.getElementById('confirmDelete');
   const cancelClearCartBtn = document.getElementById('cancelClearCart');
   const confirmClearCartBtn = document.getElementById('confirmClearCart');
   const clearCartBtn = document.getElementById('clearCartBtn');

   // Set up delete button click handlers
   document.querySelectorAll('.delete-item-btn').forEach(btn => {
      btn.addEventListener('click', function() {
         currentDeleteBtn = this;
         overlay.style.display = 'block';
         confirmationDialog.style.display = 'block';
         clearCartDialog.style.display = 'none';
         document.body.style.overflow = 'hidden';
      });
   });

   // Setup clear cart button handler
   if (clearCartBtn) {
      clearCartBtn.addEventListener('click', function(e) {
         e.preventDefault();
         if (this.classList.contains('disabled')) return;
         
         overlay.style.display = 'block';
         confirmationDialog.style.display = 'none';
         clearCartDialog.style.display = 'block';
         document.body.style.overflow = 'hidden';
      });
   }

   // Cancel delete
   cancelDeleteBtn.addEventListener('click', function() {
      overlay.style.display = 'none';
      confirmationDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
      currentDeleteBtn = null;
   });

   // Cancel clear cart
   cancelClearCartBtn.addEventListener('click', function() {
      overlay.style.display = 'none';
      clearCartDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
   });

   // Confirm delete
   confirmDeleteBtn.addEventListener('click', function() {
      if(currentDeleteBtn) {
         const cartId = currentDeleteBtn.getAttribute('data-cart-id');
         const form = document.getElementById('item_form_' + cartId);
         const deleteInput = document.createElement('input');
         deleteInput.type = 'hidden';
         deleteInput.name = 'delete';
         deleteInput.value = '1';
         form.appendChild(deleteInput);
         form.submit();
      }
      overlay.style.display = 'none';
      confirmationDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
      currentDeleteBtn = null;
   });

   // Confirm clear cart
   confirmClearCartBtn.addEventListener('click', function() {
      window.location.href = 'cart.php?delete_all';
   });

   // Close dialog when clicking overlay
   overlay.addEventListener('click', function() {
      overlay.style.display = 'none';
      confirmationDialog.style.display = 'none';
      clearCartDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
      currentDeleteBtn = null;
   });

   // Checkout functionality with selected items
   const checkoutBtn = document.getElementById('checkoutBtn');
   if (checkoutBtn) {
      checkoutBtn.addEventListener('click', function(e) {
         e.preventDefault();
         
         // Get all checked checkboxes
         const checkboxes = document.querySelectorAll('.product-checkbox:checked');
         const selectedItems = Array.from(checkboxes).map(checkbox => checkbox.value);
         
         // If no items are selected but checkboxes exist, show alert
         if (selectedItems.length === 0 && document.querySelectorAll('.product-checkbox').length > 0) {
            alert('Please select at least one item to checkout');
            return;
         }
         
         // Create a form to submit the selected items
         const form = document.createElement('form');
         form.method = 'post';
         form.action = 'checkout.php';
         
         // Add selected items as hidden inputs
         selectedItems.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items[]';
            input.value = item;
            form.appendChild(input);
         });
         
         // Submit the form
         document.body.appendChild(form);
         form.submit();
      });
   }

   // Highlight update button when quantity changes
   document.querySelectorAll('.qty').forEach(input => {
      input.addEventListener('change', function() {
         const form = this.form;
         form.querySelector('button[name="update_qty"]').style.backgroundColor = '#FF6B35';
         form.querySelector('button[name="update_qty"]').style.color = '#fff';
      });
   });
});
</script>

</body>
</html>