<?php
$servername = "localhost";
$username = "usrdbfarmaadmon";
$password = "nA7PKgFQHzsZ6Wd";
$dbname = "dbfarmaadmon";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT increment_id, entity_id, created_at FROM sales_flat_order WHERE shipping_method = 'vidalink_shipping' and status = 'complete' and state = 'complete'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    while($rowp = $result->fetch_assoc()) {
        $sql = "SELECT comment FROM sales_flat_order_status_history where parent_id = ".$rowp["entity_id"];
        $nf = true;
        $resulth = $conn->query($sql);
        while($rowh = $resulth->fetch_assoc()) {
            if($rowh["comment"] != NULL){
                if (strpos($rowh["comment"], 'NF ') !== false) {
                    $nf = false;
                    //echo $rowh["comment"]. "<br>";
                    break;
                }
            }
        }
        if($nf){echo $rowp["increment_id"] . ", " . $rowp["created_at"] . "<br>";}
    }
}
$conn->close();