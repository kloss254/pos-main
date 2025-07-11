<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS Cashier Terminal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="cashier-styles.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        #cashier-app {
    display: flex;
    min-height: 100vh;
}
.cashier-main-content {
    margin-left: 250px; /* match sidebar width */
    width: calc(100% - 250px); /* adjust width */
    overflow: auto;
}


       .product-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    text-align: center;
    padding: 0;
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: scale(1.03);
}

.product-image-container {
    width: 100%;
    height: 150px;
    overflow: hidden;
}

.product-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image-container img {
    transform: scale(1.05);
}

.product-info {
    padding: 12px 10px;
    background-color: #fff;
}

.product-info .product-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 5px;
    color: #333;
}

.product-info .product-price {
    color: #2d89ef;
    font-weight: bold;
    font-size: 15px;
    margin-bottom: 8px;
}

        }
        .payment-method-btn.active {
            background-color: #2d89ef;
            color: #fff;
        }


        .payment-method-btn.active:hover{
            background-color: #0056b3;
            color: #fff;
        }


.product-image-container {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto;
}

        .product-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }

.product-name-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 5px;
}

.product-image-container:hover .product-name-overlay {
    opacity: 1;
}.cashier-main-content {
    margin-left: 250px;
    padding: 20px;
    width: calc(100% - 250px);
}
x;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* auto responsive */
    gap: 15px;
    justify-content: center;
}
@media (max-width: 768px) {
    .cashier-sidebar {
        position: fixed;
        left: -100%;
        width: 250px;
        transition: left 0.3s ease;
        z-index: 999;
    }

    .cashier-sidebar.active {
        left: 0;
    }

    .cashier-main-content {
        margin-left: 0;
        width: 100%;
    }

    .sidebar-toggle-btn {
        display: block;
        position: fixed;
        top: 15px;
        left: 15px;
        background-color: #2c3e50;
        color: white;
        border: none;
        padding: 10px;
        z-index: 1000;
        border-radius: 5px;
        font-size: 18px;
    }
}


.product-card {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-4px);
}
.products-section {
    max-height: 650px;
    overflow-y: auto;
    padding: 10px;
    box-sizing: border-box;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns */
    gap: 20px; /* spacing between cards */
    padding: 10px;
    box-sizing: border-box;
}
.customer-info-group label {
    display: block;
    margin-top: 12px;
    font-weight: bold;
    color: #444;
}

.customer-info-group input {
    width: 150%;
    padding: 7px 5px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
    background-color: #f9f9f9;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.customer-info-group input:focus {
    border-color: #2d89ef;
    box-shadow: 0 0 5px rgba(45, 137, 239, 0.4);
    outline: none;
    background-color: #fff;
}
    .cashier-sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: white;
      padding: 20px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
    }
        .cashier-sidebar .sidebar-menu ul li a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    color: #fff;
    font-size: 1.1em;
    font-weight: 500;
    border-radius: 5px;
    transition: all 0.3s ease;
}
.cashier-sidebar .sidebar-menu ul li a.active i {
    color: white; /* White icon for active */
}

.cashier-sidebar .sidebar-menu ul li a:hover:not(.active) {
    background-color: #34495e; /* Light blue hover */
    color: #fff; /* Lighter text color on hover */
}
body {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    </style>
</head>
<body>
    <button class="sidebar-toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div id="cashier-app">
        <aside class="cashier-sidebar">
            <div class="logo">
                <img src="logo.png" alt="POS Logo"> <span>POS</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                <li><a href="cashier-dashboard.php" class="sidebar-link active"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="cashier-orders.php" class="sidebar-link " ><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="cashier-sales.php" class="sidebar-link"><i class="fas fa-cash-register"></i> Sales</a></li>
             <li><a href="cashier-barcode.php" class="sidebar-link"><i class="fas fa-barcode"></i> Barcode</a></li>

             <li><a href="logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

                </ul>
            </nav>
            <div class="user-cards">
                <div class="user-card active">
                    <span class="initials">DJ</span>
                    <div class="user-details">
                        <span class="name">Dilys Joy</span>
                        <span class="role">Cashier</span>
                    </div>
                </div>
                <div class="user-card">
                    <span class="initials">FLO</span>
                    <div class="user-details">
                        <span class="name">Florence</span>
                        <span class="role">Cashier</span>
                    </div>
                </div>
            </div>
    <a href="logout.php" class="sidebar-logout" id="cashier-logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>

        </aside>

        <main class="cashier-main-content">
            <header class="cashier-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="product-search" placeholder="Search product here..." />
                </div>
                <div class="header-icons">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-envelope"></i>
                    <div class="user-avatar"></div>
                </div>
            </header>

            <section id="sales-section-cashier" class="cashier-content-section active">
                <div class="sales-area">
                    <div class="sales-panel products-section">
                        <div class="categories-nav">
                            <button class="category-btn active" data-category="All">All</button>
                        </div>
                        <div id="product-list-cashier" class="product-grid"></div>
                        <p id="no-products-message" style="display:none; color: #888; text-align:center;">No products found.</p>

                    </div>

                    <div class="sales-panel cart-section">
                        <div class="cart-header">
                            <h3>Current Order</h3>
                            <button class="clear-cart-btn"><i class="fas fa-trash"></i> Clear</button>
                        </div>
                        <ul id="cart-list-cashier" class="cart-items"></ul>

                        <div class="customer-info-and-total">
                            <div class="customer-info-group">
                                <label for="customer-name">Customer Name:</label>
                                <input type="text" id="customer-name" placeholder="Name" />
                                <label for="customer-phone">Customer Phone:</label>
                                <input type="text" id="customer-phone" placeholder="Phone" />
                                <label for="order-qty">Quantity:</label>
                                <input type="number" id="order-qty" min="1" value="1" />
                                <label for="order-discount">Discount:</label>
                                <input type="number" id="order-discount" min="0" value="0" />
                               

                            </div>
                            <div class="cart-summary-line">
                                <span>Subtotal</span>
                                <span><span id="subtotal-cashier">Ksh0.00</span></span>
                            </div>
                            <div class="cart-summary-line">
                                <span>Tax (5%)</span>
                                <span><span id="tax-cashier">Ksh0.00</span></span>
                            </div>
                            <div class="cart-summary-line total-line">
                                <span>Total</span>
                                <span><span id="total-cashier">Ksh0.00</span></span>
                            </div>
                        </div>

                        <div class="payment-options">
                            <button class="payment-method-btn active" data-method="Cash"><i class="fas fa-money-bill-wave"></i> Cash</button>
                           <button id="mpesa-pay-btn" style="background-color: green; color: white; border: none; padding: 10px 20px; border-radius: 5px;">
    Pay via M-Pesa
</button>

                        </div>

                        <div class="checkout-actions">
                            <button id="cashier-checkout-btn" class="complete-sale-btn"></i> Checkout</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
    let cart = [];

    function toggleSidebar() {
        const sidebar = document.querySelector('.cashier-sidebar');
        sidebar.classList.toggle('active');
    }

    function addToCart(product_id, name, price, tax) {
        const quantity = parseInt(document.getElementById('order-qty').value) || 1;
        const discount = parseInt(document.getElementById('order-discount').value) || 0;

       const existing = cart.find(p => p.product_id === product_id);
if (existing) {
    existing.quantity += quantity;
    existing.discount += discount;
} else {
    cart.push({ product_id, name, price, tax, quantity, discount });
}

        updateCartUI();
    }

    function updateCartUI() {
        const cartList = document.getElementById('cart-list-cashier');
        cartList.innerHTML = '';
        let subtotal = 0;
        cart.forEach(item => {
            const lineTotal = (item.price * item.quantity) - item.discount;
            subtotal += lineTotal;
            const li = document.createElement('li');
            li.innerHTML = `
    <strong>${item.name}</strong><br>
    Qty: ${item.quantity} @ Ksh${item.price} <br>
    Discount: Ksh${item.discount} <br>
    <strong>Total: Ksh${lineTotal.toFixed(2)}</strong>
`;

            cartList.appendChild(li);
        });
        const tax = subtotal * 0.05;
        const total = subtotal + tax;
        document.getElementById('subtotal-cashier').textContent = `Ksh${subtotal.toFixed(2)}`;
        document.getElementById('tax-cashier').textContent = `Ksh${tax.toFixed(2)}`;
        document.getElementById('total-cashier').textContent = `Ksh${total.toFixed(2)}`;
    }

    document.addEventListener("DOMContentLoaded", function () {
        fetch('products.php')
            .then(res => res.json())
            .then(products => {
                const productList = document.getElementById("product-list-cashier");

                products.forEach(product => {
                    const productCard = document.createElement("div");
                    productCard.className = "product-card";
                    productCard.setAttribute('data-name', product.product_name.toLowerCase());

                    productCard.innerHTML = `
                       <div class="product-image-container">
    <img src="${product.image}" alt="${product.product_name}" onerror="this.src='placeholder.jpg'" />
</div>
<div class="product-info">
    <div class="product-name">${product.product_name}</div>
    <div class="product-price">Ksh ${product.price}</div>
    <button class="add-to-cart-btn"
        data-id="${product.id}" 
        data-name="${product.product_name}" 
        data-price="${product.price}" 
        data-tax="${product.tax}">Add</button>
</div>

                    `;

                    productList.appendChild(productCard);
                });

                // Attach event listeners AFTER the buttons are added
                document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        const id = parseInt(button.dataset.id);
                        const name = button.dataset.name;
                        const price = parseFloat(button.dataset.price);
                        const tax = parseFloat(button.dataset.tax);
                        addToCart(id, name, price, tax);
                    });
                });
            })
            .catch(err => {
                console.error("Failed to fetch products:", err);
            });
    });

    document.getElementById("product-search").addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();
        const productCards = document.querySelectorAll(".product-card");

        let anyVisible = false;
        productCards.forEach(card => {
            const name = card.getAttribute("data-name");
            if (name.includes(searchTerm)) {
                card.style.display = "block";
                anyVisible = true;
            } else {
                card.style.display = "none";
            }
        });

        document.getElementById("no-products-message").style.display = anyVisible ? "none" : "block";
    });

    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.querySelector('.clear-cart-btn').addEventListener('click', () => {
        cart = [];
        updateCartUI();
    });

    let selectedPaymentMethod = "Cash"; // default

document.querySelectorAll('.payment-method-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        selectedPaymentMethod = this.dataset.method; // update selected payment method
    });
});

document.querySelector('[data-method="Cash"]').addEventListener('click', () => {
    const totalText = document.getElementById('total-cashier').textContent.replace('Ksh', '').trim();
    const totalAmount = parseFloat(totalText);

    const amountPaid = prompt(`Customer's Total is Ksh${totalAmount}. Enter amount paid:`);

    if (amountPaid !== null && !isNaN(amountPaid)) {
        const change = parseFloat(amountPaid) - totalAmount;
        if (change < 0) {
            alert(`Insufficient amount. Customer still owes Ksh${Math.abs(change).toFixed(2)}`);
        } else {
            alert(`Payment accepted.\nChange to return: Ksh${change.toFixed(2)}`);
            triggerCheckout(); // trigger order submission
        }
    }
});

document.getElementById('mpesa-pay-btn').addEventListener('click', () => {
    const phone = document.getElementById('customer-phone').value.trim();
    const totalText = document.getElementById('total-cashier').textContent;
    const match = totalText.match(/[\d,.]+/);
    const amount = match ? parseFloat(match[0].replace(',', '')) : 0;

    if (!phone || isNaN(amount) || amount <= 0) {
        alert("Enter valid phone number and total amount.");
        return;
    }

    console.log("Sending STK:", { phone, amount });

    fetch('stk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone, amount })
    })
    .then(res => res.json())
    .then(data => {
        if (data.ResponseCode === "0") {
            alert("M-Pesa STK Push sent. Enter your PIN to confirm.");
            setTimeout(triggerCheckout, 5000); // optional delay before confirming order
        } else {
            alert("STK Push failed: " + (data.errorMessage || 'Unknown error.'));
        }
    })
    .catch(err => {
        alert("Failed to reach backend.");
        console.error(err);
    });
});

function triggerCheckout() {
    const customer_name = document.getElementById('customer-name').value.trim();
    const customer_phone = document.getElementById('customer-phone').value.trim();
    const user_id = 1;

    if (!customer_name || !customer_phone || cart.length === 0) {
        alert("Please fill in customer details and cart before checkout.");
        return;
    }

    fetch('submit_order.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        customer_name,
        customer_phone,
        payment_method: selectedPaymentMethod,
        user_id,
        cart
    })
})

    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("Order completed successfully!");
            cart = [];
            updateCartUI();
            document.getElementById('customer-name').value = '';
            document.getElementById('customer-phone').value = '';
        } else {
            alert("Order failed.");
        }
    })
    .catch(err => {
        console.error("Error submitting order:", err);
    });
}
document.getElementById('cashier-checkout-btn').addEventListener('click', triggerCheckout);

</script>


    


</body>
</html>