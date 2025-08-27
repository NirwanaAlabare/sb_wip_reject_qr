<div>
    <div class="loading-container-fullscreen" wire:loading>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loading-reject-in-out">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="row g-3">
        <div class="d-flex justify-content-center gap-1">
            <button type="button" class="btn btn-sm btn-sb-outline {{ $mode == "in-out" ? "active" : "" }}" {{ $mode == "in-out" ? "disabled" : "" }} id="button-in-out">SUM</button>
            <button type="button" class="btn btn-sm btn-reject {{ $mode == "in" ? "active" : "" }}" {{ $mode == "in" ? "disabled" : "" }} id="button-in">IN</button>
            <button type="button" class="btn btn-sm btn-rework {{ $mode == "out" ? "active" : "" }}" {{ $mode == "out" ? "disabled" : "" }} id="button-out">OUT</button>
        </div>

        {{-- Reject IN --}}
        <div class="col-12 col-md-12 {{ $mode != "in" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-reject">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}REJECT IN</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalRejectIn }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4" wire:ignore>
                            <div class="d-flex flex-column gap-3 h-100">
                                <input type="text" class="qty-input border h-100" id="scannedItemRejectIn" name="scannedItemRejectIn">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="text-center mb-0">OUTSTANDING CHECK</h5>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="text-center"><b>{{ $totalRejectIn }}</b></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" wire:model="rejectInSearch" placeholder="Search...">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="rejectInOutputType" id="reject-in-output-type" wire:model="rejectInOutputType">
                                        <option value="all">ALL</option>
                                        <option value="qc">QC</option>
                                        {{-- <option value="qcf">QC FINISHING</option> --}}
                                        <option value="packing">FINISHING</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="rejectInLine" id="reject-in-line" wire:model="rejectInLine">
                                        <option value="" selected>Pilih Line</option>
                                        @foreach ($lines as $line)
                                            <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 d-none">
                                    <button type="button" class="btn btn-sm btn-rework w-100 fw-bold" wire:click="saveAllRejectIn">ALL REJECT OUT</button>
                                </div>
                            </div>
                            <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-bordered w-100">
                                    <thead>
                                        <tr class="text-center align-middle">
                                            <th>No.</th>
                                            <th>Kode</th>
                                            <th>Waktu</th>
                                            <th>Line</th>
                                            <th>Master Plan</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Dept.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($rejectInList) < 1)
                                            <tr class="text-center align-middle">
                                                <td colspan="9" class="text-center">Data tidak ditemukan</td>
                                            </tr>
                                        @else
                                            @foreach ($rejectInList as $rejectIn)
                                                @php
                                                    $thisRejectInChecked = null;

                                                    if ($rejectInSelectedList) {
                                                        $thisRejectInChecked = $rejectInSelectedList->filter(function ($item) use ($rejectIn) {
                                                            return $item['master_plan_id'] == $rejectIn->master_plan_id && $item['defect_type_id'] == $rejectIn->defect_type_id && $item['so_det_id'] == $rejectIn->so_det_id;
                                                        });
                                                    }
                                                @endphp
                                                <tr class="text-center align-middle">
                                                    <td>{{ $loop->index+1 }}</td>
                                                    <td>{{ $rejectIn->kode_numbering }}</td>
                                                    <td>{{ $rejectIn->reject_time }}</td>
                                                    <td>{{ strtoupper(str_replace("_", " ", $rejectIn->sewing_line)) }}</td>
                                                    <td>{{ $rejectIn->ws }}<br>{{ $rejectIn->style }}<br>{{ $rejectIn->color }}</td>
                                                    <td>{{ $rejectIn->size }}</td>
                                                    <td>{{ $rejectIn->defect_type }}</td>
                                                    <td>{{ $rejectIn->reject_qty }}</td>
                                                    <td class="fw-bold {{ $rejectIn->output_type == 'qc' ? 'text-danger' : ($rejectIn->output_type == 'qcf' ? 'text-pink' : 'text-success') }}">{{ $rejectIn->output_type == "packing" ? "FINISHING" : strtoupper($rejectIn->output_type) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Defect OUT --}}
        <div class="col-12 col-md-12 {{ $mode != "out" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-rework">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}REJECT OUT</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b></b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="d-inline-flex gap-1 bg-white border p-1 rounded mb-3">
                            <button class="btn btn-primary btn-sm" onclick="">WIP</button>
                            <button class="btn btn-light btn-sm text-primary" onclick="">Sent</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Kode</th>
                                        <th>Waktu</th>
                                        <th>Dept.</th>
                                        <th>Line</th>
                                        <th>Worksheet</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Quality Check</th>
                                        <th>Grade</th>
                                        <th>Defect Type Check</th>
                                        <th>Defect Area Check</th>
                                        <th>Gambar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>TOTAL</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- All Defect --}}
        <div class="col-12 col-md-12 {{ $mode != "in-out" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-sb">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}Defect In Out Summary</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalRejectInOut }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()" onclick="rejectInOutReload()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="d-flex align-items-end gap-3 mb-3">
                                <div>
                                    <label class="form-label">From</label>
                                    <input type="date" class="form-control" value="{{ date("Y-m-d", strtotime("-7 days")) }}" id="dateFrom" wire:model="rejectInOutFrom" onchange="rejectInOutReload()">
                                </div>
                                <span class="mb-2">-</span>
                                <div>
                                    <label class="form-label">To</label>
                                    <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="dateTo" wire:model="rejectInOutTo" onchange="rejectInOutReload()">
                                </div>
                            </div>
                            <div class="mb-3" wire:ignore>
                                <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i> Export</button>
                            </div>
                        </div>
                        <div class="table-responsive" wire:ignore>
                            <table class="table table-bordered w-100" id="reject-in-out-table" >
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Total IN</th>
                                        <th>Total PROCESS</th>
                                        <th>Total OUT</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Show Defect Area --}}
    <div class="show-defect-area" id="show-defect-area" wire:ignore>
        <div class="position-relative d-flex flex-column justify-content-center align-items-center">
            <button type="button" class="btn btn-lg btn-light rounded-0 hide-defect-area-img" onclick="onHideRejectAreaImage()">
                <i class="fa-regular fa-xmark fa-lg"></i>
            </button>
            <div class="defect-area-img-container mx-auto">
                <div class="defect-area-img-point" id="defect-area-img-point-show"></div>
                <img src="" alt="" class="img-fluid defect-area-img" id="defect-area-img-show">
            </div>
        </div>
    </div>

    {{-- Defect In Out Detail Modal --}}
    <div class="modal" tabindex="-1" id="reject-in-out-modal" wire:ignore>
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light fw-bold">
                    <h5 class="modal-title">Defect In Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="text" class="form-control" id="rejectInOutDetailDate" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Line</label>
                                <select class="form-select select2-reject-in-out-modal" id="rejectInOutDetailLine" onchange="rejectInOutDetailReload()">
                                    <option value="" selected>All Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2-reject-in-out-modal" id="rejectInOutDetailDepartment" onchange="rejectInOutDetailReload()">
                                    <option value="">All Department</option>
                                    <option value="qc">QC</option>
                                    {{-- <option value="qcf">QC FINISHING</option> --}}
                                    <option value="packing">FINISHING</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row g-1 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">IN</label>
                                    <input type="text" class="form-control" id="rejectInOutDetailIn" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">PROCESS</label>
                                    <input type="text" class="form-control" id="rejectInOutDetailProcess" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">OUT</label>
                                    <input type="text" class="form-control" id="rejectInOutDetailOut" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered w-100" id="reject-in-out-detail-table">
                                    <thead>
                                        <tr>
                                            <th>Time IN</th>
                                            <th>Time OUT</th>
                                            <th>Line</th>
                                            <th>Dept.</th>
                                            <th>QR</th>
                                            <th>No. WS</th>
                                            <th>Style</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Area</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal" data-bs-backdrop="static" tabindex="-1" id="reject-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-reject text-light">
                    <h5 class="modal-title">REJECT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kode</label>
                                    <input type="text" class="form-control" wire:model="scannedRejectIn" readonly>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="text" class="form-control" wire:model="rejectInTimeModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control d-none" wire:model="rejectInOutputType" readonly>
                                    <input type="text" class="form-control" value="{{ ($rejectInOutputType && $rejectInOutputType == "packing" ? "FINISHING" : strtoupper($rejectInOutputType)) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Line</label>
                                    <input type="text" class="form-control" wire:model="rejectInLineModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Worksheet</label>
                                    <input type="text" class="form-control" wire:model="rejectInWorksheetModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Style</label>
                                    <input type="text" class="form-control" wire:model="rejectInStyleModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="text" class="form-control" wire:model="rejectInColorModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Size</label>
                                    <input type="text" class="form-control" wire:model="rejectInSizeModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Defect Type</label>
                                    <input type="text" class="form-control" wire:model="rejectInTypeModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Defect Area</label>
                                    <input type="text" class="form-control" wire:model="rejectInAreaModal" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Quality Check</label>
                                    <select class="form-select" wire:model="rejectInQuality">
                                        <option value="">Pilih Quality</option>
                                        <option value="reworked">GOOD</option>
                                        <option value="rejected">REJECT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="border-1 mb-3">
                        <div class="{{ $rejectInQuality && $rejectInQuality == "rejected" ? "" : "d-none" }}">
                            @if ($rejectDetails && count($rejectDetails) > 0)
                                @for ($i = 0; $i < count($rejectDetails); $i++)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <label class="form-label me-1 mb-0">Reject Type</label>
                                                </div>
                                                <div wire:ignore id="select-reject-type-container">
                                                    <select class="form-select reject-modal-select2" id="reject-type-select2-{{ $i }}" data-index="{{ $i }}" onchange="selectRejectType(this)">
                                                        <option value="" selected>Select reject type</option>
                                                        @foreach ($defectTypes as $defect)
                                                            <option value="{{ $defect->id }}">
                                                                {{ $defect->defect_type }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <label class="form-label me-1 mb-0">Reject Area</label>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <div class="w-75" wire:ignore id="select-reject-area-container">
                                                        <select class="form-select reject-modal-select2" id="reject-area-select2-{{ $i }}" data-index="{{ $i }}" onchange="selectRejectArea(this)">
                                                            <option value="" selected>Select reject area</option>
                                                            @foreach ($defectAreas as $defect)
                                                                <option value="{{ $defect->id }}">
                                                                    {{ $defect->defect_area }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="w-25">
                                                        <button type="button" wire:click="selectRejectAreaPosition({{ $i }})" id="select-reject-area-position-{{ $i }}" class="btn btn-dark w-100">
                                                            <i class="fa-regular fa-image"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            @endif
                            <div class="d-flex justify-content-end gap-1 mt-3">
                                <button type="button" class="btn btn-sm btn-success" wire:click="addRejectDetail"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" wire:click="removeRejectDetail"><i class="fa fa-minus"></i></button>
                                <button type="button" class="btn btn-sm btn-sb" wire:click="resetRejectDetails"><i class="fa fa-arrow-rotate-left"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <div id="regular-submit-reject" wire:ignore.self>
                        <button type="button" class="btn btn-success" wire:click="submitRejectIn">Selesai</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('datatables/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-rowgroup/css/rowGroup.bootstrap4.min.css') }}">

    {{-- DataTables --}}
    <script src="{{ asset('datatables/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Init Reject Modal Select2
        function initRejectSelect2() {
            $('.reject-modal-select2').each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    return;
                }

                // Re-init
                $(this).select2({
                    theme: "bootstrap-5",
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    dropdownParent: $('#reject-modal .modal-content')
                });
            });
        }

        // Reinit Reject Modal Select2
        Livewire.on('reinitSelect2', () => {
            setTimeout(() => {
                initRejectSelect2();
            }, 50);
        });

        document.addEventListener("DOMContentLoaded", async function () {
            document.getElementById('scannedItemRejectIn').focus();

            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });

            // Reject Modal Select2
            $('.reject-modal-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content')
            });

            $('.select2-reject-in-out-modal').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-in-out-modal')
            });

            $('#button-in').on('click', async function (e) {
                @this.changeMode("in")
            })

            $('#button-out').on('click', async function (e) {
                @this.changeMode("out")
            })

            $('#button-in-out').on('click', async function (e) {
                @this.changeMode("in-out")
            })
        });

        // REJECT IN
        function selectRejectType(element) {
            if (element.value) {
                @this.setRejectType(element.value, element.getAttribute("data-index"));
            }
        }

        function selectRejectArea(element) {
            if (element.value) {
                @this.setRejectArea(element.value, element.getAttribute("data-index"));
            }
        }

        Livewire.on('clearSelectRejectAreaPoint', ($i) => {
            $('#reject-type-select2-'+$i).val("").trigger('change');
            $('#reject-area-select2-'+$i).val("").trigger('change');
        });

        var scannedItemRejectIn = document.getElementById("scannedItemRejectIn");
        scannedItemRejectIn.addEventListener("change", async function () {
            @this.scannedRejectIn = this.value;

            // submit
            @this.preSubmitRejectIn();

            this.value = '';
        });

        // init scan
        Livewire.on('qrInputFocus', async (mode) => {
            if (mode == "in") {
                document.getElementById('scannedItemRejectIn').focus();
                document.getElementById('button-out').disabled = false;
            } else if (mode == "out") {
                document.getElementById('scannedItemRejectOut').focus()
                document.getElementById('button-in').disabled = false;
            }
        });

        // Reject Area Image
        function onShowRejectAreaImage(defectAreaImage, x, y) {
            Livewire.emit('showRejectAreaImage', defectAreaImage, x, y);
        }

        Livewire.on('showRejectAreaImage', async function (defectAreaImage, x, y) {
            await showRejectAreaImage(defectAreaImage);

            let defectAreaImageElement = document.getElementById('defect-area-img-show');
            let defectAreaImagePointElement = document.getElementById('defect-area-img-point-show');

            defectAreaImageElement.style.display = 'block'

            let rect = await defectAreaImageElement.getBoundingClientRect();

            let pointWidth = null;
            if (rect.width == 0) {
                pointWidth = 35;
            } else {
                pointWidth = 0.03 * rect.width;
            }

            defectAreaImagePointElement.style.width = pointWidth+'px';
            defectAreaImagePointElement.style.height = defectAreaImagePointElement.style.width;
            defectAreaImagePointElement.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.display = 'block';
        });

        function onHideRejectAreaImage() {
            hideRejectAreaImage();

            Livewire.emit('hideRejectAreaImageClear');
        }

        // Clear Reject In Input
        Livewire.on('clearRejectModal', async function (defectAreaImage, x, y) {
            $('.reject-modal-select2').each(function () {
                $(this).val(null).trigger("change");
            });
        });

        // Reject In Out
        let rejectInOutDatatable = $("#reject-in-out-table").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-reject-in-out-daily') }}',
                dataType: 'json',
                data: function (d) {
                    d.dateFrom = $("#dateFrom").val();
                    d.dateTo = $("#dateTo").val();
                }
            },
            columns: [
                {
                    data: 'tanggal',
                },
                {
                    data: 'tanggal',
                },
                {
                    data: 'total_in',
                },
                {
                    data: 'total_process',
                },
                {
                    data: 'total_out',
                }
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `<button type='button' class='btn btn-sb-secondary btn-sm' onclick='getRejectInOutDetail("`+data+`")'><i class='fa fa-search'></i></button>`
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
        });

        function rejectInOutReload() {
            $("#reject-in-out-table").DataTable().ajax.reload();
        }

        // Reject In Out Detail
        let rejectInOutDetailDatatable = $("#reject-in-out-detail-table").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-reject-in-out-detail') }}',
                data: function (d) {
                    d.tanggal = $("#rejectInOutDetailDate").val();
                    d.line = $("#rejectInOutDetailLine").val();
                    d.departemen = $("#rejectInOutDetailDepartment").val();
                },
                dataType: 'json',
            },
            columns: [
                {
                    data: 'time_in',
                },
                {
                    data: 'time_out',
                },
                {
                    data: 'sewing_line',
                },
                {
                    data: 'output_type',
                },
                {
                    data: 'kode_numbering',
                },
                {
                    data: 'no_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'defect_type',
                },
                {
                    data: 'defect_area',
                },
                {
                    data: 'gambar',
                },
                {
                    data: 'status',
                },
            ],
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return data ? data.replace("_", " ").toUpperCase() : '-';
                    }
                },
                {
                    targets: [3],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "packing") {
                            textColor = "text-success";
                        } else {
                            textColor = "text-danger";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data ? (data == "packing" ? "FINISHING" : data.toUpperCase()) : '-')+`</span>`;
                    }
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        return `<button class="btn btn-dark" onclick="onShowRejectAreaImage('`+row.gambar+`', `+row.reject_area_x+`, `+row.reject_area_y+`)"><i class="fa fa-image"></i></button>`
                    }
                },
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "reworked") {
                            textColor = "text-rework";
                        } else {
                            textColor = "text-defect";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data ? data.toUpperCase() : '-')+`</span>`;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
        });

        function rejectInOutDetailReload() {
            $("#reject-in-out-detail-table").DataTable().ajax.reload(() => {
                $("#rejectInOutDetailIn").val("-");
                $("#rejectInOutDetailProcess").val("-");
                $("#rejectInOutDetailOut").val("-");

                $.ajax({
                    url: "{{ route("get-reject-in-out-detail-total") }}",
                    type: "get",
                    data: {
                        tanggal : $("#rejectInOutDetailDate").val(),
                        line : $("#rejectInOutDetailLine").val(),
                        departemen : $("#rejectInOutDetailDepartment").val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response) {
                            $("#rejectInOutDetailIn").val(response.rejectIn);
                            $("#rejectInOutDetailProcess").val(response.defectProcess);
                            $("#rejectInOutDetailOut").val(response.rejectOut);
                        }
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);
                    }
                });

                rejectInOutReload();
            });
        }

        async function getRejectInOutDetail(tanggal) {
            $("#rejectInOutDetailDate").val(tanggal);

            rejectInOutDetailReload();

            $("#reject-in-out-modal").modal("show");
        }

        // Reject In Out Export
        function exportExcel(elm) {
            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            $.ajax({
                url: "{{ route("export-reject-in-out") }}",
                type: 'post',
                data: {
                    dateFrom : $("#dateFrom").val(),
                    dateTo : $("#dateTo").val(),
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Data berhasil di export.',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Defect In Out {{ Auth::user()->Groupp }} "+$("#dateFrom").val()+" - "+$("#dateTo").val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endpush
