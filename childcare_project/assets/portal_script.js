document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('jwt');
    let userRole = null;
    const menuSection = document.getElementById('menu-section');
    const roomSection = document.getElementById('room-section');
    const dataSection = document.getElementById('data-section');
    const childrenList = document.getElementById('children-list');
    const roomList = document.getElementById('room-list');
    const backToRoomsButton = document.getElementById('back-to-rooms');

    // Decode JWT Token
    if (token) {
        try {
            const decodedToken = JSON.parse(atob(token.split('.')[1]));
            userRole = decodedToken.data.role;
        } catch (error) {
            console.error('Error decoding token:', error);
        }
    }

    /** 
     * Reset portal state to show room selection.
     */
    function resetPortalState() {
        menuSection.style.display = 'none';
        roomSection.style.display = 'block';
        dataSection.style.display = 'none';
        childrenList.innerHTML = '';
    }

    /**
     * Fetch and display rooms.
     */
    fetch('./api/room_selection.php', {
        method: 'GET',
        headers: {
            Authorization: `Bearer ${token}`,
        },
    })
        .then((response) => response.json())
        .then((data) => {
            roomList.innerHTML = '';
            data.rooms.forEach((room) => {
                const roomButton = document.createElement('button');
                roomButton.textContent = room.name;
                roomButton.addEventListener('click', () => loadChildrenForRoom(room.id));
                roomList.appendChild(roomButton);
            });
        })
        .catch((error) => console.error('Error fetching rooms:', error));

    /**
     * Load and display children for a room.
     */
    function loadChildrenForRoom(roomId) {
        resetPortalState();
        roomSection.style.display = 'none';
        dataSection.style.display = 'block';
        menuSection.style.display = 'block';

        backToRoomsButton.addEventListener('click', () => {
            resetPortalState();
        });

        fetch(`./api/children.php?room_id=${roomId}`, {
            method: 'GET',
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                childrenList.innerHTML = '';
                data.children.forEach((child) => {
                    createChildCard(child);
                    createChildForm(child);
                });
            })
            .catch((error) => console.error('Error loading children:', error));
    }

    /**
     * Create a child card with profile functionality.
     */
    function createChildCard(child) {
        const childCard = document.createElement('div');
        childCard.className = 'child-card';

        const childName = document.createElement('h3');
        childName.textContent = child.name;
        childCard.appendChild(childName);

        // Add "View Profile" button
        const viewProfileButton = document.createElement('button');
        viewProfileButton.type = 'button'; // Avoid triggering form submission
        viewProfileButton.textContent = 'View Profile';
        viewProfileButton.addEventListener('click', () => showChildProfile(child.id, child.name));
        childCard.appendChild(viewProfileButton);

        // Attach child ID to the card for later reference
        childCard.dataset.childId = child.id;

        // Append the child card to the children list
        childrenList.appendChild(childCard);
    }

    /**
     * Create a form container for the child.
     */
    function createChildForm(child) {
        const formContainer = document.createElement('div');
        formContainer.className = 'child-form';
        formContainer.dataset.childId = child.id;
        formContainer.style.display = 'none'; // Hidden until a section is selected
        childrenList.appendChild(formContainer);
    }

    /**
     * Handle menu selection to toggle input fields.
     */
    menuSection.addEventListener('click', (e) => {
        if (e.target.classList.contains('menu-button')) {
            const section = e.target.dataset.section;
            toggleChildInputs(section);
        }
    });

    /**
     * Toggle inputs inside each child form based on the selected section.
     */
    function toggleChildInputs(section) {
        const childCards = document.querySelectorAll('.child-card');
        childCards.forEach((childCard) => {
            const childId = childCard.dataset.childId; // Extract child ID from card
            if (!childId) {
                console.error('Child ID is missing in the child card dataset.');
                return;
            }

            // Clear any existing form inside the child card
            let form = childCard.querySelector('.child-form');
            if (form) {
                form.remove();
            }

            // Create a new form
            form = document.createElement('form');
            form.className = 'child-form';
            form.dataset.childId = childId; // Attach child ID to the form

            // Populate form inputs based on the section
            switch (section) {
                case 'diet':
                    createDietInputs(form);
                    break;
                case 'activities':
                    createActivityInputs(form);
                    break;
                case 'health':
                    createHealthInputs(form);
                    break;
                case 'nappy':
                    createNappyInputs(form);
                    break;
                default:
                    break;
            }

            // Add the form to the child card
            childCard.appendChild(form);

            // Add recent entries preview
            const historyPreview = document.createElement('div');
            historyPreview.className = 'history-preview';
            form.appendChild(historyPreview);

            // Fetch recent entries using the extracted function
            fetchRecentEntries(section, form.dataset.childId, historyPreview);


            // Add submit button
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.textContent = 'Submit';
            form.appendChild(submitButton);

            // Add submit event
            form.onsubmit = (e) => {
                e.preventDefault();
                submitForm(form, section);
            };
        });
    }

    /**
     * Fetch and display recent entries for a specific section and child.
     */
    function fetchRecentEntries(section, childId, historyPreview) {
        fetch(`./api/${section}.php?child_id=${childId}`, {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                historyPreview.innerHTML = ''; // Clear previous entries
                if (data[section] && data[section].length > 0) {
                    data[section].slice(0, 2).forEach((entry) => {
                        const entryDiv = document.createElement('div');
                        entryDiv.textContent = formatEntry(section, entry); // Format entry for display
                        historyPreview.appendChild(entryDiv);
                    });
                } else {
                    historyPreview.textContent = 'No recent entries available.';
                }
            })
            .catch((error) => console.error(`Error fetching ${section} recent entries:`, error));
    }


    function submitForm(form, section) {
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
    
        // Debugging: Log FormData contents
        console.log('Submitting the following data:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
    
        // Perform the POST request
        fetch(`./api/${section}.php?child_id=${childId}`, {
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
                    const historyPreview = form.querySelector('.history-preview');
                    if (historyPreview) {
                        fetchRecentEntries(section, childId, historyPreview);
                    }
                } else {
                    alert(`Error: ${data.error}`);
                    console.error(data.error);
                }
            })
            .catch((error) => console.error(`Error submitting ${section} data:`, error));
    }
    


    function formatEntry(section, entry) {
        switch (section) {
            case 'diet':
                return `Meal: ${entry.meal_type}, Food: ${entry.food_items}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'activities':
                return `Activity: ${entry.activity}, Duration: ${entry.duration} mins, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'health':
                return `Temp: ${entry.temperature}°C, Symptoms: ${entry.symptoms || 'None'}, Medication: ${entry.medication_given || 'None'}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'nappy':
                return `Type: ${entry.nappy_type}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            default:
                return JSON.stringify(entry); // Fallback for unexpected data
        }
    }
    

    /**
     * Show child profile modal.
     */
    function showChildProfile(childId, childName) {
        const modal = document.getElementById('child-profile-modal');
        const modalChildName = document.getElementById('modal-child-name');

        modalChildName.textContent = `Profile of ${childName}`;

        ['diet', 'activities', 'health', 'nappy'].forEach((section) => {
            fetch(`./api/${section}.php?child_id=${childId}`, {
                method: 'GET',
                headers: { Authorization: `Bearer ${token}` },
            })
                .then((response) => response.json())
                .then((data) => {
                    const historyList = document.querySelector(`#profile-${section}-section .history-list`);
                    historyList.innerHTML = '';

                    if (data[section] && data[section].length > 0) {
                        data[section].forEach((entry) => {
                            const historyItem = document.createElement('div');
                            historyItem.textContent = formatEntry(section, entry);
                            historyList.appendChild(historyItem);
                        });
                    } else {
                        historyList.innerHTML = '<p>No history available.</p>';
                    }
                })
                .catch((error) => console.error(`Error fetching ${section} history:`, error));
        });

        modal.style.display = 'block';
    }

    document.getElementById('close-profile-modal').onclick = function () {
        const modal = document.getElementById('child-profile-modal');
        modal.style.display = 'none';
    };

    /**
     * Create inputs for diet section.
     */
    function createDietInputs(form) {
        // Ensure we don't duplicate existing inputs
        form.innerHTML = '';

        // Meal Type Dropdown
        const mealTypeLabel = document.createElement('label');
        mealTypeLabel.textContent = 'Meal Type:';
        form.appendChild(mealTypeLabel);

        const mealTypeSelect = document.createElement('select');
        mealTypeSelect.name = 'meal_type';
        mealTypeSelect.innerHTML = '<option value="">Select Meal Type</option>';
        form.appendChild(mealTypeSelect);

        // Fetch meal types from the backend
        fetch('./api/meal_types.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    data.meal_types.forEach((type) => {
                        const option = document.createElement('option');
                        option.value = type.id; // Use the meal type ID
                        option.textContent = type.name; // Display the meal type name
                        mealTypeSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to fetch meal types:', data.message);
                }
            })
            .catch((error) => console.error('Error fetching meal types:', error));

        // Add event listener to load food items when a meal type is selected
        mealTypeSelect.addEventListener('change', (e) => {
            const mealTypeId = e.target.value;
            if (mealTypeId) {
                loadFoodItems(mealTypeId, foodItemsSelect);
            } else {
                foodItemsSelect.innerHTML = ''; // Clear food items if no meal type selected
            }
        });

        // Food Items Multi-Select
        const foodItemsLabel = document.createElement('label');
        foodItemsLabel.textContent = 'Food Items:';
        form.appendChild(foodItemsLabel);

        const foodItemsContainer = document.createElement('div');
        foodItemsContainer.className = 'food-items-container';

        const foodItemsSelect = document.createElement('select');
        foodItemsSelect.name = 'food_items[]';
        foodItemsSelect.multiple = true; // Allow multi-selection
        foodItemsContainer.appendChild(foodItemsSelect);

        const addFoodItemButton = document.createElement('button');
        addFoodItemButton.type = 'button';
        addFoodItemButton.textContent = '+';
        addFoodItemButton.addEventListener('click', showAddFoodItemModal);
        foodItemsContainer.appendChild(addFoodItemButton);

        form.appendChild(foodItemsContainer);

        // Load existing food items
        loadFoodItems(foodItemsSelect);

    }

    function loadFoodItems(mealTypeId, selectElement) {
        
        fetch(`./api/food_items.php?meal_type_id=${mealTypeId}`, {
            method: 'GET',
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                selectElement.innerHTML = ''; // Clear existing options
    
                if (data.status === 'success' && data.food_items.length > 0) {
                    data.food_items.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id; // Use the food item ID
                        option.textContent = item.name; // Display the food item name
                        selectElement.appendChild(option);
                    });
                } else {
                    console.warn('No food items found');
                }
            })
            .catch((error) => console.error('Error fetching food items:', error));
    }
    

    function showAddFoodItemModal() {
        const modal = document.getElementById('add-food-item-modal');
        modal.style.display = 'block';
    
        const form = modal.querySelector('form');
        const mealTypeSelect = modal.querySelector('select[name="meal_type_id"]');
    
        // Populate meal types dynamically if not already populated
        if (mealTypeSelect.options.length === 1) { // Only contains the "General" option
            fetch('./api/meal_types.php', {
                method: 'GET',
                headers: { Authorization: `Bearer ${token}` },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        data.meal_types.forEach((mealType) => {
                            const option = document.createElement('option');
                            option.value = mealType.id;
                            option.textContent = mealType.name;
                            mealTypeSelect.appendChild(option);
                        });
                    } else {
                        console.error('Failed to load meal types:', data.message);
                    }
                })
                .catch((error) => console.error('Error fetching meal types:', error));
        }
    
        // Handle form submission
        form.onsubmit = (e) => {
            e.preventDefault();
    
            const formData = new FormData(form);
            const selectedMealType = mealTypeSelect.value;

            if (!selectedMealType) {
                // Show confirmation for adding a general food item
                if (!confirm('No meal type selected. This food item will be available for all meal types. Do you want to continue?')) {
                    return;
                }
            }

            fetch('./api/food_items.php', {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        console.log('Food item added successfully');
                        const foodItemSelect = document.querySelector('select[name="food_items[]"]');
                        loadFoodItems(foodItemSelect); // Refresh the food items dropdown
                        modal.style.display = 'none'; // Close the modal
                    } else {
                        console.error('Failed to add food item:', data.message);
                    }
                })
                .catch((error) => console.error('Error adding food item:', error));
        };
    }
    

    // Close modal logic
    document.getElementById('close-food-item-modal').onclick = function () {
        document.getElementById('add-food-item-modal').style.display = 'none';
    };

    /**
     * Create inputs for activities section.
     */
    function createActivityInputs(form) {
        const activity = document.createElement('input');
        activity.name = 'activity';
        activity.placeholder = 'Activity';
        activity.required = true; // Make it mandatory
        form.appendChild(activity);

        const duration = document.createElement('input');
        duration.name = 'duration';
        duration.placeholder = 'Duration (minutes)';
        duration.type = 'number'; // Ensure input is numeric
        duration.required = true; // Make it mandatory
        form.appendChild(duration);
    }

    /**
     * Create inputs for health section.
     */
    function createHealthInputs(form) {
        const temperature = document.createElement('input');
        temperature.name = 'temperature';
        temperature.placeholder = 'Temperature';
        temperature.type = 'number'; // Ensure input is numeric
        temperature.step = '0.1'; // Allow decimals
        temperature.required = true; // Make it mandatory
        form.appendChild(temperature);

        const symptoms = document.createElement('textarea');
        symptoms.name = 'symptoms';
        symptoms.placeholder = 'Symptoms';
        form.appendChild(symptoms);

        const medication = document.createElement('textarea');
        medication.name = 'medication_given';
        medication.placeholder = 'Medication Given';
        form.appendChild(medication);
    }

    /**
     * Create inputs for nappy section.
     */
    function createNappyInputs(form) {
        const nappyType = document.createElement('select');
        nappyType.name = 'nappy_type';
        nappyType.required = true; // Make it mandatory

        // Add dropdown options
        ['Select Nappy Type', 'Wet', 'Soiled', 'Mixed'].forEach((optionText) => {
            const option = document.createElement('option');
            option.value = optionText.toLowerCase();
            option.textContent = optionText;
            nappyType.appendChild(option);
        });

        form.appendChild(nappyType);
    }

});
