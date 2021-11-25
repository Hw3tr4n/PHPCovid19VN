<?php
    echo "<table style='border: solid 1px black; text-align: center; width: 570px'>";
    echo "<tr><th>Số ca nhiễm</th><th>Đang điều trị</th><th>Số ca hồi phục</th><th>Tử vong</th></tr>";

    try {
        $conn = new PDO('pgsql:host=localhost;dbname=TestCSDL;port=5432', 'postgres', 'anonymous99');
        $sql = "SELECT SUM(covid19) as inf , SUM(covid19active) as act, SUM(covid19recover) as rec, SUM(covid19dead) as dead FROM provinces";
        $stmt= $conn->query($sql);
        $publishers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($publishers) {
            // show the publishers
            foreach ($publishers as $publisher) {
                echo "<td style='width: 100px; '>". $publisher['inf'] . '</td>';
                echo "<td style='width: 100px;'>". $publisher['act'] . '</td>';
                echo "<td style='width: 100px; '>". $publisher['rec'] . '</td>';
                echo "<td style='width: 100px; '>". $publisher['dead'] . '</td>';
            }
        }
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
    echo "</table>";
?>