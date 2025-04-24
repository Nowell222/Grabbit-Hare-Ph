$(document).ready(function () {
    $('.conversation-item').on('click', function () {
       var conversationId = $(this).data('conversation-id');
 
       // Highlight the active conversation
       $('.conversation-item').removeClass('active');
       $(this).addClass('active');
 
       // Load conversation messages using AJAX
       $.ajax({
          url: 'load_messages.php', // separate PHP file to handle message loading
          type: 'GET',
          data: { conversation: conversationId },
          success: function (response) {
             $('.chat-messages').html(response); // update the chat area
          },
          error: function () {
             alert('Failed to load messages. Please try again.');
          }
       });
    });
 });
 