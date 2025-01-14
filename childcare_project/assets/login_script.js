document.addEventListener("DOMContentLoaded", function() {
    const backButton = document.getElementById("back_button");
    const loginForm = document.getElementById('login_form');
    const registerButton = document.getElementById("register_button");

    // Handle "Go Back" button click to redirect to index.html
    backButton.addEventListener("click", function() {
        window.location.href = 'index.html';
    });

    registerButton.addEventListener("click", function() {
        window.location.href = 'register.html'; // Redirect to the login page
    });

    // Function to validate JWT on page load
    function checkLoggedIn() {
        const token = localStorage.getItem('jwt');
        if (token) {
            // Validate the token by calling the validate_jwt.php endpoint
            fetch('../api/validate_jwt.php', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'valid') {
                    window.location.href = 'portal.html'; // Redirect if valid
                    
                } else {
                    localStorage.removeItem('jwt'); // Remove invalid token
                }
            })
            .catch(error => {
                console.error('JWT validation error:', error);
                localStorage.removeItem('jwt'); // Clean up if there's an error
            });
        }
    }

    // Call checkLoggedIn on page load to see if the user is already logged in
    checkLoggedIn();

    // Handle login form submission
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission behavior

        // Create FormData object from the form
        const formData = new FormData(loginForm);

        // Fetch request to the login.php API
        fetch('../api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text()) // Read raw response as text
        .then(text => {
            console.log("Raw response:", text); // Log raw response for debugging
            try {
                // Parse the JSON response
                const data = JSON.parse(text);
    
                if (data.status === 'success') {
                    console.log('Login successful:', data.token);
    
                    // Store the JWT token securely
                    localStorage.setItem('jwt', data.token); 
    
                    // Redirect to portal page after successful login
                    window.location.href = 'portal.html';
                } else {
                    // If login fails, display an error message
                    alert('Login failed: ' + data.message);
                }
            } catch (error) {
                console.error('Error parsing JSON:', error);
                alert('An error occurred while processing your login request.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error); // Handle fetch errors
        });
    });
});
