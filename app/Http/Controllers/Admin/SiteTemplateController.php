<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\TemplateRegistryService;
use Illuminate\Http\Request;

class SiteTemplateController extends Controller
{
    /**
     * Top-level template studio index (resolves active tenant site or first active location).
     */
    public function index(Request $request)
    {
        $siteId = session('active_site_id');
        $site = null;
        if ($siteId) {
            $site = Location::find($siteId);
        }
        if (!$site) {
            $site = Location::where('is_active', true)->first() ?? Location::first();
        }

        if (!$site) {
            return redirect()->route('admin.sites.index')->with('error', 'Silakan buat site terlebih dahulu.');
        }

        return $this->gallery($site);
    }

    /**
     * Display default templates gallery for this site.
     */
    public function gallery(Location $site)
    {
        $templates = TemplateRegistryService::all();
        $activeTemplateId = $site->active_template ?: 'modern-glass';
        $currentConfig = $site->getResolvedTemplateConfig();

        return view('admin.templates.gallery', compact('site', 'templates', 'activeTemplateId', 'currentConfig'));
    }

    /**
     * Activate a template for this site without forced redirect.
     */
    public function select(Request $request, Location $site, string $templateId)
    {
        $templates = TemplateRegistryService::all();
        if (!isset($templates[$templateId])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Template tidak valid.'], 422);
            }
            return redirect()->back()->with('error', 'Template tidak valid.');
        }

        $site->update(['active_template' => $templateId]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'         => true,
                'message'         => "Template \"{$templates[$templateId]['name']}\" berhasil diaktifkan.",
                'active_template' => $templateId,
                'template_name'   => $templates[$templateId]['name']
            ]);
        }

        return redirect()->route('admin.sites.template.gallery', $site)
            ->with('success', "Template \"{$templates[$templateId]['name']}\" berhasil diaktifkan.");
    }

    /**
     * Open visual template customizer for this site.
     */
    public function customizer(Request $request, Location $site)
    {
        $templateId = $request->query('template', $site->active_template ?: 'modern-glass');
        $template = TemplateRegistryService::get($templateId);
        $config = $site->getResolvedTemplateConfig($templateId);
        $allTemplates = TemplateRegistryService::all();
        $hotspotProfiles = $site->hotspotProfiles()->orderBy('name')->get();

        return view('admin.templates.customizer', compact('site', 'template', 'templateId', 'config', 'allTemplates', 'hotspotProfiles'));
    }

    /**
     * Save customized template configuration.
     */
    public function update(Request $request, Location $site)
    {
        $validated = $request->validate([
            'template_id'        => 'nullable|string|max:50',
            'brand_name'         => 'required|string|max:100',
            'brand_tagline'      => 'nullable|string|max:150',
            'instagram'          => 'nullable|string|max:100',
            'logo_url'           => 'nullable|string|max:500',
            'logo_white'         => 'nullable|string|max:500',
            'logo_file'          => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'logo_white_file'    => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'wallpaper_file'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'topbar_title'      => 'nullable|string|max:100',
            'topbar_color'      => 'nullable|string|max:30',
            'hero_title'         => 'required|string|max:150',
            'hero_subtitle'      => 'nullable|string|max:255',
            'input_placeholder' => 'nullable|string|max:100',
            'button_text'        => 'nullable|string|max:100',
            'primary_color'      => 'required|string|max:30',
            'accent_color'       => 'required|string|max:30',
            'bg_type'            => 'required|string|in:gradient,color,image',
            'bg_value'           => 'required|string|max:500',
            'promo_enabled'      => 'nullable|boolean',
            'promo_image'        => 'nullable|string|max:500',
            'promo_image_2'      => 'nullable|string|max:500',
            'promo_image_3'      => 'nullable|string|max:500',
            'promo_file_1'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'promo_file_2'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'promo_file_3'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'promo_badge'        => 'nullable|string|max:50',
            'promo_title'        => 'nullable|string|max:150',
            'promo_text'         => 'nullable|string|max:500',
            'promo_link'         => 'nullable|string|max:255',
            'tos_text'           => 'nullable|string|max:1000',
            'survey_profile'     => 'nullable|string|max:100',
            'voucher_profile'    => 'nullable|string|max:100',
            'enabled_tabs'       => 'nullable|array',
            'custom_css'         => 'nullable|string|max:5000',
        ]);

        // Sanitize custom CSS
        if (!empty($validated['custom_css'])) {
            $cleanedCss = strip_tags($validated['custom_css']);
            $cleanedCss = preg_replace('/<\s*\/?\s*(style|script)[^>]*>/i', '', $cleanedCss);
            $validated['custom_css'] = $cleanedCss;
        }

        // Handle direct logo file upload
        if ($request->hasFile('logo_file')) {
            $logoPath = $request->file('logo_file')->store('branding/logos', 'public');
            $validated['logo_url'] = '/storage/' . $logoPath;
        }

        // Handle white logo file upload
        if ($request->hasFile('logo_white_file')) {
            $whitePath = $request->file('logo_white_file')->store('branding/logos', 'public');
            $validated['logo_white'] = '/storage/' . $whitePath;
        }

        // Handle direct wallpaper file upload
        if ($request->hasFile('wallpaper_file')) {
            $bgPath = $request->file('wallpaper_file')->store('branding/wallpapers', 'public');
            $validated['bg_value'] = '/storage/' . $bgPath;
            $validated['bg_type'] = 'image';
        }

        // Handle promo banners upload
        if ($request->hasFile('promo_file_1')) {
            $p1Path = $request->file('promo_file_1')->store('branding/promos', 'public');
            $validated['promo_image'] = '/storage/' . $p1Path;
        }
        if ($request->hasFile('promo_file_2')) {
            $p2Path = $request->file('promo_file_2')->store('branding/promos', 'public');
            $validated['promo_image_2'] = '/storage/' . $p2Path;
        }
        if ($request->hasFile('promo_file_3')) {
            $p3Path = $request->file('promo_file_3')->store('branding/promos', 'public');
            $validated['promo_image_3'] = '/storage/' . $p3Path;
        }

        $validated['promo_enabled'] = (bool) ($request->input('promo_enabled', 0));
        unset($validated['logo_file'], $validated['logo_white_file'], $validated['wallpaper_file'], $validated['promo_file_1'], $validated['promo_file_2'], $validated['promo_file_3']);

        $templateId = $request->input('template_id') ?: ($request->query('template') ?: ($site->active_template ?: 'access-code'));
        $currentConfigs = $site->template_config ?? [];
        $currentConfigs[$templateId] = $validated;

        $site->update([
            'template_config' => $currentConfigs,
        ]);

        return redirect()->back()
            ->with('success', 'Template configuration saved successfully.');
    }
}
