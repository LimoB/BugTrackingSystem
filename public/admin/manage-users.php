<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all users
$query = "SELECT * FROM Users";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            /* Dark background */
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 600px;
            min-width: 300px;
            border-radius: 8px;
            /* Rounded corners */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            /* Soft shadow */
        }

        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #000;
            text-decoration: none;
        }

        /* Modal Form Styling */
        .modal form {
            display: flex;
            flex-direction: column;
        }

        .modal label {
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 16px;
        }

        .modal input[type="text"],
        .modal input[type="email"],
        .modal select {
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal button {
            background-color: #28a745;
            /* Green color for buttons */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal button:hover {
            background-color: #218838;
            /* Darker green when hovering */
        }

        /* Specific Styles for Update Modal */
        #updateUserModal .modal-content {
            width: 60%;
            /* Slightly wider for better visibility */
        }

        #updateUserModal label {
            color: #333;
            /* Darker text for the update form */
        }

        #updateUserModal input,
        #updateUserModal select {
            width: 100%;
            /* Full width inputs for a cleaner look */
            box-sizing: border-box;
            /* Ensures padding does not overflow */
        }

        #updateUserModal button {
            background-color: #007bff;
            /* Blue color for update buttons */
        }

        #updateUserModal button:hover {
            background-color: #0056b3;
            /* Darker blue when hovering */
        }

        /* Specific Styles for View User Modal */
        #viewUserModal .modal-content {
            width: 60%;
            /* Slightly wider for better visibility */
            background-color: #f9f9f9;
            /* Light gray background for a softer feel */
            border-radius: 8px;
            /* Rounded corners */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            /* Soft shadow */
            padding: 30px;
        }

        #viewUserModal .modal-content h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            /* Dark text for better readability */
        }

        #viewUserModal .modal-content .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
        }

        #viewUserModal .modal-content .close-btn:hover {
            color: #000;
            text-decoration: none;
        }

        #viewUserModal .modal-content .user-info {
            font-size: 16px;
            color: #555;
        }

        #viewUserModal .modal-content .user-info .info-item {
            margin-bottom: 10px;
        }

        #viewUserModal .modal-content .user-info .info-item strong {
            color: #333;
            /* Darker text for labels */
        }

        /* Button for creating a user */
        .create-user-btn {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .create-user-btn:hover {
            background-color: #218838;
        }

        /* To show modal */
        .modal.show {
            display: block;
        }
    </style>

</head>

<body>

    <div id="content-area">
        <!-- Create User Button -->
        <button class="create-user-btn" onclick="openCreateUserModal()">Create User</button>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <a href="#" class="view-user-link" data-id="<?php echo $user['id']; ?>">View</a> |
                            <a href="#" class="update-user-link" data-id="<?php echo $user['id']; ?>">Update</a> |
                            <a href="#" class="delete-user-link" data-id="<?php echo $user['id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- CREATE USER MODAL -->
    <!-- CREATE USER MODAL -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeCreateUserModal()">&times;</span>
            <h2>Create New User</h2>
            <form id="createUserForm">
                <label for="userName">Name:</label><br>
                <input type="text" id="userName" name="userName" required><br><br>

                <label for="userEmail">Email:</label><br>
                <input type="email" id="userEmail" name="userEmail" required><br><br>

                <label for="userPassword">Password:</label><br>
                <input type="password" id="userPassword" name="userPassword" required><br><br>

                <label for="userRole">Role:</label><br>
                <select id="userRole" name="userRole" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                    <option value="developer">Developer</option> <!-- Added Developer role -->
                </select><br><br>

                <button type="submit">Create User</button>
            </form>
        </div>
    </div>


    <!-- VIEW USER MODAL -->
    <div id="viewUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>User Details</h2>
            <div id="userDetailsContainer"></div>
        </div>
    </div>
    <!-- UPDATE USER MODAL -->
    <div id="updateUserModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Update User</h2>
            <form id="updateUserForm">
                <input type="hidden" id="updateUserId" name="userId">
                <label for="updateUserName">Name:</label><br>
                <input type="text" id="updateUserName" name="userName" required><br><br>
                <label for="updateUserEmail">Email:</label><br>
                <input type="email" id="updateUserEmail" name="userEmail" required><br><br>
                <label for="updateUserRole">Role:</label><br>
                <select id="updateUserRole" name="userRole" required>
                    <option value="admin">Admin</option>
                    <option value="developer">Developer</option> <!-- Added Developer role -->
                    <option value="user">User</option>
                </select><br><br>
                <button type="submit">Update User</button>
            </form>
        </div>
    </div>

    <script>
        // Open Create User Modal
        function openCreateUserModal() {
            document.getElementById("createUserModal").style.display = "block";
        }

        // // Close Create User Modal
        // function closeCreateUserModal() {
        //     document.getElementById("createUserModal").style.display = "none";
        // }

        // Close the modal when the "X" button is clicked
        $(document).on('click', '.close-btn', function() {
            $(this).closest('.modal').hide(); // Hide the modal that contains the clicked "X" button
        });


        ///i will come back to this
        // Close the modal if clicked outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("createUserModal")) {
                document.getElementById("createUserModal").style.display = "none";
            }
            if (event.target == document.getElementById("viewUserModal")) {
                document.getElementById("viewUserModal").style.display = "none";
            }
            if (event.target == document.getElementById("updateUserModal")) {
                document.getElementById("updateUserModal").style.display = "none";
            }
        }



        // Show the update user modal after fetching user data
        // $('#updateUserModal').show(); // Make sure this is triggered after populating the data


        // Handle Create User Form Submission
        // Handle Create User Form Submission
        $(document).on('submit', '#createUserForm', function(e) {
            e.preventDefault();

            var userData = {
                userName: $('#userName').val(),
                userEmail: $('#userEmail').val(),
                userPassword: $('#userPassword').val(), // Include the password
                userRole: $('#userRole').val()
            };

            $.ajax({
                url: 'create-user-action.php',
                type: 'POST',
                data: userData,
                success: function(response) {
                    alert(response);
                    location.reload(); // Reload to show the new user
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });

            document.getElementById("createUserModal").style.display = "none"; // Close the modal
        });


        // View User Details
        $(document).on('click', '.view-user-link', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');

            $.ajax({
                url: 'fetch-user-details.php',
                type: 'GET',
                data: {
                    user_id: userId
                },
                success: function(data) {
                    $('#userDetailsContainer').html(data);
                    $('#viewUserModal').show();
                },
                error: function() {
                    alert("Failed to load user details.");
                }
            });
        });




        // Fetch user details for update modal
        $(document).on('click', '.update-user-link', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');

            $.ajax({
                url: 'fetch-user-details-update.php',
                type: 'GET',
                data: {
                    user_id: userId
                },
                success: function(data) {
                    const user = JSON.parse(data); // Assuming response is JSON
                    $('#updateUserId').val(user.id);
                    $('#updateUserName').val(user.name);
                    $('#updateUserEmail').val(user.email);
                    $('#updateUserRole').val(user.role);
                    $('#updateUserModal').show();
                }
            });
        });


        // Handle Update User Form Submission
        // Handle Update User Form Submission
        // Handle Update User Form Submission
        $(document).on('submit', '#updateUserForm', function(e) {
            e.preventDefault();

            var userData = {
                userId: $('#updateUserId').val(),
                userName: $('#updateUserName').val(),
                userEmail: $('#updateUserEmail').val(),
                userRole: $('#updateUserRole').val()
            };

            $.ajax({
                url: 'update-user-action.php',
                type: 'POST',
                data: userData,
                success: function(response) {
                    alert(response); // Alert the user about the update status
                    location.reload(); // Reload the page to show updated user data
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error); // Show any errors that occurred
                }
            });

            document.getElementById("updateUserModal").style.display = "none"; // Close the modal
        });



        // Delete User
        $(document).on('click', '.delete-user-link', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');

            if (confirm("Are you sure you want to delete this user?")) {
                $.ajax({
                    url: 'delete-user-action.php',
                    type: 'POST',
                    data: {
                        user_id: userId
                    },
                    success: function(response) {
                        alert(response);
                        location.reload(); // Reload to show the updated list
                    },
                    error: function() {
                        alert("Failed to delete user.");
                    }
                });
            }
        });
    </script>

</body>

</html>