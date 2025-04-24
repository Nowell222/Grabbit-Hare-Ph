<?php
include '../components/connect.php';

if (isset($_GET['conversation'])) {
   $message_id = $_GET['conversation'];

   // Get main message
   $stmt = $conn->prepare("SELECT * FROM messages WHERE id = ?");
   $stmt->execute([$message_id]);
   $main_message = $stmt->fetch(PDO::FETCH_ASSOC);

   // Display main message
   echo '<div class="chat-message message-client">';
   echo '<div class="chat-bubble">';
   echo '<div class="bubble-header"><span class="bubble-name">' . htmlspecialchars($main_message['name']) . '</span><span class="bubble-time">' . $main_message['sent_date'] . '</span></div>';
   echo '<div class="bubble-content">' . nl2br(htmlspecialchars($main_message['message'])) . '</div>';
   echo '</div></div>';

   // Get replies
   $replies = $conn->prepare("SELECT * FROM messages WHERE parent_msg_id = ? ORDER BY sent_date ASC");
   $replies->execute([$message_id]);

   while ($reply = $replies->fetch(PDO::FETCH_ASSOC)) {
      $is_admin = $reply['is_admin'] ? 'message-admin' : 'message-client';
      echo '<div class="chat-message ' . $is_admin . '">';
      echo '<div class="chat-bubble">';
      echo '<div class="bubble-header"><span class="bubble-name">' . ($is_admin == 'message-admin' ? 'Admin' : htmlspecialchars($reply['name'])) . '</span><span class="bubble-time">' . $reply['sent_date'] . '</span></div>';
      echo '<div class="bubble-content">' . nl2br(htmlspecialchars($reply['message'])) . '</div>';
      echo '</div></div>';
   }
}
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   function refreshMessages(conversationId) {
       $.ajax({
           url: 'get_messages_html.php?conversation=' + conversationId,
           method: 'GET',
           success: function(data) {
               $('#message-list').html(data);
           },
           error: function() {
               alert('Error refreshing messages');
           }
       });
   }