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
        
        // Determine if this is a drink or food (assuming category_id is stored in data attribute)
        currentItemCategory = this.getAttribute('data-category-id') || 
                            (this.querySelector('.menu-item-prices').textContent.includes('Large') ? 2 : 1);
        
        // Update selection panel
        selectedItemName.textContent = currentSelectedItem.name;
        
        // Set default size and price based on category
        if (currentItemCategory == 2) { // Drinks
            currentSelectedSize = 'Large';
            currentSelectedPrice = currentSelectedItem.regularPrice;
        } else { // Food
            currentSelectedSize = 'Regular';
            currentSelectedPrice = currentSelectedItem.regularPrice;
        }
        
        quantityInput.value = 1;
        notesInput.value = '';
        
        // Update price options
        priceOptions.forEach(option => option.classList.remove('selected'));
        
        // Set the appropriate option as selected
        if (currentItemCategory == 2) { // Drinks
            document.getElementById('regular-option').textContent = 'Large';
            document.getElementById('solo-option').textContent = 'Small';
            document.getElementById('regular-option').classList.add('selected');
        } else { // Food
            document.getElementById('regular-option').textContent = 'Regular';
            document.getElementById('solo-option').textContent = 'Solo';
            document.getElementById('regular-option').classList.add('selected');
        }
        
        // Hide solo option if not available
        document.getElementById('solo-option').style.display = 
            currentSelectedItem.soloPrice ? 'block' : 'none';
        
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

    function printOrder(orderItems, orderTotal) {
        return new Promise((resolve) => {
            // Create a printer-friendly HTML
            const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Order Receipt</title>
                <style>
                    @page {
                        size: 58mm 210mm;
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        font-family: 'Arial Narrow', 'Courier New', monospace;
                        font-weight: bold;
                        font-size: 10px;
                        width: 58mm;
                        margin: 0;
                        padding: 2mm;
                        line-height: 1.2;
                    }
                    .receipt-header {
                        text-align: center;
                        margin-bottom: 3px;
                        padding-bottom: 3px;
                        border-bottom: 1px dashed #000;
                    }
                    .receipt-title {
                        font-weight: bold;
                        font-size: 11px;
                        margin-bottom: 2px;
                        text-transform: uppercase;
                    }
                    .receipt-date {
                        font-weight: bold;
                        font-size: 9px;
                        margin-bottom: 3px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 5px;
                        table-layout: fixed;
                    }
                    th {
                        text-align: left;
                        border-bottom: 1px dashed #000;
                        padding: 1px 0;
                        font-weight: bold;
                        font-size: 10px;
                    }
                    td {
                        padding: 1px 0;
                        vertical-align: top;
                        font-size: 10px;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .col-item {
                        width: 28%;
                    }
                    .col-size {
                        width: 12%;
                    }
                    .col-qty {
                        width: 10%;
                        text-align: center;
                    }
                    .col-price {
                        width: 18%;
                        text-align: right;
                        padding-right: 5px;
                    }
                    .col-notes {
                        width: 30%;
                        word-break: break-word;
                        padding-left: 3px;
                    }
                    .total-row {
                        font-weight: bold;
                        border-top: 1px dashed #000;
                        font-size: 10px;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 5px;
                        font-size: 8px;
                        padding-top: 3px;
                        border-top: 1px dashed #000;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .cut-line {
                        text-align: center;
                        margin: 5px 0;
                        font-size: 10px;
                        white-space: nowrap;
                    }
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
                    </tfoot>
                </table>
                
                <div class="cut-line">••••••••••••••••••••</div>
                
                <div class="footer">
                    Thank you for your order!<br>
                    Please visit us again
                </div>
                
                <script>
                    // Automatically trigger print when loaded
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
});