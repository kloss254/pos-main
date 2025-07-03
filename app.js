if (!localStorage.getItem('users')) {
    const dummyUsers = [
        { username: 'admin', password: 'adminpassword', role: 'admin' },
        { username: 'cashier', password: 'cashierpassword', role: 'cashier' }
    ];
    localStorage.setItem('users', JSON.stringify(dummyUsers));
    console.log("Dummy users initialized in Local Storage."); 
}
let products = JSON.parse(localStorage.getItem("products")) || [
    { id: 1, name: "Milk", price: 2.5, stock: 10, image: "placeholder.jpg", category: "Dairy" },
    { id: 2, name: "Bread", price: 1.2, stock: 5, image: "placeholder.jpg", category: "Baked Goods" },
    { id: 3, name: "Eggs", price: 3.0, stock: 8, image: "placeholder.jpg", category: "Dairy" },
    { id: 4, name: "Cheese", price: 5.0, stock: 12, image: "placeholder.jpg", category: "Dairy" },
    { id: 5, name: "Apples", price: 0.8, stock: 50, image: "placeholder.jpg", category: "Fruits" },
    { id: 6, name: "Bananas", price: 0.5, stock: 30, image: "placeholder.jpg", category: "Fruits" },
    { id: 7, name: "Orange Juice", price: 3.5, stock: 15, image: "placeholder.jpg", category: "Beverages" },
    { id: 8, name: "Coffee", price: 4.0, stock: 20, image: "placeholder.jpg", category: "Beverages" },
    { id: 9, name: "Pasta", price: 2.0, stock: 25, image: "placeholder.jpg", category: "Main Course" },
    { id: 10, name: "Chicken Soup", price: 3.5, stock: 18, image: "placeholder.jpg", category: "Soups" }
];
let cart = JSON.parse(localStorage.getItem("cart")) || [];
let orders = JSON.parse(localStorage.getItem("orders")) || [];
let suppliers = JSON.parse(localStorage.getItem("suppliers")) || [];
// Categories can now be dynamically pulled from products or manually managed
let categories = JSON.parse(localStorage.getItem("categories")) || []; 
if (!localStorage.getItem("categories")) {
    categories = ["Dairy", "Baked Goods", "Fruits", "Breakfast", "Main Course", "Soups"];
    localStorage.setItem("categories", JSON.stringify(categories));
}

function saveProducts() {
    localStorage.setItem("products", JSON.stringify(products));
}

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}

function saveOrders() {
    localStorage.setItem("orders", JSON.stringify(orders));
}

function saveSuppliers() {
    localStorage.setItem("suppliers", JSON.stringify(suppliers));
}

function saveCategories() {
    localStorage.setItem("categories", JSON.stringify(categories));
}

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2);
}

function login(e) {
    e.preventDefault();
    const usernameInput = document.getElementById("username").value.trim();
    const passwordInput = document.getElementById("password").value.trim();
    const errorEl = document.getElementById("message");

    errorEl.textContent = ""; // Clear previous error

    const storedUsers = JSON.parse(localStorage.getItem("users")) || [];
    const foundUser = storedUsers.find(user => user.username === usernameInput && user.password === passwordInput);

    if (foundUser) {
        localStorage.setItem("loggedIn", "true");
        localStorage.setItem("currentUsername", foundUser.username);
        localStorage.setItem("currentUserRole", foundUser.role); // Store the user's role

        if (foundUser.role === 'admin') {
            window.location.href = "index.html";
        } else if (foundUser.role === 'cashier') {
            window.location.href = "cashier.html";
        } else {
            errorEl.textContent = 'Unknown user role. Please contact support.';
            localStorage.removeItem("loggedIn");
        }
    } else {
        errorEl.textContent = "Invalid username or password.";
    }
}

// This 'registerUser' function should ideally reside in register.html's script block.
function registerUser(e) {
    e.preventDefault();

    const username = document.getElementById("reg-username").value.trim();
    const password = document.getElementById("reg-password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();
    const errorMessage = document.getElementById("register-error");
    const successMessage = document.getElementById("register-success");

    errorMessage.style.display = "none";
    successMessage.style.display = "none";

    if (password !== confirmPassword) {
        errorMessage.textContent = "Passwords do not match.";
        errorMessage.style.display = "block";
        return;
    }

    let storedUsers = JSON.parse(localStorage.getItem("users")) || [];

    const userExists = storedUsers.some(user => user.username === username);

    if (userExists) {
        errorMessage.textContent = "Username already exists. Please choose another.";
        errorMessage.style.display = "block";
        return;
    }

    // Default new registrations to 'cashier' role unless a specific admin registration path is created
    storedUsers.push({ username: username, password: password, role: 'cashier' }); 
    localStorage.setItem("users", JSON.stringify(storedUsers));

    successMessage.textContent = "Registration successful! You can now log in.";
    successMessage.style.display = "block";

    document.getElementById("reg-username").value = "";
    document.getElementById("reg-password").value = "";
    document.getElementById("confirm-password").value = "";

    setTimeout(() => {
        window.location.href = "login.html";
    }, 2000);
}

function logout() {
    const confirmLogout = confirm("Are you sure you want to logout?");
    if (confirmLogout) {
        localStorage.removeItem("loggedIn");
        localStorage.removeItem("currentUsername");
        localStorage.removeItem("currentUserRole"); // Clear role on logout
        window.location.href = "login.html";
    }
}
// In your app.js
function showPage(pageId) {
    console.log(`showPage('${pageId}') called.`); // ADD THIS LOG
    // Hide all sections in the main content area
    const sections = document.querySelectorAll('main section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the active section
    const activeSection = document.getElementById(pageId);
    if (activeSection) {
        activeSection.style.display = 'block';
        console.log(`Section '${pageId}' found and displayed.`);
    } else {
        console.warn(`Section '${pageId}' not found in HTML.`);
    }

    // Set the active class on the sidebar link
    setActiveSidebarLink(pageId);

    // Call render functions specific to the page (already there, good)
    if (pageId === 'sales') {
        loadProductsForSale(true);
        updateCartDisplay(true);
    } else if (pageId === 'products') {
        renderProductList();
    } else if (pageId === 'inventory') {
        renderInventory();
    } else if (pageId === 'suppliers') {
        renderSupplierList();
    } else if (pageId === 'category') {
        renderCategoryList();
    } else if (pageId === 'orders') {
        renderOrderHistory();
    } else if (pageId === 'reports') {
        renderReports();
    }
}

// --- Admin Page Navigation and Page Display ---
function setActiveSidebarLink(pageId) {
    const links = document.querySelectorAll('#sidebar .sidebar-menu a');
    links.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(`showPage('${pageId}')`)) {
            link.classList.add('active');
        }
    });
    const mainHeaderTitle = document.querySelector('.main-header h2');
    if (mainHeaderTitle) {
        mainHeaderTitle.textContent = pageId.charAt(0).toUpperCase() + pageId.slice(1);
    }
}

function showPage(pageId) {
    const sections = document.querySelectorAll('main section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the active section
    const activeSection = document.getElementById(pageId);
    if (activeSection) {
        activeSection.style.display = 'block';
    }

    setActiveSidebarLink(pageId);
    if (pageId === 'sales') {
        loadProductsForSale(true); // Renders products for sales (used by both admin and cashier)
        updateCartDisplay(true); // Updates cart display (used by both)
    } else if (pageId === 'products') {
        renderProductList(); // For managing product inventory
    } else if (pageId === 'inventory') {
        renderInventory(); // For viewing low stock
    } else if (pageId === 'suppliers') {
        renderSupplierList();
    } else if (pageId === 'category') { // Corrected from 'categories' to 'category' based on HTML
        renderCategoryList();
    } else if (pageId === 'orders') {
        renderOrderHistory(); // Renamed for clarity
    } else if (pageId === 'reports') {
        renderReports();
    }
}

function renderProductList() { // For the 'Products' admin page
    const list = document.getElementById("product-list-view");
    if (!list) return; // Ensure element exists on current page

    list.innerHTML = "";

    if (products.length === 0) {
        list.innerHTML = "<li>No products added yet.</li>";
        return;
    }

    products.forEach((p) => {
        const li = document.createElement("li");
        li.innerHTML = `
            <span>${p.name} - $${formatCurrency(p.price)} - Stock: ${p.stock}</span>
            <div>
                <button onclick="editProduct(${p.id})">Edit</button>
                <button onclick="deleteProduct(${p.id})" class="delete-btn">Delete</button>
            </div>
        `;
        list.appendChild(li);
    });
    // No need to saveProducts here, it's saved after add/edit/delete actions
}

function addProduct(e) {
    e.preventDefault();

    const nameInput = document.getElementById("product-name");
    const priceInput = document.getElementById("product-price");
    const stockInput = document.getElementById("product-stock");
    const categoryInput = document.getElementById("product-category"); // Assuming this input exists in admin panel
    const imageInput = document.getElementById("product-image"); // Assuming this input exists

    const name = nameInput.value.trim();
    const price = parseFloat(priceInput.value);
    const stock = parseInt(stockInput.value);
    const category = categoryInput ? categoryInput.value.trim() : 'Uncategorized';
    const image = imageInput ? imageInput.value.trim() : 'placeholder.jpg'; // Default or placeholder

    if (!name || isNaN(price) || isNaN(stock) || price <= 0 || stock < 0) {
        alert("Please enter valid product name, a positive price, and non-negative stock.");
        return;
    }

    if (products.some(p => p.name.toLowerCase() === name.toLowerCase())) {
        alert("A product with this name already exists.");
        return;
    }

    const newProduct = {
        id: products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1,
        name,
        price: parseFloat(price.toFixed(2)),
        stock,
        category,
        image // Add image to product data
    };

    products.push(newProduct);
    saveProducts();
    renderProductList(); // Refresh admin product list
    loadProductsForSale(false); // Refresh product buttons in admin sales (pass false to avoid cashier rendering)
    renderInventory(); // Refresh inventory
    // Refresh categories if this product's category is new
    loadCategoriesForCashier(); // This will refresh the cashier categories list
    alert('Product added successfully!');

    nameInput.value = "";
    priceInput.value = "";
    stockInput.value = "";
    if (categoryInput) categoryInput.value = "";
    if (imageInput) imageInput.value = "";
}

function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const newName = prompt("Enter new name for " + product.name + ":", product.name);
    if (newName === null || newName.trim() === "") return;

    // Check for duplicate name if changed, excluding the current product
    if (products.some(p => p.id !== productId && p.name.toLowerCase() === newName.trim().toLowerCase())) {
        alert("A product with this new name already exists.");
        return;
    }

    const newPrice = parseFloat(prompt("Enter new price for " + product.name + ":", product.price));
    if (isNaN(newPrice) || newPrice <= 0) {
        alert("Invalid price.");
        return;
    }

    const newStock = parseInt(prompt("Enter new stock for " + product.name + ":", product.stock));
    if (isNaN(newStock) || newStock < 0) {
        alert("Invalid stock.");
        return;
    }

    const newCategory = prompt("Enter new category for " + product.name + ":", product.category || 'Uncategorized');
    if (newCategory === null) return;

    const newImage = prompt("Enter new image URL for " + product.name + ":", product.image || 'placeholder.jpg');
    if (newImage === null) return;


    product.name = newName.trim();
    product.price = parseFloat(newPrice.toFixed(2));
    product.stock = newStock;
    product.category = newCategory.trim();
    product.image = newImage.trim();

    saveProducts();
    renderProductList();
    loadProductsForSale(false); // Refresh product buttons in admin sales
    renderInventory();
    loadCategoriesForCashier(); // Update cashier categories
    alert('Product updated successfully!');
}

function deleteProduct(productId) {
    if (!confirm("Are you sure you want to delete this product?")) {
        return;
    }
    products = products.filter(p => p.id !== productId);
    saveProducts();
    renderProductList();
    loadProductsForSale(false); // Refresh product buttons in admin sales
    renderInventory();
    loadCategoriesForCashier(); // Update cashier categories
    alert('Product deleted successfully!');
}
let currentCategory = "All"; // Initialize for cashier
const TAX_RATE = 0.05; // 5% tax example

// --- Cashier DOM Elements ---
const productListCashier = document.getElementById('product-list-cashier');
const cartListCashier = document.getElementById('cart-list-cashier');
const subtotalCashierSpan = document.getElementById('subtotal-cashier');
const taxCashierSpan = document.getElementById('tax-cashier');
const totalCashierSpan = document.getElementById('total-cashier');
const customerNameInputCashier = document.getElementById('customer-name');
const customerPhoneInputCashier = document.getElementById('customer-phone');
const productSearchInputCashier = document.getElementById('product-search');
const categoriesNavCashier = document.querySelector('.categories-nav');
const paymentMethodBtns = document.querySelectorAll('.payment-method-btn');
const clearCartBtnCashier = document.querySelector('.clear-cart-btn');
const holdOrderBtnCashier = document.querySelector('.hold-order-btn');


// --- Admin Sales DOM Elements (existing) ---
const productListAdmin = document.getElementById('productList');
const cartListAdmin = document.getElementById("cartList");
const totalElementAdmin = document.getElementById("total");
const customerNameInputAdmin = document.querySelector("#sales-section #customer-name"); // Specific to sales section
const customerPhoneInputAdmin = document.querySelector("#sales-section #customer-phone"); // Specific to sales section


// Function to load products for sale, handles both admin and cashier views
function loadProductsForSale(isAdminView = false, categoryFilter = currentCategory, searchTerm = "") {
    let targetElement = isAdminView ? productListAdmin : productListCashier;

    if (!targetElement) return; // No relevant product list element found on current page

    targetElement.innerHTML = ""; // Clear previous products

    const filteredProducts = products.filter(product => {
        const matchesCategory = (categoryFilter === "All" || product.category === categoryFilter);
        const matchesSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase());
        return matchesCategory && matchesSearch;
    });

    if (filteredProducts.length === 0) {
        targetElement.innerHTML = `<p style="text-align: center; color: #7f8c8d; margin-top: 20px;">No products found.</p>`;
        return;
    }

    if (isAdminView) {
        // Admin view: simple buttons
        filteredProducts.forEach(product => {
            const button = document.createElement("button");
            button.innerHTML = `<strong>${product.name}</strong><br>$${formatCurrency(product.price)}<span>Stock: ${product.stock}</span>`;
            button.onclick = () => addToCart(product.id, 1, true); // Pass isAdminView=true for admin cart
            button.disabled = product.stock <= 0;
            button.classList.add("product-button");
            targetElement.appendChild(button);
        });
    } else {
        // Cashier view: detailed product cards
        filteredProducts.forEach(product => {
            const productCard = document.createElement('div');
            productCard.classList.add('product-card');

            const existingCartItem = cart.find(item => item.id === product.id);

            if (existingCartItem) {
                productCard.classList.add('with-quantity'); // Apply class for quantity controls
                productCard.innerHTML = `
                    <img src="${product.image || 'placeholder.jpg'}" alt="${product.name}">
                    <div class="product-info">
                        <h4>${product.name}</h4>
                        <div class="product-details">
                            <span>${product.category || 'N/A'}</span>
                        </div>
                        <p class="price">$${product.price.toFixed(2)}</p>
                        <div class="quantity-controls">
                            <button class="quantity-minus" data-id="${product.id}">-</button>
                            <span class="quantity">${existingCartItem.quantity}</span>
                            <button class="quantity-plus" data-id="${product.id}">+</button>
                        </div>
                    </div>
                `;
            } else {
                
                productCard.innerHTML = `
    <div class="product-image-container">
        <img src="${product.image}" alt="${product.product_name}">
        <div class="product-name-overlay">${product.product_name}</div>
    </div>
    <p>Ksh ${product.price}</p>
    <button onclick="addToCart(${product.id}, '${product.product_name}', ${product.price}, ${product.tax})">Add</button>
`

            }
            targetElement.appendChild(productCard);
        });
    }
}

// Function to add/update item in cart (now handles quantity +/-)
function addToCart(productId, quantityChange = 1) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingCartItemIndex = cart.findIndex(item => item.id === productId);

    if (existingCartItemIndex > -1) {
        // Item exists, update quantity
        const newQuantity = cart[existingCartItemIndex].quantity + quantityChange;
        if (newQuantity > 0) {
            // Check if there's enough stock
            if (quantityChange > 0 && product.stock === 0) {
                alert("Product is out of stock!");
                return;
            }
            cart[existingCartItemIndex].quantity = newQuantity;
            product.stock -= quantityChange; // Adjust actual product stock
        } else {
            // If new quantity is 0 or less, remove from cart
            product.stock += cart[existingCartItemIndex].quantity; // Return stock
            cart.splice(existingCartItemIndex, 1);
        }
    } else if (quantityChange > 0) {
        // New item, add to cart
        if (product.stock === 0) {
            alert("Product is out of stock!");
            return;
        }
        cart.push({ ...product, quantity: 1 }); // Add with quantity 1
        product.stock--; // Decrease stock
    }

    saveProducts(); // Save updated products (with decreased stock)
    saveCart(); // Save updated cart

    const currentPath = window.location.pathname;
    if (currentPath.includes('index.html') || currentPath === '/') {
        updateCartDisplay(true); // Update admin cart
        loadProductsForSale(true); // Refresh admin product list
    } else if (currentPath.includes('cashier.html')) {
        updateCartDisplay(false); // Update cashier cart
        loadProductsForSale(false, currentCategory, productSearchInputCashier.value); // Refresh cashier product list
    }
}

// Function to remove item from cart
function removeFromCart(productId) { // Now accepts productId
    const itemToRemoveIndex = cart.findIndex(item => item.id === productId);
    if (itemToRemoveIndex === -1) return;

    const itemToRemove = cart[itemToRemoveIndex];
    const product = products.find(p => p.id === itemToRemove.id);

    if (product) {
        product.stock += itemToRemove.quantity; // Add stock back for all removed quantity
        saveProducts(); // Save updated products (with increased stock)
    }

    cart.splice(itemToRemoveIndex, 1); // Remove item from cart array
    saveCart(); // Save updated cart

    const currentPath = window.location.pathname;
    if (currentPath.includes('index.html') || currentPath === '/') {
        updateCartDisplay(true); // Update admin cart
        loadProductsForSale(true); // Refresh admin product list
    } else if (currentPath.includes('cashier.html')) {
        updateCartDisplay(false); // Update cashier cart
        loadProductsForSale(false, currentCategory, productSearchInputCashier.value); // Refresh cashier product list
    }
}


function updateCartDisplay(isAdminView = false) {
    let currentCartList = isAdminView ? cartListAdmin : cartListCashier;
    let currentSubtotalElement = isAdminView ? null : subtotalCashierSpan; // Only cashier has subtotal/tax elements
    let currentTaxElement = isAdminView ? null : taxCashierSpan;
    let currentTotalElement = isAdminView ? totalElementAdmin : totalCashierSpan;

    if (!currentCartList || !currentTotalElement) return; // Neither elements found, perhaps not on a sales page

    currentCartList.innerHTML = ""; // Clear existing cart items
    let subtotal = 0;

    if (cart.length === 0) {
        currentCartList.innerHTML = `<li style="text-align: center; color: #7f8c8d; padding: 20px;">No items in cart.</li>`;
    } else {
        cart.forEach((item) => {
            if (isAdminView) {
                // Admin view: simple list item
                const li = document.createElement("li");
                li.innerHTML = `
                    ${item.name} - $${formatCurrency(item.price)} x ${item.quantity}
                    <button onclick="removeFromCart(${item.id})">Remove</button>
                `;
                currentCartList.appendChild(li);
            } else {
                // Cashier view: detailed cart item (as per design)
                const li = document.createElement('li');
                li.classList.add('cart-item');
                li.innerHTML = `
                    <img src="${item.image || 'placeholder.jpg'}" alt="${item.name}" class="cart-item-img">
                    <div class="cart-item-details">
                        <span class="cart-item-name">${item.name}</span>
                        <span class="cart-item-price">$${item.price.toFixed(2)}</span>
                    </div>
                    <div class="cart-item-quantity-controls">
                        <button class="quantity-minus" data-id="${item.id}">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="quantity-plus" data-id="${item.id}">+</button>
                    </div>
                    <button class="cart-item-remove" data-id="${item.id}"><i class="fas fa-times"></i></button>
                `;
                currentCartList.appendChild(li);
            }
            subtotal += item.price * item.quantity;
        });
    }

    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax;

    if (currentSubtotalElement) { // Only update if these elements exist (cashier page)
        currentSubtotalElement.textContent = subtotal.toFixed(2);
        currentTaxElement.textContent = tax.toFixed(2);
    }
    currentTotalElement.textContent = total.toFixed(2);
}

// Checkout function for Admin page
function checkout() {
    performCheckout(customerNameInputAdmin, customerPhoneInputAdmin, totalElementAdmin);
}

// Checkout function for Cashier page
function checkoutCashier() {
    performCheckout(customerNameInputCashier, customerPhoneInputCashier, totalCashierSpan);
}

function performCheckout(customerNameInput, customerPhoneInput, totalElement) {
    const customerName = customerNameInput ? customerNameInput.value.trim() : 'N/A';
    const customerPhone = customerPhoneInput ? customerPhoneInput.value.trim() : 'N/A';

    if (!customerName || !customerPhone) {
        alert("Please enter customer name and phone number for the order.");
        return;
    }

    if (cart.length === 0) {
        alert("Cart is empty! Please add products before checking out.");
        return;
    }

    const totalAmount = parseFloat(totalElement.textContent);

    const newOrder = {
        id: Date.now(), // Simple unique ID
        customer: { name: customerName, phone: customerPhone },
        items: JSON.parse(JSON.stringify(cart)), // Deep copy cart items
        total: totalAmount,
        date: new Date().toLocaleString()
    };

    orders.push(newOrder);
    saveOrders();

    alert(`Sale completed successfully for ${customerName}! Total: $${formatCurrency(totalAmount)}`);

    // Reset cart and customer info
    cart = [];
    saveCart();

    if (customerNameInput) customerNameInput.value = "";
    if (customerPhoneInput) customerPhoneInput.value = "";

    const currentPath = window.location.pathname;
    if (currentPath.includes('index.html') || currentPath === '/') {
        updateCartDisplay(true);
        loadProductsForSale(true);
        if (document.getElementById("orders") && document.getElementById("orders").offsetParent !== null) {
            renderOrderHistory();
        }
        if (document.getElementById("reports") && document.getElementById("reports").offsetParent !== null) {
            renderReports();
        }
    } else if (currentPath.includes('cashier.html')) {
        updateCartDisplay(false);
        loadProductsForSale(false, currentCategory, productSearchInputCashier.value);
    }
}

// --- Cashier Specific Functions ---
function loadCategoriesForCashier() {
    if (!categoriesNavCashier) return;

    // Dynamically get unique categories from products
    const uniqueCategories = ['All']; // Always start with 'All'
    products.forEach(product => {
        if (product.category && !uniqueCategories.includes(product.category)) {
            uniqueCategories.push(product.category);
        }
    });

    categoriesNavCashier.innerHTML = ''; // Clear existing buttons

    uniqueCategories.forEach(category => {
        const categoryCount = category === 'All'
            ? products.length
            : products.filter(p => p.category === category).length;

        const button = document.createElement('button');
        button.classList.add('category-btn');
        if (category === currentCategory) {
            button.classList.add('active');
        }
        button.dataset.category = category; // Custom data attribute for category
        button.innerHTML = `${category}<span>${categoryCount} Items</span>`;
        categoriesNavCashier.appendChild(button);

        button.addEventListener('click', () => {
            // Remove 'active' from previous category
            document.querySelector('.category-btn.active')?.classList.remove('active');
            // Add 'active' to clicked category
            button.classList.add('active');
            currentCategory = category;
            loadProductsForSale(false, currentCategory, productSearchInputCashier.value); // Reload products based on new category and current search
        });
    });
}

function clearCashierCart() {
    if (confirm("Are you sure you want to clear the entire cart?")) {
        // Return stock for all items in cart before clearing
        cart.forEach(itemInCart => {
            const product = products.find(p => p.id === itemInCart.id);
            if (product) {
                product.stock += itemInCart.quantity;
            }
        });
        saveProducts(); // Save updated product stock

        cart = [];
        localStorage.removeItem('cart');
        updateCartDisplay(false);
        loadProductsForSale(false, currentCategory, productSearchInputCashier.value);
    }
}


// --- Inventory Management (Used by Admin) ---
function renderInventory() {
    const thresholdInput = document.getElementById("thresholdInput");
    const tbody = document.querySelector("tbody");

    if (!tbody || !thresholdInput) return; // Ensure elements exist

    const threshold = parseInt(thresholdInput.value);
    tbody.innerHTML = "";

    if (isNaN(threshold) || threshold < 0) {
        alert("Please enter a valid non-negative number for the stock threshold.");
        thresholdInput.value = 5; // Reset to default
        renderInventory(); // Re-render with default
        return;
    }

    const filteredProducts = products.filter(product => product.stock <= threshold);

    if (filteredProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">No products found below the specified stock threshold.</td></tr>';
        return;
    }

    filteredProducts.forEach(product => {
        const tr = document.createElement("tr");
        const status = product.stock === 0 ? 'Out of Stock' : 'Low Stock';
        const statusClass = product.stock === 0 ? 'out-of-stock-status' : 'low-stock-status'; // Use classes for styling

        tr.innerHTML = `
            <td>${product.name}</td>
            <td>$${formatCurrency(product.price)}</td>
            <td>${product.stock}</td>
            <td class="${statusClass}"><strong>${status}</strong></td>
        `;
        tbody.appendChild(tr);
    });
}

// --- Supplier Management (Used by Admin) ---
function addSupplier(e) {
    e.preventDefault();

    const nameInput = document.getElementById("nameInput");
    const phoneInput = document.getElementById("phoneInput");
    const companyInput = document.getElementById("companyInput");

    const name = nameInput.value.trim();
    const phone = phoneInput.value.trim();
    const company = companyInput.value.trim();

    if (!name || !phone || !company) {
        alert("Please fill in all supplier fields (Name, Phone, Company).");
        return;
    }

    if (suppliers.some(s => s.name.toLowerCase() === name.toLowerCase())) {
        alert("A supplier with this name already exists.");
        return;
    }

    suppliers.push({ name, phone, company });
    saveSuppliers();
    renderSupplierList();
    alert('Supplier added successfully!');

    nameInput.value = "";
    phoneInput.value = "";
    companyInput.value = "";
}

function renderSupplierList() {
    const list = document.getElementById("supplier-list");
    if (!list) return;

    list.innerHTML = "";

    if (suppliers.length === 0) {
        list.innerHTML = "<li>No suppliers added yet.</li>";
        return;
    }

    suppliers.forEach((s, i) => {
        const li = document.createElement("li");
        li.innerHTML = `
            <span>${s.name} - ${s.company} - ${s.phone}</span>
            <button onclick="deleteSupplier(${i})" class="delete-btn">Delete</button>
        `;
        list.appendChild(li);
    });
}

function deleteSupplier(index) {
    if (!confirm("Are you sure you want to delete this supplier?")) {
        return;
    }
    suppliers.splice(index, 1);
    saveSuppliers();
    renderSupplierList();
    alert('Supplier deleted successfully!');
}


// --- Category Management (Used by Admin) ---
function addCategory(e) {
    e.preventDefault();

    const nameInput = document.getElementById("category-name");
    const name = nameInput.value.trim();
    if (!name) {
        alert("Category name is required.");
        return;
    }

    if (categories.includes(name.toLowerCase())) {
        alert("This category already exists.");
        return;
    }

    categories.push(name.toLowerCase());
    saveCategories();
    renderCategoryList();
    // Also refresh categories for cashier view if on that page, or if a product category is added
    loadCategoriesForCashier();
    alert('Category added successfully!');

    nameInput.value = "";
}

function renderCategoryList() {
    const list = document.getElementById("CategoryList");
    if (!list) return;

    list.innerHTML = "";

    if (categories.length === 0) {
        list.innerHTML = "<li>No categories added yet.</li>";
        return;
    }

    categories.forEach((cat, i) => {
        const li = document.createElement("li");
        li.innerHTML = `
            <span>${cat.charAt(0).toUpperCase() + cat.slice(1)}</span>
            <button onclick="deleteCategory(${i})" class="delete-btn">Delete</button>
        `;
        list.appendChild(li);
    });
}

function deleteCategory(index) {
    if (!confirm("Are you sure you want to delete this category?")) {
        return;
    }
    const categoryToDelete = categories[index];
    // Optional: Warn if products are still in this category
    const productsInCategory = products.filter(p => p.category && p.category.toLowerCase() === categoryToDelete);
    if (productsInCategory.length > 0) {
        if (!confirm(`There are ${productsInCategory.length} products associated with this category. Deleting it will set their category to 'Uncategorized'. Continue?`)) {
            return;
        }
        productsInCategory.forEach(p => p.category = 'Uncategorized');
        saveProducts();
        loadProductsForSale(true); // Refresh admin sales if active
        loadProductsForSale(false, currentCategory, productSearchInputCashier?.value || ''); // Refresh cashier products
    }

    categories.splice(index, 1);
    saveCategories();
    renderCategoryList();
    loadCategoriesForCashier(); // Update cashier categories
    alert('Category deleted successfully!');
}


// --- Order History Management (Used by Admin) ---
function renderOrderHistory() { // Renamed from renderOrders
    const list = document.getElementById("list");
    if (!list) return;

    list.innerHTML = "";

    if (orders.length === 0) {
        list.innerHTML = "<li>No orders yet.</li>";
        return;
    }

    const sortedOrders = [...orders].sort((a, b) => new Date(b.date) - new Date(a.date));

    sortedOrders.forEach(order => {
        const li = document.createElement("li");
        li.classList.add("order-item");

        const itemsList = order.items.map(item => `
            <li>${item.name} ($${formatCurrency(item.price)}) x ${item.quantity}</li>
        `).join("");

        li.innerHTML = `
            <h3>Order ID: ${order.id}</h3>
            <p><strong>Date:</strong> ${order.date}</p>
            <p><strong>Customer:</strong> ${order.customer.name} (${order.customer.phone})</p>
            <p><strong>Total:</strong> $${formatCurrency(order.total)}</p>
            <h4>Items:</h4>
            <ul>${itemsList}</ul>
            <button onclick="deleteOrder(${order.id})" class="delete-btn">Delete Order</button>
            <hr>
        `;
        list.appendChild(li);
    });
}

function deleteOrder(orderId) {
    if (!confirm("Are you sure you want to delete this order? This action cannot be undone.")) {
        return;
    }
    orders = orders.filter(order => order.id !== orderId);
    saveOrders();
    renderOrderHistory(); // Re-render the list after deletion
    renderReports(); // Update reports if order deleted
    alert('Order deleted successfully!');
}


// --- Reports Management (Used by Admin) ---
function renderReports() {
    const reportTotalOrders = document.getElementById("reportTotalOrders");
    const reportTotalRevenue = document.getElementById("reportTotalRevenue");
    const reportTotalItems = document.getElementById("reportTotalItems");

    if (!reportTotalOrders || !reportTotalRevenue || !reportTotalItems) return; // Ensure elements exist

    let totalOrders = orders.length;
    let totalRevenue = 0;
    let totalItemsSold = 0;

    orders.forEach(order => {
        totalRevenue += order.total;
        order.items.forEach(item => {
            totalItemsSold += item.quantity;
        });
    });

    reportTotalOrders.textContent = totalOrders;
    reportTotalRevenue.textContent = formatCurrency(totalRevenue);
    reportTotalItems.textContent = totalItemsSold;
}
document.getElementById("barcode-input").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        const barcode = e.target.value.trim();

        fetch(`get_product.php?barcode=${barcode}`)
            .then(res => res.json())
            .then(product => {
                if (product) {
                    addToCart(product.id, product.product_name, product.price);
                } else {
                    alert("Product not found.");
                }
                e.target.value = "";
            });
    }
});
document.getElementById("mpesa-pay-btn").addEventListener("click", function () {
    const phone = document.getElementById("customer-phone").value.trim();

    if (!phone) {
        alert("Please enter customer phone number");
        return;
    }

    fetch("stk.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `phone=${encodeURIComponent(phone)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ResponseCode === "0") {
            alert("STK Push sent successfully! Check your phone.");
        } else {
            alert("Failed to initiate STK Push: " + data.ResponseDescription);
        }
    })
    .catch(err => {
        console.error(err);
        alert("An error occurred. Please try again.");
    });
});


