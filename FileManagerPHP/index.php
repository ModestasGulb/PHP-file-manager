<html>

<head>
    <link rel="stylesheet" href="Style.css">
    <title>PHP file manager</title>
</head>

<body>
    <?php

    // Logout logic

    session_start();
    if ($_GET['logout'] == true) {
        session_destroy();
        header('Location:/FileManagerPHP/');
    }

    if (isset($_GET['logout']) && $_GET['logout'] == true) {
        session_start();
        unset($_SESSION['username']);
        unset($_SESSION['password']);
        unset($_SESSION['logged_in']);
    }

    // Authentication

    if (isset($_POST['login']) && $_POST['uname'] == 'test' && $_POST['psw'] == 'test') {
        $_SESSION['logged_in'] = true;
        $_SESSION['timeout'] = time();
        $_SESSION['username'] = 'test';
    }

    if ($_SESSION['logged_in'] == false) {

        // Login form

        print('<form method="POST" id="logIn"><h1>PHP file manager</h1><div><label for="uname"><b>Username</b></label>
    <input type="text" placeholder="test" name="uname" required><label for="psw"><b>Password</b></label>
    <input type="password" placeholder="test" name="psw" required><button type="submit" name="login">Login</button><div></form>');
        if (isset($_POST['login'])) {
            print('<br><h3>Wrong username or password</h3><br>');
        }
    } else {


        // Logout link

        print('<div id="log_out"><a href="index.php?logout=true"><b>logout</b></a></div>');

        // Show path

        print('<h1>Path:');
        if (isset($_GET['path'])) {
            print("/FileManagerPHP" . substr(($_GET['path']), 1));
        } else {
            print($_SERVER['REQUEST_URI']);
        }

        $dirToScan = './';
        if (isset($_GET['path'])) {
            $dirToScan = $_GET['path'];
        }

        print('</h1>');

        // Available actions toolbar

        print('<nav>');

        // Go back button

        if (!isset($_GET['path'])) {
            print('<form><button type="submit" id="back_btn" disabled>&#x2934</button></form>');
        } else if (dirname($_SERVER['REQUEST_URI'], 2) == "/FileManagerPHP") {
            print('<form action="' . dirname($_SERVER['REQUEST_URI'], 2) . ' "method="POST">
            <button type="submit" id="back_btn">&#x2934</button></form>');
        } else {
            print('<form action="' . dirname($_SERVER['REQUEST_URI']) . '/' . ' "method="POST">
            <button type="submit" id="back_btn">&#x2934</button></form>');
        }

        // Home button

        print('<form action="/FileManagerPHP" method="POST"><button type="submit" id="home_btn">&#127968</button></form>');

        // Create folder form

        print('<form method="POST"><button type="submit" id="create_folder_btn">&#128193</button><input type="text" 
        name="folderName" id="create_folder_input" placeholder="Enter folder name"></form>');

        // Folder name validation and folder creation

        if (isset($_POST['folderName'])) {
            $forbiddenCharacters = ['/', '\\', ':', '*', '?', '<', '>', '"'];
            $containsForbidden = false;
            foreach ($forbiddenCharacters as $value) {
                if (strpos($_POST['folderName'], $value) !== false) {
                    $containsForbidden = true;
                    break;
                }
            }
            if ($containsForbidden === true) {
                $systemMsg  = '<br>Unable to create folder. Reason: folder cannot contain any of these characters: / \ : * ? < > "';
            } else if ($_POST['folderName'] == "") {
                $systemMsg = 'Enter folder name';
            } else if (file_exists(getcwd() . substr($_GET['path'], 1) . $_POST['folderName'])) {
                $systemMsg = '<br>Unable to create folder. Reason: folder with the same name already exists';
            } else {
                if (!isset($_GET['path'])) {
                    $systemMsg  = getcwd() . substr($_GET['path'], 1) . '/' . $_POST['folderName'];
                    mkdir(getcwd() . substr($_GET['path'], 1) . '/' . $_POST['folderName']);
                    $systemMsg = 'Folder created successfully!';
                } else {
                    mkdir(getcwd() . substr($_GET['path'], 1) . $_POST['folderName']);
                    $systemMsg = 'Folder created successfully!';
                }
            }
        }

        // Delete file validation and execution

        if (isset($_POST['filePathDelete'])) {
            if ((basename($_POST['filePathDelete']) == 'index.php') || (basename($_POST['filePathDelete']) == 'Style.css') ||
                (basename($_POST['filePathDelete']) == 'Manual.txt')
            ) {
                $systemMsg = 'Cannot delete FileManagerPHP files';
            } else if (unlink($_POST['filePathDelete'])) {
                $systemMsg = 'File was deleted successfully';
            } else {
                $systemMsg = 'Unable to delete file';
            }
        }

        // Upload file button

        print('<form method="post" enctype="multipart/form-data" id="uploadFile"><label for="fileToUpload">Choose file</label>
            <input type="file" name="fileToUpload" id="fileToUpload"><input type="submit" value="Upload" name="uploadFile"></form>');

        // Upload file validation and execution

        if (isset($_POST["uploadFile"])) {
            $target_dir = 'Uploads/';
            $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file already exists
            if (file_exists($target_file)) {
                $systemMsg = "Sorry, file already exists.";
                $uploadOk = 0;
            }
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 1048576000) {
                $systemMsg = "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            // Allow certain file formats
            if (
                $imageFileType != "txt" && $imageFileType != "pdf" && $imageFileType != "doc"
                && $imageFileType != "xdoc"
            ) {
                $systemMsg = "Sorry, only TXT, PDF, DOC & XDOC files are allowed.";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                $systemMsg .= " Your file was not uploaded.";
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    $systemMsg = "File was successfully uploaded";
                } else {
                    $systemMsg .= "Sorry, there was an error uploading your file.";
                }
            }
        }

        // File download execution

        if (isset($_POST['filePathDownload'])) {
            $file = './' . $_POST['filePathDownload'];
            $fileToDownloadEscaped = str_replace("&nbsp;", " ", htmlentities($file, null, 'utf-8'));
            header('Content-Description: File Transfer');
            header('Content-Type: application/txt');
            header('Content-Disposition: attachment; filename=' . basename($fileToDownloadEscaped));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileToDownloadEscaped));
            flush();
            readfile($fileToDownloadEscaped);
            exit;
        }

        print('</nav>');

        // Display messages

        print('<div>' . $systemMsg . '</div>');

        // Create table

        print('<table><thead><tr><th>Name</th><th>Type</th><th>Actions</th></tr></thead><tbody>');

        $fileList = scandir($dirToScan);
        foreach ($fileList as $value) {
            if ($value != '.' && $value != '..') {
                $Row = '<tr>';
                if (is_dir($dirToScan . $value)) {
                    if (!isset($_GET['path'])) {
                        $pathLink = $_SERVER['REQUEST_URI'] . '?path=' . $dirToScan . $value;
                    } else {
                        $pathLink = $_SERVER['REQUEST_URI'] . $value;
                    }

                    // Name
                    $Row .= '<td><a href="' . $pathLink . '/">' . $value . '</a></td>';

                    // Type
                    $Row .= '<td>Directory</td>';

                    // Actions
                    $Row .= '<td></td>';
                } else {
                    // Name
                    $Row .= '<td>' . $value . '</td>';

                    // Type
                    $Row .= '<td>File</td>';

                    // Actions
                    $Row .= '<td class="actions"><form method="POST">
                    <button type="submit" class="delete" name="filePathDelete" value="' . $dirToScan . $value .
                        '">Delete</button></form><form method="POST">
                        <button type="submit" class="download" name="filePathDownload" value="' . $dirToScan . $value .
                        '">Download</button></form></td>';
                }
                $Row .= '</tr>';
            }
            print($Row);
        }

        print('</tbody></table>');
    }
    ?>


</body>

</html>