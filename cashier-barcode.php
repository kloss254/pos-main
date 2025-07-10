<?php
// cashier-barcode.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Scanner | POS System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --sidebar-width: 280px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .barcode-container {
            max-width: 900px;
            margin: 30px auto;
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .barcode-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .barcode-header h2 {
            color: var(--dark);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .scan-section {
            margin-bottom: 30px;
        }

        #barcode-scan-input {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            transition: var(--transition);
        }

        #barcode-scan-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        #barcode-product-result {
            margin: 15px 0;
            min-height: 24px;
            font-size: 0.95rem;
        }

        .scanned-items-container {
            margin-top: 30px;
        }

        .scanned-items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .scanned-items-header h3 {
            font-size: 1.3rem;
            color: var(--dark);
        }

        .clear-cart-btn {
            background-color: transparent;
            color: var(--danger);
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .clear-cart-btn:hover {
            color: #d1144a;
        }

        #scanned-items-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
        }

        .scanned-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-gray);
            transition: var(--transition);
        }

        .scanned-item:last-child {
            border-bottom: none;
        }

        .scanned-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .scanned-item-name {
            font-weight: 500;
            flex: 1;
        }

        .scanned-item-qty {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 15px;
        }

        .scanned-item-qty button {
            width: 28px;
            height: 28px;
            border: 1px solid var(--light-gray);
            background-color: var(--white);
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .scanned-item-qty button:hover {
            background-color: var(--light-gray);
        }

        .scanned-item-price {
            font-weight: 600;
            min-width: 100px;
            text-align: right;
        }

        .scanned-item-remove {
            color: var(--danger);
            margin-left: 15px;
            cursor: pointer;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: var(--transition);
        }

        .scanned-item-remove:hover {
            background-color: rgba(247, 37, 133, 0.1);
        }

        .totals-section {
            margin-top: 25px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .totals-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .totals-line.total-line {
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--light-gray);
        }

        .payment-section {
            margin-top: 30px;
        }

        .payment-options {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .payment-btn {
            flex: 1;
            min-width: 150px;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }

        .payment-btn.cash-btn {
            background-color: var(--primary);
            color: var(--white);
        }

        .payment-btn.cash-btn:hover {
            background-color: var(--primary-dark);
        }

        .payment-btn.mpesa-btn {
            background-color: #0b6623;
            color: var(--white);
        }

        .payment-btn.mpesa-btn:hover {
            background-color: #0a5a1f;
        }

        .checkout-section {
            margin-top: 25px;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background-color: var(--success);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
        }

        .checkout-btn:hover {
            background-color: #3ab4d9;
        }

        .customer-info {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .customer-info h4 {
            margin-bottom: 15px;
            color: var(--dark);
        }

        .customer-info-group {
            margin-bottom: 15px;
        }

        .customer-info-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--dark);
        }

        .customer-info-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .customer-info-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .empty-cart-message {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
            font-size: 1rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .scanned-item {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .barcode-container {
                margin: 20px;
                padding: 20px;
            }

            .payment-options {
                flex-direction: column;
            }

            .payment-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        <div class="barcode-header">
            <h2><i class="fas fa-barcode"></i> Barcode Scanner</h2>
        </div>

        <div class="scan-section">
            <input type="text" id="barcode-scan-input" placeholder="Scan or type barcode..." autofocus />
            <div id="barcode-product-result"></div>
        </div>

        <div class="scanned-items-container">
            <div class="scanned-items-header">
                <h3>Scanned Items</h3>
                <button class="clear-cart-btn" id="clear-cart-btn">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>

            <div id="scanned-items-list">
                <div class="empty-cart-message">No items scanned yet</div>
            </div>
        </div>

        <div class="customer-info">
            <h4><i class="fas fa-user"></i> Customer Information</h4>
            <div class="customer-info-group">
                <label for="customer-name">Customer Name:</label>
                <input type="text" id="customer-name" placeholder="Enter customer name" />
            </div>
            <div class="customer-info-group">
                <label for="customer-phone">Phone Number:</label>
                <input type="text" id="customer-phone" placeholder="2547XXXXXXXX" />
            </div>
        </div>

        <div class="totals-section">
            <div class="totals-line">
                <span>Subtotal:</span>
                <span id="subtotal">Ksh0.00</span>
            </div>
            <div class="totals-line">
                <span>Tax (5%):</span>
                <span id="tax">Ksh0.00</span>
            </div>
            <div class="totals-line total-line">
                <span>Total:</span>
                <span id="total">Ksh0.00</span>
            </div>
        </div>

        <div class="payment-section">
            <h4><i class="fas fa-money-bill-wave"></i> Payment Method</h4>
            <div class="payment-options">
                <button class="payment-btn cash-btn" onclick="payCash()">
                    <i class="fas fa-money-bill-wave"></i> Cash
                </button>
                <button class="payment-btn mpesa-btn" onclick="payMpesa()">
                    <i class="fas fa-mobile-alt"></i> M-Pesa
                </button>
            </div>
        </div>

        <div class="checkout-section">
            <button class="checkout-btn" id="checkout-btn" onclick="completeCheckout()">
                <i class="fas fa-check-circle"></i> Complete Checkout
            </button>
        </div>
    </div>

    <script>
let cart = [];

document.addEventListener("DOMContentLoaded", function() {
    updateCartUI();

    document.getElementById("barcode-scan-input").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            const barcode = this.value.trim();
            if (!barcode) return;

            fetch(`get_product_by_barcode.php?barcode=${barcode}`)
                .then(res => res.json())
                .then(product => {
                    const result = document.getElementById("barcode-product-result");

                    if (product.error) {
                        result.innerHTML = `<p style="color:var(--danger);">${product.error}</p>`;
                    } else {
                        result.innerHTML = `<p style="color:var(--success);"><i class="fas fa-check-circle"></i> ${product.product_name} added</p>`;
                        addProductToCart(product);
                    }

                    this.value = "";
                    setTimeout(() => {
                        result.innerHTML = "";
                    }, 2000);
                })
                .catch(err => {
                    console.error("Barcode fetch error:", err);
                    document.getElementById("barcode-product-result").innerHTML = 
                        `<p style="color:var(--danger);">Error fetching product</p>`;
                });
        }
    });

    document.getElementById("clear-cart-btn").addEventListener("click", function() {
        if (cart.length > 0 && confirm("Are you sure you want to clear all items?")) {
            cart = [];
            updateCartUI();
        }
    });
});

function updateCartUI() {
    const list = document.getElementById("scanned-items-list");
    list.innerHTML = "";

    if (cart.length === 0) {
        list.innerHTML = `<div class="empty-cart-message">No items scanned yet</div>`;
        document.getElementById("checkout-btn").disabled = true;
        document.getElementById("subtotal").textContent = `Ksh0.00`;
        document.getElementById("tax").textContent = `Ksh0.00`;
        document.getElementById("total").textContent = `Ksh0.00`;
        return;
    }

    document.getElementById("checkout-btn").disabled = false;

    let subtotal = 0;

    cart.forEach((item, index) => {
        const price = parseFloat(item.price);
        const quantity = parseInt(item.quantity);
        const discount = parseFloat(item.discount || 0);

        const total = (price * quantity) - discount;
        subtotal += total;

        const itemElement = document.createElement("div");
        itemElement.className = "scanned-item";
        itemElement.innerHTML = `
            <span class="scanned-item-name">${item.name}</span>
            <div class="scanned-item-qty">
                <button onclick="updateItemQuantity(${index}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="updateItemQuantity(${index}, 1)">+</button>
            </div>
            <span class="scanned-item-price">Ksh${total.toFixed(2)}</span>
            <span class="scanned-item-remove" onclick="removeItem(${index})">
                <i class="fas fa-times"></i>
            </span>
        `;
        list.appendChild(itemElement);
    });

    subtotal = parseFloat(subtotal) || 0;
    const tax = subtotal * 0.05;
    const total = subtotal + tax;

    document.getElementById("subtotal").textContent = `Ksh${subtotal.toFixed(2)}`;
    document.getElementById("tax").textContent = `Ksh${tax.toFixed(2)}`;
    document.getElementById("total").textContent = `Ksh${total.toFixed(2)}`;
}

function addProductToCart(product) {
    const existingItem = cart.find(item => item.product_id === product.id);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            product_id: product.id,
            name: product.product_name,
            price: parseFloat(product.price),
            tax: parseFloat(product.tax || 0),
            quantity: 1,
            discount: 0
        });
    }

    updateCartUI();
}

function updateItemQuantity(index, change) {
    const newQuantity = cart[index].quantity + change;

    if (newQuantity < 1) {
        removeItem(index);
        return;
    }

    cart[index].quantity = newQuantity;
    updateCartUI();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function payMpesa() {
    const phone = document.getElementById("customer-phone").value.trim();
    const totalText = document.getElementById("total").textContent;
    const total = parseFloat(totalText.replace('Ksh', '').trim());

    if (!phone) {
        alert("Please enter customer phone number first");
        return;
    }

    if (isNaN(total) || total <= 0) {
        alert("Invalid total amount");
        return;
    }

    if (!confirm(`Send M-Pesa payment request of Ksh${total.toFixed(2)} to ${phone}?`)) {
        return;
    }

    fetch('stk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone, amount: total })
    })
    .then(res => res.json())
    .then(data => {
        if (data.ResponseCode === "0") {
            alert("M-Pesa STK Push sent successfully. Please wait for customer to enter PIN.");
        } else {
            alert("Payment failed: " + (data.errorMessage || 'Unknown error'));
        }
    })
    .catch(err => {
        alert("Failed to process payment. Please try again.");
        console.error(err);
    });
}
function payCash() {
    const totalText = document.getElementById("total").textContent;
    const total = parseFloat(totalText.replace('Ksh', '').trim());

    if (isNaN(total) || total <= 0) {
        alert("Invalid total amount");
        return;
    }

    const amountReceived = prompt(`Enter amount received (Cash):`, total.toFixed(2));
    if (amountReceived === null) return; // user cancelled
    const received = parseFloat(amountReceived);

    if (isNaN(received) || received < total) {
        alert("Insufficient amount received.");
        return;
    }

    const change = received - total;
    alert(`Cash payment accepted.\nChange to return: Ksh${change.toFixed(2)}`);
    completeCheckout();
}
function completeCheckout() {
    if (cart.length === 0) {

        alert("Cart is empty!");
        return;
    }

    const customerName = document.getElementById("customer-name").value.trim();
    const customerPhone = document.getElementById("customer-phone").value.trim();

    const orderData = {
        customer_name: customerName,
        customer_phone: customerPhone,
        cart: cart,
        total: parseFloat(document.getElementById("total").textContent.replace('Ksh', '').trim())
    };

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Order completed successfully!");
            cart = [];
            updateCartUI();
            document.getElementById("customer-name").value = "";
            document.getElementById("customer-phone").value = "";
        } else {
            alert("Error saving order. Please try again.");
        }
    })
    .catch(err => {
        console.error("Order save error:", err);
        alert("Error completing checkout.");
    });
}

</script>
</body>
</html>
