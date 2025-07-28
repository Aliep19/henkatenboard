<?php
// Fetch news images for Production and QA (limit 5 each)
$query_news = "SELECT filename, category, description, uploaded_at 
               FROM news_images 
               WHERE category IN ('production', 'qa') 
               AND uploaded_at >= CURDATE() - INTERVAL 1 MONTH
               ORDER BY FIELD(category, 'production', 'qa'), uploaded_at DESC
               LIMIT 10";

// Fetch henkaten data from method, material, and machine
$query_henkaten = "
    (SELECT 'method' AS category, gambar AS filename, alasan AS description, created_at AS uploaded_at
     FROM method 
     WHERE created_at >= CURDATE() - INTERVAL 1 MONTH AND gambar IS NOT NULL)
    UNION ALL
    (SELECT 'material' AS category, gambar AS filename, alasan AS description, created_at AS uploaded_at
     FROM material 
     WHERE created_at >= CURDATE() - INTERVAL 1 MONTH AND gambar IS NOT NULL)
    UNION ALL
    (SELECT 'machine' AS category, gambar AS filename, alasan AS description, created_at AS uploaded_at
     FROM machine 
     WHERE created_at >= CURDATE() - INTERVAL 1 MONTH AND gambar IS NOT NULL)
    ORDER BY uploaded_at DESC
    LIMIT 15";

$news_images = [];
// Execute news images query
$result_news = mysqli_query($conn, $query_news);
while ($row = mysqli_fetch_assoc($result_news)) {
    $news_images[] = [
        'filename' => 'assets/img/uploads/news/' . $row['filename'],
        'category' => $row['category'],
        'description' => $row['description'],
        'uploaded_at' => $row['uploaded_at']
    ];
}
mysqli_free_result($result_news);

// Execute henkaten query
$result_henkaten = mysqli_query($conn, $query_henkaten);
while ($row = mysqli_fetch_assoc($result_henkaten)) {
    $news_images[] = [
        'filename' => 'uploads/' . $row['filename'], // Path for henkaten images
        'category' => $row['category'],
        'description' => $row['description'],
        'uploaded_at' => $row['uploaded_at']
    ];
}
mysqli_free_result($result_henkaten);

$hasNews = !empty($news_images);
?>
<?php mysqli_close($conn); ?>
