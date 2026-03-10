{{--
    @component announcement-banner
    Renders ALL active announcements visible to the current user.
    Usage: <x-announcement-banner />
--}}

@php
    use App\Services\CommunicationService;
    $commService    = app(CommunicationService::class);
    $announcements  = $commService->getActiveAnnouncementsForUser(auth()->user());
    $urgentBanners  = $announcements->where('type', 'urgent');
    $normalBanners  = $announcements->where('type', '!=', 'urgent');
@endphp

@if($announcements->isNotEmpty())
    {{-- Normal banners (info + warning) --}}
    <div class="space-y-2 mb-4" x-data="{ dismissed: JSON.parse(localStorage.getItem('dismissedAnnouncements') || '[]') }">
        @foreach($normalBanners as $ann)
        <div
            x-show="!dismissed.includes({{ $ann->id }})"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="announcement-banner announcement-banner-{{ $ann->type }} group"
        >
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                @if($ann->type === 'warning')
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-black leading-tight">{{ $ann->title }}</p>
                <p class="text-xs mt-0.5 leading-relaxed opacity-80 line-clamp-2">{{ $ann->content }}</p>
                <p class="text-[9px] font-semibold opacity-60 mt-1 uppercase tracking-widest">
                    {{ $ann->sender->formatted_name }} · {{ $ann->created_at->diffForHumans() }}
                </p>
            </div>

            {{-- Dismiss button --}}
            <button
                @click="dismissed = [...dismissed, {{ $ann->id }}]; localStorage.setItem('dismissedAnnouncements', JSON.stringify(dismissed))"
                class="shrink-0 p-1.5 rounded-lg hover:bg-black/10 transition-colors opacity-50 hover:opacity-100"
                title="Tutup"
            >
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        @endforeach

        {{-- Urgent banners: always shown (non-dismissible from banner; modal handles it) --}}
        @foreach($urgentBanners as $ann)
        <div class="announcement-banner announcement-banner-urgent group">
            <div class="shrink-0 mt-0.5">
                <div class="w-9 h-9 rounded-xl bg-red-500/20 flex items-center justify-center animate-pulse">
                    <i class="fas fa-bell text-red-600 text-sm"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[8px] font-black bg-red-600 text-white px-1.5 py-0.5 rounded uppercase tracking-widest">URGENT</span>
                    <p class="text-sm font-black leading-tight">{{ $ann->title }}</p>
                </div>
                <p class="text-xs mt-0.5 leading-relaxed opacity-80 line-clamp-2">{{ $ann->content }}</p>
                <p class="text-[9px] font-semibold opacity-60 mt-1 uppercase tracking-widest">
                    {{ $ann->sender->formatted_name }} · {{ $ann->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
@endif
