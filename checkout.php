<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

// Define shipping fee and tax rate constants
define('SHIPPING_FEE', 50.00); // Flat rate shipping fee
define('TAX_RATE', 0.001); // 12% VAT

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = $_POST['flat'] .', '. $_POST['street'] .', '. $_POST['city'] .', '. $_POST['state'] .', '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   
   $selected_items = isset($_POST['selected_items']) ? $_POST['selected_items'] : array();
   $shipping_fee = isset($_POST['shipping_fee']) ? (float)$_POST['shipping_fee'] : SHIPPING_FEE;
   
   if(!empty($selected_items)){
      $placeholders = rtrim(str_repeat('?,', count($selected_items)), ',');
      
      $get_selected_items = $conn->prepare("SELECT * FROM `cart` WHERE id IN ($placeholders) AND user_id = ?");
      $get_selected_items->execute(array_merge($selected_items, [$user_id]));
      
      if($get_selected_items->rowCount() > 0){
         $cart_items = array();
         $subtotal = 0;
         
         while($fetch_item = $get_selected_items->fetch(PDO::FETCH_ASSOC)){
            $cart_items[] = $fetch_item['name'].' ('.$fetch_item['price'].' x '. $fetch_item['quantity'].') - ';
            $subtotal += ($fetch_item['price'] * $fetch_item['quantity']);
         }
         
         $tax = $subtotal * TAX_RATE;
         $grand_total = $subtotal + $shipping_fee + $tax;
         $total_products = implode($cart_items);
         
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, subtotal, shipping_fee, tax, total_price, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $subtotal, $shipping_fee, $tax, $grand_total, 'pending']);
         $delete_selected = $conn->prepare("DELETE FROM `cart` WHERE id IN ($placeholders)");
         $delete_selected->execute($selected_items);
         
         $message[] = 'Order placed successfully!';
      }else{
         $message[] = 'No selected items found in your cart!';
      }
   }else{
      $message[] = 'Please select items to checkout!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout | Meat Shop</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      :root {
         --primary: #ff6b6b;
         --primary-light: #ff8e8e;
         --secondary: #4ecdc4;
         --dark: #292f36;
         --light: #f7fff7;
         --accent: #ffd166;
      }
      
      .checkout-container {
         max-width: 1200px;
         margin: 2rem auto;
         padding: 2rem;
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 2rem;
         background: white;
         border-radius: 15px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      
      .order-summary {
         background: #f9f9f9;
         padding: 2rem;
         border-radius: 10px;
         border: 1px solid #eee;
      }
      
      .order-summary h3, .checkout-form h3 {
         font-size: 1.8rem;
         color: var(--dark);
         margin-bottom: 1.5rem;
         padding-bottom: 1rem;
         border-bottom: 2px solid var(--primary);
      }
      
      .order-item {
         display: flex;
         justify-content: space-between;
         padding: 1rem 0;
         border-bottom: 1px solid #eee;
      }
      
      .order-item:last-child {
         border-bottom: none;
      }
      
      .grand-total {
         font-size: 1.5rem;
         font-weight: bold;
         margin-top: 2rem;
         padding-top: 1rem;
         border-top: 2px solid var(--primary);
         display: flex;
         justify-content: space-between;
      }
      
      .checkout-form .input-group {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 1.5rem;
         margin-bottom: 1.5rem;
      }
      
      .inputBox {
         margin-bottom: 1.5rem;
      }
      
      .inputBox label {
         display: block;
         margin-bottom: 0.5rem;
         color: var(--dark);
         font-weight: 500;
      }
      
      .inputBox input, .inputBox select {
         width: 100%;
         padding: 12px 15px;
         border: 1px solid #ddd;
         border-radius: 8px;
         font-size: 1rem;
         transition: all 0.3s;
      }
      
      .inputBox input:focus, .inputBox select:focus {
         border-color: var(--primary);
         outline: none;
         box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
      }
      
      .btn-checkout {
         background: var(--primary);
         color: white;
         border: none;
         padding: 15px 30px;
         font-size: 1.1rem;
         border-radius: 8px;
         cursor: pointer;
         width: 100%;
         transition: all 0.3s;
         margin-top: 1rem;
      }
      
      .btn-checkout:hover {
         background: var(--primary-light);
         transform: translateY(-2px);
      }
      
      .btn-checkout:disabled {
         background: #ccc;
         cursor: not-allowed;
         transform: none;
      }
      
      .payment-methods {
         margin-top: 2rem;
      }
      
      .payment-method {
         display: flex;
         align-items: center;
         padding: 1rem;
         border: 1px solid #eee;
         border-radius: 8px;
         margin-bottom: 1rem;
         cursor: pointer;
         transition: all 0.3s;
      }
      
      .payment-method:hover {
         border-color: var(--primary);
      }
      
      .payment-method i {
         font-size: 1.5rem;
         margin-right: 1rem;
         color: var(--primary);
      }
      
      @media (max-width: 768px) {
         .checkout-container {
            grid-template-columns: 1fr;
            padding: 1rem;
         }
         
         .checkout-form .input-group {
            grid-template-columns: 1fr;
         }
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="checkout-container">
   <div class="order-summary">
      <h3><i class="fas fa-shopping-basket"></i> Order Summary</h3>
      
      <?php
         $subtotal = 0;
         $cart_items = array();
         $selected_items = isset($_POST['selected_items']) ? $_POST['selected_items'] : array();
         
         if(!empty($selected_items)){
            $placeholders = rtrim(str_repeat('?,', count($selected_items)), ',');
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE id IN ($placeholders) AND user_id = ?");
            $select_cart->execute(array_merge($selected_items, [$user_id]));
            
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
                  $subtotal += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <div class="order-item">
            <span><?= $fetch_cart['name']; ?></span>
            <span>Php <?= number_format($fetch_cart['price'] * $fetch_cart['quantity'], 2); ?></span>
         </div>
         <div class="order-details">
            <small><?= $fetch_cart['quantity']; ?> x Php <?= number_format($fetch_cart['price'], 2); ?></small>
         </div>
      <?php
               }
               
               $shipping_fee = SHIPPING_FEE;
               $tax = $subtotal * TAX_RATE;
               $grand_total = $subtotal + $shipping_fee + $tax;
      ?>
         <div class="order-item">
            <span>Subtotal:</span>
            <span>Php <?= number_format($subtotal, 2); ?></span>
         </div>
         
         <div class="order-item">
            <span>Shipping Fee:</span>
            <span>Php <?= number_format($shipping_fee, 2); ?></span>
         </div>
         
         <div class="order-item">
            <span>Tax (.1% VAT):</span>
            <span>Php <?= number_format($tax, 2); ?></span>
         </div>
         
         <div class="grand-total">
            <span>Total:</span>
            <span>Php <?= number_format($grand_total, 2); ?></span>
         </div>
         
         <input type="hidden" name="total_products" value="<?= htmlspecialchars(implode($cart_items)); ?>">
         <input type="hidden" name="subtotal" value="<?= $subtotal; ?>">
         <input type="hidden" name="shipping_fee" value="<?= $shipping_fee; ?>">
         <input type="hidden" name="tax" value="<?= $tax; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
      <?php
            } else {
               echo '<p class="empty">No selected items found in your cart!</p>';
            }
         } else {
            echo '<p class="empty">Please select items to checkout!</p>';
         }
      ?>
   </div>
   
   <form action="" method="POST" class="checkout-form">
      <h3><i class="fas fa-truck"></i> Delivery Information</h3>
      
      <div class="input-group">
         <div class="inputBox">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" maxlength="20" required>
         </div>
         <div class="inputBox">
            <label for="number">Phone Number</label>
            <input type="number" id="number" name="number" placeholder="Ex. 9xx xxx xxx" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
      </div>
      
      <div class="inputBox">
         <label for="email">Email Address</label>
         <input type="email" id="email" name="email" placeholder="Enter your email" maxlength="50" required>
      </div>
      
      <div class="input-group">
         <div class="inputBox">
            <label for="flat">Barangay</label>
            <input type="text" id="flat" name="flat" placeholder="Maraykit" maxlength="50" required>
         </div>
         <div class="inputBox">
            <label for="street">Street</label>
            <input type="text" id="street" name="street" placeholder="e.g. Mabini Street" maxlength="50" required>
         </div>
      </div>
      
      <div class="input-group">
         <div class="inputBox">
            <label for="city">City</label>
            <input type="text" id="city" name="city" placeholder="e.g. San Juan" maxlength="50" required>
         </div>
         <div class="inputBox">
            <label for="state">State/Province</label>
            <input type="text" id="state" name="state" placeholder="e.g. Batangas" maxlength="50" required>
         </div>
      </div>
      
      <div class="input-group">
         <div class="inputBox">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" placeholder="e.g. Philippines" maxlength="50" required>
         </div>
         <div class="inputBox">
            <label for="pin_code">Postal Code</label>
            <input type="number" id="pin_code" name="pin_code" placeholder="e.g. 1000" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" required>
         </div>
      </div>
      
      <div class="payment-methods">
         <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
         
         <div class="inputBox">
            <select name="method" class="box" required>
               <option value="" disabled selected>Select payment method</option>
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="gcash">GCash</option>
               <option value="paymaya">PayMaya</option>
            </select>
         </div>
      </div>
      
      <?php if(!empty($selected_items)): ?>
         <?php foreach($selected_items as $item_id): ?>
            <input type="hidden" name="selected_items[]" value="<?= htmlspecialchars($item_id); ?>">
         <?php endforeach; ?>
      <?php endif; ?>
      
      <button type="submit" name="order" class="btn-checkout <?= ($subtotal > 0)?'':'disabled'; ?>">
         <i class="fas fa-shopping-bag"></i> Complete Order
      </button>
   </form>
</div>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>