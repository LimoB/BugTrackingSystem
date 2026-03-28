<?php
session_start();
include('../../config/config.php');
include('../../includes/auth.php');

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$query = "SELECT * FROM Projects";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 600px;
            min-width: 300px;
        }

        .close-btn,
        .close-view-btn,
        .close-update-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-btn:hover,
        .close-view-btn:hover,
        .close-update-btn:hover,
        .close-btn:focus,
        .close-view-btn:focus,
        .close-update-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #createProjectButton {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
        }

        #createProjectButton:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
        }

        #message {
            margin: 10px 0;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid;
            border-radius: 5px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>

    <a href="#" id="createProjectButton" class="button">Create New Project</a>

    <!-- Message Container -->
    <div id="message"></div>

    <table>
        <thead>
            <tr>
                <th>Project ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($project = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $project['id']; ?></td>
                    <td><?php echo htmlspecialchars($project['name']); ?></td>
                    <td><?php echo ucfirst($project['status']); ?></td>
                    <td>
                        <a href="#" class="view-project-link" data-id="<?php echo $project['id']; ?>">View</a> |
                        <a href="#" class="update-project-link" data-id="<?php echo $project['id']; ?>">Update</a> |
                        <a href="#" class="delete-project-link" data-id="<?php echo $project['id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- CREATE MODAL -->
    <div id="createProjectModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Create New Project</h2>
            <form id="createProjectForm">
                <label for="projectName">Project Name</label>
                <input type="text" id="projectName" name="projectName" required>

                <label for="projectDescription">Project Description</label>
                <textarea id="projectDescription" name="projectDescription" required></textarea>

                <label for="projectStatus">Status</label>
                <select id="projectStatus" name="projectStatus" required>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>

                <button type="submit" class="submit-btn">Create Project</button>
            </form>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div id="viewProjectModal" class="modal">
        <div class="modal-content">
            <span class="close-view-btn">&times;</span>
            <h2>Project Details</h2>
            <div id="projectDetailsContainer"></div>
        </div>
    </div>

    <!-- UPDATE MODAL -->
    <div id="updateProjectModal" class="modal">
        <div class="modal-content">
            <span class="close-update-btn">&times;</span>
            <h2>Update Project</h2>
            <div id="updateProjectFormContainer"></div>
        </div>
    </div>

    <script>
        // CREATE MODAL
        const createModal = document.getElementById("createProjectModal");
        const createBtn = document.getElementById("createProjectButton");
        const closeCreateBtn = document.getElementsByClassName("close-btn")[0];

        createBtn.onclick = function() {
            createModal.style.display = "block";
        }

        closeCreateBtn.onclick = function() {
            createModal.style.display = "none";
        }

        // VIEW MODAL
        const viewModal = document.getElementById("viewProjectModal");
        const closeViewBtn = document.getElementsByClassName("close-view-btn")[0];

        $(document).on('click', '.view-project-link', function(e) {
            e.preventDefault();
            const projectId = $(this).data('id');

            $.ajax({
                url: 'fetch-project-details.php',
                type: 'GET',
                data: {
                    project_id: projectId
                },
                success: function(data) {
                    $('#projectDetailsContainer').html(data);
                    viewModal.style.display = "block";
                },
                error: function() {
                    alert("Failed to load project details.");
                }
            });
        });

        closeViewBtn.onclick = function() {
            viewModal.style.display = "none";
        }

        // UPDATE MODAL
        const updateModal = document.getElementById("updateProjectModal");
        const closeUpdateBtn = document.getElementsByClassName("close-update-btn")[0];

        $(document).on('click', '.update-project-link', function(e) {
            e.preventDefault();
            const projectId = $(this).data('id');

            $.ajax({
                url: 'fetch-project-update-form.php',
                type: 'GET',
                data: {
                    project_id: projectId
                },
                success: function(data) {
                    $('#updateProjectFormContainer').html(data);
                    updateModal.style.display = "block";
                },
                error: function() {
                    alert("Failed to load update form.");
                }
            });
        });

        closeUpdateBtn.onclick = function() {
            updateModal.style.display = "none";
        }

        // DELETE PROJECT
        $(document).on('click', '.delete-project-link', function(e) {
            e.preventDefault();
            const projectId = $(this).data('id');
            if (confirm('Are you sure you want to delete this project?')) {
                $.ajax({
                    url: 'delete-project-action.php',
                    type: 'POST',
                    data: {
                        project_id: projectId
                    },
                    success: function(response) {
                        alert(response);
                        location.reload(); // Reload the page to reflect changes
                    },
                    error: function(xhr, status, error) {
                        alert("Error: " + error);
                    }
                });
            }
        });

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target == createModal) createModal.style.display = "none";
            if (event.target == viewModal) viewModal.style.display = "none";
            if (event.target == updateModal) updateModal.style.display = "none";
        }

        // AJAX: Create project
        // AJAX: Create project
        // AJAX: Create project
        $("#createProjectForm").submit(function(event) {
            event.preventDefault();

            const name = $("#projectName").val();
            const description = $("#projectDescription").val();
            const status = $("#projectStatus").val(); // Get the selected status

            $.ajax({
                url: 'create-project-action.php',
                type: 'POST',
                data: {
                    projectName: name,
                    projectDescription: description,
                    projectStatus: status
                },
                success: function(response) {
                    console.log("Raw response:", response); // Log full raw response

                    try {
                        // Attempt to parse the response as JSON
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        console.log("Parsed response:", data); // Log parsed response

                        // Check if the success message exists before appending it
                        if (data.success) {
                            $('#message').html('<div class="alert success">' + data.success + '</div>');

                            // Optional: Automatically hide after 2 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 2000);

                        } else if (data.error) {
                            $('#message').html('<div class="alert error">' + data.error + '</div>');
                        }

                    } catch (e) {
                        console.log("Invalid JSON response:", response);
                        $('#message').html('<div class="alert error">Invalid response format from server.</div>').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", error); // Log any AJAX errors
                    $('#message').html('<div class="alert error">Error: ' + error + '</div>').show();
                }
            });
        });
    </script>
</body>

</html>