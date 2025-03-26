document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const adminTabs = document.querySelectorAll('.admin-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    adminTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            adminTabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Logout functionality (if not handled server-side)
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'login.php';
            }
        });
    }

    // Form validation for add event form
    const addEventForm = document.getElementById('addEventForm');
    if (addEventForm) {
        addEventForm.addEventListener('submit', function(e) {
            // Basic form validation
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            // Image file validation
            const imageInput = document.getElementById('eventImage');
            if (imageInput.files.length === 0) {
                isValid = false;
                imageInput.classList.add('error');
            } else {
                // Check file type and size
                const file = imageInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    alert('Please upload a valid image (JPEG, PNG, or GIF)');
                }

                if (file.size > maxSize) {
                    isValid = false;
                    alert('Image size should not exceed 5MB');
                }
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields and upload a valid image.');
            }
        });
    }

    // Dynamic department selection
    const departmentSelect = document.getElementById('eventDepartment');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            console.log('Selected department:', this.value);
        });
    }

    // Optional: Date validation to prevent past dates
    const dateInput = document.getElementById('eventDate');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            // Remove time component for accurate comparison
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                alert('Please select a future date');
                this.value = ''; // Clear the input
            }
        });
    }
});
