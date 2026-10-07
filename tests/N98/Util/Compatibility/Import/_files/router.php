<?php
// Minimal router for PHP's built-in server, mirroring magento.watch's real URL shape
// (/{distribution}/versions and /{distribution}/versions/{version}) so MagentoWatchClientTest can
// point the real client at this fixture server instead of reimplementing its logic in a double.

header('Content-Type: application/json');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/mage-os/versions') {
    readfile(__DIR__ . '/versions-collection.json');
    return true;
}

if ($path === '/mage-os/versions/3.0.0') {
    readfile(__DIR__ . '/version-entry.json');
    return true;
}

http_response_code(404);
echo json_encode(['error' => 'not found']);

return true;
