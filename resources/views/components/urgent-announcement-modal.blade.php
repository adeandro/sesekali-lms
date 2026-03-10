{{--
    @component urgent-announcement-modal
    Shows a blocking SweetAlert2 modal for URGENT announcements.
    User must click "Paham" before they can interact with the page.
    Dismissed state stored in sessionStorage per announcement ID.
    Usage: <x-urgent-announcement-modal />
--}}

@php
    use App\Services\CommunicationService;
    $commService = app(CommunicationService::class);
    $urgentList  = $commService->getActiveAnnouncementsForUser(auth()->user())
                               ->where('type', 'urgent')
                               ->values();

    // Prepare plain array here so @json() never sees a PHP arrow function
    $urgentData = $urgentList->map(function ($a) {
        return [
            'id'      => $a->id,
            'title'   => $a->title,
            'content' => $a->content,
            'sender'  => $a->sender->formatted_name,
            'time'    => $a->created_at->diffForHumans(),
        ];
    })->values()->toArray();
@endphp

@if(!empty($urgentData))
<script>
(function() {
    const sessionKey = 'acknowledgedUrgent';
    const acknowledged = JSON.parse(sessionStorage.getItem(sessionKey) || '[]');

    const pendingUrgent = @json($urgentData);

    // Filter out already-acknowledged ones for this session
    const toShow = pendingUrgent.filter(a => !acknowledged.includes(a.id));

    if (toShow.length === 0) return;

    let index = 0;

    function showUrgentModal(announcement) {
        const swalConfig = {
            title: `<span class="text-red-700 uppercase tracking-widest font-black text-xs flex items-center gap-2">
                        <i class="fas fa-bell animate-bounce"></i> PENGUMUMAN PENTING
                    </span>`,
            html: `
                <div class="mt-3 p-5 bg-red-50 rounded-2xl border border-red-200 text-left">
                    <h3 class="text-base font-black text-red-900 leading-tight">${announcement.title}</h3>
                    <p class="text-sm text-red-700 mt-2 leading-relaxed">${announcement.content}</p>
                    <p class="text-[10px] text-red-400 mt-3 font-semibold uppercase tracking-wide">
                        Dari: ${announcement.sender} · ${announcement.time}
                    </p>
                </div>
                <p class="text-xs text-gray-500 mt-3">Klik tombol di bawah untuk mengakui pengumuman ini.</p>
            `,
            confirmButtonText: '<i class="fas fa-check mr-2"></i> Paham & Tutup',
            confirmButtonColor: '#dc2626',
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: 'rgba(0,0,0,0.6)',
            customClass: {
                popup:   'rounded-3xl overflow-hidden shadow-2xl border-0',
                confirm: 'rounded-xl px-8 py-3 font-black uppercase tracking-wide text-xs',
            }
        };

        Swal.fire(swalConfig).then(() => {
            // Mark as acknowledged in sessionStorage
            const acked = JSON.parse(sessionStorage.getItem(sessionKey) || '[]');
            acked.push(announcement.id);
            sessionStorage.setItem(sessionKey, JSON.stringify(acked));

            // Show next urgent if any
            index++;
            if (index < toShow.length) {
                showUrgentModal(toShow[index]);
            }
        });
    }

    // Wait for DOM + SweetAlert2 to be ready then show first
    window.addEventListener('load', () => showUrgentModal(toShow[0]));
})();
</script>
@endif
