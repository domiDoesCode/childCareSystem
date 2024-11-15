document.addEventListener("DOMContentLoaded", function () {
    // Helper function to retrieve cookie value by name
    function getCookie(name) {
        console.log("Available cookies:", document.cookie);
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null; // Return null if cookie not found
    }

    // Check if the user is already logged in via cookie
    const userToken = getCookie("user_token");
    console.log("User Token:", userToken); // Log the cookie value

    const loginButton = document.getElementById("login_button");
    const portalButton = document.getElementById("portal_button");

    if (userToken) {
        console.log("User is already logged in. Hiding login button, and showing Portal button");

        loginButton.style.display = "none";
        portalButton.style.display = "block";

    } else {
        console.log("No valid cookie found. Showing login button, hiding portal button");

        // Show login button and hide portal button
        loginButton.style.display = "block";
        portalButton.style.display = "none";
    }

    // Set up the login button click event
    if (loginButton) {
        loginButton.addEventListener("click", function () {
            console.log("Redirecting to login page.");
            window.location.href = 'login.html'; // Redirect to login page
        });
    }

    if (portalButton) {
        portalButton.addEventListener("click", function () {
            console.log("Redirecting to portal page.");
            window.location.href = 'portal.html'; // Redirect to login page
        });
    }

    // Set up the "Read More" buttons
    const readMoreButtons = document.querySelectorAll(".read_more_btn");
    if (readMoreButtons.length > 0) {
        readMoreButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                const readMoreContainer = this.nextElementSibling;
                readMoreContainer.style.display = 
                    readMoreContainer.style.display === "block" ? "none" : "block";
            });
        });
    } else {
        console.error("No 'Read More' buttons found.");
    }
});
