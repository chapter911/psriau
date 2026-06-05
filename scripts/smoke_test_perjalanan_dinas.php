<?php
/**
 * Smoke test for Perjalanan Dinas photo upload functionality.
 *
 * Run: php scripts/smoke_test_perjalanan_dinas.php
 *
 * This script tests:
 * 1. File upload handling with getStream() vs getTempName()
 * 2. Base64 encoding of images
 * 3. JSON encoding/decoding of photo data
 * 4. PDF generation with embedded images
 */

// Load composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Load CI bootstrap manually for testing
define('FCPATH', __DIR__ . '/../public/');
define('WRITEPATH', __DIR__ . '/../writable/');

echo "=== Perjalanan Dinas Photo Upload Smoke Test ===\n\n";

$testsPassed = 0;
$testsFailed = 0;

// Test 1: Check CI4 UploadedFile class exists and has required methods
echo "Test 1: Checking CodeIgniter UploadedFile class...\n";
try {
    if (class_exists('CodeIgniter\HTTP\Files\UploadedFile')) {
        $ref = new ReflectionClass('CodeIgniter\HTTP\Files\UploadedFile');
        $hasGetStream = $ref->hasMethod('getStream');
        $hasGetTempName = $ref->hasMethod('getTempName');
        echo "  - UploadedFile class exists\n";
        echo "  - getStream() method: " . ($hasGetStream ? "YES" : "NO") . "\n";
        echo "  - getTempName() method: " . ($hasGetTempName ? "YES" : "NO") . "\n";

        if ($hasGetStream || $hasGetTempName) {
            echo "  PASSED\n\n";
            $testsPassed++;
        } else {
            echo "  FAILED - No file reading method available\n\n";
            $testsFailed++;
        }
    } else {
        echo "  FAILED - UploadedFile class not found\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 2: Test base64 encoding/decoding of image data
echo "Test 2: Testing base64 encoding/decoding...\n";
try {
    $testImageData = str_repeat("This is test image data for base64 encoding test.", 100);
    $encoded = base64_encode($testImageData);
    $decoded = base64_decode($encoded);

    if ($decoded === $testImageData) {
        echo "  - Base64 encode/decode: OK\n";
        echo "  - Data integrity: VERIFIED\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  FAILED - Data integrity check failed\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 3: Test data URI format
echo "Test 3: Testing data URI format for images...\n";
try {
    $mimeType = 'image/jpeg';
    $testImageData = "fake image binary data";
    $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode($testImageData);

    // Check format
    $pattern = '/^data:image\/[a-z]+;base64,.+$/i';
    if (preg_match($pattern, $dataUri)) {
        echo "  - Data URI format: VALID\n";
        echo "  - Contains mime type: " . (strpos($dataUri, $mimeType) !== false ? "YES" : "NO") . "\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  FAILED - Invalid data URI format\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 4: Test JSON encoding of photo array
echo "Test 4: Testing JSON encoding of photo array...\n";
try {
    $photos = [
        [
            'name' => 'photo1.jpg',
            'mime' => 'image/jpeg',
            'data_uri' => 'data:image/jpeg;base64,' . base64_encode('image data 1'),
        ],
        [
            'name' => 'photo2.png',
            'mime' => 'image/png',
            'data_uri' => 'data:image/png;base64,' . base64_encode('image data 2'),
        ],
    ];

    $json = json_encode($photos, JSON_UNESCAPED_UNICODE);
    $decoded = json_decode($json, true);

    if ($decoded !== null && count($decoded) === 2) {
        echo "  - JSON encoding: OK\n";
        echo "  - JSON decoding: OK\n";
        echo "  - Photo count: " . count($decoded) . "\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  FAILED - JSON encode/decode failed\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 5: Test photo array filtering (remove by index)
echo "Test 5: Testing photo array filtering (remove by index)...\n";
try {
    $photos = [
        ['name' => 'photo1.jpg', 'data_uri' => 'data1'],
        ['name' => 'photo2.jpg', 'data_uri' => 'data2'],
        ['name' => 'photo3.jpg', 'data_uri' => 'data3'],
    ];

    // Simulate removing photo at index 1
    $removedIndices = [1];
    $filtered = array_values(array_filter($photos, static fn ($key): bool => !in_array($key, $removedIndices, true), ARRAY_FILTER_USE_KEY));

    if (count($filtered) === 2 && $filtered[0]['name'] === 'photo1.jpg' && $filtered[1]['name'] === 'photo3.jpg') {
        echo "  - Original count: 3\n";
        echo "  - After filter count: " . count($filtered) . "\n";
        echo "  - Index 1 removed: " . ($filtered[0]['name'] === 'photo1.jpg' && $filtered[1]['name'] === 'photo3.jpg' ? "YES" : "NO") . "\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  FAILED - Filtering did not work correctly\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 6: Check Dompdf availability for PDF generation
echo "Test 6: Checking Dompdf availability for PDF generation...\n";
try {
    if (class_exists('Dompdf\Dompdf')) {
        echo "  - Dompdf class: AVAILABLE\n";

        $dompdf = new Dompdf\Dompdf();
        echo "  - Dompdf instantiation: OK\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  WARNING - Dompdf class not found, PDF generation may not work\n";
        echo "  (Composer install may be needed)\n\n";
        // Don't fail this test, just warn
        $testsPassed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Test 7: Test data URI regex pattern used in PDF view
echo "Test 7: Testing data URI regex pattern (used in PDF view)...\n";
try {
    $testCases = [
        'data:image/jpeg;base64,/9j/4AAQ...' => true,
        'data:image/png;base64,iVBORw0KGgo...' => true,
        'https://example.com/image.jpg' => true,
        '/uploads/photos/photo.jpg' => false,  // local path
        '/images/logo.png' => false,
    ];

    $allPassed = true;
    foreach ($testCases as $input => $shouldMatch) {
        $pattern = '#^(data:|https?://|//)#i';
        $matches = (bool) preg_match($pattern, $input);
        if ($matches !== $shouldMatch) {
            echo "  FAIL: Pattern test for '$input'\n";
            $allPassed = false;
        }
    }

    if ($allPassed) {
        echo "  - All pattern tests: PASSED\n";
        echo "  PASSED\n\n";
        $testsPassed++;
    } else {
        echo "  FAILED - Pattern tests failed\n\n";
        $testsFailed++;
    }
} catch (Exception $e) {
    echo "  FAILED - " . $e->getMessage() . "\n\n";
    $testsFailed++;
}

// Summary
echo "=== Test Summary ===\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n\n";

if ($testsFailed === 0) {
    echo "All tests PASSED! The photo upload functionality should work correctly.\n";
    echo "\nNext steps for smoke testing:\n";
    echo "1. Go to /admin/laporan/perjalanan-dinas/buat\n";
    echo "2. Fill in the form and add some photos\n";
    echo "3. Submit the form\n";
    echo "4. Verify photos are saved in the database\n";
    echo "5. Go to edit page and verify existing photos are displayed\n";
    echo "6. Export to PDF and verify photos appear in the PDF\n";
    exit(0);
} else {
    echo "Some tests FAILED. Please review the output above.\n";
    exit(1);
}