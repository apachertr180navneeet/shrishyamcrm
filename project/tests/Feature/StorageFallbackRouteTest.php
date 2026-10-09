<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageFallbackRouteTest extends TestCase
{
    public function test_can_serve_storage_file_via_fallback_route()
    {
        // Store real fake file in storage/app/public
        $dir = storage_path('app/public/uploads/documents');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $testFilePath = $dir . '/test_member_avatar.jpg';
        file_put_contents($testFilePath, 'fake_image_content');

        $response = $this->get('/storage/uploads/documents/test_member_avatar.jpg');
        $response->assertStatus(200);

        if (file_exists($testFilePath)) {
            unlink($testFilePath);
        }
    }

    public function test_path_traversal_blocked_on_storage_route()
    {
        $response = $this->get('/storage/../../.env');
        $response->assertStatus(404);
    }

    public function test_non_existent_file_returns_404()
    {
        $response = $this->get('/storage/uploads/documents/non_existent_file_99999.jpg');
        $response->assertStatus(404);
    }
}
