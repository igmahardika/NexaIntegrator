@extends('layouts.admin')
@section('title', 'Buat Kampanye')
@section('page-title', 'Buat Kampanye Baru')
@section('page-subtitle', 'Atur iklan dan survey builder')

@section('content')
<form method="POST" action="{{ route('admin.campaigns.store') }}" enctype="multipart/form-data"
    x-data="campaignBuilder()" @submit.prevent="submitForm">

    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Campaign Info -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Campaign Info Card -->
            <div class="card p-6">
                <h3 class="section-title">Informasi Kampanye</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Judul Kampanye <span class="text-red-400">*</span></label>
                        <input type="text" name="title" class="input" required value="{{ old('title') }}" x-model="title" placeholder="e.g. Promo Merdeka 2026">
                    </div>

                    <div>
                        <label class="label">Nama Sponsor</label>
                        <input type="text" name="sponsor_name" class="input" value="{{ old('sponsor_name') }}" x-model="sponsor" placeholder="e.g. Kopi Mantap Co.">
                    </div>

                    <div>
                        <label class="label">Advertiser</label>
                        <select name="advertiser_id" class="input">
                            <option value="">— Tidak ada —</option>
                            @foreach($advertisers as $adv)
                            <option value="{{ $adv->id }}" {{ old('advertiser_id') == $adv->id ? 'selected' : '' }}>
                                {{ $adv->name }} ({{ $adv->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="input" value="{{ old('start_date', date('Y-m-d')) }}">
                    </div>

                    <div>
                        <label class="label">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="input" value="{{ old('end_date') }}">
                    </div>
                </div>
            </div>

            <!-- Ad Media Card -->
            <div class="card p-6">
                <h3 class="section-title">Media Iklan</h3>

                <div class="space-y-4">
                    <div>
                        <label class="label">URL Video (YouTube Embed / Direct)</label>
                        <input type="url" name="video_url" class="input" value="{{ old('video_url') }}"
                            placeholder="https://www.youtube.com/embed/VIDEO_ID">
                        <p class="text-xs text-slate-500 mt-1">Gunakan URL embed YouTube. Contoh: https://www.youtube.com/embed/dQw4w9WgXcQ</p>
                    </div>

                    <div>
                        <label class="label">Minimum Tonton (detik)</label>
                        <input type="number" name="min_watch_duration" class="input" value="{{ old('min_watch_duration', 10) }}" x-model="minWatch" min="0" max="600">
                        <p class="text-xs text-slate-500 mt-1">Tombol survei terkunci selama N detik pertama video</p>
                    </div>

                    <div>
                        <label class="label">Banner Iklan (gambar, max 2MB)</label>
                        <input type="file" name="ad_banner" accept="image/*" class="input py-2">
                    </div>

                    <div class="flex items-center gap-2.5">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked class="checkbox">
                        <label for="is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Kampanye aktif</label>
                    </div>
                </div>
            </div>

            <!-- Survey Question Builder -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title mb-0">Survey Question Builder</h3>
                    <button type="button" @click="addQuestion()"
                        class="btn-primary text-xs flex items-center gap-1.5 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Pertanyaan
                    </button>
                </div>

                <div class="space-y-4" id="questions-list">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <div class="bg-white/3 border border-white/10 rounded-xl p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="text-xs font-bold text-slate-400 mt-2.5 w-5 flex-shrink-0" x-text="index + 1 + '.'"></span>
                                <div class="flex-1">
                                    <input type="text" :name="'questions[' + index + '][text]'" x-model="q.text"
                                        class="input mb-2" placeholder="Teks pertanyaan..." required>

                                    <div class="flex gap-2 flex-wrap">
                                        <select :name="'questions[' + index + '][type]'" x-model="q.type" class="input w-auto text-sm py-1.5">
                                            <option value="single_choice">Pilihan Ganda (1)</option>
                                            <option value="multiple_choice">Pilihan Ganda (banyak)</option>
                                            <option value="text">Teks Bebas</option>
                                            <option value="rating">Rating 1-5</option>
                                        </select>

                                        <label class="flex items-center gap-1.5 text-xs text-slate-400">
                                            <input type="checkbox" :name="'questions[' + index + '][required]'" value="1"
                                                x-model="q.required" class="accent-indigo-500">
                                            Wajib
                                        </label>
                                    </div>
                                </div>
                                <button type="button" @click="removeQuestion(index)"
                                    class="btn-danger py-1 px-2 text-xs flex-shrink-0 mt-1">✕</button>
                            </div>

                            <!-- Options for choice types -->
                            <div x-show="q.type === 'single_choice' || q.type === 'multiple_choice'" x-cloak class="ml-8">
                                <p class="text-xs text-slate-500 mb-2">Opsi Jawaban:</p>
                                <div class="space-y-1.5">
                                    <template x-for="(opt, oi) in q.options" :key="oi">
                                        <div class="flex gap-2">
                                            <input type="text" :name="'questions[' + index + '][options][' + oi + ']'"
                                                x-model="q.options[oi]" class="input py-1.5 text-sm" :placeholder="'Opsi ' + (oi+1)">
                                            <button type="button" @click="removeOption(q, oi)"
                                                class="text-slate-500 hover:text-red-400 px-2 text-lg leading-none">×</button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="q.options.push('')"
                                    class="mt-2 text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
                                    + Tambah opsi
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="questions.length === 0" class="text-center py-8 text-slate-500 text-sm border border-dashed border-white/10 rounded-xl">
                        Belum ada pertanyaan. Klik "Tambah Pertanyaan" untuk mulai.
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Actions & Live Preview -->
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="section-title">Aksi</h3>
                <button type="submit" class="btn-primary w-full mb-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span>Simpan Kampanye</span>
                </button>
                <a href="{{ route('admin.campaigns.index') }}" class="btn-secondary w-full text-center block text-sm">
                    Batal
                </a>
            </div>

            <!-- Live Mobile Smartphone Preview Card -->
            <div class="card p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Simulasi Smartphone
                    </span>
                    <span class="badge bg-indigo-500/20 text-indigo-300 text-[10px] border border-indigo-500/30">Live View</span>
                </div>

                <!-- Phone Mockup Frame -->
                <div class="w-full max-w-[280px] mx-auto bg-slate-950 rounded-[28px] border-4 border-slate-800 shadow-2xl p-2.5 overflow-hidden text-[11px]">
                    <!-- Phone Notch / Speaker -->
                    <div class="w-20 h-3.5 bg-slate-800 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-slate-900"></div>
                    </div>

                    <div class="bg-slate-900 rounded-2xl p-3 border border-white/5 space-y-2.5">
                        <!-- Simulated Header -->
                        <div class="text-center pb-2 border-b border-white/5">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-indigo-600 flex items-center justify-center text-white mb-1 shadow-md shadow-indigo-600/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                </svg>
                            </div>
                            <div class="font-bold text-white text-xs truncate" x-text="sponsor || title || 'WiFiPads Portal'"></div>
                            <div class="text-[9px] text-slate-400">Survei Singkat WiFi Gratis</div>
                        </div>

                        <!-- Simulated Video Badge -->
                        <div class="bg-slate-950 rounded-lg p-2 border border-white/5 text-center text-[10px] text-slate-400">
                            <div class="flex items-center justify-center gap-1 text-indigo-400 font-semibold mb-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                </svg>
                                <span>Iklan Sponsor</span>
                            </div>
                            <span class="text-[9px] text-slate-400" x-text="'Tonton ' + minWatch + ' detik untuk buka form'"></span>
                        </div>

                        <!-- Questions Preview -->
                        <div class="space-y-2 pt-1 max-h-48 overflow-y-auto">
                            <template x-for="(q, index) in questions" :key="q.id">
                                <div class="bg-white/5 rounded-lg p-2 border border-white/5">
                                    <div class="font-semibold text-white text-[10px] mb-1 leading-snug">
                                        <span x-text="(index + 1) + '. '"></span>
                                        <span x-text="q.text || 'Pertanyaan ' + (index + 1)"></span>
                                        <span x-show="q.required" class="text-red-400">*</span>
                                    </div>

                                    <!-- Choices Preview -->
                                    <template x-if="q.type === 'single_choice' || q.type === 'multiple_choice'">
                                        <div class="space-y-1 mt-1">
                                            <template x-for="(opt, oi) in q.options" :key="oi">
                                                <div class="flex items-center gap-1.5 p-1 rounded bg-white/5 text-[9px] text-slate-300">
                                                    <div class="w-2.5 h-2.5 rounded-full border border-slate-500"></div>
                                                    <span x-text="opt || 'Pilihan ' + (oi + 1)" class="truncate"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Rating Preview -->
                                    <template x-if="q.type === 'rating'">
                                        <div class="flex gap-1 mt-1">
                                            <template x-for="i in 5" :key="i">
                                                <div class="w-5 h-5 rounded bg-white/10 text-[9px] flex items-center justify-center font-bold text-slate-300" x-text="i"></div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Text Preview -->
                                    <template x-if="q.type === 'text'">
                                        <div class="p-1 rounded bg-slate-950 text-[9px] text-slate-400 italic mt-1 border border-white/5">
                                            Jawaban teks pengunjung...
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div x-show="questions.length === 0" class="text-center py-4 text-[10px] text-slate-400">
                                Belum ada pertanyaan dibuat
                            </div>
                        </div>

                        <!-- Simulated Submit Button -->
                        <div class="w-full py-1.5 bg-indigo-600/70 rounded-lg text-white font-semibold text-[10px] flex items-center justify-center gap-1">
                            <span>Dapatkan Akses WiFi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden submit button for x-data form -->
    <button type="submit" id="real-submit" style="display:none"></button>
</form>
@endsection

@section('scripts')
<script>
function campaignBuilder() {
    return {
        title: '{{ old('title', '') }}',
        sponsor: '{{ old('sponsor_name', '') }}',
        minWatch: {{ old('min_watch_duration', 10) }},
        questions: [],
        nextId: 1,

        addQuestion() {
            this.questions.push({
                id: this.nextId++,
                text: '',
                type: 'single_choice',
                options: ['', ''],
                required: true,
            });
        },

        removeQuestion(index) {
            this.questions.splice(index, 1);
        },

        removeOption(q, oi) {
            if (q.options.length <= 1) return;
            q.options.splice(oi, 1);
        },

        submitForm(e) {
            document.getElementById('real-submit').click();
        }
    };
}
</script>
@endsection
