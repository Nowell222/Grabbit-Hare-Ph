<?php
/**
 * Premium Meats - Footer Component
 * Styled to match the rabbit-themed nature store aesthetic
 */
?>

<footer class="footer">
   <div class="footer-content">
      <section class="footer-grid">
         <div class="footer-box">
            <h3>Quick Links</h3>
            <a href="home.php"><i class="fas fa-angle-right"></i> Home</a>
            <a href="about.php"><i class="fas fa-angle-right"></i> About</a>
            <a href="shop.php"><i class="fas fa-angle-right"></i> Shop</a>
            <a href="contact.php"><i class="fas fa-angle-right"></i> Contact</a>
         </div>

         <div class="footer-box">
            <h3>Extra Links</h3>
            <a href="user_login.php"><i class="fas fa-angle-right"></i> Login</a>
            <a href="user_register.php"><i class="fas fa-angle-right"></i> Register</a>
            <a href="cart.php"><i class="fas fa-angle-right"></i> Cart</a>
            <a href="orders.php"><i class="fas fa-angle-right"></i> Orders</a>
         </div>

         <div class="footer-box">
            <h3>Contact Us</h3>
            <a href="tel:1234567890"><i class="fas fa-phone"></i> 0912345678</a>
            <a href="tel:11122233333"><i class="fas fa-phone"></i> 0998765321</a>
            <a href="mailto:support@premiummeat.com"><i class="fas fa-envelope"></i> support@grabbithare.com</a>
            <a href="https://maps.google.com"><i class="fas fa-map-marker-alt"></i> Muzon, San Juan, Batangas </a>
         </div>

         <div class="footer-box">
            <h3>Follow Us</h3>
            <a href="#"><i class="fab fa-facebook-f"></i> Facebook</a>
            <a href="#"><i class="fab fa-twitter"></i> Twitter</a>
            <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
            <a href="#"><i class="fab fa-pinterest"></i> Pinterest</a>
         </div>
      </section>
      
      <div class="footer-decoration"></div>
      
      <div class="credit">
         &copy; Copyright @ <?= date('Y'); ?> by <span>Grabbit Hare</span> | All rights reserved!
      </div>
   </div>
</footer>

<style>
.footer {
   background-color: var(--dark);
   color: var(--light);
   padding: 3rem 5% 2rem;
   position: relative;
   overflow: hidden;
   margin-top: 5rem;
   font-family: 'Poppins', sans-serif;
}

.footer::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   right: 0;
   height: 5px;
   background: linear-gradient(90deg, var(--primary-light), var(--primary), var(--primary-light));
}

.footer-content {
   max-width: 1200px;
   margin: 0 auto;
   position: relative;
   z-index: 1;
}

.footer-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
   gap: 2rem;
   margin-bottom: 2rem;
}

.footer-box h3 {
   color: var(--accent);
   font-size: 1.2rem;
   font-weight: 700;
   margin-bottom: 1.5rem;
   position: relative;
   padding-bottom: 0.8rem;
   text-transform: uppercase;
   letter-spacing: 1px;
}



.footer-box a {
   display: block;
   color: var(--light);
   padding: 0.5rem 0;
   transition: all 0.3s ease;
   opacity: 0.8;
   text-decoration: none;
}

.footer-box a:hover {
   color: var(--accent);
   transform: translateX(5px);
   opacity: 1;
}

.footer-box a i {
   font-size: 2rem;
   margin-right: 90px;
   color: var(--primary-light);
   transition: transform 0.3s ease;
}

.footer-box a:hover i {
   transform: translateX(3px);
}

.footer-decoration {
   position: absolute;
   width: 100%;
   height: 60px;
   bottom: 70px;
   left: 0;
   background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 70' preserveAspectRatio='none'%3E%3Cpath fill='%237D9D6A' opacity='0.1' d='M0,0 C720,100 1440,0 1440,0 L1440,70 L0,70 Z'%3E%3C/path%3E%3C/svg%3E");
   background-size: cover;
   background-repeat: no-repeat;
   z-index: -1;
   opacity: 0.3;
}

.credit {
   color: white; 
   text-align: center;
   border-top: 1px solid rgba(246, 247, 235, 0.1);
   padding-top: 2rem;
   font-size: 0.9rem;
   color: var(--light);
   opacity: 0.7;
}

.credit span {
   color: white; 
   color: var(--primary-light);
   font-weight: 600;
}

/* Rabbit paw print decorations */
.footer::after {
   content: '';
   position: absolute;
   bottom: 20px;
   right: 20px;
   width: 180px;
   height: 60px;
   background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 30' fill='%237D9D6A' opacity='0.1'%3E%3Ccircle cx='10' cy='10' r='5'/%3E%3Ccircle cx='20' cy='5' r='3'/%3E%3Ccircle cx='25' cy='10' r='3'/%3E%3Ccircle cx='30' cy='5' r='3'/%3E%3Ccircle cx='40' cy='10' r='5'/%3E%3Ccircle cx='50' cy='5' r='3'/%3E%3Ccircle cx='55' cy='10' r='3'/%3E%3Ccircle cx='60' cy='5' r='3'/%3E%3Ccircle cx='70' cy='10' r='5'/%3E%3Ccircle cx='80' cy='5' r='3'/%3E%3Ccircle cx='85' cy='10' r='3'/%3E%3Ccircle cx='90' cy='5' r='3'/%3E%3C/svg%3E");
   background-repeat: repeat-x;
   background-size: contain;
   pointer-events: none;
   z-index: 0;
}

@media (max-width: 768px) {
   .footer {
      padding: 2rem 5% 1.5rem;
   }
   
   .footer-grid {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
   }
   
   .footer-box h3 {
      font-size: 20rem;
      margin-bottom: 1rem;
   }
   
   .footer-box a {
      margin-right: 4rem ;
      padding: 0.4rem 0;
      font-size: 0.9rem;
   }
   
   .footer-decoration {
      height: 40px;
      bottom: 60px;
   }
}
</style>