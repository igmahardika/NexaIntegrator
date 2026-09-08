<?php

namespace App\Policies;

use App\Models\SurveyCampaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CampaignPolicy
{
    /**
     * Determine whether the user can view any campaigns.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'advertiser']);
    }

    /**
     * Determine whether the user can view the specific campaign.
     */
    public function view(User $user, SurveyCampaign $campaign): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $campaign->advertiser_id === $user->id;
    }

    /**
     * Determine whether the user can create campaigns.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'advertiser']);
    }

    /**
     * Determine whether the user can update the campaign.
     */
    public function update(User $user, SurveyCampaign $campaign): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $campaign->advertiser_id === $user->id;
    }

    /**
     * Determine whether the user can delete the campaign.
     */
    public function delete(User $user, SurveyCampaign $campaign): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $campaign->advertiser_id === $user->id;
    }
}
