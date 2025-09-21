<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use App\Services\RegisterTenant;
use App\Tenant;
use Illuminate\Support\Facades\DB;

uses(
    Tests\TestCase::class,
    // Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function mockTenant(): Tenant
{
    // Clean up any existing tenant with the same ID
    $existingTenant = Tenant::find('toko_testing');
    if ($existingTenant) {
        // Delete the tenant (this should also clean up tenant-specific data)
        try {
            $existingTenant->delete();
        } catch (Exception $e) {
            // Ignore errors during cleanup
        }
    }
    
    // For MySQL, we still need to clean up the database
    if (DB::getDriverName() === 'mysql') {
        $dbName = 'lakasir_toko_testing';
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
        } catch (Exception $e) {
            // Ignore if database doesn't exist
        }
    }
    
    // For SQLite in testing, clean up tenant database files
    if (DB::getDriverName() === 'sqlite') {
        $dbPath = database_path('tenant_toko_testing.sqlite');
        if (file_exists($dbPath)) {
            try {
                unlink($dbPath);
            } catch (Exception $e) {
                // Ignore if file can't be deleted
            }
        }
    }
    
    $data = [
        'name' => 'toko_testing',
        'domain' => 'toko_testing.'.config('tenancy.central_domains')[0],
        'email' => 'toko_testing@mail.com',
        'password' => 'password',
        'full_name' => 'Toko Testing',
        'shop_name' => 'Toko Testing',
        'business_type' => 'Retail',
    ];
    $sRegisterTenant = new RegisterTenant();
    $tenant = $sRegisterTenant->create($data);

    return $tenant;
}
