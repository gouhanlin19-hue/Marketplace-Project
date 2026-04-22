<?php
session_start();

if (!isset($_SESSION['item_id'])) {
    die("No item selected.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }
        .payment-box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        input, button, select {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background: black;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="payment-box">
    <h2>Payment Page</h2>

    <form action="confirm_order.php" method="POST">
        <select name="card_type" required>
            <option value="">Select Card Type</option>
            <option value="Visa">Visa</option>
            <option value="MasterCard">MasterCard</option>
        </select>

        <input type="text" name="card_number" id="card_number"
           placeholder="1234 5678 1234 5678"
           maxlength="19"
           required>
        <input type="text" name="card_name" placeholder="Name on Card" required>
        <input type="text" name="expiry" placeholder="MM/YY" required>
        <input type="text" name="cvv" placeholder="CVV" required>

        <button type="submit">Pay Now</button>
    </form>
</div>

<script>
document.getElementById("card_number").addEventListener("input", function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');

    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;

    e.target.value = formatted;
});
</script>
</body>
</html>