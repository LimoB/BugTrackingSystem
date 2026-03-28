<?php
include('../../config/config.php');

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $query = "SELECT * FROM Users WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>User Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f7fc;
                }

                .container {
                    width: 80%;
                    margin: 40px auto;
                    padding: 20px;
                    background-color: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .header {
                    text-align: center;
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    color: #333;
                }

                .user-details {
                    margin-top: 20px;
                    padding: 20px;
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                }

                .user-details p {
                    margin: 10px 0;
                    font-size: 16px;
                    color: #555;
                }

                .user-details label {
                    font-weight: bold;
                    color: #333;
                }

                .user-details .field {
                    margin-bottom: 10px;
                }

                .action-btn {
                    padding: 10px 20px;
                    background-color: #007BFF;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    text-decoration: none;
                    cursor: pointer;
                    display: inline-block;
                }

                .action-btn:hover {
                    background-color: #0056b3;
                }

                .close-btn {
                    color: #aaa;
                    font-size: 28px;
                    font-weight: bold;
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    cursor: pointer;
                }

                .close-btn:hover {
                    color: black;
                }
            </style>
        </head>

        <body>

            <div class="container">
                <div class="header">
                    User Details
                </div>

                <div class="user-details">
                    <!-- Close button (X) -->
                    <span class="close-btn" onclick="closeUserDetails()">&times;</span>

                    <div class="field"><label>Name:</label> <span><?php echo htmlspecialchars($user['name']); ?></span></div>
                    <div class="field"><label>Email:</label> <span><?php echo htmlspecialchars($user['email']); ?></span></div>
                    <div class="field"><label>Role:</label> <span><?php echo htmlspecialchars($user['role']); ?></span></div>
                    <div class="field"><label>Created At:</label> <span><?php echo htmlspecialchars($user['created_at']); ?></span></div>
                    <div class="field"><label>Last Updated:</label> <span><?php echo htmlspecialchars($user['updated_at']); ?></span></div>

                    <!-- Go Back Button (Removed link, kept for other purposes) -->
                    <!-- <a href="manage-users.php" class="action-btn">Go Back</a> -->
                </div>
            </div>

            <script>
                // Function to close the user details view (hide the container)
                function closeUserDetails() {
                    document.querySelector('.container').style.display = 'none';
                }
            </script>

        </body>

        </html>

<?php
    } else {
        echo json_encode(['error' => 'User not found']);
    }
}
?>