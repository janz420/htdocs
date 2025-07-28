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

    const paymentAmountInput = document.getElementById('payment-amount');
    const displayTotal = document.getElementById('display-total');
    const displayAmount = document.getElementById('display-amount');
    const displayChange = document.getElementById('display-change');

    
    let orderItems = JSON.parse(localStorage.getItem('orderItems')) || [];
    let currentSelectedItem = null;
    let currentSelectedSize = 'Regular'; // Default size
    let currentSelectedPrice = 0;
    let currentItemCategory = null; // To track if it's food or drink
    
    // Initialize order table
    updateOrderTable();

    // Handle category filtering with AJAX
    categoryFilterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            
            // Update active button styling
            categoryFilterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Reset selection panel state
            selectionPanel.style.display = 'none';
            document.querySelectorAll('.menu-item-card').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Reset price options visibility (important!)
            document.querySelector('.price-options').style.display = 'block';
            document.getElementById('regular-option').style.display = 'block';
            document.getElementById('solo-option').style.display = 'block';
            
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
    });
    
    // Use event delegation for menu items (works for dynamically loaded content)
    document.getElementById('menu-items-container').addEventListener('click', function(e) {
        const menuItemCard = e.target.closest('.menu-item-card');
        if (menuItemCard) {
            selectMenuItem.call(menuItemCard);
        }
    });
    
    // Select menu item
    function selectMenuItem() {
        // Remove previous selection
        document.querySelectorAll('.menu-item-card').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selection to clicked item
        this.classList.add('selected');
        
        // Get item details from data attributes
        currentSelectedItem = {
            id: this.getAttribute('data-id'),
            name: this.getAttribute('data-name'),
            regularPrice: parseFloat(this.getAttribute('data-regular-price')),
            soloPrice: this.getAttribute('data-solo-price') ? 
                    parseFloat(this.getAttribute('data-solo-price')) : null
        };
        
        // Get category ID (default to 1 if not specified)
        currentItemCategory = parseInt(this.getAttribute('data-category-id')) || 1;
        
        // Update selection panel
        selectedItemName.textContent = currentSelectedItem.name;
        
        // Get price option elements
        const regularOption = document.getElementById('regular-option');
        const soloOption = document.getElementById('solo-option');
        const priceOptionsContainer = document.querySelector('.price-options');
        
        // Always show the container first, then hide if needed
        priceOptionsContainer.style.display = 'block';
        regularOption.style.display = 'block';
        soloOption.style.display = 'block';
        
        // Reset all options
        document.querySelectorAll('.price-option').forEach(option => option.classList.remove('selected'));
        
        // Handle different categories
        if (currentItemCategory === 2) { // Drinks
            currentSelectedSize = 'Large';
            currentSelectedPrice = currentSelectedItem.regularPrice;
            
            regularOption.textContent = 'Large';
            soloOption.textContent = 'Small';
            regularOption.classList.add('selected');
            soloOption.style.display = currentSelectedItem.soloPrice ? 'block' : 'none';
        } 
        else if (currentItemCategory === 3) { // Addon
            currentSelectedSize = 'Addon';
            currentSelectedPrice = currentSelectedItem.regularPrice;
            
            // Hide the entire price options container for addons
            priceOptionsContainer.style.display = 'none';
        }
        else { // Food (default)
            currentSelectedSize = 'Regular';
            currentSelectedPrice = currentSelectedItem.regularPrice;
            
            regularOption.textContent = 'Regular';
            soloOption.textContent = 'Solo';
            regularOption.classList.add('selected');
            soloOption.style.display = currentSelectedItem.soloPrice ? 'block' : 'none';
        }
        
        // Reset quantity and notes
        quantityInput.value = 1;
        notesInput.value = '';
        
        // Show selection panel
        selectionPanel.style.display = 'block';
    }
    
    // Handle price option selection
    priceOptions.forEach(option => {
        option.addEventListener('click', function() {
            priceOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            currentSelectedSize = this.textContent;
            currentSelectedPrice = currentSelectedSize === 'Regular' || currentSelectedSize === 'Large' ? 
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
                notes: notes,
                category: currentItemCategory // Store category for reference
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
    paymentAmountInput.addEventListener('input', updatePaymentInfo);
    function updateOrderTable() {
        // Clear the table
        orderTableBody.innerHTML = '';
        let orderTotal = 0;
        
        // Add each item to the table
        orderItems.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td hidden>${item.id}</td>
                <td>${item.name}</td>
                <td>${item.size}</td>
                <td>₱${item.price.toFixed(2)}</td>
                <td>
                    <input type="number" class="quantity-input" 
                           value="${item.quantity}" min="1" 
                           data-index="${index}">
                </td>
                <td>₱${item.subtotal.toFixed(2)}</td>
                <td style="width:2%">${item.notes || ''}</td>
                <td>
                    <button class="btn btn-delete remove-item fa fa-trash" 
                            data-index="${index}"></button>
                </td>
            `;
            orderTableBody.appendChild(row);
            
            // Add to total
            orderTotal += item.subtotal;
            updatePaymentInfo();
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
    document.getElementById('submit-order').addEventListener('click', async function() {
        if (orderItems.length === 0) {
            alert('Please add items to the order first.');
            return;
        }

        const submitBtn = document.getElementById('submit-order');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            const response = await fetch('submit_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    orderItems: orderItems
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to submit order');
            }

            // Success - print the order
            await printOrder(data.orderDetails, data.orderTotal);
            
            // Clear the order
            orderItems = [];
            localStorage.removeItem('orderItems');
            updateOrderTable();
            
            alert('Order submitted successfully!');
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Order';
        }
    });

    // Update the printOrder function to include payment info
    function printOrder(orderItems, orderTotal) {
        return new Promise((resolve) => {
            const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
            const change = paymentAmount - orderTotal;
            
            const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Order Receipt</title>
                <style>
                    /* ... (existing styles) ... */
                </style>
            </head>
            <body>
                <div class="receipt-header">
                    <div class="receipt-title">TUPAD BALAY</div>
                    <div class="receipt-date">${new Date().toLocaleString()}</div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th class="col-item">Item</th>
                            <th class="col-size">Size</th>
                            <th class="col-qty">Qty</th>
                            <th class="col-price">Price</th>
                            <th class="col-notes">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orderItems.map(item => `
                            <tr>
                                <td class="col-item">${item.name}</td>
                                <td class="col-size">${item.size}</td>
                                <td class="col-qty">${item.quantity}</td>
                                <td class="col-price">₱${item.price}</td>
                                <td class="col-notes">${item.notes || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3"></td>
                            <td>Total:</td>
                            <td class="text-right">₱${orderTotal.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>Amount:</td>
                            <td class="text-right">₱${paymentAmount.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>Change:</td>
                            <td class="text-right">₱${change >= 0 ? change.toFixed(2) : '0.00'}</td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="cut-line">••••••••••••••••••••</div>
                
                <div class="footer">
                    Thank you for your order!<br>
                    Please visit us again
                </div>
                
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 100);
                        }, 100);
                    };
                </script>
            </body>
            </html>
            `;

            // Open print window
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Resolve the promise when printing is done
            printWindow.onbeforeunload = function() {
                resolve();
            };
        });
    }
    function updatePaymentInfo() {
        const orderTotal = orderItems.reduce((sum, item) => sum + item.subtotal, 0);
        const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
        const change = paymentAmount - orderTotal;
        
        displayTotal.textContent = `₱${orderTotal.toFixed(2)}`;
        displayAmount.textContent = `₱${paymentAmount.toFixed(2)}`;
        displayChange.textContent = `₱${change >= 0 ? change.toFixed(2) : '0.00'}`;
        
        // Highlight change if negative (insufficient payment)
        displayChange.style.color = change < 0 ? 'red' : '';
    }
});