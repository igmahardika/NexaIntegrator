<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_is_superadmin_alias_and_roles(): void
    {
        $superadmin = new User(['role' => 'superadmin']);
        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertTrue($superadmin->isSuperadmin());

        $siteAdmin = new User(['role' => 'site_admin']);
        $this->assertTrue($siteAdmin->isSiteAdmin());
        $this->assertFalse($siteAdmin->isSuperAdmin());

        $operator = new User(['role' => 'operator']);
        $this->assertTrue($operator->isOperator());

        $cashier = new User(['role' => 'cashier']);
        $this->assertTrue($cashier->isCashier());

        $advertiser = new User(['role' => 'advertiser']);
        $this->assertTrue($advertiser->isAdvertiser());
    }
}
