<!-- function generatePageUrl($page_id) {
        global $conn;

        $segments = [];
        $current_id = $page_id;

        while ($current_id !== null) {
            $stmt = $conn->prepare("SELECT slug, parent_id FROM pages WHERE id = ?");
            $stmt->bind_param("i", $current_id);
            $stmt->execute();
            $stmt->bind_result($slug, $parent_id);
            $stmt->fetch();
            $segments[] = $slug;
            $current_id = $parent_id;
            $stmt->close();
        }

        $segments = array_reverse($segments);
        return implode('/', $segments);
    } -->

    function generatePageUrl($conn, $page_id) {
    $segments = [];
    $current_id = $page_id;

    while ($current_id !== null) {
        $stmt = $conn->prepare("SELECT slug, parent_id FROM pages WHERE id = ?");
        $stmt->bind_param("i", $current_id);
        $stmt->execute();
        $stmt->bind_result($slug, $parent_id);
        $stmt->fetch();
        $segments[] = $slug;
        $current_id = $parent_id;
        $stmt->close();
    }

    $segments = array_reverse($segments);
    return implode('/', $segments);
}
