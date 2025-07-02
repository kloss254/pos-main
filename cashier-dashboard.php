<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS Cashier Terminal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="cashier-styles.css" />
    <style>

        .product-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-card button {
            margin-top: 10px;
            background-color: #2d89ef;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
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
}
        .cashier-main-content {
    max-width: 1300px; /* wider layout */
    margin: 0 auto;
    padding: 20px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* auto responsive */
    gap: 15px;
    justify-content: center;
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
                <li><a href="#" class="sidebar-link"><i class="fas fa-calculator"></i> Accounting</a></li>
                <li><a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
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
            <a href="#" onclick="logout()" class="sidebar-logout" id="cashier-logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                            <button class="payment-method-btn active" data-method="Mpesa"><i class="fas fa-mobile-alt"></i> Mpesa</button>
                        </div>

                        <div class="checkout-actions">
                            <button id="cashier-checkout-btn" class="complete-sale-btn"></i> Complete Sale</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>


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
                        <img src="${product.image}" alt="${product.product_name}">
                        <div class="product-name-overlay">${product.product_name}</div>
                    </div>
                    <p>Ksh ${product.price}</p>
                    <button onclick="addToCart(${product.id}, '${product.product_name}', ${product.price}, ${product.tax})">Add</button>
                `;

                productList.appendChild(productCard);
            });
        });

    let cart = [];

    function addToCart(product_id, name, price, tax) {
        const quantity = parseInt(document.getElementById('order-qty').value) || 1;
        const discount = parseInt(document.getElementById('order-discount').value) || 0;
        cart.push({ product_id, name, price, tax, quantity, discount });
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
            li.textContent = `${item.name} x${item.quantity} - Ksh${lineTotal.toFixed(2)} (Discount: Ksh${item.discount})`;
            cartList.appendChild(li);
        });
        const tax = subtotal * 0.05;
        const total = subtotal + tax;
        document.getElementById('subtotal-cashier').textContent = `Ksh${subtotal.toFixed(2)}`;
        document.getElementById('tax-cashier').textContent = `Ksh${tax.toFixed(2)}`;
        document.getElementById('total-cashier').textContent = `Ksh${total.toFixed(2)}`;
    }

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

    document.getElementById('cashier-checkout-btn').addEventListener('click', () => {
        const customer_name = document.getElementById('customer-name').value.trim();
        const customer_phone = document.getElementById('customer-phone').value.trim();
        const payment_method = document.querySelector('.payment-method-btn.active')?.dataset.method;
        const user_id = 1;

        if (!customer_name || !customer_phone || cart.length === 0) {
            alert("Please fill in customer details and cart before checkout.");
            return;
        }

        fetch('submit_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_name, customer_phone, payment_method, user_id, cart })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Order completed successfully!");
                cart = [];
                updateCartUI();
                document.getElementById('customer-name').value = '';
                document.getElementById('customer-phone').value = '';
            }
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

    function printReceipt(customer_name, cart, total) {
    let receiptWindow = window.open('', '_blank');
    receiptWindow.document.write('<html><head><title>Receipt</title><style>body{font-family:monospace;padding:20px;}h2{text-align:center;}table{width:100%;border-collapse:collapse;}td,th{padding:8px;border:1px solid #ccc;}tfoot{font-weight:bold;}</style></head><body>');
    receiptWindow.document.write(`<h2>Receipt for ${customer_name}</h2>`);
    receiptWindow.document.write('<table><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Discount</th><th>Total</th></tr></thead><tbody>');
    
    let subtotal = 0;
    cart.forEach(item => {
        let lineTotal = (item.price * item.quantity) - item.discount;
        subtotal += lineTotal;
        receiptWindow.document.write(`<tr>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>${item.price}</td>
            <td>${item.discount}</td>
            <td>${lineTotal.toFixed(2)}</td>
        </tr>`);
    });

    let tax = subtotal * 0.05;
    let grandTotal = subtotal + tax;
    receiptWindow.document.write(`</tbody><tfoot><tr><td colspan="4">Subtotal</td><td>Ksh${subtotal.toFixed(2)}</td></tr>
                                  <tr><td colspan="4">Tax (5%)</td><td>Ksh${tax.toFixed(2)}</td></tr>
                                  <tr><td colspan="4">Total</td><td>Ksh${grandTotal.toFixed(2)}</td></tr></tfoot></table>`);
    receiptWindow.document.write('</body></html>');
    receiptWindow.document.close();
    receiptWindow.print();
}


    </script>
</body>
</html>
