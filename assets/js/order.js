document.addEventListener('DOMContentLoaded', function() {
    const orderTableBody = document.getElementById('order-items');
    const orderTotalElement = document.getElementById('order-total');
    const categoryFilterButtons = document.querySelectorAll('.btn-filter');
    const selectionPanel = document.getElementById('selection-panel');
    const selectedItemName = document.getElementById('selected-item-name');
    const quantityInput = document.getElementById('quantity');
    const notesInput = document.getElementById('notes');
    const cancelButton = document.getElementById('cancel-selection');
    const addToOrderButton = document.getElementById('add-to-order');
    const priceOptions = document.querySelectorAll('.price-option');
    
    let orderItems = JSON.parse(localStorage.getItem('orderItems')) || [];
    let currentSelectedItem = null;
    let currentSelectedSize = 'Regular';
    let currentSelectedPrice = 0;
    
    // Initialize order table
    updateOrderTable();
    
    // Handle category filtering with AJAX
    categoryFilterButtons.forEach(button => {
    document.addEventListener('DOMContentLoaded', function() {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            
            // Update active button styling
            categoryFilterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Load menu items via AJAX
            fetch(`filter_menu.php?category=${category}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('menu-items-container').innerHTML = data;
                    
                    // Reattach event listeners to menu items
                    document.querySelectorAll('.menu-item-card').forEach(item => {
                        item.addEventListener('click', selectMenuItem);
                    });
                })
                .catch(error => console.error('Error:', error));
        });
          // Use event delegation for menu items (works for dynamically loaded content)
        document.getElementById('menu-items-container').addEventListener('click', function(e) {
            const menuItemCard = e.target.closest('.menu-item-card');
            if (menuItemCard) {
                // Remove previous selection
                document.querySelectorAll('.menu-item-card').forEach(item => {
                    item.classList.remove('selected');
                });
                
                // Add selection to clicked item
                menuItemCard.classList.add('selected');
                
                // Get item details from data attributes
                currentSelectedItem = {
                    id: menuItemCard.getAttribute('data-id'),
                    name: menuItemCard.getAttribute('data-name'),
                    regularPrice: parseFloat(menuItemCard.getAttribute('data-regular-price')),
                    soloPrice: menuItemCard.getAttribute('data-solo-price') ? 
                            parseFloat(menuItemCard.getAttribute('data-solo-price')) : null
                };
                
                // Update selection panel
                selectedItemName.textContent = currentSelectedItem.name;
                currentSelectedSize = 'Regular';
                currentSelectedPrice = currentSelectedItem.regularPrice;
                quantityInput.value = 1;
                notesInput.value = '';
                
                // Update price options
                priceOptions.forEach(option => option.classList.remove('selected'));
                document.getElementById('regular-option').classList.add('selected');
                
                // Hide solo option if not available
                document.getElementById('solo-option').style.display = 
                    currentSelectedItem.soloPrice ? 'block' : 'none';
                
                // Show selection panel
                selectionPanel.style.display = 'block';
            }
        });
    });
    
    // Select menu item
    function selectMenuItem() {
        // Remove previous selection
        document.querySelectorAll('.menu-item-card').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selection to clicked item
        this.classList.add('selected');
        
        // Get item details
        const itemId = this.getAttribute('data-id');
        const itemName = this.querySelector('.menu-item-name').textContent;
        const priceText = this.querySelector('.menu-item-prices').textContent;
        
        // Extract prices
        const regularPriceMatch = priceText.match(/Regular: ₱([\d.]+)/);
        const soloPriceMatch = priceText.match(/Solo: ₱([\d.]+)/);
        
        const regularPrice = regularPriceMatch ? parseFloat(regularPriceMatch[1]) : 0;
        const soloPrice = soloPriceMatch ? parseFloat(soloPriceMatch[1]) : null;
        
        // Store current selection
        currentSelectedItem = {
            id: itemId,
            name: itemName,
            regularPrice: regularPrice,
            soloPrice: soloPrice
        };
        
        // Update selection panel
        selectedItemName.textContent = itemName;
        
        // Reset selection
        currentSelectedSize = 'Regular';
        currentSelectedPrice = regularPrice;
        quantityInput.value = 1;
        notesInput.value = '';
        
        // Update price options
        priceOptions.forEach(option => option.classList.remove('selected'));
        document.getElementById('regular-option').classList.add('selected');
        
        // Hide solo option if not available
        document.getElementById('solo-option').style.display = soloPrice ? 'block' : 'none';
        
        // Show selection panel
        selectionPanel.style.display = 'block';
    }
    
    // Attach event listeners to menu items
    document.querySelectorAll('.menu-item-card').forEach(item => {
        item.addEventListener('click', selectMenuItem);
    });
    
    // Handle price option selection
    priceOptions.forEach(option => {
        option.addEventListener('click', function() {
            priceOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            currentSelectedSize = this.getAttribute('data-size');
            currentSelectedPrice = currentSelectedSize === 'Regular' ? 
                currentSelectedItem.regularPrice : 
                currentSelectedItem.soloPrice;
        });
    });
    
    // Cancel selection
    cancelButton.addEventListener('click', function() {
        selectionPanel.style.display = 'none';
        document.querySelectorAll('.menu-item-card').forEach(item => {
            item.classList.remove('selected');
        });
    });
    
    // Add to order
    addToOrderButton.addEventListener('click', function() {
        if (!currentSelectedItem) return;
        
        const quantity = parseInt(quantityInput.value);
        const notes = notesInput.value.trim();
        
        if (quantity < 1) {
            alert('Please enter a valid quantity');
            return;
        }
        
        // Check if item already exists in order
        const existingItemIndex = orderItems.findIndex(item => 
            item.id === currentSelectedItem.id && 
            item.size === currentSelectedSize &&
            item.notes === notes);
        
        if (existingItemIndex >= 0) {
            // Update existing item
            orderItems[existingItemIndex].quantity += quantity;
            orderItems[existingItemIndex].subtotal = 
                orderItems[existingItemIndex].quantity * currentSelectedPrice;
        } else {
            // Add new item to order
            orderItems.push({
                id: currentSelectedItem.id,
                name: currentSelectedItem.name,
                size: currentSelectedSize,
                price: currentSelectedPrice,
                quantity: quantity,
                subtotal: quantity * currentSelectedPrice,
                notes: notes
            });
        }
        
        // Save to localStorage
        localStorage.setItem('orderItems', JSON.stringify(orderItems));
        updateOrderTable();
        
        // Reset selection
        selectionPanel.style.display = 'none';
        document.querySelectorAll('.menu-item-card').forEach(item => {
            item.classList.remove('selected');
        });
    });
    
    function updateOrderTable() {
        // Clear the table
        orderTableBody.innerHTML = '';
        let orderTotal = 0;
        
        // Add each item to the table
        orderItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.id}</td>
                <td>${item.name}</td>
                <td>${item.size}</td>
                <td>${item.price.toFixed(2)}</td>
                <td>
                    <input type="number" class="quantity-input" 
                           value="${item.quantity}" min="1" 
                           data-index="${index}">
                </td>
                <td>${item.subtotal.toFixed(2)}</td>
                <td>${item.notes || ''}</td>
                <td>
                    <button class="btn btn-delete remove-item" 
                            data-index="${index}">Remove</button>
                </td>
            `;
            orderTableBody.appendChild(row);
            
            // Add to total
            orderTotal += item.subtotal;
        });
        
        // Update total
        orderTotalElement.textContent = orderTotal.toFixed(2);
        
        // Add event listeners to quantity inputs
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const newQuantity = parseInt(this.value);
                
                if (newQuantity > 0) {
                    orderItems[index].quantity = newQuantity;
                    orderItems[index].subtotal = 
                        newQuantity * orderItems[index].price;
                    localStorage.setItem('orderItems', JSON.stringify(orderItems));
                    updateOrderTable();
                }
            });
        });
        
        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                orderItems.splice(index, 1);
                localStorage.setItem('orderItems', JSON.stringify(orderItems));
                updateOrderTable();
            });
        });
    }
    
    // Submit order
    document.getElementById('submit-order').addEventListener('click', function() {
        if (orderItems.length === 0) {
            alert('Please add items to the order first.');
            return;
        }
        
        // Here you would typically send the order data to the server
        console.log('Submitting order:', orderItems);
        alert(`Order submitted! Total: ₱${orderTotal.toFixed(2)}`);
        
        // Clear the order
        orderItems = [];
        localStorage.removeItem('orderItems');
        updateOrderTable();
    });
});
