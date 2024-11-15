document.addEventListener("DOMContentLoaded", function() {
    const backToLoginButton = document.getElementById("go_back_login");
    const backToHomeButton = document.getElementById('go_back_home');

    // Handle "Go Back" button click to redirect to index.html
    backToHomeButton.addEventListener("click", function() {
        window.location.href = 'index.html';
    });

    backToLoginButton.addEventListener("click", function() {
        window.location.href = 'login.html'; // Redirect to the login page
    });


    const registerForm = document.getElementById('register_form');

    // Add an event listener for form submission
    registerForm.addEventListener('submit', function(event) {
        // Prevent the default form submission
        event.preventDefault();

        // Get form data
        const formData = new FormData(registerForm);
        
        // Make an AJAX request using Fetch API
        fetch('api/register.php', {  // Make sure this points to the correct PHP file
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();  // Parse the JSON response
        })
        .then(data => {
            if (data.success) {
                // Redirect to a success page or show a success message
                alert('Registration successful!');
                console.log('Redirecting to portal...');
                window.location.href = 'portal.html'; // Redirect to the portal
            } else {
                // If registration fails, display an error message
                alert('Registration failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    });
});
