<?php 
require_once('_header.php'); 
$tablename = "userdetails";

?>

<div class="container">
    <h3>Student Details</h3>

    <a href="addstudent.php" class="btn">Add New Student Details</a>

    <?php
    $studentdetails = $dbclass->read($tablename);
    /* echo '<pre>';
    echo print_r($studentdetails);
    echo '</pre>'; 
    SELECT `id`, `names`, `email`, `dob`, `gender`, `address`, `roleid`, `isStudent`, `isActive`, `regno`, `password`, 
    `createdon`, `updatedon` FROM `userdetails` WHERE 1
    */
    if (!empty($studentdetails)) {   
    ?>
    <table class="table table-hover" border="4">
        <thead>
            <tr>
                <th>id</th>
                <th>Student Name</th>
                <th>email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $r=1;
            foreach ($studentdetails as $student) {
            ?>
            <tr>
                <td><?php echo $r; ?></td>

                <td><?php echo $student['names']; ?></td>
                <td><?php echo $student['email']; ?></td>
                <td>
                    <a href="addstudent.php?id=<?php echo $student['id'];?>&upd=1"  class="btn btn-primary">edit</a>&nbsp;
                    <a href="addstudent.php?id=<?php echo $student['id'];?>&del=1"  class="btn btn-danger">delete</a>
                </td>
            </tr>
            <?php
            $r++;
            }
            ?>     

        </tbody>
    </table>
    <?php
    # code...
    } else {
        echo 'No students data was found';
    }
    ?>
</div>
<?php require_once('_footer.php'); ?>