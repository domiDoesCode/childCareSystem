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
            // // Call this function after fetching the user's role during login
            // adjustUIBasedOnRole(userRole);
        } catch (error) {
            console.error('Error decoding token:', error);
        }
    }

//     function adjustUIBasedOnRole(roleId) {
//     const forms = document.querySelectorAll('.child-form');
//     const dashboardButton = document.getElementById('dashboard-button');
//     const sectionButtons = document.querySelectorAll('.section-button');

//     if (roleId === 3) { // Parent role
//         // Hide forms and dashboard button for parents
//         forms.forEach(form => form.style.display = 'none');
//         dashboardButton.style.display = 'none';

//         // Allow only profile viewing
//         sectionButtons.forEach(button => {
//             button.style.display = button.dataset.section === 'profile' ? 'inline-block' : 'none';
//         });
//     } else if (roleId === 2) { // Caregiver role
//         // Caregivers can see forms but not admin-only sections
//         dashboardButton.style.display = 'block';
//         sectionButtons.forEach(button => button.style.display = 'inline-block');
//     } else if (roleId === 1) { // Admin role
//         // Admin has full access
//         dashboardButton.style.display = 'block';
//         sectionButtons.forEach(button => button.style.display = 'inline-block');
//     }
// }

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
    showLoadingSpinner(); // Show the spinner before starting the fetch
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
        .catch((error) => console.error('Error fetching rooms:', error))
        .finally(() => {
            hideLoadingSpinner(); // Always hide the spinner after submission
        });

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

        showLoadingSpinner(); // Show the spinner before starting the fetch
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
            .catch((error) => console.error('Error loading children:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
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

        fadeInElement(childCard); // Apply fade-in animation to the card
        document.querySelector('#children-list').appendChild(childCard);

        // Attach child ID to the card for later reference
        childCard.dataset.childId = child.id;

        // Append the child card to the children list
        childrenList.appendChild(childCard);
    }

    /**
     * Create a form container for the child.
     */
    function createChildForm(child) {
        // if (section === 'dashboard') {
        //     return; // Skip creating child forms for the dashboard
        // }

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
        if (section === 'dashboard') {
            showDashboardModal();
            return; // Skip further processing for forms.
        }
        
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

            // Add fade-in effect to the form
            fadeInElement(form);

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
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch(`./api/${section}.php?child_id=${childId}`, {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                console.log(`Fetched ${section} data:`, data); // Debugging response
                historyPreview.innerHTML = ''; // Clear previous entries
                if (data[section] && data[section].length > 0) {
                    data[section].slice(0, 3).forEach((entry) => {
                        const entryDiv = document.createElement('div');
                        entryDiv.textContent = formatEntry(section, entry); // Format entry for display
                        historyPreview.appendChild(entryDiv);
                    });
                } else {
                    historyPreview.textContent = 'No recent entries available.';
                }
            })
            .catch((error) => console.error(`Error fetching ${section} recent entries:`, error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }

    /**
     * Submit data from the forms
     */
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
        } else if (section === 'activities') {
            const activityType = form.querySelector('[name="activity_type"]').value;
            const activities = Array.from(form.querySelector('[name="activities[]"]').selectedOptions).map(opt => opt.value);
    
            if (!activityType || activities.length === 0) {
                alert('Please select an activity type and at least one activity.');
                return;
            } 
    
            formData.append('activity_type_id', activityType);
            activities.forEach(activity => formData.append('activity_ids[]', activity));
        } else if (section === 'health') {
            const temperature = form.querySelector('[name="temperature"]').value.trim();
            const symptoms = Array.from(form.querySelector('[name="symptoms[]"]').selectedOptions).map(opt => opt.value);
            const medications = Array.from(form.querySelector('[name="medications[]"]').selectedOptions).map(opt => opt.value);
        
            if (!temperature || isNaN(temperature) || (symptoms.length === 0 && medications.length === 0)) {
                alert('Please enter a valid temperature and select at least one symptom or medication.');
                return;
            }
        
            formData.append('temperature', temperature);
            symptoms.forEach((symptom) => formData.append('symptom_ids[]', symptom));
            medications.forEach((medication) => formData.append('medication_ids[]', medication));
        } if (section === 'nappy') {
            const nappyTypeId = form.querySelector('[name="nappy_type"]').value; // Retrieve the ID
        
            if (!nappyTypeId) {
                alert('Please select a nappy type.');
                return;
            }
        
            formData.append('nappy_type', nappyTypeId); // Append as nappy_type_id
        }
        
    
        // Debugging: Log FormData contents
        console.log('Submitting the following data:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
    

        // Perform the POST requestž
        showLoadingSpinner(); // Show the spinner before starting the fetch
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
            .catch((error) => console.error(`Error submitting ${section} data:`, error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }
    

    /**
     * Format the Historical data for a better visual representation
     */
    function formatEntry(section, entry) {
        switch (section) {
            case 'diet':
                return `Meal: ${entry.meal_type}, Food: ${entry.food_items}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'activities':
                return `Activity Type: ${entry.activity_type}, Activity: ${entry.activities}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'health':
                return `Temp: ${entry.temperature}°C, Symptoms: ${entry.symptoms || 'None'}, Medication: ${entry.medications || 'None'}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            case 'nappy':
                return `Type: ${entry.nappy_type}, Date: ${new Date(entry.date_recorded).toLocaleString()}`;
            default:
                return JSON.stringify(entry); // Fallback for unexpected data
        }
    }
    

    // /**
    //  * Show child profile modal.
    //  */
    // function showChildProfile(childId, childName) {
    //     const modal = document.getElementById('child-profile-modal');
    //     const modalChildName = document.getElementById('modal-child-name');

    //     modalChildName.textContent = `Profile of ${childName}`;

    //     ['diet', 'activities', 'health', 'nappy'].forEach((section) => {
    //         showLoadingSpinner(); // Show the spinner before starting the fetch
    //         fetch(`./api/${section}.php?child_id=${childId}`, {
    //             method: 'GET',
    //             headers: { Authorization: `Bearer ${token}` },
    //         })
    //             .then((response) => response.json())
    //             .then((data) => {
    //                 const historyList = document.querySelector(`#profile-${section}-section .history-list`);
    //                 historyList.innerHTML = '';

    //                 if (data[section] && data[section].length > 0) {
    //                     data[section].forEach((entry) => {
    //                         const historyItem = document.createElement('div');
    //                         historyItem.textContent = formatEntry(section, entry);
    //                         historyList.appendChild(historyItem);
    //                     });
    //                 } else {
    //                     historyList.innerHTML = '<p>No history available.</p>';
    //                 }
    //             })
    //             .catch((error) => console.error(`Error fetching ${section} history:`, error))
    //             .finally(() => {
    //                 hideLoadingSpinner(); // Always hide the spinner after submission
    //             });
    //     });

    //     modal.style.display = 'block';
    // }

    // document.getElementById('close-profile-modal').onclick = function () {
    //     const modal = document.getElementById('child-profile-modal');
    //     modal.style.display = 'none';
    // };

    /**
     * Show child profile modal with recent entries.
     */
    function showChildProfile(childId, childName) {
        const modal = document.getElementById('child-profile-modal');
        const modalChildName = document.getElementById('modal-child-name');
        modalChildName.textContent = `Profile of ${childName}`;

        ['diet', 'activities', 'health', 'nappy'].forEach((section) => {
            const historyPreview = document.querySelector(`#profile-${section}-section .history-preview`);
            fetchRecentEntries(section, childId, historyPreview); // Use fetchRecentEntries for recent entries
        });

        modal.style.display = 'block';
    }

    /**
     * Close the child profile modal.
     */
    document.getElementById('close-profile-modal').onclick = function () {
        const modal = document.getElementById('child-profile-modal');
        modal.style.display = 'none';
    };

    // Attach event listeners to all buttons with the "history-button" class
    document.querySelectorAll('.history-button').forEach((button) => {
        button.addEventListener('click', (e) => {
            const section = button.getAttribute('data-section'); // Get section from data attribute
            const childId = button.getAttribute('data-child-id'); // Get child ID from data attribute
            if (section && childId) {
                showSectionHistory(section, childId); // Call the function with section and childId
            } else {
                console.error('Missing section or child ID for history button.');
            }
        });
    });

    /**
     * Show history modal for a specific section.
     */
    function showSectionHistory(section, childId) {
        const modal = document.getElementById('history-modal');
        const modalTitle = document.getElementById('history-modal-title');
        const historyList = document.getElementById('history-list');

        modalTitle.textContent = `Full History of ${section.charAt(0).toUpperCase() + section.slice(1)}`;
        historyList.innerHTML = ''; // Clear previous content

        showLoadingSpinner(); // Show spinner while loading
        fetch(`./api/${section}.php?child_id=${childId}`, {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data[section] && data[section].length > 0) {
                    data[section].forEach((entry) => {
                        const entryDiv = document.createElement('div');
                        entryDiv.textContent = formatEntry(section, entry);
                        historyList.appendChild(entryDiv);
                    });
                } else {
                    historyList.textContent = 'No entries available.';
                }
            })
            .catch((error) => console.error(`Error fetching ${section} history:`, error))
            .finally(() => {
                hideLoadingSpinner(); // Hide spinner after loading
            });

        modal.style.display = 'block';
    }

    /**
     * Close the history modal.
     */
    document.getElementById('close-history-modal').onclick = function () {
        const modal = document.getElementById('history-modal');
        modal.style.display = 'none';
    };

/*
* ----------------------------------------------------------------------------
* DIET
* ----------------------------------------------------------------------------
*/

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
        showLoadingSpinner(); // Show the spinner before starting the fetch
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
            .catch((error) => console.error('Error fetching meal types:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });

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


    /**
     * Fetch and load food items based on the meal type.
     */
    function loadFoodItems(mealTypeId, selectElement) { 
        showLoadingSpinner(); // Show the spinner before starting the fetch
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

                // Automatically set the meal type in the general form
                const mealTypeSelect = selectElement.closest('form').querySelector('select[name="meal_type"]');
                if (mealTypeSelect) {
                    mealTypeSelect.value = mealTypeId; // Update the meal type selection
                }
            })
            .catch((error) => console.error('Error fetching food items:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }
    
    /**
     * Show the Modal for new food item addition
     */
    function showAddFoodItemModal() {
        const modal = document.getElementById('add-food-item-modal');
        const form = modal.querySelector('form');
        const mealTypeSelect = modal.querySelector('select[name="meal_type_id"]');
        const foodItemInput = modal.querySelector('input[name="name"]');

        // Add fade-in effect to the form
        fadeInElement(modal);
    
        // Reset modal fields when opened
        form.reset(); // Clears the form inputs
        mealTypeSelect.innerHTML = '<option value="">General (available for all meal types)</option>'; // Reset meal types
        modal.style.display = 'block';
    
        // Populate meal types dynamically
        showLoadingSpinner(); // Show the spinner before starting the fetch
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
            .catch((error) => console.error('Error fetching meal types:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    
        // Handle form submission
        form.onsubmit = (e) => {
            e.preventDefault();
    
            const formData = new FormData(form);
            const selectedMealType = mealTypeSelect.value; // Meal type selected by user
            const foodItemName = foodItemInput.value.trim();
    
            if (!foodItemName) {
                alert('Please enter a food item name.');
                return;
            }
    
            if (!selectedMealType) {
                // Show confirmation for adding a general food item
                if (!confirm('No meal type selected. This food item will be available for all meal types. Do you want to continue?')) {
                    return;
                }
            }
    
            showLoadingSpinner(); // Show the spinner before starting the fetch
            fetch('./api/food_items.php', {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        alert('Food item added successfully!');
                        
                        // Refresh the corresponding food items dropdown in the diet form
                        const foodItemSelects = document.querySelectorAll('select[name="food_items[]"]');
                        foodItemSelects.forEach((select) => {
                            const mealTypeId = selectedMealType || select.closest('form').querySelector('select[name="meal_type"]').value;
                            loadFoodItems(mealTypeId, select);
                        });
    
                        // Reset and close the modal
                        form.reset();
                        modal.style.display = 'none';
                    } else {
                        alert(`Failed to add food item: ${data.message}`);
                        console.error('Failed to add food item:', data.message);
                    }
                })
                .catch((error) => console.error('Error adding food item:', error))
                .finally(() => {
                    hideLoadingSpinner(); // Always hide the spinner after submission
                });
        };
    }
    
    // Close modal logic
    document.getElementById('close-food-item-modal').onclick = function () {
        document.getElementById('add-food-item-modal').style.display = 'none';
    };

/*
* ----------------------------------------------------------------------------
* ACTIVITIES
* ----------------------------------------------------------------------------
*/

    /**
     * Create inputs for activities section.
     */
    function createActivityInputs(form) {
        // Ensure we don't duplicate existing inputs
        form.innerHTML = '';

        // Activity Type Dropdown
        const activityTypeLabel = document.createElement('label');
        activityTypeLabel.textContent = 'Activity Type:';
        form.appendChild(activityTypeLabel);

        const activityTypeSelect = document.createElement('select');
        activityTypeSelect.name = 'activity_type';
        activityTypeSelect.innerHTML = '<option value="">Select Activity Type</option>';
        form.appendChild(activityTypeSelect);

        // Fetch activity types from the backend
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch('./api/activity_types.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    data.activity_types.forEach((type) => {
                        const option = document.createElement('option');
                        option.value = type.id; // Use the activity type ID
                        option.textContent = type.name; // Display the activity type name
                        activityTypeSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to fetch activity types:', data.message);
                }
            })
            .catch((error) => console.error('Error fetching activity types:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });

        // Add event listener to load activities when an activity type is selected
        activityTypeSelect.addEventListener('change', (e) => {
            const activityTypeId = e.target.value;
            if (activityTypeId) {
                loadActivities(activityTypeId, activitiesSelect);
            } else {
                activitiesSelect.innerHTML = ''; // Clear activities if no activity type selected
            }
        });

        // Activities Multi-Select
        const activitiesLabel = document.createElement('label');
        activitiesLabel.textContent = 'Activities:';
        form.appendChild(activitiesLabel);

        const activitiesContainer = document.createElement('div');
        activitiesContainer.className = 'activities-container';

        const activitiesSelect = document.createElement('select');
        activitiesSelect.name = 'activities[]';
        activitiesSelect.multiple = true; // Allow multi-selection
        activitiesContainer.appendChild(activitiesSelect);

        const addActivityButton = document.createElement('button');
        addActivityButton.type = 'button';
        addActivityButton.textContent = '+';
        addActivityButton.addEventListener('click', showAddActivityModal);
        activitiesContainer.appendChild(addActivityButton);

        form.appendChild(activitiesContainer);

        // Load existing activities
        loadActivities(null, activitiesSelect);
    }

    /**
     * Fetch and load activities based on the activity type.
     */
    function loadActivities(activityTypeId, selectElement) {
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch(`./api/activity_definitions.php?activity_type_id=${activityTypeId}`, {
            method: 'GET',
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                selectElement.innerHTML = ''; // Clear existing options

                if (data.status === 'success' && data.activities.length > 0) {
                    data.activities.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id; // Use the activity ID
                        option.textContent = item.name; // Display the activity name
                        selectElement.appendChild(option);
                    });
                } else {
                    console.warn('No activities found');
                }
            })
            .catch((error) => console.error('Error fetching activities:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }

    /**
     * Show the "Add Activity" modal.
     */
    function showAddActivityModal() {
        const modal = document.getElementById('add-activity-modal');
        const form = modal.querySelector('form');
        const activityTypeSelect = modal.querySelector('select[name="activity_type_id"]');
        const activityInput = modal.querySelector('input[name="name"]');

        // Add fade-in effect to the form
        fadeInElement(modal);

        // Reset modal fields when opened
        form.reset(); // Clears the form inputs
        activityTypeSelect.innerHTML = '<option value="">General (available for all activity types)</option>'; // Reset activity types
        modal.style.display = 'block';

        // Populate activity types dynamically
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch('./api/activity_types.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    data.activity_types.forEach((type) => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        activityTypeSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to load activity types:', data.message);
                }
            })
            .catch((error) => console.error('Error fetching activity types:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });

        // Handle form submission
        form.onsubmit = (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const selectedActivityType = activityTypeSelect.value; // Activity type selected by user
            const activityName = activityInput.value.trim();

            if (!activityName) {
                alert('Please enter an activity name.');
                return;
            }

            if (!selectedActivityType) {
                // Show confirmation for adding a general activity
                if (!confirm('No activity type selected. This activity will be available for all activity types. Do you want to continue?')) {
                    return;
                }
            }

            showLoadingSpinner(); // Show the spinner before starting the fetch
            fetch('./api/activity_definitions.php', {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        alert('Activity added successfully!');

                        // Refresh the corresponding activities dropdown in the activities form
                        const activitySelects = document.querySelectorAll('select[name="activities[]"]');
                        activitySelects.forEach((select) => {
                            const activityTypeId = selectedActivityType || select.closest('form').querySelector('select[name="activity_type"]').value;
                            loadActivities(activityTypeId, select);
                        });

                        // Reset and close the modal
                        form.reset();
                        modal.style.display = 'none';
                    } else {
                        alert(`Failed to add activity: ${data.message}`);
                        console.error('Failed to add activity:', data.message);
                    }
                })
                .catch((error) => console.error('Error adding activity:', error))
                .finally(() => {
                    hideLoadingSpinner(); // Always hide the spinner after submission
                });
        };
    }

    // Close modal logic
    document.getElementById('close-activity-modal').onclick = function () {
        document.getElementById('add-activity-modal').style.display = 'none';
    };

    /*
    * ----------------------------------------------------------------------------
    * HEALTH
    * ----------------------------------------------------------------------------
    */

    function createHealthInputs(form) {
        // Clear existing inputs
        form.innerHTML = '';

        // Temperature Input
        const temperatureLabel = document.createElement('label');
        temperatureLabel.textContent = 'Temperature (°C):';
        form.appendChild(temperatureLabel);

        const temperatureInput = document.createElement('input');
        temperatureInput.name = 'temperature';
        temperatureInput.type = 'number';
        temperatureInput.step = '0.1'; // Allow decimals
        temperatureInput.required = true;
        form.appendChild(temperatureInput);

        // Symptoms Multi-Select
        const symptomsLabel = document.createElement('label');
        symptomsLabel.textContent = 'Symptoms:';
        form.appendChild(symptomsLabel);

        const symptomsContainer = document.createElement('div');
        symptomsContainer.className = 'symptoms-container';

        const symptomsSelect = document.createElement('select');
        symptomsSelect.name = 'symptoms[]';
        symptomsSelect.multiple = true; // Allow multi-selection
        symptomsContainer.appendChild(symptomsSelect);

        const addSymptomButton = document.createElement('button');
        addSymptomButton.type = 'button';
        addSymptomButton.textContent = '+';
        addSymptomButton.addEventListener('click', showAddSymptomModal);
        symptomsContainer.appendChild(addSymptomButton);

        form.appendChild(symptomsContainer);

        // Load existing symptoms
        loadSymptoms(symptomsSelect);

        // Medications Multi-Select
        const medicationsLabel = document.createElement('label');
        medicationsLabel.textContent = 'Medications:';
        form.appendChild(medicationsLabel);

        const medicationsContainer = document.createElement('div');
        medicationsContainer.className = 'medications-container';

        const medicationsSelect = document.createElement('select');
        medicationsSelect.name = 'medications[]';
        medicationsSelect.multiple = true; // Allow multi-selection
        medicationsContainer.appendChild(medicationsSelect);

        const addMedicationButton = document.createElement('button');
        addMedicationButton.type = 'button';
        addMedicationButton.textContent = '+';
        addMedicationButton.addEventListener('click', showAddMedicationModal);
        medicationsContainer.appendChild(addMedicationButton);

        form.appendChild(medicationsContainer);

        // Load existing medications
        loadMedications(medicationsSelect);

        // Form Submission
        form.onsubmit = (e) => {
            e.preventDefault();
            submitForm(form, 'health');
        };

    }

    /**
     * Load symptoms into the dropdown.
     */
    function loadSymptoms(selectElement) {
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch('./api/symptoms.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                selectElement.innerHTML = ''; // Clear existing options
                if (data.status === 'success' && data.symptoms.length > 0) {
                    data.symptoms.forEach((symptom) => {
                        const option = document.createElement('option');
                        option.value = symptom.id; // Use symptom ID
                        option.textContent = symptom.name; // Display symptom name
                        selectElement.appendChild(option);
                    });
                } else {
                    console.warn('No symptoms found.');
                }
            })
            .catch((error) => console.error('Error fetching symptoms:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }

    /**
     * Load medications into the dropdown.
     */
    function loadMedications(selectElement) {
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch('./api/medications.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                selectElement.innerHTML = ''; // Clear existing options
                if (data.status === 'success' && data.medications.length > 0) {
                    data.medications.forEach((medication) => {
                        const option = document.createElement('option');
                        option.value = medication.id; // Use medication ID
                        option.textContent = medication.name; // Display medication name
                        selectElement.appendChild(option);
                    });
                } else {
                    console.warn('No medications found.');
                }
            })
            .catch((error) => console.error('Error fetching medications:', error))
            .finally(() => {
                hideLoadingSpinner(); // Always hide the spinner after submission
            });
    }

    /**
        * Fetch and load activities based on the activity type.
        */
    function showAddSymptomModal() {
        const modal = document.getElementById('add-symptom-modal');
        const form = modal.querySelector('form');
        const symptomInput = modal.querySelector('input[name="name"]');

        // Add fade-in effect to the form
        fadeInElement(modal);

        // Reset modal fields when opened
        form.reset();
        modal.style.display = 'block';

        // Handle form submission
        form.onsubmit = (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const symptomName = symptomInput.value.trim();

            if (!symptomName) {
                alert('Please enter a symptom name.');
                return;
            }

            showLoadingSpinner(); // Show the spinner before starting the fetch
            fetch('./api/symptoms.php', {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        alert('Symptom added successfully!');
                        
                        // Refresh the symptoms dropdown in the health form
                        const symptomSelects = document.querySelectorAll('select[name="symptoms[]"]');
                        symptomSelects.forEach((select) => loadSymptoms(select));
                        
                        // Reset and close the modal
                        form.reset();
                        modal.style.display = 'none';
                    } else {
                        alert(`Failed to add symptom: ${data.message}`);
                    }
                })
                .catch((error) => console.error('Error adding symptom:', error))
                .finally(() => {
                    hideLoadingSpinner(); // Always hide the spinner after submission
                });
        };

            // Close modal logic
            document.getElementById('close-symptom-modal').onclick = function () {
            document.getElementById('add-symptom-modal').style.display = 'none';
        };
    }

    function showAddMedicationModal() {
        const modal = document.getElementById('add-medication-modal');
        const form = modal.querySelector('form');
        const medicationInput = modal.querySelector('input[name="name"]');

        // Add fade-in effect to the form
        fadeInElement(modal);

        // Reset modal fields when opened
        form.reset();
        modal.style.display = 'block';

        // Handle form submission
        form.onsubmit = (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const medicationName = medicationInput.value.trim();

            if (!medicationName) {
                alert('Please enter a medication name.');
                return;
            }

            showLoadingSpinner(); // Show the spinner before starting the fetch
            fetch('./api/medications.php', {
                method: 'POST',
                headers: { Authorization: `Bearer ${token}` },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        alert('Medication added successfully!');
                        
                        // Refresh the medications dropdown in the health form
                        const medicationSelects = document.querySelectorAll('select[name="medications[]"]');
                        medicationSelects.forEach((select) => loadMedications(select));
                        
                        // Reset and close the modal
                        form.reset();
                        modal.style.display = 'none';
                    } else {
                        alert(`Failed to add medication: ${data.message}`);
                    }
                })
                .catch((error) => console.error('Error adding medication:', error))
                .finally(() => {
                    hideLoadingSpinner(); // Always hide the spinner after submission
                });
        };

            // Close modal logic
            document.getElementById('close-medication-modal').onclick = function () {
            document.getElementById('add-medication-modal').style.display = 'none';
        };

    }

    /*
    * ----------------------------------------------------------------------------
    * NAPPY
    * ----------------------------------------------------------------------------
    */
    function createNappyInputs(form) {
        form.innerHTML = ''; // Clear existing inputs
    
        const nappyTypeLabel = document.createElement('label');
        nappyTypeLabel.textContent = 'Nappy Type:';
        form.appendChild(nappyTypeLabel);
    
        const nappyTypeSelect = document.createElement('select');
        nappyTypeSelect.name = 'nappy_type'; // Matches the name in the form submission
        nappyTypeSelect.innerHTML = '<option value="">Select Nappy Type</option>'; // Default option
    
        // Load nappy types dynamically from the backend
        showLoadingSpinner(); // Show the spinner before starting the fetch
        fetch('./api/nappy_types.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    data.nappy_types.forEach((type) => {
                        const option = document.createElement('option');
                        option.value = type.id; // Use the ID as the value
                        option.textContent = type.name; // Display the name
                        nappyTypeSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to fetch nappy types:', data.message);
                }
            })
            .catch((error) => console.error('Error fetching nappy types:', error))
            .finally(() => {
            hideLoadingSpinner(); // Always hide the spinner after submission
        });
    
        form.appendChild(nappyTypeSelect);
    }

    /*
    * ----------------------------------------------------------------------------
    * DASHBOARD OVERVIEW
    * ----------------------------------------------------------------------------
    */
    function showDashboardModal() {
        const modal = document.getElementById('dashboard-modal');
        modal.style.display = 'block';
        fadeInElement(modal); // Apply fade-in effect
        loadDashboardOverview();
    }
    
    function hideDashboardModal() {
        const modal = document.getElementById('dashboard-modal');
        modal.style.display = 'none';
    }
    
    // Add event listeners
    document.getElementById('view-dashboard-button').addEventListener('click', showDashboardModal);
    document.getElementById('close-dashboard-modal').addEventListener('click', hideDashboardModal);

    function loadDashboardOverview() {
        // Show the spinner while loading
        showLoadingSpinner();

        // Call all summary functions with error handling
        Promise.allSettled([
            loadDietSummary(),
            loadActivitiesSummary(),
            loadHealthSummary(),
            loadNappySummary(),
        ]).then((results) => {
            results.forEach((result, index) => {
                if (result.status === 'rejected') {
                    console.error(`Error loading summary ${index + 1}:`, result.reason);
                }
            });
            // Hide the spinner after all calls have settled
            hideLoadingSpinner();
        });
    }

    function loadDietSummary() {
        fetch('./api/diet_summary.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                const dietList = document.getElementById('diet-summary-list');
                dietList.innerHTML = '';
                if (data.summary.length > 0) {
                    data.summary.forEach((entry) => {
                        const li = document.createElement('li');
                        li.textContent = `${entry.meal_type}: ${entry.total_entries} entries`;
                        dietList.appendChild(li);
                    });
                } else {
                    dietList.textContent = 'No diet entries today.';
                }
            })
            .catch((error) => console.error('Error fetching diet summary:', error));
    }

    function loadActivitiesSummary() {
        fetch('./api/activities_summary.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                const activitiesContainer = document.getElementById('activity-summary-details');
                activitiesContainer.innerHTML = ''; // Clear previous summary
                if (data.summary.length > 0) {
                    data.summary.forEach((entry) => {
                        const activityDiv = document.createElement('div');
                        activityDiv.textContent = `${entry.activity_type}: ${entry.total_entries} entries`;
                        activitiesContainer.appendChild(activityDiv);
                    });
                } else {
                    activitiesContainer.textContent = 'No activities recorded today.';
                }
            })
            .catch((error) => console.error('Error fetching activities summary:', error));
    }
    

    function loadHealthSummary() {
        fetch('./api/health_summary.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                const healthContainer = document.getElementById('health-summary-details');
                healthContainer.innerHTML = ''; // Clear previous summary
    
                if (data.summary && Object.keys(data.summary).length > 0) {
                    const minTemp = data.summary.min_temperature
                        ? `Min Temperature: ${parseFloat(data.summary.min_temperature).toFixed(1)}°C`
                        : 'Min Temperature: No temperature data found today.';
                    const maxTemp = data.summary.max_temperature
                        ? `Max Temperature: ${parseFloat(data.summary.max_temperature).toFixed(1)}°C`
                        : 'Max Temperature: No temperature data found today.';
                    const totalEntries = `Total Health Entries: ${data.summary.total_entries}`;
    
                    healthContainer.innerHTML = `<p>${minTemp}</p><p>${maxTemp}</p><p>${totalEntries}</p>`;
                } else {
                    healthContainer.textContent = 'No health entries recorded today.';
                }
            })
            .catch((error) => console.error('Error fetching health summary:', error));
    }
    
    
    function loadNappySummary() {
        fetch('./api/nappy_summary.php', {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                const nappyList = document.getElementById('nappy-summary-list');
                nappyList.innerHTML = '';
                if (data.summary.length > 0) {
                    data.summary.forEach((entry) => {
                        const li = document.createElement('li');
                        li.textContent = `${entry.nappy_type}: ${entry.total_entries} entries`;
                        nappyList.appendChild(li);
                    });
                } else {
                    nappyList.textContent = 'No nappy entries today.';
                }
            })
            .catch((error) => console.error('Error fetching nappy summary:', error));
    }

     // Attach event listeners for all "View Details" buttons
    document.querySelectorAll('.view-details-button').forEach((button) => {
        button.addEventListener('click', (e) => {
            const section = button.getAttribute('data-section');
        if (section) {
                showDashboardDetails(section);
            } else {
                console.error('Section not specified for View Details button.');
            }
        });
    });

    // Close Details Modal
    document.getElementById('close-details-modal').addEventListener('click', () => {
        const modal = document.getElementById('details-modal');
        modal.style.display = 'none';
    });

    function showDashboardDetails(section) {
        const modal = document.getElementById('details-modal');
        const modalTitle = document.getElementById('details-modal-title');
        const detailsList = document.getElementById('details-list');
    
        modalTitle.textContent = `Detailed Entries for ${section.charAt(0).toUpperCase() + section.slice(1)}`;
        detailsList.innerHTML = ''; // Clear previous content
    
        showLoadingSpinner(); // Show spinner while fetching data
    
        // Fetch data from the respective API
        fetch(`./api/${section}_summary.php?details=true`, {
            method: 'GET',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.summary && data.summary.length > 0) {
                    // Group entries by child name
                    const groupedEntries = data.summary.reduce((acc, entry) => {
                        const childName = entry.child_name || 'Unknown';
                        if (!acc[childName]) {
                            acc[childName] = [];
                        }
                        acc[childName].push(entry);
                        return acc;
                    }, {});
    
                    // Display grouped entries
                    for (const childName in groupedEntries) {
                        const childSection = document.createElement('div');
                        childSection.className = 'child-group';
                        childSection.innerHTML = `<h3>${childName}</h3>`;
    
                        const childEntries = groupedEntries[childName];
                        childEntries.forEach((entry) => {
                            const entryDiv = document.createElement('div');
                            entryDiv.className = 'details-entry';
                            entryDiv.innerHTML = formatDetailedEntry(section, entry);
                            childSection.appendChild(entryDiv);
                        });
    
                        detailsList.appendChild(childSection);
                    }
                } else {
                    detailsList.textContent = 'No entries found.';
                }
            })
            .catch((error) => console.error(`Error fetching ${section} details:`, error))
            .finally(() => {
                hideLoadingSpinner(); // Hide spinner after data is loaded
                modal.style.display = 'block'; // Show the modal
            });
    }
    

    function formatDetailedEntry(section, entry) {
        switch (section) {
            case 'diet':
                return `
                    <strong>Meal Type:</strong> ${entry.meal_type || 'N/A'}<br>
                    <strong>Food Item:</strong> ${entry.food_item || 'N/A'}<br>
                    <strong>Date:</strong> ${new Date(entry.date_recorded).toLocaleString()}
                `;
            case 'activities':
                return `
                    <strong>Activity Type:</strong> ${entry.activity_type || 'N/A'}<br>
                    <strong>Activity:</strong> ${entry.activity || 'N/A'}<br>
                    <strong>Date:</strong> ${new Date(entry.date_recorded).toLocaleString()}
                `;
            case 'health':
                return `
                    <strong>Temperature:</strong> ${entry.temperature ? `${entry.temperature}°C` : 'N/A'}<br>
                    <strong>Symptoms:</strong> ${entry.symptoms || 'None'}<br>
                    <strong>Medications:</strong> ${entry.medications || 'None'}<br>
                    <strong>Date:</strong> ${new Date(entry.date_recorded).toLocaleString()}
                `;
            case 'nappy':
                return `
                    <strong>Nappy Type:</strong> ${entry.nappy_type || 'N/A'}<br>
                    <strong>Date:</strong> ${new Date(entry.date_recorded).toLocaleString()}
                `;
            default:
                return `<strong>Data:</strong> ${JSON.stringify(entry)}`;
        }
    }
    
    
    
    // Event listener to close the details modal
    document.getElementById('close-details-modal').onclick = function () {
        const modal = document.getElementById('details-modal');
        modal.style.display = 'none';
    };
    
    
    

    /*
    * ----------------------------------------------------------------------------
    * OTHER
    * ----------------------------------------------------------------------------
    */
    function showLoadingSpinner() {
        document.getElementById('loading-spinner').style.display = 'block';
    }
    function hideLoadingSpinner() {
        document.getElementById('loading-spinner').style.display = 'none';
    }
    
    function fadeInElement(element) {
        element.classList.add('fade-in');
    }

});

    
