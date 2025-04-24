<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
};

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   // Image upload handling
   if(!empty($_FILES['image']['name'])){
      $image = $_FILES['image']['name'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_size = $_FILES['image']['size'];
      $image_folder = 'uploaded_img/';
      $image_extension = pathinfo($image, PATHINFO_EXTENSION);
      $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
      
      // Create unique filename with timestamp
      $new_image = uniqid().'.'.$image_extension;
      
      if(!in_array(strtolower($image_extension), $allowed_extensions)){
         $message[] = 'Image extension not supported! Please upload JPG, JPEG, PNG or GIF';
      }elseif($image_size > 2000000){
         $message[] = 'Image size is too large! Maximum 2MB allowed';
      }else{
         // Get old image to delete
         $select_old_image = $conn->prepare("SELECT image FROM `users` WHERE id = ?");
         $select_old_image->execute([$user_id]);
         $fetch_old_image = $select_old_image->fetch(PDO::FETCH_ASSOC);
         
         // Delete old image if it exists and is not the default
         if($fetch_old_image['image'] != '' && $fetch_old_image['image'] != 'default.jpg' && file_exists($image_folder.$fetch_old_image['image'])){
            unlink($image_folder.$fetch_old_image['image']);
         }
         
         // Upload new image
         move_uploaded_file($image_tmp_name, $image_folder.$new_image);
         
         // Update image in database
         $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_image->execute([$new_image, $user_id]);
         $message[] = 'Profile image updated successfully!';
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass = $_POST['prev_pass'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   if($old_pass == $empty_pass){
      // Password field was empty, no update required
   }elseif($old_pass != $prev_pass){
      $message[] = 'old password not matched!';
   }elseif($new_pass != $cpass){
      $message[] = 'confirm password not matched!';
   }else{
      if($new_pass != $empty_pass){
         $update_user_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_user_pass->execute([$cpass, $user_id]);
         $message[] = 'password updated successfully!';
      }else{
         $message[] = 'please enter a new password!';
      }
   }
   
}

// Fetch current profile info
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>
   
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
      
      .form-container {
         max-width: 500px;
         margin: 5rem auto;
         padding: 3rem;
         position: relative;
         background: rgba(255, 255, 255, 0.9);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.2);
         border: 1px solid rgba(85, 122, 70, 0.1);
      }
      
      .form-container::before {
         content: '';
         position: absolute;
         top: -5px;
         right: -5px;
         bottom: -5px;
         left: -5px;
         border: 2px dashed var(--primary-light);
         border-radius: 20px;
         opacity: 0.6;
         z-index: -1;
      }
      
      /* Rabbit silhouette decorations */
      .form-container::after {
         content: '';
         position: absolute;
         width: 180px;
         height: 80px;
         background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 45' fill='%23557A46' opacity='0.1'%3E%3Cpath d='M95,20c-2.6,0-5.2,2.1-7.1,4.3c-0.5-6.4-5.7-11.1-11.9-10.5C74,5.4,67.5,0,59.5,0c-8.9,0-16.3,7.4-16.3,16.3 c0,1.6,0.3,3.1,0.7,4.5c-3.3-1.4-6.9-2.2-10.7-2.2c-4.9,0-9.5,1.3-13.4,3.5C17.7,10.1,9.4,1.8,0,1.1v12c4.2,0.5,7.7,3.7,8.8,7.9 c-1.6,2.8-2.6,6.1-2.6,9.5c0,10.7,8.7,19.4,19.4,19.4c4.4,0,8.5-1.5,11.8-4c3.3,2.5,7.3,4,11.8,4c10.7,0,19.4-8.7,19.4-19.4 c0-3.9-1.1-7.5-3.1-10.5c1.3,0.5,2.8,0.8,4.3,0.8c7,0,10.3-5.6,10.3-5.6s3.9,3.8,8.1,3.8c4.8,0,8.8-5.2,8.8-5.2S99.8,20,95,20z'/%3E%3C/svg%3E");
         background-repeat: no-repeat;
         background-size: contain;
         pointer-events: none;
         opacity: 0.3;
         bottom: 10px;
         right: 10px;
         transform: rotate(5deg);
      }
      
      form {
         display: flex;
         flex-direction: column;
         gap: 1.5rem;
         position: relative;
         z-index: 1;
      }
      
      h3 {
         font-size: 2.2rem;
         color: var(--dark);
         font-weight: 800;
         text-align: center;
         margin-bottom: 1.5rem;
         position: relative;
         padding-bottom: 15px;
      }
      
      h3::before, 
      h3::after {
         content: '🌿';
         position: absolute;
         top: 0;
         font-size: 1.5rem;
      }
      
      h3::before {
         left: 30px;
      }
      
      h3::after {
         right: 30px;
      }
      
      h3::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 100px;
         height: 3px;
         background: linear-gradient(90deg, transparent, var(--primary), transparent);
      }
      
      .box {
         width: 100%;
         padding: 16px;
         font-size: 1rem;
         border: 2px solid var(--primary-light);
         border-radius: 10px;
         background: rgba(255, 255, 255, 0.7);
         transition: all 0.3s ease;
         color: var(--text);
         font-weight: 500;
      }
      
      .box:focus {
         outline: none;
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(85, 122, 70, 0.3);
         background: rgba(255, 255, 255, 0.9);
      }
      
      .btn {
         width: 100%;
         padding: 15px;
         border-radius: 30px;
         cursor: pointer;
         font-size: 1.1rem;
         font-weight: 700;
         text-transform: uppercase;
         letter-spacing: 1px;
         transition: all 0.3s ease;
         position: relative;
         overflow: hidden;
         display: flex;
         align-items: center;
         justify-content: center;
         background: var(--primary);
         color: white;
         border: none;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         margin-top: 10px;
      }
      
      .btn:hover {
         background: var(--dark);
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .btn:hover::before {
         left: 100%;
      }
      
      p {
         text-align: center;
         color: var(--text);
         margin: 15px 0 5px;
         font-size: 0.9rem;
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
      
      .message {
         position: sticky;
         top: 0;
         margin: 0 auto;
         max-width: 1200px;
         background-color: var(--light);
         padding: 1rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1.5rem;
         z-index: 1000;
         border-left: 5px solid var(--secondary);
         border-radius: 5px;
         margin-bottom: 1.5rem;
         animation: fadeIn 0.3s ease forwards;
      }
      
      @keyframes fadeIn {
         0% { opacity: 0; transform: translateY(-10px); }
         100% { opacity: 1; transform: translateY(0); }
      }
      
      /* User info styling */
      .user-info {
         margin-bottom: 20px;
         text-align: center;
      }
      
      .user-avatar {
         width: 120px;
         height: 120px;
         border-radius: 50%;
         overflow: hidden;
         margin: 0 auto 15px;
         border: 3px solid var(--primary-light);
         background-color: #f5f5f5;
         display: flex;
         align-items: center;
         justify-content: center;
         position: relative;
      }
      
      .user-avatar img {
         width: 100%;
         height: 100%;
         object-fit: cover;
      }
      
      .user-avatar i {
         font-size: 60px;
         color: var(--primary-light);
      }
      
      .user-name {
         font-size: 1.2rem;
         font-weight: 600;
         color: var(--dark);
      }
      
      /* Image upload styling */
      .image-upload-container {
         margin-bottom: 20px;
         position: relative;
      }
      
      .image-upload-label {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         gap: 10px;
         cursor: pointer;
         padding: 10px;
         border-radius: 10px;
         background-color: rgba(255, 255, 255, 0.7);
         border: 2px dashed var(--primary-light);
         transition: all 0.3s ease;
         text-align: center;
      }
      
      .image-upload-label:hover {
         background-color: rgba(255, 255, 255, 0.9);
         border-color: var(--primary);
      }
      
      .image-upload-label i {
         font-size: 24px;
         color: var(--primary);
      }
      
      .image-upload-text {
         font-size: 0.9rem;
         color: var(--text);
      }
      
      .image-upload-input {
         display: none;
      }
      
      /* Camera icon overlay */
      .camera-overlay {
         position: absolute;
         bottom: 0;
         right: 0;
         background: var(--primary);
         width: 32px;
         height: 32px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         color: white;
         font-size: 16px;
         border: 2px solid white;
         cursor: pointer;
         transition: all 0.3s ease;
      }
      
      .camera-overlay:hover {
         background: var(--dark);
         transform: scale(1.1);
      }
      
      /* File name display */
      .file-name {
         font-size: 0.8rem;
         color: var(--secondary);
         margin-top: 5px;
         max-width: 100%;
         overflow: hidden;
         text-overflow: ellipsis;
         white-space: nowrap;
         text-align: center;
      }
      
      /* Password section styling */
      .password-section {
         border-top: 1px dashed var(--primary-light);
         padding-top: 20px;
         margin-top: 10px;
      }
      
      .password-title {
         font-size: 1.2rem;
         color: var(--dark);
         margin-bottom: 15px;
         text-align: center;
         font-weight: 600;
      }
      
      /* Section titles */
      .section-title {
         font-size: 1.2rem;
         color: var(--dark);
         margin-bottom: 15px;
         text-align: center;
         font-weight: 600;
      }
      
      @media (max-width: 768px) {
         .form-container {
            padding: 2rem;
            margin: 3rem auto;
            width: 90%;
         }
         
         h3 {
            font-size: 1.8rem;
         }
         
         h3::before, 
         h3::after {
            display: none;
         }
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <!-- Rabbit decorations -->
   <div class="rabbit-decoration"></div>
   <div class="rabbit-decoration"></div>
   
   <?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
   ?>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Update Profile</h3>
      
      <!-- User info display -->
      <div class="user-info">
         <div class="user-avatar">
            <?php if(!empty($fetch_profile['image']) && file_exists('uploaded_img/'.$fetch_profile['image'])): ?>
               <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="User Avatar">
            <?php else: ?>
               <i class="fas fa-user"></i>
            <?php endif; ?>
            
            <!-- Camera overlay that triggers file input -->
            <label for="image-upload" class="camera-overlay">
               <i class="fas fa-camera"></i>
            </label>
         </div>
         <div class="user-name">
            <?= $fetch_profile['name'] ?? 'User'; ?>
         </div>
      </div>
      
      <!-- Image upload section -->
      <div class="image-upload-container">
         <div class="section-title">Profile Image</div>
         <label for="image-upload" class="image-upload-label">
            <i class="fas fa-cloud-upload-alt"></i>
            <div class="image-upload-text">Click to upload a new profile image</div>
            <div class="image-upload-text">(JPG, JPEG, PNG, GIF - Max 2MB)</div>
         </label>
         <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/gif" id="image-upload" class="image-upload-input">
         <div id="file-name" class="file-name"></div>
      </div>
      
      <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
      
      <!-- Basic info section -->
      <div class="section-title">Basic Information</div>
      <input type="text" name="name" required placeholder="Enter your username" maxlength="20" class="box" value="<?= $fetch_profile['name']; ?>">
      
      <input type="email" name="email" required placeholder="Enter your email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= $fetch_profile['email']; ?>">
      
      <!-- Password update section -->
      <div class="password-section">
         <div class="password-title">Update Password</div>
         <input type="password" name="old_pass" placeholder="Enter old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="new_pass" placeholder="Enter new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="cpass" placeholder="Confirm new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      </div>
      
      <button type="submit" name="submit" class="btn">
         <i class="fas fa-user-edit" style="margin-right: 8px;"></i> Update Profile
      </button>
   </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
   // Display file name when selected
   document.getElementById('image-upload').addEventListener('change', function() {
      const fileName = this.files[0]?.name || '';
      document.getElementById('file-name').textContent = fileName;
      
      // Show visual feedback that a file is selected
      if(fileName) {
         document.querySelector('.image-upload-label').style.borderColor = 'var(--primary)';
         document.querySelector('.image-upload-label').style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
         document.querySelector('.image-upload-text').textContent = 'Selected: ' + fileName;
      }
   });
</script>

</body>
</html>