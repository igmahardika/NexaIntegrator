<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    use HasFactory, HasUuids;

    /**
     * The database connection name for tenant models.
     */
    protected $connection = 'tenant';

    public $incrementing = false;
    protected $keyType = 'string';
}
