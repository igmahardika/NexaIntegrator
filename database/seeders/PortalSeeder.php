<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\PortalMember;
use App\Models\PortalVoucher;
use App\Models\SurveyCampaign;
use App\Models\SurveyQuestion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PortalSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Users ----
        $admin = User::firstOrCreate(
            ['email' => 'admin@wifipads.com'],
            [
                'name'     => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role'     => 'superadmin',
            ]
        );

        $advertiser = User::firstOrCreate(
            ['email' => 'sponsor@wifipads.com'],
            [
                'name'     => 'Demo Advertiser',
                'password' => Hash::make('password123'),
                'role'     => 'advertiser',
            ]
        );

        $this->command->info('✓ Users seeded (admin@wifipads.com & sponsor@wifipads.com)');

        // ---- 2. Default Location ----
        $location = Location::firstOrCreate(
            ['slug' => 'default-location'],
            [
                'id'              => Str::uuid()->toString(),
                'name'            => 'WiFiPads Demo Location',
                'address'         => 'Jl. Sudirman No. 1, Jakarta Pusat',
                'router_ip'       => '192.168.88.1',
                'router_port'     => 8728,
                'router_user'     => 'admin',
                'router_password' => 'admin',
                'dns_name'        => 'wifi.login',
                'is_active'       => true,
            ]
        );

        $this->command->info('✓ Default location seeded');

        // ---- 3. Survey Campaign ----
        $campaign = SurveyCampaign::firstOrCreate(
            ['title' => 'Demo Campaign - Brand Preference Survey'],
            [
                'id'                  => Str::uuid()->toString(),
                'advertiser_id'       => $advertiser->id,
                'sponsor_name'        => 'Demo Brand Co.',
                'video_url'           => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'min_watch_duration'  => 10,
                'start_date'          => Carbon::today(),
                'end_date'            => Carbon::today()->addYear(),
                'is_active'           => true,
            ]
        );

        // ---- 4. Survey Questions ----
        if ($campaign->questions()->count() === 0) {
            SurveyQuestion::create([
                'id'            => Str::uuid()->toString(),
                'campaign_id'   => $campaign->id,
                'question_text' => 'Berapa usia Anda?',
                'question_type' => 'single_choice',
                'options'       => ['< 18 tahun', '18 - 24 tahun', '25 - 34 tahun', '35 - 44 tahun', '45 - 54 tahun', '55+ tahun'],
                'order'         => 0,
                'is_required'   => true,
            ]);

            SurveyQuestion::create([
                'id'            => Str::uuid()->toString(),
                'campaign_id'   => $campaign->id,
                'question_text' => 'Jenis kelamin Anda?',
                'question_type' => 'single_choice',
                'options'       => ['Laki-laki', 'Perempuan', 'Prefer tidak menjawab'],
                'order'         => 1,
                'is_required'   => true,
            ]);

            SurveyQuestion::create([
                'id'            => Str::uuid()->toString(),
                'campaign_id'   => $campaign->id,
                'question_text' => 'Brand minuman favorit Anda?',
                'question_type' => 'multiple_choice',
                'options'       => ['Coca-Cola', 'Pepsi', 'Teh Botol Sosro', 'Good Day', 'Aqua', 'Pocari Sweat', 'Lainnya'],
                'order'         => 2,
                'is_required'   => false,
            ]);

            SurveyQuestion::create([
                'id'            => Str::uuid()->toString(),
                'campaign_id'   => $campaign->id,
                'question_text' => 'Seberapa sering Anda menggunakan WiFi publik? (1 = Jarang, 5 = Sangat Sering)',
                'question_type' => 'rating',
                'options'       => null,
                'order'         => 3,
                'is_required'   => false,
            ]);

            SurveyQuestion::create([
                'id'            => Str::uuid()->toString(),
                'campaign_id'   => $campaign->id,
                'question_text' => 'Ada saran atau komentar untuk kami?',
                'question_type' => 'text',
                'options'       => null,
                'order'         => 4,
                'is_required'   => false,
            ]);
        }

        $this->command->info('✓ Campaign and 5 survey questions seeded');

        // ---- 5. Vouchers ----
        if (PortalVoucher::where('batch_name', 'DEMO-BATCH-001')->count() === 0) {
            PortalVoucher::generateBatch(
                batchName:       'DEMO-BATCH-001',
                count:           10,
                durationMinutes: 60,
                rateLimit:       '5M/5M',
                locationId:      $location->id,
                expiredAt:       Carbon::now()->addMonths(3)
            );

            PortalVoucher::generateBatch(
                batchName:       'VIP-BATCH-001',
                count:           5,
                durationMinutes: 480,
                rateLimit:       '20M/20M',
                locationId:      $location->id,
                expiredAt:       Carbon::now()->addMonths(1)
            );
        }

        $this->command->info('✓ 15 demo vouchers seeded (2 batches)');

        // ---- 6. Portal Member (Staff) ----
        PortalMember::firstOrCreate(
            ['username' => 'kasir01'],
            [
                'id'           => Str::uuid()->toString(),
                'password'     => Hash::make('staff123'),
                'full_name'    => 'Budi Santoso (Kasir)',
                'role'         => 'staff',
                'rate_limit'   => '20M/20M',
                'shared_users' => 2,
                'is_active'    => true,
            ]
        );

        PortalMember::firstOrCreate(
            ['username' => 'vip_owner'],
            [
                'id'           => Str::uuid()->toString(),
                'password'     => Hash::make('vip123'),
                'full_name'    => 'Owner VIP',
                'role'         => 'vip',
                'rate_limit'   => '50M/50M',
                'shared_users' => 5,
                'is_active'    => true,
            ]
        );

        $this->command->info('✓ Portal members seeded (kasir01 / vip_owner)');
        $this->command->newLine();
        $this->command->line('  <fg=green>Superadmin:</> admin@wifipads.com / password123');
        $this->command->line('  <fg=green>Advertiser:</> sponsor@wifipads.com / password123');
        $this->command->line('  <fg=green>Staff member:</> kasir01 / staff123');
        $this->command->line('  <fg=green>VIP member:</> vip_owner / vip123');
    }
}
