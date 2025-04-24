<?php
// Include your database connection
include 'components/connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if conversation ID is provided
if (!isset($_GET['conversation_id']) || empty($_GET['conversation_id'])) {
    echo "<div class='error-message'>Error: Conversation ID is required</div>";
    exit;
}

$conversation_id = intval($_GET['conversation_id']);

try {
    // Get the main message - PDO style
    $stmt = $conn->prepare("SELECT * FROM messages WHERE id = :id");
    $stmt->bindParam(':id', $conversation_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Conversation not found");
    }
    
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
    
    // Output the refresh button
    ?>
    <div class="refresh-container">
        <button id="refresh-messages" class="refresh-button" onclick="refreshMessages(<?= $msg['id'] ?>)">
            <i class="fas fa-sync-alt"></i> Refresh Messages
        </button>
    </div>
    
    <div id="message-list">
        <?php
        // Render all messages
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
    <?php
    
} catch (Exception $e) {
    echo "<div class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

/**
 * Helper function to get message replies - PDO version
 * @param PDO $conn Database connection
 * @param int $parent_id Parent message ID
 * @return array Array of reply messages
 */
function getMessageReplies($conn, $parent_id) {
    $replies = [];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM messages WHERE parent_id = :parent_id");
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $replies[] = $row;
        }
    } catch (Exception $e) {
        // Silently fail and return empty array
    }
    
    return $replies;
}
?>