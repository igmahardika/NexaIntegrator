{{-- Active users table partial matching Image 3 DNA --}}
@if(count($activeUsers) > 0)
<div class="overflow-x-auto">
    <table class="w-full text-xs">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-2xs font-bold uppercase tracking-wider">
                <th class="text-left py-2 px-3 rounded-l-lg">Device / User</th>
                <th class="text-left py-2 px-3">MAC Address</th>
                <th class="text-left py-2 px-3">IP Subnet</th>
                <th class="text-left py-2 px-3">Up Time</th>
                <th class="text-left py-2 px-3">Traffic (Rx/Tx)</th>
                <th class="py-2 px-3 rounded-r-lg text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
            @foreach($activeUsers as $user)
            <tr class="table-row">
                <td class="py-2.5 px-3">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $user['user'] ?: 'Active Client' }}</span>
                    </div>
                </td>
                <td class="py-2.5 px-3 font-mono text-slate-600 font-medium">{{ $user['mac'] ?: '-' }}</td>
                <td class="py-2.5 px-3 font-mono text-slate-600">{{ $user['ip'] ?: '-' }}</td>
                <td class="py-2.5 px-3 text-slate-500 font-medium">{{ $user['uptime'] ?: '-' }}</td>
                <td class="py-2.5 px-3 text-slate-500 font-mono">
                    {{ number_format(($user['bytes_in'] ?? 0) / 1048576, 1) }}MB /
                    {{ number_format(($user['bytes_out'] ?? 0) / 1048576, 1) }}MB
                </td>
                <td class="py-2.5 px-3 text-right">
                    @if($routerLocation && $user['mac'])
                    <button
                        onclick="kickUser('{{ $user['mac'] }}', '{{ $routerLocation->id }}')"
                        class="btn-danger text-xs py-1 px-2.5 font-semibold"
                    >Disconnect</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@elseif(!$routerLocation)
<p class="text-slate-500 text-xs text-center py-8">No edge router configured for this location.</p>
@else
<p class="text-slate-500 text-xs text-center py-8">No active client sessions on edge router at this time.</p>
@endif
