<?php require_once('_header.php'); ?>
<a href="index.php">back home</a> &nbsp;
<a href="students.php">Students</a> &nbsp;

<?php
//manage checking for adding or updating
    $id = (isset($_GET['id']))?$_GET['id']:0;
    $tablename ="userdetails";
    $where = ["id"=>$id];

    $forupdate = (isset($_GET['upd']))?$_GET['upd']:0;
    $fordelete = (isset($_GET['del']))?$_GET['del']:0;
    if (!$fordelete) {
    //innitialise the form input fields
    $inptstudentname = "";
    $inptemail = "";
    $inptdob = "";
    $inptgender = "";
    $inptaddress = "";
    $inptregno = "";
    $inptpassword = "";

    $tasktitle =($forupdate)?"Update Student Details":"Add Student Details";

    if ($forupdate) {
        $student = $dbclass->readone($tablename,$where);
        //print_r($student);
        $inptstudentname=$student["names"];
        $inptemail = $student["email"];
        $inptdob = $student["dob"];
        $inptgender = $student["gender"];
        $inptaddress = $student["address"];
        $inptregno = $student["regno"];
        $inptpassword = $student["password"];
    }

?>
<h3><?php echo $tasktitle; ?></h3>
<?php
   if (isset($_POST['btnAddStudent'])) {
        $studentname = $_POST['stdname'];
        $email = $_POST['email'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $address= $_POST['address'];
        $roleid = $_POST['roleid'];
        $isStudent =  $_POST['isStudent'];
        $isActive= $_POST['isActive'];
        $regno = $_POST['regno'];
        $default_password = ("123pass");
        $savedpassword = $_POST['savedpassword'];
        $hasshedpassword = (isset($_POST['password']))?$_POST['password']:$default_password;
        //$hasshedpassword = (isset($_POST['password']))?sha1($_POST['password']):$default_password;
        //if the savedpassword is the same as the hash of our password,
        // then we dont update the        
        $password = ($savedpassword=$hasshedpassword)?$savedpassword:$hasshedpassword;
        
        $data = [
             "names" => $studentname,
             "email" => $email,
             "dob" => $dob,
             "gender" => $gender,
             "address" => $address,
             "roleid" => $roleid,
             "isStudent" => $isStudent,
             "isActive" => $isActive,
             "regno" => $regno,
             "password" => $password             
        ];
        
        
        $lastinsert = ($forupdate)?$dbclass->update($tablename,$data,
        'id=:id',$where):$dbclass->insert($tablename,$data);
        try {
            if ($lastinsert) {
                echo '<div class="alert">successfully perfomed </div>';
                header('Location: students.php');
            } else {
                echo '<div class="alert">Failed to register student data</div>';
            }
            
        } catch (Exception $th) {
            echo $th;
        }
   } 
?>
<div class="container">
    <form class="" action="" method="post">
        <div class="row">
            <div class="col-md-6">
                <h4>Bio data</h4>
                <div class="form-group" >
                    <label for="studentnames">Student Name </label>
                    <input type="text" name="stdname" id="studentnames" class="form-control" value="<?php echo $inptstudentname;?>">
                </div>
                <div  >
                    <label for="studentemail">Email</label>
                    <input type="email" name="email" id="email" class="form-control"  value="<?php echo $inptemail;?>" required>
                </div>
                <div  >
                    <label for="studentdob">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="form-control" value="<?php echo $inptdob;?>" required>
                </div>
                <div >
                    <label for="studentgender">Gender</label>
                    <div class="form-group"> 
                        <select name="gender" id="gender" class="form-control" required>
                            <!-- <option value="">-- Select gender --</option> -->
                            <option value="Female" <?php if($inptgender=="Female") echo " selected";?>>Female</option>
                            <option value="Male"<?php if($inptgender=="Male") echo " selected";?>>Male</option>
                        </select>   
                    </div>
                </div>
                <div >
                    <label for="studentaddress">Address</label>
                    <textarea name="address" id="address" cols="3" class="form-control"><?php echo $inptaddress;?></textarea>
                </div>
                <div  >
                    <label for="studentregno">Reg no</label>
                    <input type="text" name="regno" id="regno" class="form-control" value="<?php echo $inptregno;?>" required>
                </div>
                <div  >
                    <label for="studentpassword">Password</label>
                    <div class="form-group"> 
                        <input type="hidden" name="isActive" value="1">
                        <input type="hidden" name="isStudent" value="1">
                        <input type="hidden" name="roleid" value="1">
                        <input type="password" name="password" id="password" class="form-control">
                        <?php if ($forupdate) { ?>
                        <input type="hidden" name="id" value="<?php echo $id;?>">
                        <?php
                            echo '<small><code>Fill the password only if you want to update your current one.</code></small>';
                        }?>
                        <input type="hidden" name="savedpassword" value="<?php echo $inptpassword; ?>">
                    </div>
                </div>
                
            </div>
            <div class="col-md-6">
                <h4>Course Enrolment</h4>
                <hr>
            </div>
            <div >
                <div>
                    <input type="submit" class="btn btn-raised btn-primary btn-round waves-effect" name="btnAddStudent" value="<?php echo ($forupdate)?"Update ":"Save ";?> Details">
                </div></div>
        </div>
    </form>
</div>
<?php        
    } else {
?>
<div class="alert alert-danger">You are about to delete this record, are you sure you have to?
    <hr>
    <a href="students.php" class="btn btn-primary">Cancel</a>
    <?php
    if (isset($_POST['btnDelete'])) {
        $recdeleted = $dbclass->delete($tablename,'id=:id',$where);
        if ($recdeleted) {
            echo '<div class="alert">successfully Perfomed </div>';
                header('Location: students.php');
            } else {
                echo '<div class="alert">Failed to delete data</div>';
            }        
    }
    ?>
    <form action="" method="post">
    <input type="submit" class="btn btn-danger" name="btnDelete" value="Yes">
    </form>
</div>
<?php
    }
?>
    
                

<?php require_once('_footer.php'); ?>