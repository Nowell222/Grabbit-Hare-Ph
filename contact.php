<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

// Handle message submission
if(isset($_POST['send'])){
   // Validate and sanitize inputs
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
   $msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

   // Check if required fields are empty
   if(empty($name) || empty($email) || empty($subject) || empty($msg)){
      $message[] = 'Please fill in all required fields!';
   } else {
      // Check if the same message was sent before
      $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND subject = ? AND message = ?");
      $select_message->execute([$name, $email, $subject, $msg]);

      if($select_message->rowCount() > 0){
         $message[] = 'Message already sent!';
      }else{
         try {
            // Insert the new message
            $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, subject, message, sent_date) VALUES(?,?,?,?,?,?,NOW())");
            $insert_message->execute([$user_id, $name, $email, $number, $subject, $msg]);

            if($insert_message->rowCount() > 0){
               $message[] = 'Message sent successfully!';
               
               // Clear form fields after successful submission
               $_POST = array();
            } else {
               $message[] = 'Failed to send message. Please try again.';
            }
         } catch (PDOException $e) {
            $message[] = 'Database error: ' . $e->getMessage();
         }
      }
   }
}

// Handle follow-up message submission
if(isset($_POST['send_followup'])){
   $original_msg_id = filter_var($_POST['original_msg_id'], FILTER_SANITIZE_NUMBER_INT);
   $followup_msg = filter_var($_POST['followup_msg'], FILTER_SANITIZE_STRING);
   
   if(empty($followup_msg)){
      $message[] = 'Please enter your follow-up message!';
   } else {
      // Get original message details
      $select_original = $conn->prepare("SELECT * FROM `messages` WHERE id = ? AND user_id = ?");
      $select_original->execute([$original_msg_id, $user_id]);
      
      if($select_original->rowCount() > 0){
         $original = $select_original->fetch(PDO::FETCH_ASSOC);
         
         try {
            // Insert the follow-up message with reference to original
            $insert_followup = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, subject, message, parent_msg_id, sent_date) VALUES(?,?,?,?,?,?,?,NOW())");
            $insert_followup->execute([
               $user_id, 
               $original['name'], 
               $original['email'], 
               $original['number'], 
               'Re: ' . $original['subject'], 
               $followup_msg,
               $original_msg_id,
               
            ]);

            if($insert_followup->rowCount() > 0){
               $message[] = 'Follow-up message sent successfully!';
            } else {
               $message[] = 'Failed to send follow-up message.';
            }
         } catch (PDOException $e) {
            $message[] = 'Database error: ' . $e->getMessage();
         }
      } else {
         $message[] = 'Original message not found.';
      }
   }
}

// Fetch user data if logged in
$user_data = [];
if($user_id != ''){
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
   $select_user->execute([$user_id]);
   if($select_user->rowCount() > 0){
      $user_data = $select_user->fetch(PDO::FETCH_ASSOC);
   }
}

// Fetch previous messages if logged in
$previous_messages = [];
if($user_id != ''){
   $select_prev_messages = $conn->prepare("SELECT m1.*, 
      (SELECT COUNT(*) FROM `messages` m2 WHERE m2.parent_msg_id = m1.id) as reply_count 
      FROM `messages` m1 
      WHERE m1.user_id = ? AND m1.parent_msg_id IS NULL 
      ORDER BY m1.sent_date DESC LIMIT 10");
   $select_prev_messages->execute([$user_id]);
   if($select_prev_messages->rowCount() > 0){
      while($row = $select_prev_messages->fetch(PDO::FETCH_ASSOC)){
         $previous_messages[] = $row;
      }
   }
}

// Fetch replies for a specific message
function getMessageReplies($conn, $message_id) {
   $replies = [];
   $select_replies = $conn->prepare("SELECT * FROM `messages` WHERE parent_msg_id = ? ORDER BY sent_date ASC");
   $select_replies->execute([$message_id]);
   
   if($select_replies->rowCount() > 0){
      while($row = $select_replies->fetch(PDO::FETCH_ASSOC)){
         $replies[] = $row;
      }
   }
   
   return $replies;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Us</title>
   
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
      
      html, body {
         height: 100%;
         margin: 0;
         padding: 0;
      }
      
      body {
         font-family: 'Poppins', sans-serif;
         background-image: url('https://images.unsplash.com/photo-1418065460487-3e41a6c84dc5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
         background-size: cover;
         background-attachment: fixed;
         background-position: center;
         position: relative;
         min-height: 100vh;
         display: flex;
         flex-direction: column;
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
      
      .contact-container {
         max-width: 1400px;
         width: 95%;
         margin: 2rem auto;
         padding: 2rem;
         position: relative;
         flex: 1;
      }
      
      /* Rabbit silhouette decorations */
      .contact-container::before,
      .contact-container::after {
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
      
      .contact-container::before {
         top: 30px;
         right: 50px;
         transform: rotate(10deg);
      }
      
      .contact-container::after {
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
      
      .contact-content {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 3rem;
         position: relative;
      }
      
      .contact-form-container {
         background: rgba(255, 255, 255, 0.95);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
         position: relative;
         border: 1px solid rgba(85, 122, 70, 0.1);
         height: 100%;
         display: flex;
         flex-direction: column;
      }
      
      .contact-form-container::before {
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
      
      .contact-form-container:hover::before {
         opacity: 0.5;
         top: 10px;
         right: 10px;
         bottom: 10px;
         left: 10px;
      }
      
      .contact-form-header {
         padding: 1.5rem 2rem;
         background: var(--primary-light);
         background-image: linear-gradient(to right, rgba(125, 157, 106, 0.8), rgba(85, 122, 70, 0.8));
         color: white;
         display: flex;
         align-items: center;
      }
      
      .contact-form-header h3 {
         font-size: 1.5rem;
         font-weight: 600;
         margin-left: 10px;
      }
      
      .contact-form-header i {
         font-size: 1.8rem;
      }
      
      .contact-form {
         padding: 2rem;
         background: linear-gradient(to bottom, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
         flex: 1;
         display: flex;
         flex-direction: column;
      }
      
      .input-group {
         margin-bottom: 1.5rem;
         position: relative;
         flex: 1 0 auto;
      }
      
      .input-group label {
         display: block;
         margin-bottom: 0.5rem;
         color: var(--dark);
         font-weight: 500;
         font-size: 0.95rem;
      }
      
      .input-group input,
      .input-group textarea,
      .input-group select {
         width: 100%;
         padding: 1rem;
         border: 1px solid rgba(85, 122, 70, 0.2);
         border-radius: 8px;
         background: rgba(255, 255, 255, 0.8);
         transition: all 0.3s;
         font-family: 'Poppins', sans-serif;
         color: var(--text);
      }
      
      .input-group input:focus,
      .input-group textarea:focus,
      .input-group select:focus {
         outline: none;
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(85, 122, 70, 0.2);
      }
      
      .input-group textarea {
         resize: vertical;
         min-height: 150px;
         flex: 1;
      }
      
      .input-icon {
         position: absolute;
         right: 15px;
         top: 42px;
         color: var(--primary-light);
         font-size: 1.2rem;
      }
      
      .contact-form-footer {
         display: flex;
         justify-content: space-between;
         padding: 1.5rem 2rem;
         border-top: 1px solid rgba(85, 122, 70, 0.2);
         background: rgba(255,255,255,0.5);
         margin-top: auto;
      }
      
      .btn-send {
         background: var(--primary);
         color: white;
         border: none;
         padding: 0.8rem 2rem;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 700;
         font-size: 1.05rem;
         letter-spacing: 0.5px;
         display: flex;
         align-items: center;
         justify-content: center;
         box-shadow: 0 5px 15px rgba(85, 122, 70, 0.3);
         position: relative;
         overflow: hidden;
      }
      
      .btn-send::before {
         content: '';
         position: absolute;
         top: 0;
         left: -100%;
         width: 100%;
         height: 100%;
         background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
         transition: left 0.7s ease;
      }
      
      .btn-send:hover::before {
         left: 100%;
      }
      
      .btn-send i {
         margin-right: 8px;
      }
      
      .btn-send:hover {
         background: var(--dark);
         letter-spacing: 1px;
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(44, 62, 45, 0.4);
      }
      
      .btn-reset {
         background: transparent;
         color: var(--text);
         border: 1px solid var(--primary-light);
         padding: 0.8rem 1.5rem;
         border-radius: 30px;
         cursor: pointer;
         transition: all 0.3s;
         font-weight: 500;
         font-size: 0.95rem;
      }
      
      .btn-reset:hover {
         background: var(--light);
         color: var(--dark);
      }
      
      .btn-reset i {
         margin-right: 5px;
      }
      
      .contact-info {
         display: flex;
         flex-direction: column;
         gap: 2rem;
         height: 100%;
      }
      
      .info-card {
         background: rgba(255, 255, 255, 0.95);
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         transition: all 0.3s;
         border: 1px solid rgba(85, 122, 70, 0.1);
         flex: 1;
      }
      
      .info-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 15px 40px rgba(85, 122, 70, 0.25);
      }
      
      .info-card-header {
         padding: 1.2rem 1.5rem;
         background: var(--primary-light);
         background-image: linear-gradient(to right, rgba(125, 157, 106, 0.8), rgba(85, 122, 70, 0.8));
         color: white;
         display: flex;
         align-items: center;
      }
      
      .info-card-header h3 {
         font-size: 1.3rem;
         font-weight: 600;
         margin-left: 10px;
      }
      
      .info-card-header i {
         font-size: 1.5rem;
      }
      
      .info-card-body {
         padding: 1.5rem;
         background: linear-gradient(to bottom, rgba(246, 247, 235, 0.6), rgba(246, 247, 235, 0.9));
         height: calc(100% - 60px);
      }
      
      .info-item {
         display: flex;
         margin-bottom: 1.2rem;
         position: relative;
      }
      
      .info-item:last-child {
         margin-bottom: 0;
      }
      
      .info-icon {
         width: 40px;
         height: 40px;
         background: var(--primary);
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         color: white;
         font-size: 1rem;
         margin-right: 15px;
         flex-shrink: 0;
      }
      
      .info-content {
         flex: 1;
      }
      
      .info-label {
         font-weight: 600;
         color: var(--dark);
         margin-bottom: 3px;
         font-size: 0.95rem;
      }
      
      .info-value {
         color: var(--text);
         line-height: 1.6;
         font-size: 0.9rem;
      }
      
      /* Chat Messenger Style */
      .chat-messenger {
         position: fixed;
         bottom: 30px;
         right: 30px;
         z-index: 1000;
      }
      
      .chat-icon {
         width: 60px;
         height: 60px;
         background: var(--primary);
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         color: white;
         font-size: 1.8rem;
         cursor: pointer;
         box-shadow: 0 5px 20px rgba(44, 62, 45, 0.4);
         transition: all 0.3s;
         position: relative;
      }
      
      .chat-icon:hover {
         transform: scale(1.1);
         background: var(--dark);
      }
      
      .notification-badge {
         position: absolute;
         top: -5px;
         right: -5px;
         width: 24px;
         height: 24px;
         background: #e74c3c;
         border-radius: 50%;
         color: white;
         font-size: 0.75rem;
         font-weight: 700;
         display: flex;
         align-items: center;
         justify-content: center;
         border: 2px solid white;
      }
      
      .chat-window {
         position: fixed;
         bottom: 100px;
         right: 30px;
         width: 360px;
         height: 480px;
         background: white;
         border-radius: 16px;
         box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
         display: flex;
         flex-direction: column;
         overflow: hidden;
         transform: scale(0);
         transform-origin: bottom right;
         transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
         z-index: 999;
      }
      
      .chat-window.active {
         transform: scale(1);
      }
      
      .chat-header {
         padding: 1rem 1.5rem;
         background: var(--primary);
         color: white;
         display: flex;
         align-items: center;
         justify-content: space-between;
      }
      
      .chat-header h3 {
         font-size: 1.2rem;
         font-weight: 600;
         margin: 0;
      }
      
      .chat-close {
         background: none;
         border: none;
         color: white;
         font-size: 1.5rem;
         cursor: pointer;
         padding: 0;
         display: flex;
         align-items: center;
         justify-content: center;
         width: 30px;
         height: 30px;
         border-radius: 50%;
         transition: all 0.3s;
      }
      
      .chat-close:hover {
         background: rgba(255, 255, 255, 0.2);
      }
      
      .chat-body {
         flex: 1;
         overflow-y: auto;
         padding: 1rem;
         display: flex;
         flex-direction: column;
         gap: 1rem;
         background: #f7f9fb;
      }
      
      .welcome-message {
         text-align: center;
         padding: 1rem;
         color: #666;
         font-size: 0.9rem;
      }
      
      .conversation-list {
         display: flex;
         flex-direction: column;
         gap: 0.75rem;
      }
      
      .conversation-item {
         background: white;
         border-radius: 12px;
         padding: 0.75rem 1rem;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
         cursor: pointer;
         border-left: 3px solid var(--primary);
         transition: all 0.2s;
      }
      
      .conversation-item:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }
      
      .conversation-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 0.5rem;
      }
      
      .conversation-subject {
         font-weight: 600;
         color: var(--dark);
         font-size: 0.95rem;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
         max-width: 200px;
      }
      
      .conversation-date {
         font-size: 0.75rem;
         color: #999;
      }
      
      .conversation-preview {
         font-size: 0.85rem;
         color: #666;
         line-height: 1.4;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      
      .conversation-footer {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-top: 0.5rem;
         font-size: 0.75rem;
      }
      
      .reply-count {
         color: var(--primary);
         font-weight: 600;
      }
      
      .chat-conversation {
         display: none;
         flex-direction: column;
         height: 100%;
      }
      
      .chat-conversation.active {
         display: flex;
      }
      
      .chat-conversation-header {
         padding: 0.75rem 1rem;
         background: #f1f3f5;
         border-bottom: 1px solid #e0e0e0;
         display: flex;
         align-items: center;
      }
      
      .back-to-list {
         background: none;
         border: none;
         color: var(--primary);
         cursor: pointer;
         margin-right: 0.75rem;
         display: flex;
         align-items: center;
         padding: 0;
      }
      
      .conversation-title {
         font-weight: 600;
         color: var(--dark);
         font-size: 0.95rem;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      
      .conversation-messages {
         flex: 1;
         overflow-y: auto;
         padding: 1rem;
         display: flex;
         flex-direction: column;
         gap: 1rem;
         background: #f7f9fb;
      }
      
      .message-bubble {
         max-width: 80%;
         padding: 0.75rem 1rem;
         border-radius: 18px;
         position: relative;
         line-height: 1.5;
         font-size: 0.9rem;
      }
      
      .message-time {
         font-size: 0.7rem;
         margin-top: 0.25rem;
         opacity: 0.7;
      }
      
      .message-user {
         align-self: flex-end;
         background: var(--primary);
         color: white;
         border-bottom-right-radius: 4px;
      }
      
      .message-admin {
         align-self: flex-start;
         background: #e0e0e0;
         color: #333;
         border-bottom-left-radius: 4px;
      }
      
      .conversation-reply {
         padding: 0.75rem;
         border-top: 1px solid #e0e0e0;
         display: flex;
         align-items: center;
         gap: 0.5rem;
      }
      
      .reply-input {
         flex: 1;
         border: 1px solid #ddd;
         border-radius: 20px;
         padding: 0.5rem 1rem;
         font-family: inherit;
         font-size: 0.9rem;
         outline: none;
         transition: all 0.3s;
      }
      
      .reply-input:focus {
         border-color: var(--primary);
         box-shadow: 0 0 0 3px rgba(85, 122, 70, 0.2);
      }
      
      .send-reply {
         background: var(--primary);
         color: white;
         border: none;
         width: 40px;
         height: 40px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.3s;
      }
      
      .send-reply:hover {
         background: var(--dark);
         transform: scale(1.05);
      }
      
      .map-container {
         grid-column: 1 / -1;
         height: 400px;
         border-radius: 16px;
         overflow: hidden;
         box-shadow: 0 10px 30px rgba(85, 122, 70, 0.15);
         margin-top: 3rem;
         position: relative;
      }
      
      .map-container iframe {
         width: 100%;
         height: 100%;
         border: none;
      }
      
      /* Complete the map-overlay class */
.map-overlay {
   position: absolute;
   top: 0;
   left: 0;
   right: 0;
   bottom: 0;
   background: rgba(44, 62, 45, 0.7);
   display: flex;
   align-items: center;
   justify-content: center;
   flex-direction: column;
   color: white;
   padding: 2rem;
   text-align: center;
}

.map-overlay h3 {
   font-size: 1.8rem;
   margin-bottom: 1rem;
   font-weight: 700;
}

.map-overlay p {
   font-size: 1.1rem;
   max-width: 600px;
   margin: 0 auto 1.5rem;
   line-height: 1.6;
}

.map-btn {
   background: var(--primary);
   color: white;
   border: none;
   padding: 0.8rem 2rem;
   border-radius: 30px;
   cursor: pointer;
   transition: all 0.3s;
   font-weight: 600;
   font-size: 1rem;
   display: flex;
   align-items: center;
}

.map-btn i {
   margin-right: 8px;
}

.map-btn:hover {
   background: white;
   color: var(--dark);
   transform: translateY(-2px);
}

/* Success Message Animation */
.message-container {
   position: fixed;
   top: 20px;
   right: 20px;
   z-index: 1000;
   max-width: 350px;
}

.message-alert {
   padding: 1rem 1.5rem;
   border-radius: 8px;
   margin-bottom: 10px;
   display: flex;
   align-items: center;
   box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
   animation: slideIn 0.5s forwards, fadeOut 0.5s 4.5s forwards;
   position: relative;
   overflow: hidden;
}

@keyframes slideIn {
   from {
      transform: translateX(100%);
      opacity: 0;
   }
   to {
      transform: translateX(0);
      opacity: 1;
   }
}

@keyframes fadeOut {
   from {
      transform: translateX(0);
      opacity: 1;
   }
   to {
      transform: translateX(100%);
      opacity: 0;
   }
}

.message-alert::after {
   content: '';
   position: absolute;
   bottom: 0;
   left: 0;
   height: 3px;
   width: 100%;
   background: rgba(255, 255, 255, 0.5);
   animation: timeBar 5s linear forwards;
}

@keyframes timeBar {
   from {
      width: 100%;
   }
   to {
      width: 0;
   }
}

.message-alert.success {
   background: var(--primary);
   color: white;
}

.message-alert.error {
   background: #e74c3c;
   color: white;
}

.message-alert i {
   margin-right: 10px;
   font-size: 1.2rem;
}

.message-close {
   margin-left: 10px;
   background: none;
   border: none;
   color: white;
   cursor: pointer;
   font-size: 1.2rem;
   opacity: 0.7;
   transition: all 0.3s;
}

.message-close:hover {
   opacity: 1;
}

@media (max-width: 1100px) {
   .contact-content {
      grid-template-columns: 1fr;
   }
   
   .contact-info {
      order: 1;
   }
   
   .contact-form-container {
      order: 2;
   }
}

@media (max-width: 768px) {
   .contact-container {
      padding: 1rem;
   }
   
   .section-title {
      font-size: 2.2rem;
   }
   
   .section-title::before,
   .section-title::after {
      display: none;
   }
   
   .info-card, .chat-window {
      width: 100%;
   }
   
   .chat-window {
      right: 0;
      bottom: 80px;
      width: 100%;
      height: 70vh;
      border-radius: 20px 20px 0 0;
   }
}

@media (max-width: 480px) {
   .section-title {
      font-size: 1.8rem;
   }
   
   .contact-form-footer {
      flex-direction: column;
      gap: 1rem;
   }
   
   .btn-send, .btn-reset {
      width: 100%;
   }
}
</style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="contact-container">
   <div class="section-header">
      <h1 class="section-title">Contact Us</h1>
      <p class="section-subtitle">Have questions about our premium rabbit meat products or farm operations? We're here to help! Fill out the form below and our team will respond as soon as possible.</p>
   </div>
   
   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<div class="message-container">
                  <div class="message-alert '.($msg == 'Message sent successfully!' ? 'success' : 'error').'">
                     <i class="fas '.($msg == 'Message sent successfully!' ? 'fa-check-circle' : 'fa-exclamation-circle').'"></i>
                     '.$msg.'
                     <button class="message-close"><i class="fas fa-times"></i></button>
                  </div>
               </div>';
      }
   }
   ?>
   
   <div class="contact-content">
      <div class="contact-form-container">
         <div class="contact-form-header">
            <i class="fas fa-envelope"></i>
            <h3>Send Us a Message</h3>
         </div>
         
         <form action="" method="post" class="contact-form">
            <div class="input-group">
               <label for="name">Your Name *</label>
               <input type="text" name="name" id="name" required placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ($user_id ? $user_data['name'] : ''); ?>">
               <div class="input-icon">
                  <i class="fas fa-user"></i>
               </div>
            </div>
            
            <div class="input-group">
               <label for="email">Email Address *</label>
               <input type="email" name="email" id="email" required placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ($user_id ? $user_data['email'] : ''); ?>">
               <div class="input-icon">
                  <i class="fas fa-envelope"></i>
               </div>
            </div>
            
            <div class="input-group">
               <label for="number">Phone Number (Optional)</label>
               <input type="tel" name="number" id="number" placeholder="Enter your phone number" 
value="<?php 
    echo isset($_POST['number']) 
        ? $_POST['number'] 
        : (isset($user_data['number']) ? $user_data['number'] : ''); 
?>">
               <div class="input-icon">
                  <i class="fas fa-phone"></i>
               </div>
            </div>
            
            <div class="input-group">
               <label for="subject">Subject *</label>
               <input type="text" name="subject" id="subject" required placeholder="What is your message about?" value="<?php echo isset($_POST['subject']) ? $_POST['subject'] : ''; ?>">
               <div class="input-icon">
                  <i class="fas fa-tag"></i>
               </div>
            </div>
            
            <div class="input-group">
               <label for="msg">Your Message *</label>
               <textarea name="msg" id="msg" required placeholder="Type your message here..."><?php echo isset($_POST['msg']) ? $_POST['msg'] : ''; ?></textarea>
            </div>
            
            <div class="contact-form-footer">
               <button type="submit" name="send" class="btn-send">
                  <i class="fas fa-paper-plane"></i> Send Message
               </button>
               <button type="reset" class="btn-reset">
                  <i class="fas fa-undo"></i> Reset Form
               </button>
            </div>
         </form>
      </div>
      
      <div class="contact-info">
         <div class="info-card">
            <div class="info-card-header">
               <i class="fas fa-info-circle"></i>
               <h3>Contact Information</h3>
            </div>
            
            <div class="info-card-body">
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Our Location</div>
                     <div class="info-value">Muzon City, San Juan, Batangas 4226</div>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Phone Number</div>
                     <div class="info-value">09123456789</div>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-envelope"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Email Address</div>
                     <div class="info-value">contact@grabbithare.com</div>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-clock"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Business Hours</div>
                     <div class="info-value">
                        Monday - Friday: 8:00 AM - 5:00 PM<br>
                        Saturday: 9:00 AM - 3:00 PM<br>
                        Sunday: Closed
                     </div>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="info-card">
            <div class="info-card-header">
               <i class="fas fa-question-circle"></i>
               <h3>Frequently Asked Questions</h3>
            </div>
            
            <div class="info-card-body">
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-truck"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Do you offer delivery?</div>
                     <div class="info-value">Yes, we deliver to local areas within San Juan. For shipping options, please contact us directly.</div>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-shopping-cart"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">How can I place a bulk order?</div>
                     <div class="info-value">For bulk orders, please contact us by phone or email with details of your requirements.</div>
                  </div>
               </div>
               
               <div class="info-item">
                  <div class="info-icon">
                     <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="info-content">
                     <div class="info-label">Can I schedule a store visit?</div>
                     <div class="info-value">Store Visits are available. Please contact us to before visiting.</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      
      <div class="map-container">
      <iframe
  src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d12345.67890!2d121.33571!3d13.82901!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1618555555555!5m2!1sen!2sph"
  width="600"
  height="450"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade">
</iframe>
         
         <div class="map-overlay">
            <h3>Visit Our Farm</h3>
            <p>We welcome visitors to explore our sustainable farming practices and learn about our premium rabbit meat production. Schedule a tour to see our operation firsthand.</p>
            <button class="map-btn" id="showMapBtn">
               <i class="fas fa-map-marked-alt"></i> View Interactive Map
            </button>
         </div>
      </div>
   </div>
</div>

<!-- Chat Messenger for Previous Messages -->
<?php if($user_id != '' && !empty($previous_messages)): ?>
<div class="chat-messenger">
   <div class="chat-icon" id="chatIcon">
      <i class="fas fa-comments"></i>
      <?php if(count($previous_messages) > 0): ?>
      <div class="notification-badge"><?php echo count($previous_messages); ?></div>
      <?php endif; ?>
   </div>
   
   <div class="chat-window" id="chatWindow">
      <div class="chat-header">
         <h3>Your Messages</h3>
         <button class="chat-close" id="chatClose"><i class="fas fa-times"></i></button>
      </div>
      
      <div class="chat-body">
         <div id="messagesList" class="conversation-list">
            <?php if(!empty($previous_messages)): ?>
               <?php foreach($previous_messages as $msg): ?>
                  <div class="conversation-item" data-id="<?php echo $msg['id']; ?>">
                     <div class="conversation-header">
                        <div class="conversation-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                        <div class="conversation-date"><?php echo date('M d, Y', strtotime($msg['sent_date'])); ?></div>
                     </div>
                     <div class="conversation-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : ''); ?></div>
                     <div class="conversation-footer">
                        <div class="reply-count">
                           <?php if($msg['reply_count'] > 0): ?>
                              <i class="fas fa-reply"></i> <?php echo $msg['reply_count']; ?> <?php echo $msg['reply_count'] == 1 ? 'reply' : 'replies'; ?>
                           <?php else: ?>
                              <i class="fas fa-clock"></i> Awaiting reply
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               <?php endforeach; ?>
            <?php else: ?>
               <div class="welcome-message">
                  <p>You don't have any previous messages. Send us a message using the contact form!</p>
               </div>
            <?php endif; ?>
         </div>
         
         <?php foreach($previous_messages as $msg): ?>
            <div class="chat-conversation" id="conversation-<?php echo $msg['id']; ?>">
               <div class="chat-conversation-header">
                  <button class="back-to-list"><i class="fas fa-arrow-left"></i></button>
                  <div class="conversation-title"><?php echo htmlspecialchars($msg['subject']); ?></div>
               </div>
               
               
               <div class="conversation-messages">
   <?php
   // Get all messages in this conversation thread
   $all_messages = array($msg);
   $replies = getMessageReplies($conn, $msg['id']);
   if (!empty($replies)) {
       $all_messages = array_merge($all_messages, $replies);
   }
   
   // Sort all messages by date
   usort($all_messages, function($a, $b) {
       return strtotime($a['sent_date']) - strtotime($b['sent_date']);
   });
   
   foreach ($all_messages as $message):
       // Check if the message is from the user or admin
       $is_user_message = !isset($message['is_admin']) || $message['is_admin'] == 0;
   ?>
       <div class="message-bubble <?= $is_user_message ? 'message-user' : 'message-admin' ?>">
           <?php if (!$is_user_message): ?>
               <div class="admin-badge">Admin</div>
           <?php endif; ?>
           
           <?= htmlspecialchars($message['message']) ?>
           
           <div class="message-time">
               <?= date('M d, Y h:i A', strtotime($message['sent_date'])) ?>
               <?php if ($is_user_message): ?>
                   <i class="fas fa-check<?= isset($message['is_read']) && $message['is_read'] ? '-double' : '' ?>" style="margin-left: 5px;"></i>
               <?php endif; ?>
           </div>
       </div>
   <?php endforeach; ?>
</div>
<style>
   /* Enhanced message bubble styling for better user/admin distinction */
.conversation-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #f7f9fb;   
}

.message-bubble {
    max-width: 80%;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    position: relative;
    line-height: 1.5;
    font-size: 0.9rem;
    word-wrap: break-word;
}

.message-user {
    align-self: flex-end;
    background: var(--primary);
    color: white;
    border-bottom-right-radius: 4px;
    margin-left: auto;
}

.message-admin {
    align-self: flex-start;
    background: #e0e0e0;
    color: #333;
    border-bottom-left-radius: 4px;
    margin-right: auto;
    border-left: 3px solid var(--secondary);
    position: relative;
}

.admin-badge {
    position: absolute;
    top: -10px;
    left: 10px;
    background: var(--secondary);
    color: white;
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}

.message-time {
    font-size: 0.7rem;
    margin-top: 0.25rem;
    opacity: 0.7;
    text-align: right;
}

.message-admin .message-time {
    text-align: left;
}
</style>
               
               <form class="conversation-reply" method="post">
                  <input type="hidden" name="original_msg_id" value="<?php echo $msg['id']; ?>">
                  <input type="text" name="followup_msg" class="reply-input" placeholder="Type your follow-up message...">
                  <button type="submit" name="send_followup" class="send-reply">
                     <i class="fas fa-paper-plane"></i>
                  </button>
               </form>
            </div>
         <?php endforeach; ?>
      </div>
   </div>
</div>
<?php endif; ?>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Chat messenger functionality
   const chatIcon = document.getElementById('chatIcon');
   const chatWindow = document.getElementById('chatWindow');
   const chatClose = document.getElementById('chatClose');
   const messagesList = document.getElementById('messagesList');
   const showMapBtn = document.getElementById('showMapBtn');
   const mapOverlay = document.querySelector('.map-overlay');
   
   if(chatIcon) {
      chatIcon.addEventListener('click', function() {
         chatWindow.classList.toggle('active');
      });
   }
   
   if(chatClose) {
      chatClose.addEventListener('click', function() {
         chatWindow.classList.remove('active');
      });
   }
   
   // Conversation item click events
   const conversationItems = document.querySelectorAll('.conversation-item');
   const backButtons = document.querySelectorAll('.back-to-list');
   
   conversationItems.forEach(item => {
      item.addEventListener('click', function() {
         const msgId = this.getAttribute('data-id');
         messagesList.style.display = 'none';
         document.getElementById('conversation-' + msgId).classList.add('active');
      });
   });
   
   backButtons.forEach(button => {
      button.addEventListener('click', function() {
         document.querySelectorAll('.chat-conversation').forEach(conv => {
            conv.classList.remove('active');
         });
         messagesList.style.display = 'flex';
      });
   });
   
   // Show map button
   if(showMapBtn) {
      showMapBtn.addEventListener('click', function() {
         mapOverlay.style.display = 'none';
      });
   }
   
   // Auto-hide success messages
   const messageAlerts = document.querySelectorAll('.message-alert');
   const closeButtons = document.querySelectorAll('.message-close');
   
   closeButtons.forEach(button => {
      button.addEventListener('click', function() {
         this.parentElement.style.display = 'none';
      });
   });
   
   // Auto-hide after animation completes
   messageAlerts.forEach(alert => {
      setTimeout(() => {
         alert.style.display = 'none';
      }, 5000);
   });
});
</script>
<script>
// Fetch the conversation messages when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
});

// Function to load messages
function loadMessages() {
    fetch('path/to/your/server-side-script.php')  // Replace with your PHP script to fetch messages
        .then(response => response.json())  // Assuming the PHP script returns JSON
        .then(data => {
            const messagesContainer = document.querySelector('.conversation-messages');
            messagesContainer.innerHTML = ''; // Clear current messages

            // Loop through the messages and append them to the container
            data.forEach(message => {
                const isUserMessage = !message.is_admin; // Check if the message is from the user
                const messageBubble = document.createElement('div');
                messageBubble.classList.add('message-bubble', isUserMessage ? 'message-user' : 'message-admin');

                // If it's not a user message, add the admin badge
                if (!isUserMessage) {
                    const adminBadge = document.createElement('div');
                    adminBadge.classList.add('admin-badge');
                    adminBadge.textContent = 'Admin';
                    messageBubble.appendChild(adminBadge);
                }

                // Add the message text and timestamp
                messageBubble.innerHTML += `
                    <div>${message.message}</div>
                    <div class="message-time">
                        ${new Date(message.sent_date).toLocaleString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric', 
                            hour: 'numeric', 
                            minute: 'numeric', 
                            hour12: true 
                        })}
                        ${isUserMessage ? `<i class="fas fa-check${message.is_read ? '-double' : ''}" style="margin-left: 5px;"></i>` : ''}
                    </div>
                `;

                // Append the message to the conversation container
                messagesContainer.appendChild(messageBubble);
            });
        })
        .catch(error => console.error('Error loading messages:', error));
}

// Optional: Poll for new messages every few seconds (e.g., 5 seconds)
setInterval(loadMessages, 5000);
</script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>