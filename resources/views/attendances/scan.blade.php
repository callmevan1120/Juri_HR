<x-app-layout>
    @pushOnce('scripts')
        <script>
            (() => {
                const syncScanAttendanceRouteClass = () => {
                    const isScanRoute = window.location.pathname === '/scan' || Boolean(document.querySelector('.scan-attendance-page'));

                    document.documentElement.classList.toggle('scan-attendance-route', isScanRoute);
                    document.body.classList.toggle('scan-attendance-route', isScanRoute);
                };

                syncScanAttendanceRouteClass();

                document.addEventListener('DOMContentLoaded', syncScanAttendanceRouteClass, {
                    once: true
                });
                document.addEventListener('livewire:navigated', syncScanAttendanceRouteClass);
                window.addEventListener('pageshow', syncScanAttendanceRouteClass);
                window.requestAnimationFrame(syncScanAttendanceRouteClass);
                window.setTimeout(syncScanAttendanceRouteClass, 150);
            })();
        </script>
    @endpushOnce

    <div class="user-page-shell scan-attendance-page">
        <div class="user-page-container user-page-container--wide">
            <x-user.page-header
                class="mb-3 native-scan-page-header"
                :back-href="route('home')"
                :title="__('Clock In')"
                title-id="scan-attendance-title"
                plain>
                <x-slot name="icon">
                    <x-heroicon-o-qr-code class="h-5 w-5" />
                </x-slot>
            </x-user.page-header>

            <livewire:user.scan-component />
        </div>
    </div>
</x-app-layout>
