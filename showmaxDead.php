<?php
    echo "<table style=' text-align: center;'>";
    echo "<tr><th>Tỉnh ghi nhận nhiều ca tử vong nhất hiện tại:</th</tr>";

    try {
        $conn = new PDO('pgsql:host=localhost;dbname=TestCSDL;port=5432', 'postgres', 'anonymous99');
        $sql = "select name_1 from provinces where covid19dead > 5";
        $stmt= $conn->query($sql);
        $publishers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($publishers) {
            // show the publishers
            foreach ($publishers as $publisher) {
                echo "<td style='width: 70px; color:red'>".$publisher['name_1'] .'</td>';
            }
        }
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    echo "</table>";
?>