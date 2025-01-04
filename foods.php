<?php
  session_start();
  $servername = "localhost";  
  $username = "root";         
  $password = "";             
  $dbname = "nsu_sheba";      
  
  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  

  if (!isset($_SESSION['loggedin'])) {
  header("Location: login.php");
   exit();
   }

   $student_name = $_SESSION['student_name'];
   $student_id = $_SESSION['student_id'];
 ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #d9fff0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container {
            text-align: center;
            margin: 20px auto;
            max-width: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .food-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px 0;
            padding: 10px;
        }

        .food-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 15px;
            width: 200px;
            text-align: center;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .order-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 10px;
            padding: 15px;
            width: 250px;
            /* Adjust the width if necessary */
            text-align: left;
            /* Optional: for left-aligned content */
        }

        .order-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #333;
        }

        .order-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
<script>
        async function showSection(section) {
            // Retrieve all sections
            const buySection = document.getElementById('buySection');
            const sellSection = document.getElementById('sellSection');
            const orderSection = document.getElementById('orderSection');
            const deleteSection = document.getElementById('deleteSection');

            // Hide all sections initially
            buySection.style.display = 'none';
            sellSection.style.display = 'none';
            orderSection.style.display = 'none';
            deleteSection.style.display = 'none';

            // Log the section being displayed for debugging
            console.log(`Displaying section: ${section}`);

            // Show the selected section based on the input
            if (section === 'buy') {
                buySection.style.display = 'block';
                fetchFoods(); // Call fetchFoods if "Buy Food" is selected
            } else if (section === 'sell') {
                sellSection.style.display = 'block';
            } else if (section === 'order') {
                orderSection.style.display = 'block';
                fetchOrders(); // Call fetchOrders for "Show Orders"
            } else if (section === 'delete') {
                deleteSection.style.display = 'block';
                fetchFoodsToRemove(); // Call fetchFoodsToRemove for "Remove Food"
            }
        }
        async function fetchOrders() {
            try {
                const response = await fetch('fetch_orders.php');
                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    alert('Error fetching data: ' + data.error);
                    return;
                }

                console.log(data); // Debugging

                const orderContainer = document.getElementById('orderContainer');
                orderContainer.innerHTML = ''; // Clear existing content

                data.forEach(order => {
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-card';

                    orderCard.innerHTML = `
                <h3>${order.food_name}</h3>
                <p><strong>Price:</strong> ৳${order.price}</p>
                <p><strong>Token:</strong> ${order.token}</p>
                <p><strong>Seller ID:</strong> ${order.seller_id}</p>
                <p><strong>Order Time:</strong> ${order.order_time}</p>
            `;

                    orderContainer.appendChild(orderCard);
                });
            } catch (error) {
                console.error('Error:', error);
                alert('Error fetching data. Please try again later.');
            }
        }
        async function addFood() {
            const foodName = document.getElementById('foodName').value.trim();
            const foodPrice = parseFloat(document.getElementById('foodPrice').value);
            const sellerName = document.getElementById('sellerName').value.trim();
            const sellerId = document.getElementById('sellerId').value.trim();
            const contactNumber = document.getElementById('contactNumber').value.trim();

            if (!foodName || !sellerName || !sellerId || isNaN(foodPrice) || foodPrice <= 0 || !contactNumber) {
                alert('Please fill in all fields correctly.');
                return;
            }

            const response = await fetch('food.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: foodName, price: foodPrice, sellerName, sellerId, contactNumber }),
            });

            const result = await response.json();

            if (result.status === "success") {
                alert('Food item added successfully!');
                fetchFoods();
                document.getElementById('foodName').value = '';
                document.getElementById('foodPrice').value = '';
                document.getElementById('sellerName').value = '';
                document.getElementById('sellerId').value = '';
                document.getElementById('contactNumber').value = '';
            } else {
                alert('Error: ' + result.message);
            }
        }

        async function fetchFoods() {
            try {
                const response = await fetch('food.php');
                const data = await response.json();

                console.log(data);  // Log the response data to check its structure

                if (data.error) {
                    console.error(data.error);
                    alert('Error fetching data: ' + data.error);
                    return;
                }

                const foodContainer = document.getElementById('foodContainer');
                foodContainer.innerHTML = ''; // Clear existing content

                data.forEach(food => {
                    console.log(food);  // Log each food item to inspect the structure

                    const foodCard = document.createElement('div');
                    foodCard.className = 'food-card';
                    foodCard.innerHTML = `
                <h3>${food.name}</h3>  <!-- Updated to use 'name' instead of 'food_name' -->
                <p><strong>Price:</strong> ৳${food.price}</p>
                <button class="btn" onclick="buyNow('${food.name}', ${food.price}, '${food.seller_id}')">Buy Now</button>
                <button class="btn" onclick="showSellerInfo('${food.seller_id}')">View Seller Info</button>
                <button class="btn" onclick="rateSeller('${food.seller_Id}')">Rate </button>
            `;
                    foodContainer.appendChild(foodCard);
                });
            } catch (error) {
                console.error('Error fetching food items:', error);
                alert('Error fetching food items. Please try again later.');
            }
        }

        async function rateSeller(sellerId) {
            console.log("Sending sellerId (as-is):", sellerId);

            const rating = prompt("Please rate the seller (1 to 5):");

            // Validate rating
            if (rating && !isNaN(rating) && rating >= 1 && rating <= 5) {
                console.log("Sending data:", { seller_id: sellerId, rating: parseInt(rating) });

                try {
                    const response = await fetch('rate_seller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            seller_id: sellerId,
                            rating: parseInt(rating),
                        }),
                    });

                    const result = await response.json();
                    alert(result.message);
                } catch (error) {
                    console.error('Error submitting rating:', error);
                    alert('Error submitting rating. Please try again later.');
                }
            } else {
                alert("Invalid rating. Please enter a number between 1 and 5.");
            }
        }
        function buyNow(foodName, price, sellerId) {
            const token = Math.floor(100 + Math.random() * 900); // Generate a random 3-digit token
            alert(`Request to purchase ${foodName} for ৳${price}!\nYour Token Number: ${token}`);

            fetch('order_food.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: token,
                    foodName: foodName,
                    price: price,
                    sellerId: sellerId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert('Order placed successfully!');
                    } else {
                        alert('Error placing order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while placing the order.');
                });
        }
        async function fetchFoodsToRemove() {
            try {
                console.log("Fetching foods to remove..."); // Debugging log
                const response = await fetch('fetch_foods_to_remove.php');

                if (!response.ok) {
                    throw new Error('Failed to fetch foods');
                }

                const data = await response.json();

                console.log("Data fetched:", data); // Log the entire response

                if (data.error) {
                    console.error("Error:", data.error);
                    alert('Error fetching food items to remove: ' + data.error);
                    return;
                }

                const removeFoodContainer = document.getElementById('removeFoodContainer');
                removeFoodContainer.innerHTML = ''; // Clear existing content

                if (data.length === 0) {
                    removeFoodContainer.innerHTML = '<p>No food items to remove.</p>';
                    return;
                }

                data.forEach(food => {
                    const foodCard = document.createElement('div');
                    foodCard.className = 'food-card';
                    foodCard.innerHTML = `
                <h3>${food.name}</h3>
                <p><strong>Price:</strong> ৳${food.price}</p>
                <p><strong>Id:</strong> ${food.id}</p>
                <button class="btn" onclick="removeFood(${food.id})">Remove</button>
            `;
                    removeFoodContainer.appendChild(foodCard);
                });
            } catch (error) {
                console.error('Error fetching foods to remove:', error);
                alert('Error fetching food items to remove. Please try again later.');
            }
        }
        async function removeFood(foodId) {
            if (!foodId) {
                alert("Food ID is missing.");
                return;
            }

            try {
                const response = await fetch('remove_food.php', {
                    method: 'POST',  // POST method to send data
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ food_id: foodId })  // Send food_id in JSON format
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    fetchFoodsToRemove();  // Optionally refresh the list
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error removing food. Please try again later.');
            }
        }
        


        
       
        </script>

       


    <h1>Food Marketplace</h1>
    <div class="form-container">
        <button class="btn" onclick="showSection('buy')">Buy Food</button>
        <button class="btn" onclick="showSection('sell')">Sell Food</button>
        <button class="btn" onclick="showSection('order')">Show Orders</button>
        <button class="btn" onclick="showSection('delete')">Remove Food</button>
    </div>
    <div id="buySection" class="food-list" style="display: none;">
        <h2>Available Foods</h2>
        <div class="food-list" id="foodContainer"></div>
    </div>

    <div id="sellSection" class="form-container" style="display: none;">
        <input type="text" id="foodName" placeholder="Food Name" required>
        <input type="number" id="foodPrice" placeholder="Price (৳)" required min="0">
        <input type="text" id="sellerName" placeholder="Seller Name" required>
        <input type="text" id="sellerId" placeholder="Seller ID" required>
        <input type="text" id="contactNumber" placeholder="Contact Number" required>
        <button class="btn" onclick="addFood()">Add Food</button>
    </div>

    <div id="orderSection" class="food-list" style="display: none;">
        <h2>Orders</h2>
        <div id="orderContainer"></div>
    </div>


    <div id="sellerInfoPopup" class="popup">
        <div class="popup-content" id="popupContent">
            <h3>Seller Information</h3>
            <p><strong>Name:</strong> <span id="sellerNamePopup"></span></p>
            <p><strong>ID:</strong> <span id="sellerIdPopup"></span></p>
            <p><strong>Rating:</strong> <span id="sellerRatingPopup"></span></p>
            <button class="btn" onclick="closePopup()">Close</button>
        </div>
    </div>
    <div id="deleteSection" class="food-list" style="display: none;">
        <h2>Remove Food</h2>
        <div id="removeFoodContainer"></div>
    </div>


    <script>
        async function showSection(section) {
            // Retrieve all sections
            const buySection = document.getElementById('buySection');
            const sellSection = document.getElementById('sellSection');
            const orderSection = document.getElementById('orderSection');
            const deleteSection = document.getElementById('deleteSection');

            // Hide all sections initially
            buySection.style.display = 'none';
            sellSection.style.display = 'none';
            orderSection.style.display = 'none';
            deleteSection.style.display = 'none';

            // Log the section being displayed for debugging
            console.log(`Displaying section: ${section}`);

            // Show the selected section based on the input
            if (section === 'buy') {
                buySection.style.display = 'block';
                fetchFoods(); // Call fetchFoods if "Buy Food" is selected
            } else if (section === 'sell') {
                sellSection.style.display = 'block';
            } else if (section === 'order') {
                orderSection.style.display = 'block';
                fetchOrders(); // Call fetchOrders for "Show Orders"
            } else if (section === 'delete') {
                deleteSection.style.display = 'block';
                fetchFoodsToRemove(); // Call fetchFoodsToRemove for "Remove Food"
            }
        }
        async function fetchOrders() {
            try {
                const response = await fetch('fetch_orders.php');
                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    alert('Error fetching data: ' + data.error);
                    return;
                }

                console.log(data); // Debugging

                const orderContainer = document.getElementById('orderContainer');
                orderContainer.innerHTML = ''; // Clear existing content

                data.forEach(order => {
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-card';

                    orderCard.innerHTML = `
                <h3>${order.food_name}</h3>
                <p><strong>Price:</strong> ৳${order.price}</p>
                <p><strong>Token:</strong> ${order.token}</p>
                <p><strong>Seller ID:</strong> ${order.seller_id}</p>
                <p><strong>Order Time:</strong> ${order.order_time}</p>
            `;

                    orderContainer.appendChild(orderCard);
                });
            } catch (error) {
                console.error('Error:', error);
                alert('Error fetching data. Please try again later.');
            }
        }


        async function addFood() {
            const foodName = document.getElementById('foodName').value.trim();
            const foodPrice = parseFloat(document.getElementById('foodPrice').value);
            const sellerName = document.getElementById('sellerName').value.trim();
            const sellerId = document.getElementById('sellerId').value.trim();
            const contactNumber = document.getElementById('contactNumber').value.trim();

            if (!foodName || !sellerName || !sellerId || isNaN(foodPrice) || foodPrice <= 0 || !contactNumber) {
                alert('Please fill in all fields correctly.');
                return;
            }

            const response = await fetch('food.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ name: foodName, price: foodPrice, sellerName, sellerId, contactNumber }),
            });

            const result = await response.json();

            if (result.status === "success") {
                alert('Food item added successfully!');
                fetchFoods();
                document.getElementById('foodName').value = '';
                document.getElementById('foodPrice').value = '';
                document.getElementById('sellerName').value = '';
                document.getElementById('sellerId').value = '';
                document.getElementById('contactNumber').value = '';
            } else {
                alert('Error: ' + result.message);
            }
        }

        async function fetchFoods() {
            try {
                const response = await fetch('food.php');
                const data = await response.json();

                console.log(data);  // Log the response data to check its structure

                if (data.error) {
                    console.error(data.error);
                    alert('Error fetching data: ' + data.error);
                    return;
                }

                const foodContainer = document.getElementById('foodContainer');
                foodContainer.innerHTML = ''; // Clear existing content

                data.forEach(food => {
                    console.log(food);  // Log each food item to inspect the structure

                    const foodCard = document.createElement('div');
                    foodCard.className = 'food-card';
                    foodCard.innerHTML = `
                <h3>${food.name}</h3>  <!-- Updated to use 'name' instead of 'food_name' -->
                <p><strong>Price:</strong> ৳${food.price}</p>
                <button class="btn" onclick="buyNow('${food.name}', ${food.price}, '${food.seller_id}')">Buy Now</button>
                <button class="btn" onclick="showSellerInfo('${food.seller_id}')">View Seller Info</button>
                <button class="btn" onclick="rateSeller('${food.seller_Id}')">Rate </button>
            `;
                    foodContainer.appendChild(foodCard);
                });
            } catch (error) {
                console.error('Error fetching food items:', error);
                alert('Error fetching food items. Please try again later.');
            }
        }

        async function rateSeller(sellerId) {
            console.log("Sending sellerId (as-is):", sellerId);

            const rating = prompt("Please rate the seller (1 to 5):");

            // Validate rating
            if (rating && !isNaN(rating) && rating >= 1 && rating <= 5) {
                console.log("Sending data:", { seller_id: sellerId, rating: parseInt(rating) });

                try {
                    const response = await fetch('rate_seller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            seller_id: sellerId,
                            rating: parseInt(rating),
                        }),
                    });

                    const result = await response.json();
                    alert(result.message);
                } catch (error) {
                    console.error('Error submitting rating:', error);
                    alert('Error submitting rating. Please try again later.');
                }
            } else {
                alert("Invalid rating. Please enter a number between 1 and 5.");
            }
        }
        function buyNow(foodName, price, sellerId) {
            const token = Math.floor(100 + Math.random() * 900); // Generate a random 3-digit token
            alert(`Request to purchase ${foodName} for ৳${price}!\nYour Token Number: ${token}`);

            fetch('order_food.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: token,
                    foodName: foodName,
                    price: price,
                    sellerId: sellerId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        alert('Order placed successfully!');
                    } else {
                        alert('Error placing order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while placing the order.');
                });
        }
        async function fetchFoodsToRemove() {
            try {
                console.log("Fetching foods to remove..."); // Debugging log
                const response = await fetch('fetch_foods_to_remove.php');

                if (!response.ok) {
                    throw new Error('Failed to fetch foods');
                }

                const data = await response.json();

                console.log("Data fetched:", data); // Log the entire response

                if (data.error) {
                    console.error("Error:", data.error);
                    alert('Error fetching food items to remove: ' + data.error);
                    return;
                }

                const removeFoodContainer = document.getElementById('removeFoodContainer');
                removeFoodContainer.innerHTML = ''; // Clear existing content

                if (data.length === 0) {
                    removeFoodContainer.innerHTML = '<p>No food items to remove.</p>';
                    return;
                }

                data.forEach(food => {
                    const foodCard = document.createElement('div');
                    foodCard.className = 'food-card';
                    foodCard.innerHTML = `
                <h3>${food.name}</h3>
                <p><strong>Price:</strong> ৳${food.price}</p>
                <p><strong>Id:</strong> ${food.id}</p>
                <button class="btn" onclick="removeFood(${food.id})">Remove</button>
            `;
                    removeFoodContainer.appendChild(foodCard);
                });
            } catch (error) {
                console.error('Error fetching foods to remove:', error);
                alert('Error fetching food items to remove. Please try again later.');
            }
        }


        // Function to remove food item by foodId
        // Function to remove a food item
        async function removeFood(foodId) {
            if (!foodId) {
                alert("Food ID is missing.");
                return;
            }

            try {
                const response = await fetch('remove_food.php', {
                    method: 'POST',  // POST method to send data
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ food_id: foodId })  // Send food_id in JSON format
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    fetchFoodsToRemove();  // Optionally refresh the list
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error removing food. Please try again later.');
            }
        }

        // Event listener for remove buttons
        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', function () {
                const foodId = this.getAttribute('data-food-id');  // Get food_id from the button's data attribute
                if (foodId) {
                    removeFood(foodId);  // Pass foodId to removeFood function
                } else {
                    alert("Food ID is missing.");
                }
            });
        });



        async function showSellerInfo(sellerId) {
            try {
                const response = await fetch('get_seller_rating.php?seller_id=${sellerId}');

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);

                }

                const result = await response.json();
                if (result.status === "success") {
                    const { seller_name, seller_id, average_rating } = result.data;
                    openPopup(seller_name, seller_id, average_rating);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching seller info:', error);
                alert('Error fetching seller info. Please try again later.');
            }
        }

        function openPopup(name, id, rating) {
            document.getElementById('sellerNamePopup').textContent = name;
            document.getElementById('sellerIdPopup').textContent = id;
            document.getElementById('sellerRatingPopup').textContent = rating ? rating.toFixed(1) : 'Not rated yet';
            document.getElementById('sellerInfoPopup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('sellerInfoPopup').style.display = 'none';
        }

        window.onload = () => {
            document.getElementById('buySection').style.display = 'none';
            document.getElementById('sellSection').style.display = 'none';
            document.getElementById('orderSection').style.display = 'none';
            document.getElementById('deleteSection').style.display = 'none';
        };
    </script>
</body>

</html>