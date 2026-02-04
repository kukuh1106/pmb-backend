<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| S3 Connection Test Route
|--------------------------------------------------------------------------
| Test AWS S3 connection and file operations
| Access: http://localhost:8000/test-s3
*/
Route::get('/test-s3', function () {
    try {
        $testFile = 'test/connection-test.txt';
        $testContent = 'AWS S3 Connection Test - ' . now()->toDateTimeString();
        
        // Test 1: Write file
        Storage::disk('s3')->put($testFile, $testContent);
        
        // Test 2: Check if file exists
        $exists = Storage::disk('s3')->exists($testFile);
        
        // Test 3: Read file
        $content = Storage::disk('s3')->get($testFile);
        
        // Test 4: Get file size
        $size = Storage::disk('s3')->size($testFile);
        
        // Test 5: List files in test directory
        $files = Storage::disk('s3')->files('test');
        
        // Test 6: Get temporary URL (signed URL)
        $url = Storage::disk('s3')->temporaryUrl($testFile, now()->addMinutes(5));
        
        // Test 7: Delete file
        Storage::disk('s3')->delete($testFile);
        
        return response()->json([
            'success' => true,
            'message' => '✅ AWS S3 Connection Successful!',
            'tests' => [
                'write' => '✅ File written successfully',
                'exists' => $exists ? '✅ File exists check passed' : '❌ File exists check failed',
                'read' => $content === $testContent ? '✅ File read successfully' : '❌ File read failed',
                'size' => "✅ File size: {$size} bytes",
                'list' => '✅ Files listed: ' . count($files),
                'temporary_url' => '✅ Signed URL generated',
                'delete' => '✅ File deleted successfully',
            ],
            'config' => [
                'disk' => 's3',
                'bucket' => config('filesystems.disks.s3.bucket'),
                'region' => config('filesystems.disks.s3.region'),
            ],
            'sample_url' => substr($url, 0, 100) . '...',
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '❌ AWS S3 Connection Failed',
            'error' => $e->getMessage(),
            'config' => [
                'disk' => 's3',
                'bucket' => config('filesystems.disks.s3.bucket'),
                'region' => config('filesystems.disks.s3.region'),
            ],
            'troubleshooting' => [
                '1. Check AWS credentials in .env file',
                '2. Verify bucket name and region',
                '3. Ensure IAM user has proper permissions',
                '4. Check if bucket exists in AWS Console',
            ],
        ], 500);
    }
});
