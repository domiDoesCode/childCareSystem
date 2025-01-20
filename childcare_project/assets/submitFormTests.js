// Mock functions
function checkUserRole(role, action) {
    // Mock for testing purposes
    return role === 1 || role === 2;
}

function showLoadingSpinner() {
    console.log("Loading spinner shown");
}

function hideLoadingSpinner() {
    console.log("Loading spinner hidden");
}

global.fetch = jest.fn();  // Mocking the fetch API

// The function you provided
function submitForm(form, section) {
    if (!checkUserRole(1, 'submit this form') && !checkUserRole(2, 'submit this form')) return; // Prevent unauthorized users

    const childId = form.dataset.childId; // Get the associated child ID
    if (!childId) {
        console.error('Child ID is missing in the form dataset.');
        return;
    }
    const formData = new FormData();

    // Add common fields
    formData.append('child_id', childId);

    // Handle section-specific fields
    if (section === 'diet') {
        const mealType = form.querySelector('[name="meal_type"]').value;
        const foodItems = Array.from(form.querySelector('[name="food_items[]"]').selectedOptions).map(opt => opt.value);

        if (!mealType || foodItems.length === 0) {
            alert('Please select a meal type and at least one food item.');
            return;
        }

        formData.append('meal_type_id', mealType);
        foodItems.forEach(item => formData.append('food_item_ids[]', item));
    }

    // Handle other sections (sleep, activities, health, etc.)

    // Debugging: Log FormData contents
    console.log('Submitting the following data:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    showLoadingSpinner(); // Show the spinner before starting the fetch
    fetch(`../api/${section}.php?child_id=${childId}`, {
        method: 'POST',
        headers: {
            Authorization: `Bearer ${token}`,
        },
        body: formData,
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then((data) => {
        if (data.status === 'success') {
            alert(`${section.charAt(0).toUpperCase() + section.slice(1)} entry added successfully!`);
        } else {
            alert(`Error: ${data.error}`);
            console.error(data.error);
        }
    })
    .catch((error) => console.error(`Error submitting ${section} data:`, error))
    .finally(() => {
        hideLoadingSpinner(); // Always hide the spinner after submission
    });
}


// --- Unit Tests ----
function testSubmitFormWithValidData() {
    // Setup a mock form and mock data
    const form = document.createElement('form');
    form.dataset.childId = "123";
    form.innerHTML = `
        <input name="meal_type" value="1" />
        <select name="food_items[]" multiple>
            <option value="1" selected>Food 1</option>
            <option value="2" selected>Food 2</option>
        </select>
    `;
    
    // Mock the alert function
    const alertMock = jest.spyOn(window, 'alert').mockImplementation(() => {});

    // Mock fetch to simulate success
    global.fetch.mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ status: 'success' }),
    });

    submitForm(form, 'diet');

    // Assert that fetch was called with the correct parameters
    setTimeout(() => {
        expect(global.fetch).toHaveBeenCalledWith(
            expect.stringContaining('diet.php?child_id=123'),
            expect.objectContaining({
                method: 'POST',
                body: expect.any(FormData),
            })
        );

        // Assert success message was shown
        expect(alertMock).toHaveBeenCalledWith('Diet entry added successfully!');
        alertMock.mockRestore();
    }, 1000);
}

function testSubmitFormWithMissingMealType() {
    // Setup a mock form with missing meal type
    const form = document.createElement('form');
    form.dataset.childId = "123";
    form.innerHTML = `
        <input name="meal_type" value="" />
        <select name="food_items[]" multiple>
            <option value="1" selected>Food 1</option>
        </select>
    `;

    // Mock alert
    const alertMock = jest.spyOn(window, 'alert').mockImplementation(() => {});

    submitForm(form, 'diet');
    // Expect an alert for missing meal type
    expect(alertMock).toHaveBeenCalledWith('Please select a meal type and at least one food item.');
    alertMock.mockRestore();
}

function testSubmitFormWithoutChildId() {
    // Setup a mock form without child ID
    const form = document.createElement('form');
    form.dataset.childId = ""; // No child ID

    const alertMock = jest.spyOn(window, 'alert').mockImplementation(() => {});

    submitForm(form, 'diet');
    expect(console.error).toHaveBeenCalledWith('Child ID is missing in the form dataset.');
    alertMock.mockRestore();
}

// Run the tests
testSubmitFormWithValidData();
testSubmitFormWithMissingMealType();
testSubmitFormWithoutChildId();
