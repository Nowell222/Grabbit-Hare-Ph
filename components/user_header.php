<!-- Keep the original PHP for messages -->
<?php
   if(isset($message)){
      if (!is_array($message)) {
         $message = [$message]; // Convert it to an array if it's a string
      }
      foreach($message as $msg){
         // Determine message type based on content
         $messageClass = 'message';
         $icon = 'fa-info-circle';
         if (strpos($msg, 'added to cart') !== false) {
             $messageClass = 'message success';
             $icon = 'fa-check-circle';
         } elseif (strpos($msg, 'already in cart') !== false) {
             $messageClass = 'message warning';
             $icon = 'fa-exclamation-circle';
         }
         echo '
         <div class="'.$messageClass.'">
            <i class="fas '.$icon.'"></i>
            <span>'.$msg.'</span>
         </div>
         ';
      }
   }
?>

<header class="forest-header">
   <div class="forest-bg"></div>
   <section class="flex">
      <a href="home.php" class="logo">
         <span class="rabbit-icon">🐰</span>
         Grabbit Hare<span class="accent">.</span>
      </a>

      <nav class="forest-navbar">
         <a href="home.php">Home</a>
         <a href="about.php">About</a>
         <a href="orders.php">Orders</a>
         <a href="shop.php">Shop</a>
         <a href="contact.php">Contact</a>
      </nav>

      <div class="forest-icons">
         <?php
            $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
            $total_wishlist_counts = $count_wishlist_items->rowCount();

            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_counts = $count_cart_items->rowCount();
         ?>
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php" class="forest-icon-link"><i class="fas fa-search"></i></a>
         <a href="wishlist.php" class="forest-icon-link"><i class="fas fa-heart"></i><span class="forest-count">(<?= $total_wishlist_counts; ?>)</span></a>
         <a href="cart.php" class="forest-icon-link"><i class="fas fa-shopping-cart"></i><span class="forest-count">(<?= $total_cart_counts; ?>)</span></a>
         <div id="user-btn" class="fas fa-user forest-icon-link"></div>
      </div>

      <div class="forest-profile" id="profile-menu">
         <div class="profile-leaf"></div>
         <?php          
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if($select_profile->rowCount() > 0){
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
               // Default image if user doesn't have one
               $profile_image = !empty($fetch_profile["image"]) ? 'uploaded_img/'.$fetch_profile["image"] : 'images/default-avatar.png';
         ?>
         <div class="profile-image-container">
            <img src="<?= $profile_image; ?>" alt="Profile" class="profile-image">
         </div>
         <p class="user-name"><?= $fetch_profile["name"]; ?></p>
         <a href="update_user.php" class="forest-btn">Update Profile</a>
         <div class="flex-btn">
            <a href="user_register.php" class="forest-option-btn">Register</a>
            <a href="user_login.php" class="forest-option-btn">Login</a>
         </div>
         <a href="#" class="forest-delete-btn" onclick="openLogoutPanel();">Logout</a> 
         <?php
            }else{
         ?>
         <div class="profile-image-container">
            <img src="images/default-avatar.png" alt="Profile" class="profile-image">
         </div>
         <p class="user-greeting">Please Login or Register First!</p>
         <div class="flex-btn">
            <a href="user_register.php" class="forest-option-btn">Register</a>
            <a href="user_login.php" class="forest-option-btn">Login</a>
         </div>
         <?php
            }
         ?>      
      </div>
   </section>
</header>

<!-- Logout Confirmation Panel -->
<div id="logout-panel" class="logout-panel">
   <div class="logout-box">
      <div class="forest-logout-icon">🐰</div>
      <p>Are you sure you want to hop away?</p>
      <div class="logout-buttons">
         <button onclick="logoutUser();" class="forest-confirm-btn">Yes, Hop Out</button>
         <button onclick="closeLogoutPanel();" class="forest-cancel-btn">Stay Here</button>
      </div>
   </div>
</div>

<!-- JavaScript with fixed user menu toggle -->
<script>
function openLogoutPanel() {
    document.getElementById("logout-panel").style.display = "flex";
}

function closeLogoutPanel() {
    document.getElementById("logout-panel").style.display = "none";
}

function logoutUser() {
    window.location.href = "components/user_logout.php";
}

// Auto-close messages after 2 seconds with fade effect
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 2000); // 2000ms = 2 seconds
    });

    // Add user menu toggle functionality
    const userBtn = document.getElementById('user-btn');
    const profileMenu = document.getElementById('profile-menu');

    userBtn.addEventListener('click', function(event) {
        profileMenu.classList.toggle('active');
        event.stopPropagation();
    });

    // Close profile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!userBtn.contains(event.target) && !profileMenu.contains(event.target)) {
            profileMenu.classList.remove('active');
        }
    });

    // Mobile menu toggle
    const menuBtn = document.getElementById('menu-btn');
    const navbar = document.querySelector('.forest-navbar');
    
    menuBtn.addEventListener('click', function() {
        navbar.classList.toggle('active');
    });
});
</script>

<!-- Forest-themed CSS Styles with lighter colors -->
<style>
/* Keep original message styles */
.message {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 15px 25px;
    border-radius: 8px;
    background-color: #f8f9fa;
    color: #333;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease-out;
    max-width: 80%;
    min-width: 250px;
    text-align: center;
    font-size: 15px;
    transition: opacity 0.3s ease;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border-left: 5px solid #28a745;
}

.message.warning {
    background-color: #fff3cd;
    color: #856404;
    border-left: 5px solid #ffc107;
}

.message i {
    margin-right: 12px;
    font-size: 18px;
}

.message span {
    font-weight: 500;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

/* Forest-themed Header Styles with LIGHTER COLORS */
.forest-header {
    position: relative;
    background-color: #2e8b57; /* Lighter sea green instead of dark green */
    padding: 15px 2%;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    overflow: hidden;
}

.forest-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M30,90 L40,60 L30,65 L35,40 L25,45 L30,20 L20,30 L15,10 L10,30 L0,20 L5,45 L-5,40 L0,65 L-10,60 L0,90 Z" fill="%23267349" /><path d="M80,90 L90,60 L80,65 L85,40 L75,45 L80,20 L70,30 L65,10 L60,30 L50,20 L55,45 L45,40 L50,65 L40,60 L50,90 Z" fill="%23267349" /></svg>');
    background-repeat: repeat-x;
    background-position: bottom;
    opacity: 0.4;
    z-index: -1;
}

.forest-header .flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    padding: 5px 0;
}

.forest-header .logo {
    font-size: 26px;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    display: flex;
    align-items: center;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.forest-header .logo:hover {
    transform: scale(1.05);
}

.rabbit-icon {
    margin-right: 10px;
    font-size: 28px;
    animation: hop 2s infinite;
}

@keyframes hop {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.forest-header .logo .accent {
    color: #ffb84d; /* Lighter orange */
}

.forest-navbar {
    display: flex;
    margin: 0 auto;
}

.forest-navbar a {
    color: #ffffff;
    padding: 8px 15px;
    margin: 0 5px;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    border-radius: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.forest-navbar a:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.2);
}

.forest-navbar a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 2px;
    left: 50%;
    background-color: #9fe855; /* Lighter green */
    transition: all 0.3s ease;
}

.forest-navbar a:hover::after {
    width: 80%;
    left: 10%;
}

.forest-icons {
    display: flex;
    align-items: center;
}

.forest-icon-link {
    position: relative;
    height: 40px;
    width: 40px;
    font-size: 18px;
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    margin-left: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.forest-icon-link:hover {
    color: #fff;
    background-color: rgba(159, 232, 85, 0.3); /* Lighter green */
    border-color: rgba(159, 232, 85, 0.6);
    transform: translateY(-3px);
}

.forest-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ffb84d; /* Lighter orange */
    color: #fff;
    height: 20px;
    width: 20px;
    font-size: 12px;
    line-height: 20px;
    text-align: center;
    border-radius: 50%;
}

.forest-profile {
    position: absolute;
    top: 120%;
    right: 0;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    padding: 20px;
    width: 250px;
    text-align: center;
    border-top: 5px solid #9fe855; /* Lighter green */
    display: none; /* Hidden by default, shown with JS toggle */
    animation: growDown 0.3s ease-in-out forwards;
    z-index: 10010;
    overflow: hidden;
}

/* Profile image styles */
.profile-image-container {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #9fe855; /* Lighter green */
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    position: relative;
}

.profile-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.profile-image:hover {
    transform: scale(1.05);
}

.forest-header {
    position: relative;
    z-index: 100;
    overflow: visible; /* Ensure dropdowns aren't clipped */
}

/* Add active class for JavaScript toggle */
.forest-profile.active {
    display: block;
}

.profile-leaf {
    position: absolute;
    top: -40px;
    right: -30px;
    width: 100px;
    height: 100px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M10,10 Q50,10 90,50 Q90,90 50,90 Q10,90 10,50 Q10,10 50,10 Z" fill="%239fe855" opacity="0.3" /></svg>');
    z-index: -1;
}

@keyframes growDown {
    0% {
        transform: scaleY(0);
        transform-origin: top;
    }
    100% {
        transform: scaleY(1);
        transform-origin: top;
    }
}

.user-name {
    color: #2e8b57;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
}

.user-greeting {
    color: #2e8b57;
    font-size: 16px;
    margin-bottom: 15px;
}

.forest-btn,
.forest-option-btn,
.forest-delete-btn {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    border-radius: 25px;
    color: #fff;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.forest-btn {
    background-color: #3ca370; /* Lighter green */
}

.forest-btn:hover {
    background-color: #2e8b57;
    transform: translateY(-2px);
}

.flex-btn {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.forest-option-btn {
    background-color: #9fe855; /* Lighter green */
    flex: 1;
}

.forest-option-btn:hover {
    background-color: #8ad046;
    transform: translateY(-2px);
}

.forest-delete-btn {
    background-color: #ff9d82; /* Lighter red */
}

.forest-delete-btn:hover {
    background-color: #ff7e5e;
    transform: translateY(-2px);
}

/* Logout panel forest styling */
.logout-panel {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.logout-box {
    background: #f8fdf0; /* Very light green tint */
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    width: 350px;
    animation: fadeIn 0.3s ease-in-out;
    border: 3px solid #9fe855; /* Lighter green */
}

.forest-logout-icon {
    font-size: 40px;
    margin-bottom: 15px;
    animation: bounce 1s infinite alternate;
}

@keyframes bounce {
    from { transform: translateY(0); }
    to { transform: translateY(-10px); }
}

.logout-box p {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #2e8b57;
}

.logout-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.forest-confirm-btn, .forest-cancel-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    min-width: 120px;
}

.forest-confirm-btn {
    background: #ff9d82; /* Lighter red */
    color: white;
}

.forest-confirm-btn:hover {
    background: #ff7e5e;
    transform: translateY(-2px);
}

.forest-cancel-btn {
    background: #9fe855; /* Lighter green */
    color: white;
}

.forest-cancel-btn:hover {
    background: #8ad046;
    transform: translateY(-2px);
}

/* Active navbar style for mobile */
.forest-navbar.active {
    display: flex;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .forest-navbar {
        position: absolute;
        top: 99%;
        left: 0;
        right: 0;
        background-color: #2e8b57;
        border-top: 1px solid #3ca370;
        display: none;
        flex-direction: column;
        align-items: center;
        padding: 10px 0;
        z-index: 1000;
    }
    
    .forest-navbar a {
        margin: 5px 0;
        width: 90%;
        text-align: center;
    }
    
    #menu-btn {
        display: flex !important;
    }
    
    .forest-profile {
        width: 280px;
        right: -80px;
    }
    
    .forest-profile:before {
        right: 95px;
    }
}

@media (max-width: 480px) {
    .forest-header .logo {
        font-size: 22px;
    }
    
    .rabbit-icon {
        font-size: 24px;
    }
    
    .forest-icon-link {
        height: 35px;
        width: 35px;
        font-size: 16px;
    }
    
    .logout-box {
        width: 90%;
        padding: 20px 15px;
    }
    
    .profile-image-container {
        width: 70px;
        height: 70px;
    }
}
</style>