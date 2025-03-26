document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Collect form data using FormData
        const formData = new FormData(contactForm);
        
        // Send form data via AJAX
        fetch('contact-handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Show alert based on response status
            if (data.status === 'success') {
                alert(data.message);
                contactForm.reset();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was a problem submitting the form.');
        });
    });
});