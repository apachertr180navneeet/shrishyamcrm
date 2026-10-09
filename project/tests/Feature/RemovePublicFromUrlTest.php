<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemovePublicFromUrlTest extends TestCase
{
    public function test_redirects_urls_containing_public_to_clean_urls()
    {
        // Visiting /public/admin/login should redirect to /admin/login
        $response = $this->get('/public/admin/login');
        $response->assertStatus(301);
        $response->assertRedirect('/admin/login');

        // Visiting /public/admin/certificates should redirect to /admin/certificates
        $response2 = $this->get('/public/admin/certificates');
        $response2->assertStatus(301);
        $response2->assertRedirect('/admin/certificates');
    }

    public function test_preserves_query_parameters_when_removing_public()
    {
        $response = $this->get('/public/admin/certificates?page=2&search=test');
        $response->assertStatus(301);
        $response->assertRedirect('/admin/certificates?page=2&search=test');
    }

    public function test_subfolder_url_with_public_redirects_to_subfolder_without_public()
    {
        $response = $this->get('/crm/public/admin/login');
        $response->assertStatus(301);
        $response->assertRedirect('/crm/admin/login');
    }

    public function test_normal_urls_do_not_redirect()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }
}
