<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Handle message replies
if(isset($_POST['send_reply'])){
   $message_id = $_POST['message_id'];
   $reply_text = filter_var($_POST['reply_text'], FILTER_SANITIZE_STRING);
   
   if(empty($reply_text)){
      $message[] = 'Reply cannot be empty!';
   } else {
      try {
         // Get original message details
         $get_original = $conn->prepare("SELECT * FROM messages WHERE id = ?");
         $get_original->execute([$message_id]);
         $original = $get_original->fetch(PDO::FETCH_ASSOC);
         
         // Insert the admin reply
         $insert_reply = $conn->prepare("INSERT INTO messages(user_id, name, email, number, subject, message, parent_msg_id, is_admin, sent_date) VALUES(?,?,?,?,?,?,?,?,NOW())");
         $insert_reply->execute([
            $original['user_id'], 
            'Admin', 
            '', 
            '', 
            'Re: ' . $original['subject'], 
            $reply_text,
            $message_id,
            1
         ]);

         
      } catch (PDOException $e) {
         $message[] = 'Database error: ' . $e->getMessage();
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   
   // Delete all replies associated with this message
   $delete_replies = $conn->prepare("DELETE FROM messages WHERE parent_msg_id = ?");
   $delete_replies->execute([$delete_id]);
   
   // Delete the message itself
   $delete_message = $conn->prepare("DELETE FROM messages WHERE id = ?");
   $delete_message->execute([$delete_id]);
   
   header('location:messages.php');
}

// Get the currently selected conversation (if any)
$current_conversation = null;
if(isset($_GET['conversation'])){
   $current_conversation = $_GET['conversation'];
}

// Function to get message replies
function getMessageReplies($conn, $message_id) {
   $replies = [];
   $select_replies = $conn->prepare("SELECT * FROM messages WHERE parent_msg_id = ? ORDER BY sent_date ASC");
   $select_replies->execute([$message_id]);
   
   if($select_replies->rowCount() > 0){
      while($row = $select_replies->fetch(PDO::FETCH_ASSOC)){
         $replies[] = $row;
      }
   }
   
   return $replies;
}

// Function to get conversation list
function getConversations($conn) {
   $conversations = [];
   $select_conversations = $conn->prepare("SELECT m.id, m.user_id, m.name, m.subject, m.message, m.sent_date, 
                                          (SELECT COUNT(*) FROM messages WHERE parent_msg_id = m.id) as reply_count,
                                          u.name as user_name
                                          FROM messages m 
                                          LEFT JOIN users u ON m.user_id = u.id
                                          WHERE m.parent_msg_id IS NULL 
                                          ORDER BY m.sent_date DESC");
   $select_conversations->execute();
   
   if($select_conversations->rowCount() > 0){
      while($row = $select_conversations->fetch(PDO::FETCH_ASSOC)){
         $conversations[] = $row;
      }
   }
   
   return $conversations;
}

$conversations = getConversations($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Customer Messages | Admin Panel</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   
   <style>
      :root {
         --primary-color: #4d5bf9;
         --primary-light: #eceefe;
         --secondary-color: #6c757d;
         --success-color: #10b981;
         --success-light: #ecfdf5;
         --danger-color: #ef4444;
         --danger-light: #fef2f2;
         --light-bg: #f8fafc;
         --dark-bg: #1e293b;
         --border-color: #e2e8f0;
         --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
         --radius: 12px;
      }
      
      body {
         background-color: #f5f7fa;
         color: #333;
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      
      .messenger-container {
         display: flex;
         max-width: 1200px;
         margin: 0 auto;
         height: calc(100vh - 120px);
         background: #fff;
         border-radius: var(--radius);
         box-shadow: var(--shadow);
         overflow: hidden;
      }
      
      /* Sidebar */
      .conversations-sidebar {
         width: 350px;
         border-right: 1px solid var(--border-color);
         background: #fff;
         overflow-y: auto;
      }
      
      .sidebar-header {
         padding: 1.25rem;
         border-bottom: 1px solid var(--border-color);
         background-color: #fff;
         position: sticky;
         top: 0;
         z-index: 10;
      }
      
      .sidebar-title {
         font-size: 1.25rem;
         font-weight: 600;
         color: #1e293b;
         margin: 0;
      }
      
      .conversation-list {
         list-style: none;
         padding: 0;
         margin: 0;
      }
      
      .conversation-item {
         padding: 1rem 1.25rem;
         border-bottom: 1px solid var(--border-color);
         cursor: pointer;
         transition: all 0.2s;
         display: flex;
         align-items: center;
      }
      
      .conversation-item:hover {
         background-color: var(--primary-light);
      }
      
      .conversation-item.active {
         background-color: var(--primary-light);
         border-left: 3px solid var(--primary-color);
      }
      
      .conversation-avatar {
         width: 40px;
         height: 40px;
         border-radius: 50%;
         background: var(--primary-light);
         color: var(--primary-color);
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: 600;
         margin-right: 1rem;
         flex-shrink: 0;
      }
      
      .conversation-info {
         flex: 1;
         min-width: 0;
      }
      
      .conversation-name {
         font-weight: 600;
         margin-bottom: 0.25rem;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      
      .conversation-preview {
         font-size: 0.85rem;
         color: var(--secondary-color);
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }
      
      .conversation-meta {
         display: flex;
         justify-content: space-between;
         font-size: 0.75rem;
         margin-top: 0.25rem;
      }
      
      .conversation-date {
         color: var(--secondary-color);
      }
      
      .conversation-badge {
         background-color: var(--primary-color);
         color: white;
         border-radius: 50%;
         width: 20px;
         height: 20px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 0.7rem;
      }
      
      /* Chat Area */
      .chat-area {
         flex: 1;
         display: flex;
         flex-direction: column;
         background-color: #f8fafc;
      }
      
      .chat-header {
         padding: 1.25rem;
         border-bottom: 1px solid var(--border-color);
         background-color: #fff;
         display: flex;
         align-items: center;
      }
      
      .chat-title {
         font-size: 1.1rem;
         font-weight: 600;
         margin: 0;
         flex: 1;
      }
      
      .chat-actions {
         display: flex;
         gap: 0.5rem;
      }
      
      .chat-messages {
         flex: 1;
         padding: 1.5rem;
         overflow-y: auto;
      }
      
      .chat-message {
         margin-bottom: 1.5rem;
         display: flex;
      }
      
      .chat-bubble {
         max-width: 75%;
         padding: 1rem;
         border-radius: 1rem;
         position: relative;
         word-break: break-word;
         box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      }
      
      .message-client {
         justify-content: flex-start;
      }
      
      .message-client .chat-bubble {
         background-color: #fff;
         border: 1px solid var(--border-color);
         border-top-left-radius: 0.25rem;
      }
      
      .message-admin {
         justify-content: flex-end;
      }
      
      .message-admin .chat-bubble {
         background-color: var(--primary-color);
         color: white;
         border-top-right-radius: 0.25rem;
      }
      
      .bubble-header {
         display: flex;
         justify-content: space-between;
         margin-bottom: 0.5rem;
         font-size: 0.85rem;
      }
      
      .bubble-name {
         font-weight: 600;
      }
      
      .message-client .bubble-name {
         color: #1e293b;
      }
      
      .message-admin .bubble-name {
         color: rgba(255,255,255,0.9);
      }
      
      .bubble-time {
         font-size: 0.75rem;
         opacity: 0.8;
      }
      
      .bubble-content {
         line-height: 1.5;
      }
      
      /* Contact Info Panel */
      .contact-info {
         padding: 1rem 1.5rem;
         background-color: #fff;
         border-top: 1px solid var(--border-color);
         font-size: 0.9rem;
         display: flex;
         flex-wrap: wrap;
         gap: 1rem;
      }
      
      .contact-item {
         display: flex;
         align-items: center;
         margin-right: 1.5rem;
      }
      
      .contact-item i {
         margin-right: 0.5rem;
         color: var(--secondary-color);
         width: 16px;
      }
      
      /* Reply Form */
      .reply-form {
         padding: 1rem 1.5rem;
         background-color: #fff;
         border-top: 1px solid var(--border-color);
         display: flex;
         align-items: center;
      }
      
      .form-input {
         flex: 1;
         border: 1px solid var(--border-color);
         border-radius: 24px;
         padding: 0.75rem 1.25rem;
         font-size: 1rem;
         background-color: #f8fafc;
         transition: all 0.2s;
      }
      
      .form-input:focus {
         outline: none;
         border-color: var(--primary-color);
         box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
      }
      
      .form-input::placeholder {
         color: #a0aec0;
      }
      
      .btn-send {
         background-color: var(--primary-color);
         color: white;
         border: none;
         border-radius: 50%;
         width: 42px;
         height: 42px;
         margin-left: 0.75rem;
         display: flex;
         align-items: center;
         justify-content: center;
         cursor: pointer;
         transition: all 0.2s;
      }
      
      .btn-send:hover {
         background-color: #4338ca;
         transform: translateY(-1px);
      }
      
      .btn-send:active {
         transform: translateY(1px);
      }
      
      /* Empty state */
      .empty-chat {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         height: 100%;
         text-align: center;
         padding: 2rem;
      }
      
      .empty-icon {
         font-size: 3.5rem;
         color: #cbd5e1;
         margin-bottom: 1.5rem;
      }
      
      .empty-title {
         font-size: 1.5rem;
         font-weight: 600;
         color: #334155;
         margin-bottom: 0.75rem;
      }
      
      .empty-text {
         color: #64748b;
         max-width: 400px;
      }
      
      /* Alerts */
      .alert {
         padding: 0.75rem 1rem;
         border-radius: var(--radius);
         margin-bottom: 1.5rem;
         display: flex;
         align-items: center;
         animation: slideDown 0.3s ease-out;
      }
      
      @keyframes slideDown {
         from { opacity: 0; transform: translateY(-10px); }
         to { opacity: 1; transform: translateY(0); }
      }
      
      .alert i {
         margin-right: 0.75rem;
         font-size: 1.1rem;
      }
      
      .alert-success {
         background-color: var(--success-light);
         color: var(--success-color);
         border-left: 4px solid var(--success-color);
      }
      
      .alert-danger {
         background-color: var(--danger-light);
         color: var(--danger-color);
         border-left: 4px solid var(--danger-color);
      }
      
      /* Responsive */
      @media (max-width: 768px) {
         .messenger-container {
            flex-direction: column;
            height: auto;
         }
         
         .conversations-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid var(--border-color);
            max-height: 300px;
         }
         
         .chat-area {
            min-height: 500px;
         }
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="messages-container">
   <h1 class="heading">Customer Messages</h1>
   
   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<div class="alert '.($msg == 'Reply sent successfully!' ? 'alert-success' : 'alert-danger').'">
                  <i class="fas '.($msg == 'Reply sent successfully!' ? 'fa-check-circle' : 'fa-exclamation-circle').'"></i>
                  '.$msg.'
               </div>';
      }
   }
   ?>

   <div class="messenger-container">
      <!-- Conversations Sidebar -->
      <div class="conversations-sidebar">
         <div class="sidebar-header">
            <h2 class="sidebar-title">Conversations</h2>
         </div>
         
         <ul class="conversation-list">
            <?php if(count($conversations) > 0): ?>
               <?php foreach($conversations as $conv): 
                  $is_active = ($current_conversation == $conv['id']);
                  $avatar_letter = strtoupper(substr($conv['name'], 0, 1));
                  $preview_text = strlen($conv['message']) > 30 ? substr($conv['message'], 0, 30).'...' : $conv['message'];
               ?>
                  <li class="conversation-item <?= $is_active ? 'active' : '' ?>" 
                      onclick="window.location.href='messages.php?conversation=<?= $conv['id'] ?>'">
                     <div class="conversation-avatar">
                        <?= $avatar_letter ?>
                     </div>
                     <div class="conversation-info">
                        <div class="conversation-name"><?= htmlspecialchars($conv['name']) ?></div>
                        <div class="conversation-preview"><?= htmlspecialchars($preview_text) ?></div>
                        <div class="conversation-meta">
                           <span class="conversation-date">
                              <?= date('M d, h:i A', strtotime($conv['sent_date'])) ?>
                           </span>
                           <?php if($conv['reply_count'] > 0): ?>
                              <span class="conversation-badge"><?= $conv['reply_count'] ?></span>
                           <?php endif; ?>
                        </div>
                     </div>
                  </li>
               <?php endforeach; ?>
            <?php else: ?>
               <li style="padding: 2rem; text-align: center; color: var(--secondary-color);">
                  No conversations yet
               </li>
            <?php endif; ?>
         </ul>
      </div>
      
      <!-- Chat Area -->
      <div class="chat-area">
         <?php if($current_conversation): 
            // Get the selected conversation
            $select_conversation = $conn->prepare("SELECT * FROM messages WHERE id = ?");
            $select_conversation->execute([$current_conversation]);
            $conversation = $select_conversation->fetch(PDO::FETCH_ASSOC);
            
            if($conversation):
               $replies = getMessageReplies($conn, $current_conversation);
               $avatar_letter = strtoupper(substr($conversation['name'], 0, 1));
         ?>
            <div class="chat-header">
               <h3 class="chat-title"><?= htmlspecialchars($conversation['subject']) ?></h3>
               <div class="chat-actions">
                  <a href="messages.php?delete=<?= $conversation['id'] ?>" class="btn-delete" onclick="return confirm('Delete this conversation? This action cannot be undone.')">
                     <i class="fas fa-trash-alt"></i>
                  </a>
               </div>
            </div>
            
            <div class="chat-messages" id="chat-messages">
               <!-- First message (from client) -->
               <div class="chat-message message-client">
                  <div class="chat-bubble">
                     <div class="bubble-header">
                        <span class="bubble-name"><?= htmlspecialchars($conversation['name']) ?></span>
                        <span class="bubble-time"><?= date('h:i A', strtotime($conversation['sent_date'])) ?></span>
                     </div>
                     <div class="bubble-content">
                        <?= htmlspecialchars($conversation['message']) ?>
                     </div>
                  </div>
               </div>
               
               <!-- Reply messages -->
               <?php foreach($replies as $reply): ?>
                  <div class="chat-message <?= $reply['is_admin'] ? 'message-admin' : 'message-client'; ?>">
                     <div class="chat-bubble">
                        <div class="bubble-header">
                           <span class="bubble-name"><?= $reply['is_admin'] ? 'Admin' : htmlspecialchars($reply['name']) ?></span>
                           <span class="bubble-time"><?= date('h:i A', strtotime($reply['sent_date'])) ?></span>
                        </div>
                        <div class="bubble-content">
                           <?= htmlspecialchars($reply['message']) ?>
                        </div>
                     </div>
                  </div>
               <?php endforeach; ?>
            </div>
            
            <!-- Contact info -->
            <div class="contact-info">
               <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <span><?= htmlspecialchars($conversation['email']) ?></span>
               </div>
               
               <?php if(!empty($conversation['number'])): ?>
               <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <span><?= htmlspecialchars($conversation['number']) ?></span>
               </div>
               <?php endif; ?>
               
               <div class="contact-item">
                  <i class="fas fa-calendar-alt"></i>
                  <span><?= date('F d, Y', strtotime($conversation['sent_date'])) ?></span>
               </div>
               
               <div class="contact-item">
                  <i class="fas fa-tag"></i>
                  <span>ID: <?= $conversation['user_id'] ? htmlspecialchars($conversation['user_id']) : 'Guest'; ?></span>
               </div>
            </div>
            
            <!-- Reply Form -->
            <form method="post" action="" class="reply-form">
               <input type="hidden" name="message_id" value="<?= $conversation['id'] ?>">
               <input type="text" name="reply_text" class="form-input" placeholder="Type your reply here..." required>
               <button type="submit" name="send_reply" class="btn-send">
                  <i class="fas fa-paper-plane"></i>
               </button>
            </form>
            
         <?php else: ?>
            <div class="empty-chat">
               <div class="empty-icon">
                  <i class="fas fa-exclamation-circle"></i>
               </div>
               <h3 class="empty-title">Conversation not found</h3>
               <p class="empty-text">The selected conversation could not be loaded.</p>
            </div>
         <?php endif; ?>
         
         <?php else: ?>
            <div class="empty-chat">
               <div class="empty-icon">
                  <i class="far fa-comments"></i>
               </div>
               <h3 class="empty-title">No Conversation Selected</h3>
               <p class="empty-text">Select a conversation from the sidebar to view messages.</p>
            </div>
         <?php endif; ?>
      </div>
   </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Auto scroll to latest message in the conversation
   const chatMessages = document.getElementById('chat-messages');
   if(chatMessages) {
      chatMessages.scrollTop = chatMessages.scrollHeight;
   }
   
   // Auto hide alerts after 5 seconds
   const alerts = document.querySelectorAll('.alert');
   if(alerts.length > 0) {
      setTimeout(() => {
         alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = 'all 0.3s ease-out';
            
            setTimeout(() => {
               alert.style.display = 'none';
            }, 300);
         });
      }, 5000);
   }
   
   // Mark conversation as read when clicked (visual only)
   const conversationItems = document.querySelectorAll('.conversation-item');
   conversationItems.forEach(item => {
      item.addEventListener('click', function() {
         const badge = this.querySelector('.conversation-badge');
         if(badge) {
            badge.style.display = 'none';
         }
      });
   });
});
</script>

</body>
</html>