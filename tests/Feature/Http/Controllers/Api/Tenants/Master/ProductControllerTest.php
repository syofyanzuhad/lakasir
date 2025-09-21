<?php

use App\Models\Tenants\User;
use Illuminate\Http\Response;
use Tests\RefreshDatabaseWithTenant;

use function Pest\Laravel\actingAs;

uses(RefreshDatabaseWithTenant::class);

test("can'\t create product", function () {
    $user = User::first();
    $response = actingAs($user)->postJson('/api/master/product', []);
    
    // Accept either 400 or 422 as both indicate validation/bad request errors
    expect($response->getStatusCode())->toBeIn([Response::HTTP_BAD_REQUEST, Response::HTTP_UNPROCESSABLE_ENTITY]);
});
