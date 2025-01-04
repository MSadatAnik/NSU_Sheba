<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Place</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-image: linear-gradient(to right, #f0f0f0, #d9d9d9);
            color: #333;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        form div {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .book-item {
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 10px 0;
            padding: 10px;
            background-color: #e9f5ff;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px; 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 400px; 
            border-radius: 8px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: red;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <h1>Book Place</h1>
    
    <div class="container">
        <h2>Sell Your Book</h2>
        <form id="bookForm">
            <div>
                <label for="bookName">Book Title:</label>
                <input type="text" id="bookName" required placeholder="Enter book title">
            </div>
            <div>
                <label for="author">Author:</label>
                <input type="text" id="author" required placeholder="Enter author's name">
            </div>
            <div>
                <label for="price">Price (৳):</label>
                <input type="number" id="price" required placeholder="Enter price" min="0">
            </div>
            <div>
                <label for="seller">Seller:</label>
                <input type="text" id="seller" required placeholder="Enter seller id">
            </div>
            <button type="submit" class="btn">List Book</button>
            <div id="errorMsg" class="error"></div>
        </form>

        <h2>Available Books</h2>
        <div id="bookList"></div>
    </div>

    <!-- Seller Info Modal -->
    <div id="sellerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('sellerModal')">&times;</span>
            <h3>Seller Info</h3>
            <p id="sellerInfo"></p>
        </div>
    </div>

    <!-- Buy Confirmation Modal -->
    <div id="buyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('buyModal')">&times;</span>
            <h3>Confirm Purchase</h3>
            <p id="buyConfirmation"></p>
            <button class="btn" id="confirmBuy">Yes, Buy Now</button>
            <button class="btn" onclick="closeModal('buyModal')">Cancel</button>
        </div>
    </div>

    <script>
        document.getElementById('bookForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            document.getElementById('errorMsg').textContent = ''; // Clear previous errors

            const bookName = document.getElementById('bookName').value;
            const author = document.getElementById('author').value;
            const price = document.getElementById('price').value;
            const seller = document.getElementById('seller').value;

            const book = new FormData();
            book.append('book_name', bookName);
            book.append('author', author);
            book.append('price', price);
            book.append('seller', seller);

            try {
                const response = await fetch('bookdcon.php', {
                    method: 'POST',
                    body: book,
                });

                if (response.ok) {
                    displayBooks(); // Refresh the book list
                    document.getElementById('bookForm').reset(); // Reset the form
                } else {
                    throw new Error('Failed to add book.');
                }
            } catch (error) {
                document.getElementById('errorMsg').textContent = error.message; // Display error message
            }
        });

        // Fetch and display initial books on page load
        async function displayBooks() {
            const response = await fetch('bookdcon.php');
            const books = await response.json();
            const bookListDiv = document.getElementById('bookList');
            bookListDiv.innerHTML = '';

            books.forEach((book) => {
                const bookDiv = document.createElement('div');
                bookDiv.classList.add('book-item');
                bookDiv.innerHTML = `
                    <strong>${book.book_name}</strong><br>
                    Author: ${book.author}<br>
                    Price: ৳${book.price}<br>
                    Seller: ${book.seller}
                    <div class="button-container">
                        <button class="btn" onclick="showSellerInfo('${book.seller}', '${book.author}')">Seller Info</button>
                        <button class="btn" onclick="buyBook('${book.book_name}')">Buy Now</button>
                    </div>
                `;
                bookListDiv.appendChild(bookDiv);
            });
        }

        // Display initial books on page load
        displayBooks();

        function showSellerInfo(sellerId, author) {
            document.getElementById('sellerInfo').innerText = `Seller ID: ${sellerId}\nAuthor: ${author}`;
            document.getElementById('sellerModal').style.display = 'block';
        }

        function buyBook(bookName) {
            document.getElementById('buyConfirmation').innerText = `Are you sure you want to buy "${bookName}"?`;
            document.getElementById('buyModal').style.display = 'block';

            // Handle confirmation button
            document.getElementById('confirmBuy').onclick = function() {
                alert(`You have purchased "${bookName}". Thank you!`);
                closeModal('buyModal'); // Close the modal after purchase
            };
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modals when clicking outside of them
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        };
    </script>

</body>
</html>
