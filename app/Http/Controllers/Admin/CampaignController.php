<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignRequest;
use App\Models\SurveyCampaign;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $query = SurveyCampaign::with(['advertiser', 'questions'])->withCount('responses');

        // Scoped to own campaigns if advertiser
        if ($user && $user->isAdvertiser()) {
            $query->where('advertiser_id', $user->id);
        }

        $campaigns = $query->latest()->paginate(15);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        Gate::authorize('create', SurveyCampaign::class);

        $user = Auth::user();
        $advertisers = $user->isSuperAdmin()
            ? User::where('role', 'advertiser')->get()
            : collect([$user]);

        return view('admin.campaigns.create', compact('advertisers'));
    }

    public function store(CampaignRequest $request): RedirectResponse
    {
        Gate::authorize('create', SurveyCampaign::class);

        $validated = $request->validated();
        $user = Auth::user();

        // Enforce ownership: Advertisers cannot create campaigns on behalf of others
        if ($user->isAdvertiser()) {
            $validated['advertiser_id'] = $user->id;
        }

        // Handle banner upload
        $bannerPath = null;
        if ($request->hasFile('ad_banner')) {
            $bannerPath = $request->file('ad_banner')->store('banners', 'public');
        }

        $campaign = SurveyCampaign::create([
            'title'              => $validated['title'],
            'sponsor_name'       => $validated['sponsor_name'] ?? null,
            'video_url'          => $validated['video_url'] ?? null,
            'min_watch_duration' => $validated['min_watch_duration'] ?? 0,
            'start_date'         => $validated['start_date'] ?? null,
            'end_date'           => $validated['end_date'] ?? null,
            'advertiser_id'      => $validated['advertiser_id'] ?? null,
            'is_active'          => $request->boolean('is_active', true),
            'ad_banner_path'     => $bannerPath,
        ]);

        // Create questions
        $this->syncQuestions($campaign, $request->input('questions', []));

        return redirect()->route('admin.campaigns.index')
            ->with('success', "Kampanye \"{$campaign->title}\" berhasil dibuat!");
    }

    public function edit(SurveyCampaign $campaign): View
    {
        Gate::authorize('update', $campaign);

        $campaign->load('questions');
        $user = Auth::user();
        $advertisers = $user->isSuperAdmin()
            ? User::where('role', 'advertiser')->get()
            : collect([$user]);

        return view('admin.campaigns.edit', compact('campaign', 'advertisers'));
    }

    public function update(CampaignRequest $request, SurveyCampaign $campaign): RedirectResponse
    {
        Gate::authorize('update', $campaign);

        $validated = $request->validated();
        $user = Auth::user();

        if ($user->isAdvertiser()) {
            $validated['advertiser_id'] = $user->id;
        }

        // Handle banner update
        if ($request->hasFile('ad_banner')) {
            if ($campaign->ad_banner_path) {
                Storage::disk('public')->delete($campaign->ad_banner_path);
            }
            $validated['ad_banner_path'] = $request->file('ad_banner')->store('banners', 'public');
        }

        $campaign->update([
            'title'              => $validated['title'],
            'sponsor_name'       => $validated['sponsor_name'] ?? null,
            'video_url'          => $validated['video_url'] ?? null,
            'min_watch_duration' => $validated['min_watch_duration'] ?? 0,
            'start_date'         => $validated['start_date'] ?? null,
            'end_date'           => $validated['end_date'] ?? null,
            'advertiser_id'      => $validated['advertiser_id'] ?? null,
            'is_active'          => $request->boolean('is_active', true),
            'ad_banner_path'     => $validated['ad_banner_path'] ?? $campaign->ad_banner_path,
        ]);

        // Re-sync questions
        $campaign->questions()->delete();
        $this->syncQuestions($campaign, $request->input('questions', []));

        return redirect()->route('admin.campaigns.index')
            ->with('success', "Kampanye \"{$campaign->title}\" berhasil diperbarui!");
    }

    public function destroy(SurveyCampaign $campaign): RedirectResponse
    {
        Gate::authorize('delete', $campaign);

        if ($campaign->ad_banner_path) {
            Storage::disk('public')->delete($campaign->ad_banner_path);
        }
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Kampanye berhasil dihapus.');
    }

    private function syncQuestions(SurveyCampaign $campaign, array $questions): void
    {
        foreach ($questions as $index => $q) {
            if (empty($q['text'])) continue;

            $options = null;
            if (in_array($q['type'] ?? 'single_choice', ['single_choice', 'multiple_choice'])) {
                $rawOptions = $q['options'] ?? [];
                $options = array_values(array_filter($rawOptions, fn($o) => trim($o) !== ''));
            }

            SurveyQuestion::create([
                'campaign_id'   => $campaign->id,
                'question_text' => $q['text'],
                'question_type' => $q['type'] ?? 'single_choice',
                'options'       => $options,
                'order'         => $index,
                'is_required'   => (bool) ($q['required'] ?? true),
            ]);
        }
    }
}
