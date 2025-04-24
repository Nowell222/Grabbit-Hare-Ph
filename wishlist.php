<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
   $_SESSION['message'] = 'Item removed from favorites';
   header('location:wishlist.php');
   exit();
}

if(isset($_POST['add_to_cart'])){
   $pid = $_POST['pid'];
   $name = $_POST['name'];
   $price = $_POST['price'];
   $image = $_POST['image'];
   $qty = $_POST['qty'];
   
   // Check if product already exists in cart
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ?");
   $check_cart->execute([$user_id, $pid]);
   
   if($check_cart->rowCount() > 0){
      $_SESSION['message'] = 'Product already in cart!';
   }else{
      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
      $_SESSION['message'] = 'Added to cart successfully!';
   }
   header('location:wishlist.php');
   exit();
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   $_SESSION['message'] = 'All items removed from favorites';
   header('location:wishlist.php');
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
   <title>Favorites</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
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

      .container {
         max-width: 1200px;
         margin: 0 auto;
         padding: 20px;
      }

      .products.favorites {
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

      .wishlist-container {
         display: flex;
         flex-direction: column;
         gap: 20px;
      }

      @media (min-width: 992px) {
         .wishlist-container {
            flex-direction: row;
         }

         .wishlist-items {
            flex: 3;
         }

         .wishlist-summary-container {
            flex: 1;
         }
      }

      .wishlist-items {
         width: 100%;
         margin-bottom: 20px;
      }

      /* Table styling for the wishlist items */
      .wishlist-table {
         width: 100%;
         border-collapse: collapse;
         background-color: var(--white);
         border-radius: 8px;
         overflow: hidden;
         box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }

      .wishlist-table thead {
         background-color: var(--gray-bg);
      }

      .wishlist-table th {
         padding: 15px;
         text-align: left;
         color: var(--text-color);
         font-weight: 600;
         font-size: 14px;
      }

      .wishlist-table td {
         padding: 15px;
         vertical-align: middle;
         border-top: 1px solid var(--border-color);
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

      /* Product actions styling */
      .product-actions {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 10px;
      }

      .btn {
         padding: 8px 12px;
         border-radius: 6px;
         font-size: 13px;
         cursor: pointer;
         transition: all 0.3s ease;
         display: flex;
         align-items: center;
         gap: 5px;
      }

      .btn i {
         font-size: 12px;
      }

      .add-to-cart-btn {
         background-color: var(--primary-color);
         color: var(--white);
      }

      .add-to-cart-btn:hover {
         background-color: var(--primary-dark);
      }

      .delete-btn {
         background-color: #fff2f0;
         color: var(--danger);
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

      .wishlist-summary-container {
         width: 100%;
      }

      .wishlist-summary {
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

      .wishlist-buttons {
         display: grid;
         grid-template-columns: 1fr;
         gap: 10px;
         margin-top: 20px;
      }

      .btn-lg, .option-btn, .delete-all-btn {
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

      .btn-lg {
         background-color: var(--primary-color);   
         color: var(--white);
      }

      .btn-lg:hover {
         background-color: var(--primary-dark);
      }

      .btn-lg.disabled {
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

      .wishlist-table tbody tr {
         animation: slideIn 0.4s ease forwards;
      }

      .wishlist-table tbody tr:nth-child(2) { animation-delay: 0.1s; }
      .wishlist-table tbody tr:nth-child(3) { animation-delay: 0.2s; }
      .wishlist-table tbody tr:nth-child(4) { animation-delay: 0.3s; }
      .wishlist-table tbody tr:nth-child(5) { animation-delay: 0.4s; }

      /* Responsive adjustments */
      @media (max-width: 992px) {
         .wishlist-table th, .wishlist-table td {
            padding: 12px 10px;
         }
         
         .product-image {
            width: 60px;
            height: 60px;
         }
      }

      @media (max-width: 768px) {
         .wishlist-table {
            display: block;
         }
         
         .wishlist-table thead {
            display: none;
         }
         
         .wishlist-table tbody, .wishlist-table tr, .wishlist-table td {
            display: block;
            width: 100%;
         }
         
         .wishlist-table tr {
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding: 10px;
            position: relative;
         }
         
         .wishlist-table td {
            text-align: right;
            padding: 8px 10px;
            border: none;
            position: relative;
         }
         
         .wishlist-table td:before {
            content: attr(data-label);
            float: left;
            font-weight: 600;
            color: var(--text-color);
         }
         
         .wishlist-table td:first-child {
            padding-top: 15px;
         }
         
         .wishlist-table td:last-child {
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
            flex-direction: column;
            gap: 5px;
         }
      }

      @media (max-width: 480px) {
         .wishlist-table td {
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
         
         .btn {
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
      <p>Do you really want to remove this item from favorites?</p>
      <div class="confirmation-buttons">
         <button class="cancel-btn" id="cancelDelete">Cancel</button>
         <button class="confirm-btn" id="confirmDelete">Remove</button>
      </div>
   </div>

   <!-- New dialog for clear favorites confirmation -->
   <div class="confirmation-dialog" id="clearFavoritesDialog">
      <p>Are you sure you want to clear your favorites?</p>
      <div class="confirmation-buttons">
         <button class="cancel-btn" id="cancelClearFavorites">Cancel</button>
         <button class="confirm-btn" id="confirmClearFavorites">Clear Favorites</button>
      </div>
   </div>

   <section class="products favorites">
      <h3 class="heading">Your Favorites</h3>

      <div class="wishlist-container">
         <div class="wishlist-items">
            <!-- Using a table for clear column layout -->
            <?php
               $grand_total = 0;
               $total_items = 0;
               $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
               $select_wishlist->execute([$user_id]);
               if($select_wishlist->rowCount() > 0){
            ?>
            <table class="wishlist-table">
               <thead>
                  <tr>
                     <th width="15%">Image</th>
                     <th width="30%">Item</th>
                     <th width="15%">Price</th>
                     <th width="20%">Quantity</th>
                     <th width="20%">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                     while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
                        $grand_total += $fetch_wishlist['price'];
                        $total_items++;
                  ?>
                  <form action="" method="post" class="wishlist-item-form">
                     <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
                     <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
                     <input type="hidden" name="name" value="<?= $fetch_wishlist['name']; ?>">
                     <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
                     <input type="hidden" name="image" value="<?= $fetch_wishlist['image']; ?>">
                     <tr>
                        <td data-label="Image">
                           <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="<?= $fetch_wishlist['name']; ?>" class="product-image">
                        </td>
                        <td data-label="Item">
                           <div class="product-content">
                              <h4 class="product-name"><?= $fetch_wishlist['name']; ?></h4>
                              <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="quick-view-link">
                                 <i class="fas fa-eye"></i> Quick view
                              </a>
                           </div>
                        </td>
                        <td data-label="Price" class="product-price">₱<?= formatPrice($fetch_wishlist['price']); ?></td>
                        <td data-label="Quantity">
                           <div class="quantity-wrapper">
                              <div class="qty-controls">
                                 <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                              </div>
                           </div>
                        </td>
                        <td data-label="Actions">
                           <div class="product-actions">
                              <button type="submit" name="add_to_cart" class="btn add-to-cart-btn">
                                 <i class="fas fa-cart-plus"></i> Add to Cart
                              </button>
                              <button type="button" class="btn delete-btn delete-item-btn" data-wishlist-id="<?= $fetch_wishlist['id']; ?>">
                                 <i class="fas fa-trash"></i> Remove
                              </button>
                           </div>
                        </td>
                     </tr>
                  </form>
                  <?php
                     }
                  ?>
               </tbody>
            </table>
            <?php
               } else {
                  echo '
                  <div class="empty">
                     <i class="fas fa-heart"></i>
                     Your favorites is empty
                     <p>Add some delicious items to your favorites!</p>
                     <a href="shop.php"><i class="fas fa-utensils"></i> Browse Menu</a>
                  </div>
                  ';
               }
            ?>
         </div>

         <?php if($select_wishlist->rowCount() > 0): ?>
         <div class="wishlist-summary-container">
            <div class="wishlist-summary">
               <h3 class="summary-title">Favorites Summary</h3>
               
               <div class="summary-item">
                  <span>Total Items</span>
                  <span><?= $total_items; ?></span>
               </div>
               
               <div class="summary-item total">
                  <span>Total Value</span>
                  <span class="price">₱<?= formatPrice($grand_total); ?></span>
               </div>
               
               <div class="wishlist-buttons">
                  <a href="shop.php" class="option-btn">
                     <i class="fas fa-utensils"></i> Continue Shopping
                  </a>
                  
                  <a href="#" class="delete-all-btn <?= ($grand_total > 1)?'':'disabled'; ?>" id="clearFavoritesBtn">
                     <i class="fas fa-trash"></i> Clear Favorites
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
   const clearFavoritesDialog = document.getElementById('clearFavoritesDialog');
   const cancelDeleteBtn = document.getElementById('cancelDelete');
   const confirmDeleteBtn = document.getElementById('confirmDelete');
   const cancelClearFavoritesBtn = document.getElementById('cancelClearFavorites');
   const confirmClearFavoritesBtn = document.getElementById('confirmClearFavorites');
   const clearFavoritesBtn = document.getElementById('clearFavoritesBtn');

   // Set up delete button click handlers
   document.querySelectorAll('.delete-item-btn').forEach(btn => {
      btn.addEventListener('click', function() {
         currentDeleteBtn = this;
         overlay.style.display = 'block';
         confirmationDialog.style.display = 'block';
         clearFavoritesDialog.style.display = 'none';
         document.body.style.overflow = 'hidden';
      });
   });

   // Setup clear favorites button handler
   if (clearFavoritesBtn) {
      clearFavoritesBtn.addEventListener('click', function(e) {
         e.preventDefault();
         if (this.classList.contains('disabled')) return;
         
         overlay.style.display = 'block';
         confirmationDialog.style.display = 'none';
         clearFavoritesDialog.style.display = 'block';
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

   // Cancel clear favorites
   cancelClearFavoritesBtn.addEventListener('click', function() {
      overlay.style.display = 'none';
      clearFavoritesDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
   });

   // Confirm delete
   confirmDeleteBtn.addEventListener('click', function() {
      if(currentDeleteBtn) {
         const wishlistId = currentDeleteBtn.getAttribute('data-wishlist-id');
         const form = document.createElement('form');
         form.method = 'post';
         form.action = 'wishlist.php';
         
         const wishlistIdInput = document.createElement('input');
         wishlistIdInput.type = 'hidden';
         wishlistIdInput.name = 'wishlist_id';
         wishlistIdInput.value = wishlistId;
         form.appendChild(wishlistIdInput);
         
         const deleteInput = document.createElement('input');
         deleteInput.type = 'hidden';
         deleteInput.name = 'delete';
         deleteInput.value = '1';
         form.appendChild(deleteInput);
         
         document.body.appendChild(form);
         form.submit();
      }
   });

   // Confirm clear favorites
   confirmClearFavoritesBtn.addEventListener('click', function() {
      window.location.href = 'wishlist.php?delete_all';
   });

   // Close dialog when clicking overlay
   overlay.addEventListener('click', function() {
      overlay.style.display = 'none';
      confirmationDialog.style.display = 'none';
      clearFavoritesDialog.style.display = 'none';
      document.body.style.overflow = 'auto';
      currentDeleteBtn = null;
   });

   // Add to cart button functionality - FIXED VERSION
   // Find all add to cart forms
   document.querySelectorAll('.wishlist-item-form').forEach(form => {
      form.addEventListener('submit', function(e) {
         // Only handle submission if it's for add to cart
         if (this.querySelector('[name="add_to_cart"]')) {
            const addToCartBtn = this.querySelector('.add-to-cart-btn');
            
            // Show loading state
            addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            addToCartBtn.disabled = true;
            
            // Form will submit naturally - no need to call form.submit()
         }
      });
   });
});
</script>

</body>
</html>
