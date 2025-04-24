<div class="box">
    <p>Placed on: <span><?= $fetch_orders['placed_on']; ?></span></p>
    <p>Name: <span><?= $fetch_orders['name']; ?></span></p>
    <p>Number: <span><?= $fetch_orders['number']; ?></span></p>
    <p>Address: <span><?= $fetch_orders['address']; ?></span></p>
    <p>Total Products: <span><?= $fetch_orders['total_products']; ?></span></p>
    <p>Total Price: <span>Php <?= $fetch_orders['total_price']; ?></span></p>
    <p>Payment Method: <span><?= $fetch_orders['method']; ?></span></p>
    <form action="" method="post">
        <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
        <select name="payment_status" class="select">
            <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
        </select>
        <div class="flex-btn">
            <input type="submit" value="Update" class="option-btn" name="update_payment">
            <a href="?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
        </div>
    </form>
</div>
