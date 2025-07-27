document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.btn-filter');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button styling
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // AJAX request to filter items
            if(category === 'all') {
                window.location.href = window.location.pathname;
            } else {
                fetchMenuItems(category);
            }
        });
    });
    
    function fetchMenuItems(category) {
        fetch(`filter_menu.php?category=${encodeURIComponent(category)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('menu-items-container').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
    }
});
