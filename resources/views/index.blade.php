@extends('layouts.index')

@section('content')
    @livewire('reject-in-out')

    {{-- Select Reject Area --}}
    <div class="select-defect-area" id="select-reject-area">
        <div class="defect-area-position-container">
            <div class="d-flex">
                <div class="d-flex justify-content-center align-items-center">
                    <label class="text-light bg-dark" style="padding: .375rem .75rem;height: 100%">X </label>
                    <input type="text" class="form-control rounded-0" id="reject-area-position-x" readonly>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <label class="text-light bg-dark h-100" style="padding: .375rem .75rem;height: 100%">Y </label>
                    <input type="text" class="form-control rounded-0" id="reject-area-position-y" readonly>
                </div>
            </div>
            <div class="d-flex">
                <button class="btn btn-success rounded-0" id="reject-area-confirm">
                    <i class="fa-regular fa-check"></i>
                </button>
                <button class="btn btn-danger rounded-0" id="reject-area-cancel">
                    <i class="fa-regular fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="defect-area-img-container" id="reject-area-img-container">
            <div class="defect-area-img-point" id="reject-area-img-point"></div>
            <img src="" alt="" class="img-fluid defect-area-img" id="reject-area-img">
        </div>
        <input type="hidden" class="form-control d-none" id="reject-area-index">
    </div>

    {{-- Show Reject Area --}}
    <div class="show-defect-area" id="show-reject-area">
        <div class="position-relative d-flex flex-column justify-content-center align-items-center">
            <button type="button" class="btn btn-lg btn-light rounded-0 hide-defect-area-img" onclick="onHideRejectAreaImage()">
                <i class="fa-regular fa-xmark fa-lg"></i>
            </button>
            <div class="defect-area-img-container mx-auto">
                <div class="defect-area-img-point" id="reject-area-img-point-show"></div>
                <img src="" alt="" class="img-fluid defect-area-img" id="reject-area-img-show">
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });
        });

        Livewire.on('loadingStart', () => {
            if (document.getElementById('loading-reject-in-out')) {
                $('#loading-reject-in-out').removeClass('hidden');
            }
        });

        Livewire.on('alert', (type, message) => {
            showNotification(type, message);
        });

        Livewire.on('showModal', (type) => {
            if (type == "reject") {
                showRejectModal();
            }
            if (type == "rejectIn") {
                showRejectInModal();
            }
            if (type == "rejectOut") {
                showRejectOutModal();
            }
        });

        Livewire.on('hideModal', (type) => {
            if (type == "reject") {
                hideRejectModal();
            }
            if (type == "rejectIn") {
                hideRejectInModal();
            }
            if (type == "rejectOut") {
                hideRejectOutModal();
            }
        });

        async function initRejectInScan(onScanSuccess) {
            if (html5QrcodeScannerRejectIn) {
                if ((html5QrcodeScannerRejectIn.getState() && html5QrcodeScannerRejectIn.getState() != 2)) {
                    const rejectScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                    // Start Camera
                    await html5QrcodeScannerDefectIn.start({ facingMode: "environment" }, rejectScanConfig, onScanSuccess);
                }
            }
        }

        async function clearRejectInScan() {
            console.log(html5QrcodeScannerRejectIn.getState());
            if (html5QrcodeScannerRejectIn) {
                if (html5QrcodeScannerRejectIn.getState() && html5QrcodeScannerRejectIn.getState() != 1) {
                    await html5QrcodeScannerRejectIn.stop();
                    await html5QrcodeScannerRejectIn.clear();
                }
            }
        }

        async function refreshRejectInScan(onScanSuccess) {
            await clearRejectInScan();
            await initRejectInScan(onScanSuccess);
        }

        // Scan QR Reject In
        if (document.getElementById('reject-in-reader')) {
            var html5QrcodeScannerRejectIn = new Html5Qrcode("reject-in-reader");
        }

        async function initRejectOutScan(onScanSuccess) {
            if (html5QrcodeScannerRejectOut) {
                if ((html5QrcodeScannerRejectOut.getState() && html5QrcodeScannerRejectOut.getState() != 2)) {
                    const rejectScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                    // Start Camera
                    await html5QrcodeScannerRejectOut.start({ facingMode: "environment" }, rejectScanConfig, onScanSuccess);
                }
            }
        }

        async function clearRejectOutScan() {
            console.log(html5QrcodeScannerRejectOut.getState());
            if (html5QrcodeScannerRejectOut) {
                if (html5QrcodeScannerRejectOut.getState() && html5QrcodeScannerRejectOut.getState() != 1) {
                    await html5QrcodeScannerRejectOut.stop();
                    await html5QrcodeScannerRejectOut.clear();
                }
            }
        }

        async function refreshRejectOutScan(onScanSuccess) {
            await clearRejectOutScan();
            await initRejectOutScan(onScanSuccess);
        }

        // Scan QR Reject Out
        if (document.getElementById('reject-out-reader')) {
            var html5QrcodeScannerRejectOut = new Html5Qrcode("reject-out-reader");
        }

        // Select Reject Area Position
        Livewire.on('showSelectRejectArea', async function (rejectAreaImage, x, y, index) {
            showSelectRejectArea(rejectAreaImage, x, y, index);
        });

        if (document.getElementById('select-reject-area')) {
            let rejectAreaImageContainer = document.getElementById('reject-area-img-container');
            let rejectAreaImage = document.getElementById('reject-area-img');
            let rejectAreaImagePoint = document.getElementById('reject-area-img-point');
            let rejectAreaPositionX = document.getElementById('reject-area-position-x');
            let rejectAreaPositionY = document.getElementById('reject-area-position-y');
            let rejectAreaConfirm = document.getElementById('reject-area-confirm');
            let rejectAreaCancel = document.getElementById('reject-area-cancel');
            let rejectAreaIndex = document.getElementById('reject-area-index');

            let localMousePos = { x: undefined, y: undefined };
            let globalMousePos = { x: undefined, y: undefined };

            rejectAreaImageContainer.addEventListener('mousemove', (event) => {
                let rect = rejectAreaImage.getBoundingClientRect();

                const localX = parseFloat((event.clientX - rect.left))/parseFloat(rect.width) * 100;
                const localY = parseFloat((event.clientY - rect.top))/parseFloat(rect.height) * 100;

                localMousePos = { x: localX, y: localY };

                rejectAreaImageContainer.addEventListener('click', (event) => {
                    rejectAreaImagePoint.style.width = 0.03 * rect.width+'px';
                    rejectAreaImagePoint.style.height = rejectAreaImagePoint.style.width;
                    rejectAreaImagePoint.style.left =  'calc('+localMousePos.x+'% - '+0.015 * rect.width+'px)';
                    rejectAreaImagePoint.style.top =  'calc('+localMousePos.y+'% - '+0.015 * rect.width+'px)';
                    rejectAreaImagePoint.style.display = 'block';

                    rejectAreaPositionX.value = localMousePos.x;
                    rejectAreaPositionY.value = localMousePos.y;
                });
            });

            rejectAreaConfirm.addEventListener('click', () => {
                Livewire.emit('setRejectAreaPosition', rejectAreaPositionX.value, rejectAreaPositionY.value, rejectAreaIndex.value);

                hideSelectRejectArea();
            });

            rejectAreaCancel.addEventListener('click', () => {
                rejectAreaImagePoint.style.left = '0px';
                rejectAreaImagePoint.style.top = '0px';
                rejectAreaImagePoint.style.display = 'none';

                rejectAreaPositionX.value = null;
                rejectAreaPositionY.value = null;

                Livewire.emit('setRejectAreaPosition', rejectAreaPositionX.value, rejectAreaPositionY.value, rejectAreaIndex.value);

                hideSelectRejectArea();
            });


            Livewire.on('clearSelectRejectAreaPoint', () => {
                let rejectAreaImagePoint = document.getElementById('reject-area-img-point');
                let rejectAreaPositionX = document.getElementById('reject-area-position-x');
                let rejectAreaPositionY = document.getElementById('reject-area-position-y');

                rejectAreaImagePoint.style.left = '0px';
                rejectAreaImagePoint.style.top = '0px';
                rejectAreaImagePoint.style.display = 'none';

                rejectAreaPositionX.value = null;
                rejectAreaPositionY.value = null;

                Livewire.emit('setRejectAreaPosition', rejectAreaPositionX.value, rejectAreaPositionY.value, rejectAreaIndex.value);
            });
        }

        // Invalid Input
        Livewire.on('addInvalid', async function (elementIds) {
            for (let i = 0; i < elementIds.length; i++) {
                $('#'+elementIds[i]).addClass("is-invalid");
            }
        });

        Livewire.on('removeInvalid', async function () {
            $('.is-invalid').each(() => {
                $(this).removeClass("is-invalid");
            });
        });
    </script>
@endsection
