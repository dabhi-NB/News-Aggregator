<?php
// Start a session to handle bookmarks
session_start();

// API Configuration
$apiKey = '46cea02f7a1241f98fab27166949a0d1';  // Replace with your actual NewsAPI key
$apiUrl = 'https://newsapi.org/v2/top-headlines';

// Default parameters for testing different combinations
$country = 'us';            // Default country
$category = 'business';     // Default category
$sources = '';              // Leave sources blank initially
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';  // Optional search query

// Initialize bookmarks in session
if (!isset($_SESSION['bookmarks'])) {
    $_SESSION['bookmarks'] = [];
}

// Fetch news from API with error handling
function fetchNews($url) {
    echo "<p>Request URL: $url</p>"; // Debug: Display the URL

    // Use cURL to make the API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Add a User-Agent header
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP News Aggregator'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check if the response is successful
    if ($httpCode === 200) {
        curl_close($ch);
        return json_decode($response, true);
    } else {
        // Display error message for debugging
        echo "<p>Error fetching news: HTTP $httpCode</p>";
        curl_close($ch);
        return null;
    }
}

// **1. Testing Different Parameter Combinations**

// Test 1: country + category
$requestUrl = "$apiUrl?apiKey=$apiKey&country=$country&category=$category";
$newsData = fetchNews($requestUrl);
if (!$newsData) {
    echo "<p>Test 1 failed. Trying alternative parameter combinations...</p>";
    
    // Test 2: sources (use this if `country` and `category` fail)
    $sources = 'bbc-news';  // Example source
    $requestUrl = "$apiUrl?apiKey=$apiKey&sources=$sources";
    $newsData = fetchNews($requestUrl);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Aggregator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>News Aggregator</h1>
    
    <!-- Search Form -->
    <form method="GET" action="index.php">
        <input type="text" name="q" placeholder="Search news..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- News Articles -->
    <section>
        <?php if (!empty($newsData['articles'])): ?>
            <?php foreach ($newsData['articles'] as $article): ?>
                <article>
                    <h2><?= htmlspecialchars($article['title']) ?></h2>
                    <p><?= htmlspecialchars($article['description']) ?></p>
                    <a href="<?= htmlspecialchars($article['url']) ?>" target="_blank">Read more</a>

                    <!-- Bookmarking -->
                    <form method="POST" action="index.php">
                        <input type="hidden" name="bookmark" value="<?= htmlspecialchars($article['title']) ?>">
                        <button type="submit">Bookmark</button>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No articles found.</p>
        <?php endif; ?>
    </section>

    <!-- Bookmarked Articles -->
    <section>
        <h2>Bookmarked Articles</h2>
        <?php if (!empty($_SESSION['bookmarks'])): ?>
            <ul>
                <?php foreach ($_SESSION['bookmarks'] as $bookmark): ?>
                    <li><?= htmlspecialchars($bookmark) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No bookmarks added yet.</p>
        <?php endif; ?>
    </section>

</body>
</html>
