<?php
// Include your database configuration file
include 'config.php';

// Start XML content
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Base URL of your website
$base_url = 'https://miamiairport-mia.com/'; // Replace with your actual base URL

// Add manual entry
$xml .= '<url>';
$xml .= '<loc>' . htmlspecialchars($base_url) . '</loc>';
$xml .= '<lastmod>2024-05-10</lastmod>';
$xml .= '<priority>1</priority>';
$xml .= '<changefreq>always</changefreq>';
$xml .= '</url>';

// Fetch pages with XML_Priority
$sql = "SELECT id, slug, date, XML_Priority, parent_id FROM pages WHERE XML_Priority IS NOT NULL";
$result = $conn->query($sql);

// Fetch parent slugs and map them
$parent_slugs = [];
$parent_ids = [];

while ($row = $result->fetch_assoc()) {
    if ($row['parent_id']) {
        $parent_ids[] = $row['parent_id'];
    }
}

if (!empty($parent_ids)) {
    $parent_ids_placeholder = implode(',', array_fill(0, count($parent_ids), '?'));
    $parent_sql = "SELECT id, slug FROM pages WHERE id IN ($parent_ids_placeholder)";
    $stmt = $conn->prepare($parent_sql);
    $stmt->bind_param(str_repeat('i', count($parent_ids)), ...$parent_ids);
    $stmt->execute();
    $parent_result = $stmt->get_result();

    while ($parent_row = $parent_result->fetch_assoc()) {
        $parent_slugs[$parent_row['id']] = $parent_row['slug'];
    }
}

// Reset result pointer to fetch pages
$result->data_seek(0);

// Iterate over fetched pages and create XML entries
while ($row = $result->fetch_assoc()) {
    $url_slug = $row['slug'];
    
    if ($row['parent_id'] && isset($parent_slugs[$row['parent_id']])) {
        $url_slug = $parent_slugs[$row['parent_id']] . '/' . $row['slug'];
    }
    
    $xml .= '<url>';
    $xml .= '<loc>' . htmlspecialchars($base_url . $url_slug) . '/</loc>';
    if (!empty($row['date'])) {
        $xml .= '<lastmod>' . htmlspecialchars($row['date']) . '</lastmod>';
    }
    if (!empty($row['XML_Priority'])) {
        $xml .= '<priority>' . htmlspecialchars($row['XML_Priority']) . '</priority>';
    }
    $xml .= '<changefreq>always</changefreq>';
    $xml .= '</url>';
}

// End XML content
$xml .= '</urlset>';

// Close database connection
$conn->close();

// Save XML content to sitemap.xml file
$file = 'sitemap.xml';
file_put_contents($file, $xml);

// Redirect
header("Location: sitemap.xml");
?>
