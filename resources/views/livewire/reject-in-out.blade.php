<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="changeMode, preSubmitRejectIn, submitRejectIn, refreshComponent, addRejectDetail, removeRejectDetail, resetRejectDetails, rejectInQuality, setRejectType, setRejectArea, selectRejectAreaPosition, showRejectAreaImage, showMultiRejectAreaImage, rejectOutSelectedList, rejectOutStatus, addRejectOutSelectedList, removeRejectOutSelectedList, sendRejectOut, setRejectOutTujuan, setRejectOutLine, rejectInGrade">
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
                        <h5 class="card-title text-light text-center fw-bold">REJECT IN</h5>
                        <div class="d-flex align-items-center">
                            {{-- <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalRejectIn }}</b></h5> --}}
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex flex-column gap-3 h-100">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="text-center mb-0">OUTSTANDING CHECK</h5>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="text-center"><b>{{ $totalRejectIn }}</b></h5>
                                    </div>
                                </div>
                                <input type="text" class="qty-input border h-100" id="scannedItemRejectIn" name="scannedItemRejectIn">
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
                                        <tr class="text-center align-middle">
                                            <td>

                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterKode">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterWaktu">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterLine">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterMasterPlan">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterSize">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" wire:model="rejectInFilterType">
                                            </td>
                                            <td>

                                            </td>
                                            <td>

                                            </td>
                                        </tr>
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
                                                    <td>{{ $rejectInList->firstItem() + $loop->index }}</td>
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
                            <div class="mt-3">
                                {{ $rejectInList->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject OUT --}}
        <div class="col-12 col-md-12 {{ $mode != "out" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-rework">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">REJECT OUT</h5>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="d-inline-flex gap-1 bg-white border p-1 rounded mb-3" wire:ignore>
                            <button class="btn btn-primary btn-sm" id="btn-wip" onclick="rejectOutReload('wip')">WIP</button>
                            <button class="btn btn-light btn-sm text-primary" id="btn-sent" onclick="rejectOutReload('sent')">Sent</button>
                        </div>
                    </div>
                    <input type="hidden" class="form-control d-none" id="reject-out-process" value="wip" wire:ignore.self>
                    <div class="row">
                        <div class="col-md-12">
                            {{-- WIP --}}
                            <div id="reject-out-table-wip-container" wire:ignore>
                                <div class="table-responsive">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button class="btn btn-success btn-sm" onclick="rejectWipExport(this)"><i class="fa fa-file-excel"></i> Export</button>
                                    </div>
                                    <table class="table table-sm table-bordered  w-100" id="reject-out-table-wip">
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
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>TOTAL</td>
                                                <td colspan="13"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button class="btn btn-sb-secondary btn-block mt-3" id="pre-send-reject-out-button" onclick="preSendRejectOut()" wire:ignore><i class="fa-solid fa-paper-plane"></i> SEND</button>
                            </div>
                            {{-- SENT --}}
                            <div id="reject-out-table-sent-container" class="d-none" wire:ignore>
                                <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                                    <div class="d-flex align-items-end gap-3">
                                        <div>
                                            <label class="form-label">Tanggal Awal</label>
                                            <input type="date" class="form-control form-control-sm" value="{{ date("Y-m-d") }}" id="date-from-sent" onchange="rejectOutReload('sent')">
                                        </div>
                                        <div>
                                            <label class="form-label">Tanggal Akhir</label>
                                            <input type="date" class="form-control form-control-sm" value="{{ date("Y-m-d") }}" id="date-to-sent" onchange="rejectOutReload('sent')">
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-sm" onclick="rejectOutDetailExport(this)"><i class="fa fa-file-excel"></i> Export</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered  w-100" id="reject-out-table-sent">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Tanggal</th>
                                                <th>No. Transaksi</th>
                                                <th>Tujuan</th>
                                                <th>Worksheet</th>
                                                <th>Style</th>
                                                <th>Color</th>
                                                <th>Size</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="8">TOTAL</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- All Reject --}}
        <div class="col-12 col-md-12 {{ $mode != "in-out" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-sb">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">Reject In Out Summary</h5>
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
                                <button class="btn btn-success" onclick="rejectInOutExport(this)" wire:ignore><i class="fa fa-file-excel"></i> Export</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered w-100" id="reject-in-out-table" wire:ignore>
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Total CHECK</th>
                                        <th>Total GOOD</th>
                                        <th>Total REJECT</th>
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

    {{-- Reject In Out Detail Modal --}}
    <div class="modal" tabindex="-1" id="reject-in-out-modal" wire:ignore>
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light fw-bold">
                    <h5 class="modal-title">Reject In Out</h5>
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
                            <div class="row rpw-gap-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">GOOD</label>
                                    <input type="text" class="form-control" id="rejectInOutDetailGood" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">REJECT</label>
                                    <input type="text" class="form-control" id="rejectInOutDetailReject" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered w-100" id="reject-in-out-detail-table">
                                    <thead>
                                        <tr>
                                            <th>QR</th>
                                            <th>Waktu Cek</th>
                                            <th>Dept.</th>
                                            <th>Line</th>
                                            <th>No. WS</th>
                                            <th>Style</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Defect Type QC</th>
                                            <th>Quality Check</th>
                                            <th>Grade</th>
                                            <th>Defect Type Check</th>
                                            <th>Defect Area Check</th>
                                            <th>Image</th>
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

    {{-- Reject In Modal --}}
    <div class="modal" data-bs-backdrop="static" tabindex="-1" id="reject-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
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
                                    <input type="text" class="form-control d-none" wire:model="rejectInOutputTypeModal" readonly>
                                    <input type="text" class="form-control" value="{{ ($rejectInOutputTypeModal && $rejectInOutputTypeModal == "packing" ? "FINISHING" : strtoupper($rejectInOutputTypeModal)) }}" readonly>
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
                                    <select class="form-select" wire:model="rejectInQuality" id="reject-quality">
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
                <div class="modal-footer justify-content-between gap-1">
                    <div>
                        <div class="d-flex justify-content-end gap-1 align-items-center {{ $defectInQuality && $rejectInQuality == "rejected" ? "" : "d-none" }}">
                            <label class="form-label mb-0">Grade: </label>
                            <select class="form-select" wire:model="rejectInGrade" {{ $defectInQuality && $rejectInQuality == "rejected" ? "" : "disabled" }}>
                                <option value=""></option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        <div id="regular-submit-reject" wire:ignore.self>
                            <button type="button" class="btn btn-success" wire:click="submitRejectIn">Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Out Modal --}}
    <div class="modal" data-bs-backdrop="static" tabindex="-1" id="send-reject-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb-secondary text-light">
                    <h5 class="modal-title">SEND</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-evenly align-items-end row-gap-1">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="text" class="form-control" value="{{ date('Y-m-d') }}" wire:model="rejectOutTanggal" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No. Transaksi</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="reject-out-no-transaksi" readonly>
                                <button class="btn btn-dark" onclick="getRejectOutNumber()"><i class="fa fa-arrows-rotate"></i></button>
                            </div>
                            <input type="hidden" class="form-control d-none" wire:model="rejectOutNoTransaksi" readonly>
                        </div>
                        <div class="col-md-3">
                            <div id="tujuan-rejected-container" wire:ignore>
                                <label class="form-label">Tujuan</label>
                                <select class="form-select select2bs4rejectout" name="reject-out-tujuan" id="reject-out-tujuan" onchange="setRejectOutTujuan(this)">
                                    <option value="gudang">Gudang Stock</option>
                                </select>
                            </div>
                            <div id="tujuan-reworked-container" class="d-none" wire:ignore>
                                <label class="form-label">Line</label>
                                <select class="form-select select2bs4rejectout" name="reject-out-line" id="reject-out-line" onchange="setRejectOutLine(this)">
                                    <option>Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" class="form-control" wire:model="rejectOutStatus">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-sb-secondary w-100" onclick="sendRejectOut()">
                                <i class="fa-solid fa-paper-plane"></i> SEND
                            </button>
                        </div>
                        <div class="col-md-12">
                            <h6 class="mt-3">Garment List</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="garment-list">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">Worksheet</th>
                                            <th class="text-nowrap">Style</th>
                                            <th class="text-nowrap">Color</th>
                                            <th class="text-nowrap">Size</th>
                                            <th class="text-nowrap">Grade</th>
                                            <th class="text-nowrap">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($rejectOutSelectedList) > 0)
                                            @php
                                                $rejectOutSelectedListGroup = collect($rejectOutSelectedList)->groupBy("grouping");
                                            @endphp
                                            @if ($rejectOutSelectedListGroup && count($rejectOutSelectedListGroup))
                                                @foreach ($rejectOutSelectedListGroup as $list)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $list->first()['kpno'] }}</td>
                                                        <td class="text-nowrap">{{ $list->first()['styleno'] }}</td>
                                                        <td class="text-nowrap">{{ $list->first()['color'] }}</td>
                                                        <td class="text-nowrap">{{ $list->first()['size'] }}</td>
                                                        <td class="text-nowrap">{{ $list->first()['grade'] }}</td>
                                                        <td class="text-nowrap">{{ $list->count() }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @else
                                            <tr>
                                                <td colspan="6">Tidak ada data yang dipilih</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">Total</td>
                                            <td>{{ count($rejectOutSelectedList) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Out Detail Modal --}}
    <div class="modal" data-bs-backdrop="static" tabindex="-1" id="sent-reject-modal" wire:ignore>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb-secondary text-light">
                    <h5 class="modal-title">SENT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-evenly align-items-end row-gap-1">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="text" class="form-control" value="{{ date('Y-m-d') }}" id="reject-out-tanggal-sent" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Transaksi</label>
                            <input type="hidden" class="form-control d-none" id="reject-out-id-sent" readonly>
                            <input type="text" class="form-control" id="reject-out-no-transaksi-sent" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tujuan</label>
                            <input type="text" class="form-control" id="reject-out-tujuan-sent" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">WS</label>
                            <input type="hidden" class="form-control d-none" id="act-costing-id-sent" readonly>
                            <input type="text" class="form-control" id="act-costing-ws-sent" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="style-sent" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" id="color-sent" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Size</label>
                            <input type="text" class="form-control" id="size-sent" readonly>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive mt-3" >
                                <table class="table table-bordered table-sm" id="garment-list-sent">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">Tanggal Kirim</th>
                                            <th class="text-nowrap">No. Transaksi</th>
                                            <th class="text-nowrap">Tujuan</th>
                                            <th class="text-nowrap">Kode</th>
                                            <th class="text-nowrap">Worksheet</th>
                                            <th class="text-nowrap">Style</th>
                                            <th class="text-nowrap">Color</th>
                                            <th class="text-nowrap">Size</th>
                                            <th class="text-nowrap">Quality Check</th>
                                            <th class="text-nowrap">Grade</th>
                                            <th class="text-nowrap">Defect Type Check</th>
                                            <th class="text-nowrap">Defect Area Check</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>TOTAL</td>
                                            <td colspan="11"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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
                    const id = $(this).attr('id');
                    const $dropdown = $(`.select2-dropdown:has([aria-controls="select2-${id}-results"])`);
                    const $options = $dropdown.find('.select2-results__option');

                    if ($options.length === 0) {
                        $(this).select2('destroy').select2({
                            theme: "bootstrap-5",
                            width: $(this).data('width')
                                ? $(this).data('width')
                                : $(this).hasClass('w-100')
                                    ? '100%'
                                    : 'style',
                            placeholder: $(this).data('placeholder'),
                            dropdownParent: $('#reject-modal .modal-content')
                        });
                    }

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

        function initSendRejectSelect2() {
            $('.select2bs4rejectout').each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    const id = $(this).attr('id');
                    const $dropdown = $(`.select2-dropdown:has([aria-controls="select2-${id}-results"])`);
                    const $options = $dropdown.find('.select2-results__option');

                    if ($options.length === 0) {
                        $(this).select2('destroy').select2({
                            theme: "bootstrap-5",
                            width: $(this).data('width')
                                ? $(this).data('width')
                                : $(this).hasClass('w-100')
                                    ? '100%'
                                    : 'style',
                            placeholder: $(this).data('placeholder'),
                            dropdownParent: $('#send-reject-modal')
                        });
                    }

                    return;
                }

                // Re-init
                $(this).select2({
                    theme: "bootstrap-5",
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    dropdownParent: $('#send-reject-modal')
                });
            });
        }

        Livewire.hook('message.processed', () => {
            initRejectSelect2();
            initSendRejectSelect2();
        });

        // Reinit Reject Modal Select2
        // Livewire.on('reinitSelect2', () => {
        //     setTimeout(() => {
        //         initRejectSelect2();
        //     }, 50);
        // });

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
                $("#reject-out-table-sent").DataTable().ajax.reload();
                $("#reject-out-table-wip").DataTable().ajax.reload();
            })

            $('#button-in-out').on('click', async function (e) {
                @this.changeMode("in-out")
                rejectInOutReload();
                rejectInOutDetailReload();
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
                // document.getElementById('scannedItemRejectOut').focus();
                document.getElementById('button-in').disabled = false;
            }
        });

        // Reject Area Image
        function onShowRejectAreaImage(defectAreaImage, x, y) {
            Livewire.emit('showRejectAreaImage', defectAreaImage, x, y);
        }

        Livewire.on('showRejectAreaImage', async function (defectAreaImage, xParam, yParam) {
            let x = Number(xParam) > 0 && xParam != Infinity ? xParam : 0;
            let y = Number(yParam) > 0 && yParam != Infinity ? yParam : 0;

            await showRejectAreaImage(defectAreaImage);

            let defectAreaImageElement = document.getElementById('reject-area-img-show');
            let defectAreaImagePointElement = document.getElementById('reject-area-img-point-show');

            defectAreaImageElement.style.display = 'block'

            let rect = await defectAreaImageElement.getBoundingClientRect();

            let pointWidth = null;
            if (rect.width == 0) {
                pointWidth = 25;
            } else {
                pointWidth = 0.03 * rect.width;
            }

            defectAreaImagePointElement.style.width = pointWidth+'px';
            defectAreaImagePointElement.style.height = defectAreaImagePointElement.style.width;
            defectAreaImagePointElement.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.display = 'block';

            document.getElementById("reject-area-img-types").parentElement.classList.add("d-none");
            document.getElementById("reject-area-img-types").classList.add("d-none");
        });

        function onHideRejectAreaImage() {
            hideRejectAreaImage();

            Livewire.emit('hideRejectAreaImageClear');
        }

        // Multi Reject Area Image
        function onShowMultiRejectAreaImage(defectAreaImage, position) {
            Livewire.emit('showMultiRejectAreaImage', defectAreaImage, position);
        }

        Livewire.on('showMultiRejectAreaImage', async function (defectAreaImage, position) {
            await showRejectAreaImage(defectAreaImage);

            let defectAreaImageElement = document.getElementById('reject-area-img-show');
            let defectAreaImagePointElement = document.getElementById('reject-area-img-point-show');

            defectAreaImageElement.style.display = 'block'

            let rect = await defectAreaImageElement.getBoundingClientRect();

            let pointWidth = null;
            if (rect.width == 0) {
                pointWidth = 25;
            } else {
                pointWidth = 0.03 * rect.width;
            }

            // Multi Positions
            let positions = position.split(" | ");

            var colorList = ['#e31010', '#104fe3', '#ebcd0c', '#830ceb', '#12e02a', '#ed790c', '#f54980', '#10ded7', '#854008', '#bdbdbd'];

            for (let i = 0; i < positions.length; i++) {
                if (positions[i]) {

                    let typePositions = positions[i].split(" // ");

                    if (typePositions.length >= 3) {
                        let type = typePositions[0];

                        // Type
                        let list = document.createElement('li');
                        list.innerHTML = type;

                        let badge = document.createElement('span');
                        badge.style.display = 'inline-block';
                        badge.style.width = '15px';
                        badge.style.height = '15px';
                        badge.style.borderRadius = '50%';
                        badge.style.background = i > 9 ? '#bdbdbd' : colorList[i];
                        badge.style.borderColor = i > 9 ? '#bdbdbd' : colorList[i];
                        badge.style.margin = '0 3px';
                        badge.style.position = 'relative';
                        badge.style.top = '2px';
                        badge.style.opacity = '90%';

                        list.appendChild(badge);
                        document.getElementById("reject-area-img-types").appendChild(list);
                        document.getElementById("reject-area-img-types").parentElement.classList.remove("d-none");
                        document.getElementById("reject-area-img-types").classList.remove("d-none");

                        // Area
                        let x = Number(typePositions[1]) > 0 && typePositions[1] != Infinity ? typePositions[1] : 0;
                        let y = Number(typePositions[2]) > 0 && typePositions[2] != Infinity ? typePositions[2] : 0;

                        if (i != 0) {
                            let defectAreaImagePointElementClone = defectAreaImagePointElement.cloneNode();
                            defectAreaImagePointElementClone.classList.add("reject-area-img-point-clone");
                            defectAreaImagePointElementClone.id = 'reject-area-img-point-show-'+i;

                            document.getElementById('reject-area-img-container-show').appendChild(defectAreaImagePointElementClone);

                            defectAreaImagePointElementClone.style.width = pointWidth+'px';
                            defectAreaImagePointElementClone.style.height = defectAreaImagePointElementClone.style.width;
                            defectAreaImagePointElementClone.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
                            defectAreaImagePointElementClone.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
                            defectAreaImagePointElementClone.style.display = 'block';
                            defectAreaImagePointElementClone.style.backgroundColor = colorList[i];
                            defectAreaImagePointElementClone.style.border = '3px solid '+colorList[i];
                        } else {
                            defectAreaImagePointElement.style.width = pointWidth+'px';
                            defectAreaImagePointElement.style.height = defectAreaImagePointElement.style.width;
                            defectAreaImagePointElement.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
                            defectAreaImagePointElement.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
                            defectAreaImagePointElement.style.display = 'block';
                            defectAreaImagePointElement.style.backgroundColor = colorList[i];
                            defectAreaImagePointElement.style.border = '3px solid '+colorList[i];
                        }
                    }
                }
            }
        });

        // Clear Reject In Input
        Livewire.on('clearRejectModal', async function (defectAreaImage, x, y) {
            $('.reject-modal-select2').each(function () {
                $(this).val(null).trigger("change");
            });
        });



        // REJECT OUT

        // Reject Out Wip Filter
        let rejectOutWipFilter = ["id_wip_filter", "kode_numbering_wip_filter", "waktu_wip_filter", "department_wip_filter", "line_wip_filter", "ws_wip_filter", "style_wip_filter", "size_wip_filter", "quality_check_wip_filter", "grade_wip_filter", "defect_type_check_wip_filter", "defect_area_check_wip_filter"];
        $('#reject-out-table-wip thead tr').clone(true).appendTo('#reject-out-table-wip thead');
        $('#reject-out-table-wip thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 13) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" />');

                $('input', this).on('keyup change', function() {
                    if (rejectOutWipDatatable.column(i).search() !== this.value) {
                        rejectOutWipDatatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 0) {
                    // $(this).html(`
                    //     <div class="form-check" style="scale: 1.5;translate: 50%;">
                    //         <input class="form-check-input" type="checkbox" id="checkAllReject">
                    //     </div>
                    // `);

                    $(this).html(``);
                } else {
                    $(this).empty();
                }
            }
        });

        // Reject Out Wip Table
        let rejectOutWipDatatable = $("#reject-out-table-wip").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-reject-out') }}',
                dataType: 'json',
                data: function (d) {
                    d.process = "wip";
                }
            },
            columns: [
                {
                    data: "id"
                },
                {
                    data: "kode_numbering"
                },
                {
                    data: "updated_at"
                },
                {
                    data: "output_type"
                },
                {
                    data: "username"
                },
                {
                    data: "kpno"
                },
                {
                    data: "styleno"
                },
                {
                    data: "color"
                },
                {
                    data: "size"
                },
                {
                    data: "status"
                },
                {
                    data: "grade"
                },
                {
                    data: "defect_types"
                },
                {
                    data: "defect_areas"
                },
                {
                    data: "gambar"
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-center text-nowrap align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class="form-check" style="scale: 1.5;translate: 50%;">
                                <input class="form-check-input check-stock-number" type="checkbox" data-status="`+row.status+`" onchange="checkRejectOut(this)" id="stock_number_`+meta.row+`">
                            </div>
                        `;
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
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
                    targets: [4],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        return `<span>`+(data ? data.toUpperCase() : '-')+`</span>`;
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "rejected") {
                            textColor = "text-reject";
                        } else {
                            textColor = "text-success";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data == "reworked" ? "GOOD" : 'REJECT')+`</span>`;
                    }
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        return data ? data : "-";
                    }
                },
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        return data ? data : "-";
                    }
                },
                {
                    targets: [13],
                    render: (data, type, row, meta) => {
                        return `<button class="btn btn-dark" onclick="onShowMultiRejectAreaImage('`+row.gambar+`', '`+row.reject_area_position+`')"><i class="fa fa-image"></i></button>`
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var info = api.page.info();

                var total = info.recordsTotal;  // total

                $(api.column(1).footer()).html(total);
            }
        });

        // Reject Out Sent Filter
        let rejectOutSentFilter = ["id_filter", "tanggal_sent_filter", "no_transaksi_sent_filter", "tujuan_sent_filter", "kpno_sent_filter", "styleno_sent_filter", "color_sent_filter", "size_sent_filter"]
        $('#reject-out-table-sent thead tr').clone(true).appendTo('#reject-out-table-sent thead');
        $('#reject-out-table-sent thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 7) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" id="'+rejectOutSentFilter[i]+'"/>');

                $('input', this).on('keyup change', function() {
                    if (rejectOutSentDatatable.column(i).search() !== this.value) {
                        rejectOutSentDatatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 0) {
                    $(this).html(``);
                } else {
                    $(this).empty();
                }
            }
        });

        // Reject Out Sent Table
        let rejectOutSentDatatable = $("#reject-out-table-sent").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-reject-out') }}',
                dataType: 'json',
                data: function (d) {
                    d.tanggal_awal = $("#date-from-sent").val();
                    d.tanggal_akhir = $("#date-to-sent").val();
                    d.process = "sent";
                }
            },
            columns: [
                {
                    data: "id"
                },
                {
                    data: "tanggal"
                },
                {
                    data: "no_transaksi"
                },
                {
                    data: "tujuan"
                },
                {
                    data: "kpno"
                },
                {
                    data: "styleno"
                },
                {
                    data: "color"
                },
                {
                    data: "size"
                },
                {
                    data: "qty"
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-center text-nowrap align-middle",
                    render: (data, type, row, meta) => {
                        return "<button class='btn btn-sb-secondary' onclick='showRejectOutDetail(" + JSON.stringify(row) + ");'><i class='fa fa-magnifying-glass'></i></button>";
                    }
                },
                {
                    targets: [3],
                    className: "text-center text-nowrap align-middle",
                    render: (data, type, row, meta) => {
                        return data ? data.toUpperCase() : "-";
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var info = api.page.info();

                $(api.column(8).footer()).html("...");

                $.ajax({
                    type: "get",
                    url: "{{ route('get-reject-out-total') }}",
                    data: {
                        tanggal_awal: $("#date-from-sent").val(),
                        tanggal_akhir: $("#date-to-sent").val(),
                        tanggal: $("#tanggal_sent_filter").val(),
                        no_transaksi: $("#no_transaksi_sent_filter").val(),
                        tujuan: $("#tujuan_sent_filter").val(),
                        kpno: $("#kpno_sent_filter").val(),
                        styleno: $("#styleno_sent_filter").val(),
                        color: $("#color_sent_filter").val(),
                        size: $("#size_sent_filter").val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            $(api.column(8).footer()).html(response);
                        }
                    }
                });

            }
        });

        // Reject Out Refresh
        function rejectOutReload(process = null) {
            if (process) {
                $("#reject-out-process").val(process).trigger("change");

                switchRejectOutProcess(process);
            }

            $("#reject-out-table-sent").DataTable().ajax.reload();
            $("#reject-out-table-wip").DataTable().ajax.reload();

            rejectOutSelectedListArr = [];
            selectedStatus = "";

            $("#send-reject-modal").modal("hide");
            $("#reject-out-tujuan").val("");
            $("#reject-out-line").val("");
        }

        // Switch Reject Out
        function switchRejectOutProcess(process) {
            if (process == "sent") {
                $("#reject-out-table-wip-container").addClass("d-none");
                $("#reject-out-table-sent-container").removeClass("d-none");

                $("#btn-wip").removeClass("btn-primary");
                $("#btn-wip").addClass("btn-light");
                $("#btn-wip").removeClass("text-light");
                $("#btn-wip").addClass("text-primary");

                $("#btn-sent").removeClass("btn-light");
                $("#btn-sent").addClass("btn-primary");
                $("#btn-sent").removeClass("text-primary");
                $("#btn-sent").addClass("text-light");
            } else {
                $("#reject-out-table-sent-container").addClass("d-none");
                $("#reject-out-table-wip-container").removeClass("d-none");

                $("#btn-wip").removeClass("btn-light");
                $("#btn-wip").addClass("btn-primary");
                $("#btn-wip").removeClass("text-primary");
                $("#btn-wip").addClass("text-light");

                $("#btn-sent").removeClass("btn-primary");
                $("#btn-sent").addClass("btn-light");
                $("#btn-sent").removeClass("text-light");
                $("#btn-sent").addClass("text-primary");
            }
        }

        var rejectOutSelectedListArr = [];
        var selectedStatus = '';
        // Check Reject Out
        function checkRejectOut(element) {
            let data = $('#reject-out-table-wip').DataTable().row(element.closest('tr')).data();

            if (data) {
                let stockNumberCheck =  document.getElementsByClassName('check-stock-number');
                if (element.checked) {
                    for (let i = 0; i < stockNumberCheck.length; i++) {
                        if (stockNumberCheck[i].getAttribute('data-status') != element.getAttribute('data-status')) {
                            stockNumberCheck[i].setAttribute('disabled', true);
                        } else {
                            stockNumberCheck[i].removeAttribute('disabled');
                        }
                    }

                    rejectOutSelectedListArr.push(data);

                    selectedStatus = element.getAttribute('data-status');
                } else {
                    rejectOutSelectedListArr = rejectOutSelectedListArr.filter((item) => item.kode_numbering != data.kode_numbering);

                    selectedStatus = '';

                    if (rejectOutSelectedListArr < 1) {
                        for (let i = 0; i < stockNumberCheck.length; i++) {
                            stockNumberCheck[i].removeAttribute('disabled');
                        }
                    }
                }
            }
        }

        function getRejectOutNumber() {
            document.getElementById("loading-reject-in-out").classList.remove("hidden");

            $.ajax({
                type: "get",
                url: "{{ route('get-reject-out-number') }}",
                success: function (response) {
                    document.getElementById("reject-out-no-transaksi").value = response;
                    @this.rejectOutNoTransaksi = response;
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        function preSendRejectOut() {
            if (rejectOutSelectedListArr.length > 0) {
                getRejectOutNumber();

                @this.rejectOutSelectedList = rejectOutSelectedListArr;
                @this.rejectOutStatus = selectedStatus;

                let tujuanRejected = document.getElementById('tujuan-rejected-container');
                let tujuanReworked = document.getElementById('tujuan-reworked-container');

                if (selectedStatus == 'reworked') {
                    tujuanReworked.classList.remove("d-none");
                    tujuanRejected.classList.add("d-none");
                } else if (selectedStatus == 'rejected') {
                    tujuanRejected.classList.remove("d-none");
                    tujuanReworked.classList.add("d-none");
                }

                $("#send-reject-modal").modal("show");
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "Harap pilih REJECT yang akan dikirim.",
                });
            }
        }

        function setRejectOutTujuan (element) {
            if (element.value) {
                @this.setRejectOutTujuan(element.value);
            }
        }

        function setRejectOutLine (element) {
            if (element.value) {
                @this.setRejectOutLine(element.value);
            }
        }

        function sendRejectOut() {
            Swal.fire({
                title: "Kirim Reject ke "+(selectedStatus == "reworked" ? $("#reject-out-line option:selected").text() : $("#reject-out-tujuan option:selected").text())+" ?",
                showCancelButton: true,
                confirmButtonText: "Kirim",
                confirmButtonColor: "#238380",
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.sendRejectOut();
                }
            });
        }

        Livewire.on('refreshRejectOutNumber', function() {
            getRejectOutNumber();
            rejectOutReload();
        })

        // Show Reject Out Detail
        async function showRejectOutDetail(e, modal, addons = []) {
            let data = e;

            $("#reject-out-tanggal-sent").val(e.tanggal);
            $("#reject-out-id-sent").val(e.id);
            $("#reject-out-no-transaksi-sent").val(e.no_transaksi);
            $("#reject-out-tujuan-sent").val(e.tujuan ? e.tujuan.toUpperCase() : '-');
            $("#act-costing-id-sent").val(e.act_costing_id);
            $("#act-costing-ws-sent").val(e.kpno);
            $("#style-sent").val(e.styleno);
            $("#color-sent").val(e.color);
            $("#size-sent").val(e.size);

            $("#sent-reject-modal").modal("show");

            rejectOutDetailReload();
        }

        // Reject Out Detail Filter
        $('#garment-list-sent thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" />');

            $('input', this).on('keyup change', function() {
                if (rejectOutDetailDatatable.column(i).search() !== this.value) {
                    rejectOutDetailDatatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        // Reject Out Detail Table
        let rejectOutDetailDatatable = $("#garment-list-sent").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-reject-out-detail') }}',
                dataType: 'json',
                data: function (d) {
                    d.reject_out_id = $("#reject-out-id-sent").val();
                    d.act_costing_id = $("#act-costing-id-sent").val();
                    d.color = $("#color-sent").val();
                    d.size =  $("#size-sent").val();
                }
            },
            columns: [
                {
                    data: 'tanggal',
                },
                {
                    data: 'no_transaksi',
                },
                {
                    data: 'tujuan',
                },
                {
                    data: 'kode_numbering',
                },
                {
                    data: 'kpno',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'status',
                },
                {
                    data: 'grade',
                },
                {
                    data: 'defect_types',
                },
                {
                    data: 'defect_areas',
                }
            ],
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return data ? data.toUpperCase() : '-';
                    }
                },
                {
                    targets: [8],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "reworked") {
                            textColor = "text-primary";
                        } else {
                            textColor = "text-danger";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data ? (data == "reworked" ? "GOOD" : "REJECT") : '-')+`</span>`;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var info = api.page.info();

                var total = info.recordsTotal;  // total

                $(api.column(1).footer()).html(total);
            }
        });

        function rejectOutDetailReload() {
            $("#garment-list-sent").DataTable().ajax.reload();
        }

        // Reject Wip Export
        function rejectWipExport(elm) {
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
                url: "{{ route("export-reject-wip") }}",
                type: 'post',
                data: {
                    kode_numbering: $("#kode_numbering_wip_filter").val(),
                    waktu: $("#waktu_wip_filter").val(),
                    department: $("#department_wip_filter").val(),
                    line: $("#line_wip_filter").val(),
                    ws: $("#ws_wip_filter").val(),
                    style: $("#style_wip_filter").val(),
                    size: $("#size_wip_filter").val(),
                    quality_check: $("#quality_check_wip_filter").val(),
                    grade: $("#grade_wip_filter").val(),
                    defect_type_check: $("#defect_type_check_wip_filter").val(),
                    defect_area_check: $("#defect_area_check_wip_filter").val(),
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
                    link.download = "Reject WIP "+formatDateTime(Date.now())+".xlsx";
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

        // Reject Out Detail Export
        function rejectOutDetailExport(elm) {
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
                url: "{{ route("export-reject-out-detail") }}",
                type: 'post',
                data: {
                    tanggal_awal: $("#date-from-sent").val(),
                    tanggal_akhir: $("#date-to-sent").val(),
                    tanggal: $("#tanggal_sent_filter").val(),
                    no_transaksi: $("#no_transaksi_sent_filter").val(),
                    tujuan: $("#tujuan_sent_filter").val(),
                    kpno: $("#kpno_sent_filter").val(),
                    styleno: $("#styleno_sent_filter").val(),
                    color: $("#color_sent_filter").val(),
                    size: $("#size_sent_filter").val()
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
                    link.download = "Reject Sent "+$("#date-from-sent").val()+" - "+$("#date-to-sent").val()+".xlsx";
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
                    data: 'total_good',
                },
                {
                    data: 'total_reject',
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
                    data: 'kode_numbering',
                },
                {
                    data: 'time_in',
                },
                {
                    data: 'output_type',
                },
                {
                    data: 'sewing_line',
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
                    data: 'status',
                },
                {
                    data: 'grade',
                },
                {
                    data: 'defect_types_check',
                },
                {
                    data: 'defect_areas_check',
                },
                {
                    data: 'gambar',
                },
            ],
            columnDefs: [
                {
                    targets: [2],
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
                    targets: [3],
                    render: (data, type, row, meta) => {
                        return data ? data.replace("_", " ").toUpperCase() : '-';
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "reworked") {
                            textColor = "text-success";
                        } else {
                            textColor = "text-danger";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data == "reworked" ? 'GOOD' : 'REJECT')+`</span>`;
                    }
                },
                {
                    targets: [13],
                    render: (data, type, row, meta) => {
                        if (row.reject_area_position) {
                            return `<button class="btn btn-dark" onclick="onShowMultiRejectAreaImage('`+row.gambar+`', '`+row.reject_area_position+`')"><i class="fa fa-image"></i></button>`;
                        } else {
                            return `<button class="btn btn-dark" onclick="onShowRejectAreaImage('`+row.gambar+`', `+row.reject_area_x+`, `+row.reject_area_y+`)"><i class="fa fa-image"></i></button>`;
                        }
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
                $("#rejectInOutDetailGood").val("-");
                $("#rejectInOutDetailReject").val("-");

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
                        if (response) {
                            $("#rejectInOutDetailGood").val(response.totalGood);
                            $("#rejectInOutDetailReject").val(response.totalReject);
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
        function rejectInOutExport(elm) {
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
                    link.download = "Reject In Out "+$("#dateFrom").val()+" - "+$("#dateTo").val()+".xlsx";
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
