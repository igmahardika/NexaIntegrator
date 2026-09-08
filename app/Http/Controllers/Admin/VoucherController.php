<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoucherGenerateRequest;
use App\Models\Location;
use App\Models\PortalVoucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $query = PortalVoucher::with('location')->latest();

        if ($request->filled('batch')) {
            $query->where('batch_name', $request->batch);
        }
        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }
        if ($request->filled('status')) {
            if ($request->status === 'used') $query->where('is_used', true);
            if ($request->status === 'available') $query->available();
        }

        $vouchers  = $query->paginate(20)->withQueryString();
        $batches   = PortalVoucher::select('batch_name')->distinct()->pluck('batch_name');
        $locations = Location::where('is_active', true)->get();

        return view('admin.vouchers.index', compact('vouchers', 'batches', 'locations'));
    }

    public function generate(VoucherGenerateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $expiredAt = !empty($validated['expired_at'])
            ? Carbon::parse($validated['expired_at'])
            : null;

        $created = PortalVoucher::generateBatch(
            batchName:       $validated['batch_name'],
            count:           (int) $validated['count'],
            durationMinutes: (int) $validated['duration_minutes'],
            rateLimit:       $validated['rate_limit'],
            locationId:      $validated['location_id'] ?? session('active_site_id'),
            expiredAt:       $expiredAt
        );

        return redirect()->route('admin.vouchers.index', ['batch' => $validated['batch_name']])
            ->with('success', "$created voucher berhasil dibuat untuk batch \"{$validated['batch_name']}\".");
    }

    public function printBatch(string $batchName): View
    {
        $vouchers = PortalVoucher::where('batch_name', $batchName)
            ->with('location')
            ->get();

        if ($vouchers->isEmpty()) {
            abort(404, 'Batch voucher tidak ditemukan');
        }

        return view('admin.vouchers.print', compact('vouchers', 'batchName'));
    }

    public function destroy(PortalVoucher $voucher): RedirectResponse
    {
        $batch = $voucher->batch_name;
        $voucher->delete();

        return redirect()->route('admin.vouchers.index', ['batch' => $batch])
            ->with('success', 'Voucher berhasil dihapus.');
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        $request->validate(['batch_name' => 'required|string|max:100']);
        $count = PortalVoucher::where('batch_name', $request->batch_name)->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('success', "$count voucher dalam batch \"{$request->batch_name}\" dihapus.");
    }
}
