@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Enterprise Approval Workflow Engine</h2>
                <p class="text-muted mb-0">Configure dynamic multi-stage approval chains, custom role mappings, assignment rules, and monitor real-time bottlenecks.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkflowModal">
                    <i class="bi bi-plus-circle me-1"></i> New Workflow
                </button>
                @if($activeWorkflow)
                <form action="{{ route('superadmin.workflows.seedExample') }}" method="POST" onsubmit="return confirm('Reset and seed default 6-stage institutional workflow?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-magic me-1"></i> Seed Standard Flow
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-fill bg-white shadow-sm rounded-top p-2 mb-4" id="workflowTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ !$activeWorkflow ? 'active' : '' }} fw-semibold" id="master-tab" data-bs-toggle="tab" data-bs-target="#master-pane" type="button" role="tab">
                <i class="bi bi-collection me-2"></i>Workflows Master <span class="badge bg-primary ms-1">{{ $workflows->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeWorkflow ? 'active' : '' }} fw-bold text-primary" id="visual-tab" data-bs-toggle="tab" data-bs-target="#visual-pane" type="button" role="tab">
                <i class="bi bi-diagram-3-fill me-2"></i>Visual Flowchart Studio @if($activeWorkflow) <span class="badge bg-primary ms-1">{{ $stages->count() }} Stages</span> @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="stages-tab" data-bs-toggle="tab" data-bs-target="#stages-pane" type="button" role="tab">
                <i class="bi bi-table me-2"></i>Table Builder @if($activeWorkflow) <span class="badge bg-info ms-1">{{ $stages->count() }} Stages</span> @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="rules-tab" data-bs-toggle="tab" data-bs-target="#rules-pane" type="button" role="tab">
                <i class="bi bi-signpost-split me-2"></i>Assignment Rules <span class="badge bg-secondary ms-1">{{ $rules->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics-pane" type="button" role="tab">
                <i class="bi bi-speedometer2 me-2"></i>Bottleneck Dashboard
            </button>
        </li>
    </ul>

    <div class="tab-content" id="workflowTabContent">
        <!-- TAB 1: WORKFLOWS MASTER -->
        <div class="tab-pane fade {{ !$activeWorkflow ? 'show active' : '' }}" id="master-pane" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Configured Institutional Workflows</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Workflow Name</th>
                                    <th>Description</th>
                                    <th>Stages</th>
                                    <th>Status</th>
                                    <th>Default</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workflows as $wf)
                                <tr class="{{ $activeWorkflow && $activeWorkflow->id == $wf->id ? 'table-primary bg-opacity-10' : '' }}">
                                    <td>
                                        <a href="{{ route('superadmin.workflows', ['workflow_id' => $wf->id]) }}" class="fw-bold text-decoration-none d-flex align-items-center">
                                            <i class="bi bi-diagram-2 me-2 text-primary"></i> {{ $wf->name }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">{{ $wf->description ?: 'No description provided.' }}</td>
                                    <td><span class="badge bg-dark">{{ $wf->stages_count }} Stages</span></td>
                                    <td>
                                        @if($wf->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($wf->is_default)
                                            <span class="badge bg-primary"><i class="bi bi-star-fill me-1"></i>Default System Flow</span>
                                        @else
                                            <form action="{{ route('superadmin.workflows.master.default', $wf->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary py-0 small">Make Default</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('superadmin.workflows', ['workflow_id' => $wf->id]) }}" class="btn btn-outline-secondary" title="Configure Stages">
                                                <i class="bi bi-gear"></i> Configure
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#duplicateModal{{ $wf->id }}" title="Duplicate">
                                                <i class="bi bi-copy"></i>
                                            </button>
                                            @if(!$wf->is_default)
                                            <form action="{{ route('superadmin.workflows.master.delete', $wf->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this workflow?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                            @endif
                                        </div>

                                        <!-- Duplicate Modal -->
                                        <div class="modal fade text-start" id="duplicateModal{{ $wf->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form action="{{ route('superadmin.workflows.master.duplicate', $wf->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Duplicate Workflow: {{ $wf->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">New Workflow Name</label>
                                                            <input type="text" name="new_name" class="form-control" value="{{ $wf->name }} (Copy)" required>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Create Duplicate</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No workflows configured yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: VISUAL FLOWCHART STUDIO (DRAG & DROP DESIGNER) -->
        <div class="tab-pane fade {{ $activeWorkflow ? 'show active' : '' }}" id="visual-pane" role="tabpanel">
            @if(!$activeWorkflow)
            <div class="alert alert-info shadow-sm border-0 d-flex align-items-center p-4 rounded-3 bg-white">
                <i class="bi bi-diagram-3-fill fs-1 text-primary me-4"></i>
                <div>
                    <h5 class="fw-bold mb-1">Welcome to the Drag & Drop Visual Flowchart Studio</h5>
                    <p class="mb-2 text-muted">Please select an existing institutional workflow from the Workflows Master tab or pick one below to launch the interactive visual tree builder.</p>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($workflows as $w)
                            <a href="{{ route('superadmin.workflows', ['workflow_id' => $w->id]) }}" class="btn btn-outline-primary btn-sm fw-semibold d-flex align-items-center">
                                <i class="bi bi-diagram-2 me-1"></i> {{ $w->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <!-- Studio Toolbar -->
            <div class="card border-0 shadow-sm mb-4 bg-dark text-white rounded-3">
                <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-diagram-3-fill fs-4 text-white"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="mb-0 fw-bold text-white">{{ $activeWorkflow->name }}</h5>
                                <span class="badge bg-success small"><i class="bi bi-check-circle me-1"></i>Active Chain</span>
                                <span class="badge bg-info-subtle text-info small" id="stageCountBadge">{{ $stages->count() }} Stages</span>
                            </div>
                            <small class="text-white-50">Drag palette items or reorder nodes on canvas • Instant live simulation preview before publishing</small>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="btn btn-outline-light btn-sm fw-semibold d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#livePreviewModal">
                            <i class="bi bi-play-circle-fill me-1 text-info"></i> Live Flow Simulation
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm fw-semibold d-flex align-items-center" onclick="openCustomNodeModal()">
                            <i class="bi bi-plus-lg me-1"></i> Add Custom Stage
                        </button>
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3 d-flex align-items-center shadow" id="publishWorkflowBtn" onclick="publishVisualWorkflow()">
                            <i class="bi bi-cloud-check-fill me-1"></i> Publish Workflow Changes
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left Sidebar: Drag & Drop Component Palette -->
                <div class="col-lg-3 col-md-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 85px; z-index: 1020;">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i>Stage Palette</h6>
                            <span class="badge bg-light text-muted border small">Drag to Canvas</span>
                        </div>
                        <div class="card-body p-3" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                            <p class="small text-muted mb-3">Grab any building block below and drop it right onto the flowchart drop zones to expand your approval hierarchy:</p>
                            
                            <div class="d-flex flex-column gap-2" id="paletteNodesList">
                                <!-- 1. Principal of College -->
                                <div class="palette-node p-3 rounded border bg-white shadow-sm cursor-grab position-relative transition-all hover-shadow" draggable="true" data-role="principal" data-name="Principal of College" data-desc="Initial review & endorsement by College Principal" data-actions='["approve","discuss","reject","forward"]' data-condition="any_one">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-primary-subtle text-primary fw-bold"><i class="bi bi-person-badge me-1"></i>Principal</span>
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </div>
                                    <div class="fw-bold small">Principal of College</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Initial executive review at college level</div>
                                </div>

                                <!-- 2. Approver (VC / Pro-VC / Registrar) -->
                                <div class="palette-node p-3 rounded border bg-white shadow-sm cursor-grab position-relative transition-all hover-shadow" draggable="true" data-role="vc,provc,registrar" data-name="Approver (VC / Pro-VC / Registrar)" data-desc="Executive administrative review & sign-off by leadership" data-actions='["approve","discuss","reject"]' data-condition="any_one" data-parallel="true">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-danger-subtle text-danger fw-bold"><i class="bi bi-star-fill me-1"></i>Leadership</span>
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </div>
                                    <div class="fw-bold small">Approver (VC / Pro-VC / Registrar)</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Central university leadership verification</div>
                                </div>

                                <!-- 3. Media Desk Only For Edit -->
                                <div class="palette-node p-3 rounded border bg-white shadow-sm cursor-grab position-relative transition-all hover-shadow" draggable="true" data-role="editor" data-name="Media Desk Only For Edit" data-desc="Media studio inspection, curation, and photo/video editing" data-actions='["approve","discuss"]' data-condition="any_one">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-info-subtle text-info fw-bold"><i class="bi bi-camera-video me-1"></i>Media Edit</span>
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </div>
                                    <div class="fw-bold small">Media Desk Only For Edit</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Curate & edit attached media assets</div>
                                </div>

                                <!-- 4. Approval to PA to Registrar -->
                                <div class="palette-node p-3 rounded border bg-white shadow-sm cursor-grab position-relative transition-all hover-shadow" draggable="true" data-role="pa_registrar" data-name="Approval to PA to Registrar" data-desc="Specialized administrative review by PA to Registrar" data-actions='["approve","discuss","reject","forward"]' data-condition="any_one">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-warning-subtle text-dark fw-bold"><i class="bi bi-file-earmark-check me-1"></i>PA to Registrar</span>
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </div>
                                    <div class="fw-bold small">Approval to PA to Registrar</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Specialized administrative verification</div>
                                </div>

                                <!-- 5. Then Media Can Publish -->
                                <div class="palette-node p-3 rounded border bg-white shadow-sm cursor-grab position-relative transition-all hover-shadow" draggable="true" data-role="editor" data-name="Then Media Can Publish" data-desc="Final stage: Media studio publishes activity to portal & repository" data-actions='["approve","discuss"]' data-condition="any_one">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-success-subtle text-success fw-bold"><i class="bi bi-broadcast me-1"></i>Media Publish</span>
                                        <i class="bi bi-grip-vertical text-muted"></i>
                                    </div>
                                    <div class="fw-bold small">Then Media Can Publish</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Final sign-off & public broadcast</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Canvas: Flowchart Tree Designer -->
                <div class="col-lg-9 col-md-8">
                    <div class="card border-0 shadow-sm p-4 bg-light position-relative" id="visualFlowCanvas" style="min-height: 650px; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px; border-radius: 16px;">
                        <!-- Canvas Header Info -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 bg-white p-3 rounded-3 shadow-sm border gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                                <span class="fw-bold text-dark">Visual Flowchart Canvas</span>
                                <span class="badge bg-light text-muted border">Drag nodes or cards to reorder</span>
                            </div>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="small text-muted d-flex align-items-center"><span class="d-inline-block rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span> Mandatory Stage</span>
                                <span class="small text-muted d-flex align-items-center"><span class="d-inline-block rounded-circle bg-secondary me-1" style="width: 8px; height: 8px;"></span> Optional Stage</span>
                                <span class="small text-muted d-flex align-items-center"><span class="d-inline-block rounded-circle bg-info me-1" style="width: 8px; height: 8px;"></span> Parallel Branch</span>
                            </div>
                        </div>

                        <!-- FLOWCHART START NODE -->
                        <div class="d-flex flex-column align-items-center mb-1">
                            <div class="bg-success text-white fw-bold px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2 border border-2 border-white">
                                <i class="bi bi-flag-fill"></i> START: Activity Submission Created
                            </div>
                            <small class="text-muted mt-1" style="font-size: 0.75rem;">Initiated by Constituent College / Department Faculty</small>
                        </div>

                        <!-- Drop Zone #0 (Top) -->
                        <div class="node-drop-zone my-2 py-2 text-center rounded border border-2 border-dashed border-primary border-opacity-25 bg-primary bg-opacity-10 transition-all cursor-pointer w-100 mx-auto" style="max-width: 720px;" data-drop-index="0" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                            <small class="text-primary fw-semibold"><i class="bi bi-plus-circle me-1"></i> Drop here to insert stage at the beginning</small>
                        </div>

                        <!-- ACTIVE WORKFLOW STAGES CONTAINER -->
                        <div id="flowNodesContainer" class="d-flex flex-column align-items-center w-100">
                            @foreach($stages as $index => $s)
                            <div class="workflow-node-wrapper w-100 d-flex flex-column align-items-center" data-stage-id="{{ $s->id }}" data-order="{{ $index + 1 }}" data-role="{{ $s->role_slug }}" data-name="{{ $s->name }}" data-desc="{{ $s->description }}" data-actions='{{ json_encode($s->allowed_actions_list) }}' data-condition="{{ $s->approval_condition }}" data-mandatory="{{ $s->is_mandatory ? 'true' : 'false' }}" data-active="{{ $s->is_active ? 'true' : 'false' }}" data-parallel="{{ $s->allow_parallel ? 'true' : 'false' }}">
                                
                                <!-- THE NODE CARD -->
                                <div class="workflow-node-card card border-0 shadow-sm w-100 transition-all cursor-grab {{ $s->is_mandatory ? 'border-start border-4 border-success' : ($s->allow_parallel ? 'border-start border-4 border-info' : 'border-start border-4 border-secondary') }}" draggable="true" ondragstart="handleNodeDragStart(event)" ondragend="handleNodeDragEnd(event)" style="max-width: 720px; border-radius: 12px;">
                                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-dark rounded-pill stage-sequence-badge">Stage #{{ $index + 1 }}</span>
                                            <span class="badge {{ $s->is_mandatory ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-dark' }} mandatory-badge">
                                                {{ $s->is_mandatory ? 'Mandatory Approval' : 'Optional Stage' }}
                                            </span>
                                            @if($s->allow_parallel)
                                            <span class="badge bg-info-subtle text-info parallel-badge"><i class="bi bi-people-fill me-1"></i>Parallel Branch</span>
                                            @else
                                            <span class="badge bg-info-subtle text-info parallel-badge d-none"><i class="bi bi-people-fill me-1"></i>Parallel Branch</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="node-drag-handle text-muted cursor-grab px-2 py-1 bg-light rounded small" title="Drag to reorder sequence">
                                                <i class="bi bi-grip-horizontal"></i> Drag
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border-0 py-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="openCanvasNodeEdit(this)"><i class="bi bi-pencil me-2 text-primary"></i>Edit Stage Properties</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleCanvasNodeMandatory(this)"><i class="bi bi-toggles me-2 text-warning"></i>Toggle Mandatory/Optional</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleCanvasNodeParallel(this)"><i class="bi bi-diagram-2 me-2 text-info"></i>Toggle Parallel Branch</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="removeCanvasNode(this)"><i class="bi bi-trash me-2"></i>Delete Node from Flow</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-7">
                                                <h6 class="fw-bold mb-1 node-title text-dark">{{ $s->name }}</h6>
                                                <p class="small text-muted mb-2 node-desc">{{ $s->description ?: 'No specific guidance provided.' }}</p>
                                                <div class="d-flex flex-wrap gap-1 node-roles-list">
                                                    <span class="small text-muted me-1">Required Approver(s):</span>
                                                    @foreach($s->approver_roles as $rSlug)
                                                        <span class="badge bg-primary px-2 py-1">{{ strtoupper(trim($rSlug)) }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-md-5 border-start-md ps-md-3">
                                                <div class="small fw-semibold text-dark mb-1">Allowed Action Buttons:</div>
                                                <div class="d-flex flex-wrap gap-1 mb-2 node-actions-list">
                                                    @foreach($s->allowed_actions_list as $act)
                                                        <span class="badge border bg-light text-dark">{{ ucfirst($act) }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                                                    <span class="small text-muted">Sign-off Rule:</span>
                                                    <span class="badge {{ $s->approval_condition === 'all_must' ? 'bg-danger' : 'bg-success' }} small node-condition-badge">
                                                        {{ $s->approval_condition === 'all_must' ? 'All Must Approve' : 'Any One Approves' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Quick Switch Footer inside Node -->
                                    <div class="card-footer bg-light bg-opacity-50 py-1 px-3 d-flex justify-content-between align-items-center small flex-wrap gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input node-switch-mandatory cursor-pointer" type="checkbox" role="switch" {{ $s->is_mandatory ? 'checked' : '' }} onchange="updateNodeCardState(this)">
                                            <label class="form-check-label text-muted">Mandatory</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input node-switch-parallel cursor-pointer" type="checkbox" role="switch" {{ $s->allow_parallel ? 'checked' : '' }} onchange="updateNodeCardState(this)">
                                            <label class="form-check-label text-muted">Parallel Branch</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input node-switch-active cursor-pointer" type="checkbox" role="switch" {{ $s->is_active ? 'checked' : '' }} onchange="updateNodeCardState(this)">
                                            <label class="form-check-label text-muted">Stage Enabled</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Flow Arrow & Drop Zone after Node -->
                                <div class="flow-connector my-1 d-flex flex-column align-items-center">
                                    <i class="bi bi-arrow-down text-primary fs-4"></i>
                                </div>
                                <div class="node-drop-zone my-1 py-2 text-center rounded border border-2 border-dashed border-primary border-opacity-25 bg-primary bg-opacity-10 transition-all cursor-pointer w-100 mx-auto" style="max-width: 720px;" data-drop-index="{{ $index + 1 }}" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                                    <small class="text-primary fw-semibold"><i class="bi bi-plus-circle me-1"></i> Drop here to insert stage below</small>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- FLOWCHART END NODE -->
                        <div class="d-flex flex-column align-items-center mt-3">
                            <div class="bg-dark text-white fw-bold px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2 border border-2 border-white">
                                <i class="bi bi-check2-all text-success"></i> END: Activity Finalized & Verified
                            </div>
                            <small class="text-muted mt-1" style="font-size: 0.75rem;">Official Activity Published to Institutional Repository & Media Hub</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- TAB 3: TABLE BUILDER -->
        <div class="tab-pane fade" id="stages-pane" role="tabpanel">
            @if(!$activeWorkflow)
            <div class="alert alert-warning">Please select or create a workflow first.</div>
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Stage Builder: <span class="text-primary">{{ $activeWorkflow->name }}</span></h5>
                        <small class="text-muted">Configure sequence, approver roles, specific user exceptions, and allowed button actions.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStageModal">
                        <i class="bi bi-plus me-1"></i> Add Stage
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Order</th>
                                    <th>Stage Title & Description</th>
                                    <th>Mapped Approvers</th>
                                    <th>Allowed Actions</th>
                                    <th>Condition</th>
                                    <th class="text-end">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stages as $stage)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-primary fs-6 px-2 py-1">{{ $stage->stage_order }}</span>
                                            <div class="d-flex flex-column">
                                                @if(!$loop->first)
                                                <form action="{{ route('superadmin.workflows.reorder') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="stage_id" value="{{ $stage->id }}">
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="btn btn-link p-0 text-muted" title="Move Up"><i class="bi bi-caret-up-fill"></i></button>
                                                </form>
                                                @endif
                                                @if(!$loop->last)
                                                <form action="{{ route('superadmin.workflows.reorder') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="stage_id" value="{{ $stage->id }}">
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="btn btn-link p-0 text-muted" title="Move Down"><i class="bi bi-caret-down-fill"></i></button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $stage->name }}</div>
                                        <div class="small text-muted">{{ $stage->description }}</div>
                                        @if($stage->is_mandatory)
                                            <span class="badge bg-danger-subtle text-danger small mt-1">Mandatory Stage</span>
                                        @endif
                                        @if($stage->allow_parallel)
                                            <span class="badge bg-info-subtle text-info small mt-1">Parallel Review</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted d-block">Roles:</small>
                                            @foreach($stage->approver_roles as $rSlug)
                                                <span class="badge bg-secondary me-1">{{ strtoupper($rSlug) }}</span>
                                            @endforeach
                                        </div>
                                        @if(!empty($stage->assigned_user_ids))
                                        <div class="mt-1">
                                            <small class="text-muted d-block">Specific Users:</small>
                                            @foreach($stage->assigned_user_ids as $uid)
                                                @php $uObj = $users->firstWhere('id', $uid); @endphp
                                                @if($uObj)
                                                <span class="badge bg-warning text-dark me-1"><i class="bi bi-person"></i> {{ $uObj->name }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                        @endif
                                        @if($stage->college_id)
                                        <div class="mt-1">
                                            <span class="badge bg-dark"><i class="bi bi-building"></i> {{ $stage->college->code ?? 'Scoped' }}</span>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($stage->allowed_actions_list as $act)
                                            <span class="badge border border-secondary text-dark bg-light me-1 mb-1">{{ ucfirst($act) }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($stage->approval_condition === 'all_must')
                                            <span class="badge bg-danger">All Must Approve</span>
                                        @else
                                            <span class="badge bg-success">Any One Approves</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStageModal{{ $stage->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form action="{{ route('superadmin.workflows.delete', $stage->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete stage?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>

                                        <!-- Edit Stage Modal -->
                                        <div class="modal fade text-start" id="editStageModal{{ $stage->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <form action="{{ route('superadmin.workflows.update', $stage->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-light">
                                                            <h5 class="modal-title fw-bold">Edit Stage: {{ $stage->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Sequence Order</label>
                                                                    <input type="number" name="stage_order" class="form-control" value="{{ $stage->stage_order }}" required>
                                                                </div>
                                                                <div class="col-md-9">
                                                                    <label class="form-label">Stage Title</label>
                                                                    <input type="text" name="name" class="form-control" value="{{ $stage->name }}" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Authorized Approver Roles</label>
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        @foreach($roles as $role)
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->slug }}" id="role_e_{{ $stage->id }}_{{ $role->slug }}" {{ in_array($role->slug, $stage->approver_roles) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="role_e_{{ $stage->id }}_{{ $role->slug }}">{{ $role->name }}</label>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Assign Specific Users (Optional Override)</label>
                                                                    <select name="users[]" class="form-select" multiple size="4">
                                                                        @foreach($users as $u)
                                                                            <input type="checkbox" name="users[]" value="{{ $u->id }}" {{ in_array($u->id, $stage->assigned_user_ids) ? 'checked' : '' }} class="d-none">
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="small text-muted border p-2 rounded" style="max-height: 120px; overflow-y: auto;">
                                                                        @foreach($users as $u)
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" name="users[]" value="{{ $u->id }}" id="u_e_{{ $stage->id }}_{{ $u->id }}" {{ in_array($u->id, $stage->assigned_user_ids) ? 'checked' : '' }}>
                                                                                <label class="form-check-label small" for="u_e_{{ $stage->id }}_{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Allowed Action Buttons</label>
                                                                    <div class="border p-2 rounded">
                                                                        @php $acts = ['approve' => 'Approve Submission', 'discuss' => 'Discussion / Return', 'reject' => 'Reject Activity', 'hold' => 'Hold Submission', 'forward' => 'Forward / Escalate']; @endphp
                                                                        @foreach($acts as $actKey => $actLabel)
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" name="allowed_actions[]" value="{{ $actKey }}" id="act_e_{{ $stage->id }}_{{ $actKey }}" {{ in_array($actKey, $stage->allowed_actions_list) ? 'checked' : '' }}>
                                                                                <label class="form-check-label small" for="act_e_{{ $stage->id }}_{{ $actKey }}">{{ $actLabel }}</label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Approval Condition</label>
                                                                    <select name="approval_condition" class="form-select">
                                                                        <option value="any_one" {{ $stage->approval_condition == 'any_one' ? 'selected' : '' }}>Any One Authorized Approver</option>
                                                                        <option value="all_must" {{ $stage->approval_condition == 'all_must' ? 'selected' : '' }}>All Mapped Approvers Must Sign Off</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Scope to Specific College</label>
                                                                    <select name="college_id" class="form-select">
                                                                        <option value="">-- All Colleges --</option>
                                                                        @foreach($colleges as $c)
                                                                            <option value="{{ $c->id }}" {{ $stage->college_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Description / Guidance</label>
                                                                    <textarea name="description" class="form-control" rows="2">{{ $stage->description }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No stages defined in this workflow. Click Add Stage above.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- TAB 3: ASSIGNMENT RULES -->
        <div class="tab-pane fade" id="rules-pane" role="tabpanel">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Dynamic Assignment Rules</h5>
                        <small class="text-muted">Route submissions to specific workflows based on College or Activity Category.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
                        <i class="bi bi-plus me-1"></i> Add Rule
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Priority</th>
                                    <th>Target College</th>
                                    <th>Event Category</th>
                                    <th>Assigned Workflow</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $rule->priority }}</span></td>
                                    <td>{{ $rule->college->name ?? 'All Colleges' }}</td>
                                    <td>{{ $rule->category ?: 'All Categories' }}</td>
                                    <td><span class="fw-bold text-primary">{{ $rule->workflow->name ?? 'Unknown' }}</span></td>
                                    <td class="text-end">
                                        <form action="{{ route('superadmin.workflows.rules.delete', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this assignment rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No custom assignment rules. All submissions follow the Default System Flow.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: PERFORMANCE DASHBOARD -->
        <div class="tab-pane fade" id="analytics-pane" role="tabpanel">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-primary text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 text-uppercase fw-semibold">Pending Submissions</small>
                                <h2 class="mb-0 fw-bold">{{ $dashboardMetrics['total_pending'] }}</h2>
                            </div>
                            <i class="bi bi-hourglass-split fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-info text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 text-uppercase fw-semibold">Active Workflows</small>
                                <h2 class="mb-0 fw-bold">{{ $workflows->where('is_active', true)->count() }}</h2>
                            </div>
                            <i class="bi bi-diagram-3 fs-1 text-white-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-dark-50 text-uppercase fw-semibold">Stage Bottlenecks</small>
                                <h2 class="mb-0 fw-bold">{{ count($dashboardMetrics['bottlenecks']) }}</h2>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 text-dark-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Active Stage Queue Lengths</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stage Title</th>
                                <th>Assigned Role</th>
                                <th>Pending Queue Count</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardMetrics['stage_counts'] as $sc)
                            <tr>
                                <td class="fw-bold">{{ $sc['stage_name'] }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($sc['role']) }}</span></td>
                                <td><span class="badge {{ $sc['count'] >= 5 ? 'bg-danger' : 'bg-primary' }}">{{ $sc['count'] }}</span></td>
                                <td>
                                    @if($sc['count'] >= 5)
                                        <span class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i>High Load Bottleneck</span>
                                    @else
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>Optimal Flow</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Workflow Master Modal -->
<div class="modal fade" id="addWorkflowModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.workflows.master.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Institutional Workflow</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Workflow Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sports Fast-Track Flow" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief description of when this workflow applies"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="wf_active" checked>
                        <label class="form-check-label" for="wf_active">Activate Immediately</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Workflow</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Stage Modal -->
@if($activeWorkflow)
<div class="modal fade" id="addStageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('superadmin.workflows.store') }}" method="POST">
            @csrf
            <input type="hidden" name="workflow_id" value="{{ $activeWorkflow->id }}">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Add New Stage to: {{ $activeWorkflow->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Sequence Order</label>
                            <input type="number" name="stage_order" class="form-control" value="{{ $stages->count() + 1 }}" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Stage Title</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Dean Review" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Authorized Approver Roles</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->slug }}" id="role_a_{{ $role->slug }}">
                                    <label class="form-check-label" for="role_a_{{ $role->slug }}">{{ $role->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Specific Users (Optional Override)</label>
                            <div class="small text-muted border p-2 rounded" style="max-height: 120px; overflow-y: auto;">
                                @foreach($users as $u)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="users[]" value="{{ $u->id }}" id="u_a_{{ $u->id }}">
                                        <label class="form-check-label small" for="u_a_{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Allowed Action Buttons</label>
                            <div class="border p-2 rounded">
                                @php $acts = ['approve' => 'Approve Submission', 'discuss' => 'Discussion / Return', 'reject' => 'Reject Activity', 'hold' => 'Hold Submission', 'forward' => 'Forward / Escalate']; @endphp
                                @foreach($acts as $actKey => $actLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allowed_actions[]" value="{{ $actKey }}" id="act_a_{{ $actKey }}" checked>
                                        <label class="form-check-label small" for="act_a_{{ $actKey }}">{{ $actLabel }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Approval Condition</label>
                            <select name="approval_condition" class="form-select">
                                <option value="any_one">Any One Authorized Approver</option>
                                <option value="all_must">All Mapped Approvers Must Sign Off</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scope to Specific College</label>
                            <select name="college_id" class="form-select">
                                <option value="">-- All Colleges --</option>
                                @foreach($colleges as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Guidance</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief guidance for approvers at this stage"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Stage</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Add Assignment Rule Modal -->
@if($activeWorkflow)
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('superadmin.workflows.rules.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Workflow Routing Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To Workflow</label>
                        <select name="workflow_id" class="form-select" required>
                            @foreach($workflows as $wf)
                                <option value="{{ $wf->id }}">{{ $wf->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matching College Criteria</label>
                        <select name="college_id" class="form-select">
                            <option value="">-- Any College --</option>
                            @foreach($colleges as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matching Event Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Sports Activity (leave blank for Any)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rule Priority (Higher number wins)</label>
                        <input type="number" name="priority" class="form-control" value="10" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Routing Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<!-- VISUAL FLOWCHART STUDIO MODALS -->
@if($activeWorkflow)
<!-- 1. Live Flow Simulation Modal -->
<div class="modal fade" id="livePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-25 p-2 rounded-circle text-info">
                        <i class="bi bi-play-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Live Simulation Preview: {{ $activeWorkflow->name }}</h5>
                        <small class="text-white-50">Watch how a mock activity submission travels through your visual workflow chain</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="card border bg-light mb-4 p-3 rounded-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="badge bg-primary me-2">Test Scenario</span>
                            <span class="fw-bold">Faculty International Conference Submission (Medical College)</span>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm" id="runSimulationBtn" onclick="runFlowSimulation()">
                            <i class="bi bi-caret-right-fill me-1"></i> Start Simulation Walkthrough
                        </button>
                    </div>
                </div>

                <!-- Simulation Timeline -->
                <div class="simulation-timeline position-relative ps-4 py-2" id="simulationTimelineContainer">
                    <div class="position-absolute bg-primary bg-opacity-25 top-0 bottom-0 start-0 ms-2" style="width: 3px; border-radius: 4px;"></div>
                    
                    <div class="simulation-step mb-4 position-relative">
                        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow" style="width: 24px; height: 24px; margin-left: -14px;">
                            <i class="bi bi-check small"></i>
                        </div>
                        <div class="card border-0 shadow-sm p-3 border-start border-4 border-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-dark">0. Submission Created by Faculty</h6>
                                <span class="badge bg-success small">Instant</span>
                            </div>
                            <small class="text-muted">Status set to: <code>Pending Institutional Review</code></small>
                        </div>
                    </div>

                    <div id="simulationStagesList">
                        <!-- Populated dynamically by runFlowSimulation() based on canvas stages -->
                    </div>

                    <div class="simulation-step position-relative" id="simulationEndStep" style="opacity: 0.3;">
                        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-dark text-white d-flex align-items-center justify-content-center shadow" style="width: 24px; height: 24px; margin-left: -14px;">
                            <i class="bi bi-flag-fill small"></i>
                        </div>
                        <div class="card border-0 shadow-sm p-3 bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-white">Finalized: Activity Officially Published</h6>
                                <span class="badge bg-success small">Verified</span>
                            </div>
                            <small class="text-white-50">Certificate unlocked & metrics indexed in institutional scorecards.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close Simulation</button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="runFlowSimulation()"><i class="bi bi-arrow-repeat me-1"></i> Re-run Walkthrough</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Edit Node Properties Modal -->
<div class="modal fade" id="visualNodeEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white p-3">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-sliders me-2"></i>Configure Stage Node Properties</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editNodeStageIndex">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Stage Title</label>
                    <input type="text" class="form-control" id="editNodeTitleInput" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Guidance & Description</label>
                    <textarea class="form-control" id="editNodeDescInput" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Assigned Approver Roles</label>
                    <div class="d-flex flex-wrap gap-2 border p-2 rounded bg-light">
                        @foreach($roles as $r)
                        <div class="form-check">
                            <input class="form-check-input edit-role-chk" type="checkbox" value="{{ $r->slug }}" id="e_role_{{ $r->slug }}">
                            <label class="form-check-label small" for="e_role_{{ $r->slug }}">{{ $r->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Allowed Action Buttons</label>
                    <div class="d-flex flex-wrap gap-2 border p-2 rounded bg-light">
                        @php $acts = ['approve' => 'Approve', 'discuss' => 'Discussion/Return', 'reject' => 'Reject', 'forward' => 'Forward']; @endphp
                        @foreach($acts as $key => $lbl)
                        <div class="form-check">
                            <input class="form-check-input edit-action-chk" type="checkbox" value="{{ $key }}" id="e_act_{{ $key }}">
                            <label class="form-check-label small" for="e_act_{{ $key }}">{{ $lbl }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold small">Approval Condition</label>
                    <select class="form-select" id="editNodeConditionSelect">
                        <option value="any_one">Any One Authorized Approver Can Sign Off</option>
                        <option value="all_must">All Mapped Approvers Must Sign Off (Consensus)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="saveNodeEditChanges()">Save Node Configuration</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Add Custom Node Modal -->
<div class="modal fade" id="customNodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white p-3">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Create Custom Stage Node</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Stage Title</label>
                    <input type="text" class="form-control" id="customStageTitle" placeholder="e.g. Research Committee Review" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Primary Assigned Role</label>
                    <select class="form-select" id="customStageRole">
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}">{{ $r->name }} ({{ strtoupper($r->slug) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Description</label>
                    <textarea class="form-control" id="customStageDesc" rows="2" placeholder="Custom review stage description"></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="customStageMandatory" checked>
                            <label class="form-check-label small" for="customStageMandatory">Mandatory Approval</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="customStageParallel">
                            <label class="form-check-label small" for="customStageParallel">Parallel Multi-Role</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3" onclick="insertCustomStageToCanvas()">Insert onto Flowchart</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
<style>
/* Flowchart Studio Custom Aesthetics */
.cursor-grab { cursor: grab; }
.cursor-grab:active { cursor: grabbing; }
.hover-shadow:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    transform: translateY(-2px);
    border-color: #3b82f6 !important;
}
.node-drop-zone.drop-zone-active {
    background-color: rgba(59, 130, 246, 0.25) !important;
    border-color: #2563eb !important;
    border-style: solid !important;
    transform: scale(1.02);
}
.workflow-node-card.dragging {
    opacity: 0.45;
    border: 2px dashed #2563eb !important;
}
.pulse-warning {
    animation: pulse-border 1.5s infinite;
}
@keyframes pulse-border {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
</style>
@endpush

@push('scripts')
<script>
let draggedPaletteItem = null;
let draggedCanvasNode = null;
let currentEditingNodeWrapper = null;
let hasUnsavedVisualChanges = false;

// 1. PALETTE DRAG ENGINE
document.addEventListener("DOMContentLoaded", function() {
    const paletteNodes = document.querySelectorAll(".palette-node");
    paletteNodes.forEach(node => {
        node.addEventListener("dragstart", function(e) {
            draggedPaletteItem = {
                role: this.dataset.role,
                name: this.dataset.name,
                desc: this.dataset.desc,
                actions: JSON.parse(this.dataset.actions || '["approve","discuss","reject"]'),
                condition: this.dataset.condition || 'any_one',
                mandatory: this.dataset.mandatory !== 'false',
                parallel: this.dataset.parallel === 'true'
            };
            e.dataTransfer.effectAllowed = "copy";
        });
        node.addEventListener("dragend", function() {
            draggedPaletteItem = null;
        });
    });
});

// 2. CANVAS NODE DRAG ENGINE (REORDERING)
function handleNodeDragStart(e) {
    draggedCanvasNode = e.target.closest(".workflow-node-wrapper");
    e.target.classList.add("dragging");
    e.dataTransfer.effectAllowed = "move";
}

function handleNodeDragEnd(e) {
    e.target.classList.remove("dragging");
    draggedCanvasNode = null;
}

// 3. DROP ZONE LOGIC
function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = draggedPaletteItem ? "copy" : "move";
    const dropZone = e.currentTarget;
    dropZone.classList.add("drop-zone-active");
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove("drop-zone-active");
}

function handleDrop(e) {
    e.preventDefault();
    const dropZone = e.currentTarget;
    dropZone.classList.remove("drop-zone-active");
    const dropIndex = parseInt(dropZone.dataset.dropIndex || "0");
    const container = document.getElementById("flowNodesContainer");
    if (!container) return;

    // A: DROPPING FROM PALETTE (NEW NODE)
    if (draggedPaletteItem) {
        const newNodeHtml = createNodeWrapperHtml({
            id: null,
            role: draggedPaletteItem.role,
            name: draggedPaletteItem.name,
            desc: draggedPaletteItem.desc,
            actions: draggedPaletteItem.actions,
            condition: draggedPaletteItem.condition,
            mandatory: draggedPaletteItem.mandatory,
            active: true,
            parallel: draggedPaletteItem.parallel
        });

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = newNodeHtml.trim();
        const newNodeWrapper = tempDiv.firstChild;

        if (dropIndex === 0) {
            container.prepend(newNodeWrapper);
        } else {
            const referenceZone = container.children[dropIndex - 1];
            if (referenceZone) {
                referenceZone.after(newNodeWrapper);
            } else {
                container.appendChild(newNodeWrapper);
            }
        }
        markUnsavedVisualChanges();
        reindexFlowchartSequence();
        return;
    }

    // B: REORDERING EXISTING CANVAS NODE
    if (draggedCanvasNode) {
        const wrappers = Array.from(container.children);
        const currentIndex = wrappers.indexOf(draggedCanvasNode);
        if (currentIndex === -1) return;

        if (dropIndex === 0) {
            container.prepend(draggedCanvasNode);
        } else {
            const targetRef = wrappers[dropIndex - 1];
            if (targetRef && targetRef !== draggedCanvasNode) {
                targetRef.after(draggedCanvasNode);
            }
        }
        markUnsavedVisualChanges();
        reindexFlowchartSequence();
    }
}

function createNodeWrapperHtml(data) {
    const actionsHtml = (data.actions || []).map(act => `<span class="badge border bg-light text-dark">${act.charAt(0).toUpperCase() + act.slice(1)}</span>`).join(' ');
    const rolesHtml = (data.role || 'editor').split(',').map(r => `<span class="badge bg-primary px-2 py-1">${r.trim().toUpperCase()}</span>`).join(' ');
    const borderClass = data.mandatory ? 'border-success' : (data.parallel ? 'border-info' : 'border-secondary');
    const mandatoryBadgeClass = data.mandatory ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-dark';
    const mandatoryLabel = data.mandatory ? 'Mandatory Approval' : 'Optional Stage';
    const parallelClass = data.parallel ? '' : 'd-none';

    return `
    <div class="workflow-node-wrapper w-100 d-flex flex-column align-items-center" data-stage-id="${data.id || ''}" data-role="${data.role}" data-name="${data.name}" data-desc="${data.desc}" data-actions='${JSON.stringify(data.actions || [])}' data-condition="${data.condition}" data-mandatory="${data.mandatory ? 'true' : 'false'}" data-active="${data.active ? 'true' : 'false'}" data-parallel="${data.parallel ? 'true' : 'false'}">
        <div class="workflow-node-card card border-0 shadow-sm w-100 transition-all cursor-grab border-start border-4 ${borderClass}" draggable="true" ondragstart="handleNodeDragStart(event)" ondragend="handleNodeDragEnd(event)" style="max-width: 720px; border-radius: 12px;">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-dark rounded-pill stage-sequence-badge">Stage #?</span>
                    <span class="badge ${mandatoryBadgeClass} mandatory-badge">${mandatoryLabel}</span>
                    <span class="badge bg-info-subtle text-info parallel-badge ${parallelClass}"><i class="bi bi-people-fill me-1"></i>Parallel Branch</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="node-drag-handle text-muted cursor-grab px-2 py-1 bg-light rounded small" title="Drag to reorder sequence">
                        <i class="bi bi-grip-horizontal"></i> Drag
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border-0 py-0" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="openCanvasNodeEdit(this)"><i class="bi bi-pencil me-2 text-primary"></i>Edit Stage Properties</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleCanvasNodeMandatory(this)"><i class="bi bi-toggles me-2 text-warning"></i>Toggle Mandatory/Optional</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleCanvasNodeParallel(this)"><i class="bi bi-diagram-2 me-2 text-info"></i>Toggle Parallel Branch</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="removeCanvasNode(this)"><i class="bi bi-trash me-2"></i>Delete Node from Flow</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row align-items-center g-2">
                    <div class="col-md-7">
                        <h6 class="fw-bold mb-1 node-title text-dark">${data.name}</h6>
                        <p class="small text-muted mb-2 node-desc">${data.desc || 'No specific guidance provided.'}</p>
                        <div class="d-flex flex-wrap gap-1 node-roles-list">
                            <span class="small text-muted me-1">Required Approver(s):</span>
                            ${rolesHtml}
                        </div>
                    </div>
                    <div class="col-md-5 border-start-md ps-md-3">
                        <div class="small fw-semibold text-dark mb-1">Allowed Action Buttons:</div>
                        <div class="d-flex flex-wrap gap-1 mb-2 node-actions-list">
                            ${actionsHtml}
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="small text-muted">Sign-off Rule:</span>
                            <span class="badge ${data.condition === 'all_must' ? 'bg-danger' : 'bg-success'} small node-condition-badge">
                                ${data.condition === 'all_must' ? 'All Must Approve' : 'Any One Approves'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light bg-opacity-50 py-1 px-3 d-flex justify-content-between align-items-center small flex-wrap gap-2">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input node-switch-mandatory cursor-pointer" type="checkbox" role="switch" ${data.mandatory ? 'checked' : ''} onchange="updateNodeCardState(this)">
                    <label class="form-check-label text-muted">Mandatory</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input node-switch-parallel cursor-pointer" type="checkbox" role="switch" ${data.parallel ? 'checked' : ''} onchange="updateNodeCardState(this)">
                    <label class="form-check-label text-muted">Parallel Branch</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input node-switch-active cursor-pointer" type="checkbox" role="switch" ${data.active ? 'checked' : ''} onchange="updateNodeCardState(this)">
                    <label class="form-check-label text-muted">Stage Enabled</label>
                </div>
            </div>
        </div>
        <div class="flow-connector my-1 d-flex flex-column align-items-center">
            <i class="bi bi-arrow-down text-primary fs-4"></i>
        </div>
        <div class="node-drop-zone my-1 py-2 text-center rounded border border-2 border-dashed border-primary border-opacity-25 bg-primary bg-opacity-10 transition-all cursor-pointer w-100 mx-auto" style="max-width: 720px;" data-drop-index="?" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <small class="text-primary fw-semibold"><i class="bi bi-plus-circle me-1"></i> Drop here to insert stage below</small>
        </div>
    </div>`;
}

// 4. REINDEX AND SYNCHRONIZE SEQUENCE
function reindexFlowchartSequence() {
    const container = document.getElementById("flowNodesContainer");
    if (!container) return;
    const wrappers = Array.from(container.children);
    
    wrappers.forEach((wrap, idx) => {
        const orderNum = idx + 1;
        wrap.dataset.order = orderNum;
        const seqBadge = wrap.querySelector(".stage-sequence-badge");
        if (seqBadge) seqBadge.textContent = "Stage #" + orderNum;
        
        const dropZone = wrap.querySelector(".node-drop-zone");
        if (dropZone) dropZone.dataset.dropIndex = orderNum;
    });

    const badge = document.getElementById("stageCountBadge");
    if (badge) badge.textContent = wrappers.length + " Stages";
}

function markUnsavedVisualChanges() {
    hasUnsavedVisualChanges = true;
    const btn = document.getElementById("publishWorkflowBtn");
    if (btn) {
        btn.classList.add("pulse-warning", "btn-warning", "text-dark");
        btn.classList.remove("btn-success");
        btn.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Publish Changes (${document.querySelectorAll('.workflow-node-wrapper').length} Stages)`;
    }
}

// 5. NODE TOGGLE & ACTION BUTTONS
function updateNodeCardState(checkbox) {
    const wrap = checkbox.closest(".workflow-node-wrapper");
    if (!wrap) return;
    const card = wrap.querySelector(".workflow-node-card");
    const mandatoryChk = wrap.querySelector(".node-switch-mandatory");
    const parallelChk = wrap.querySelector(".node-switch-parallel");
    const activeChk = wrap.querySelector(".node-switch-active");

    const isMandatory = mandatoryChk ? mandatoryChk.checked : true;
    const isParallel = parallelChk ? parallelChk.checked : false;
    const isActive = activeChk ? activeChk.checked : true;

    wrap.dataset.mandatory = isMandatory ? 'true' : 'false';
    wrap.dataset.parallel = isParallel ? 'true' : 'false';
    wrap.dataset.active = isActive ? 'true' : 'false';

    if (card) {
        card.classList.remove("border-success", "border-info", "border-secondary");
        card.classList.add(isMandatory ? "border-success" : (isParallel ? "border-info" : "border-secondary"));
        if (!isActive) card.style.opacity = "0.55";
        else card.style.opacity = "1";
    }

    const mBadge = wrap.querySelector(".mandatory-badge");
    if (mBadge) {
        mBadge.className = `badge ${isMandatory ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-dark'} mandatory-badge`;
        mBadge.textContent = isMandatory ? 'Mandatory Approval' : 'Optional Stage';
    }

    const pBadge = wrap.querySelector(".parallel-badge");
    if (pBadge) {
        if (isParallel) pBadge.classList.remove("d-none");
        else pBadge.classList.add("d-none");
    }

    markUnsavedVisualChanges();
}

function toggleCanvasNodeMandatory(btn) {
    const wrap = btn.closest(".workflow-node-wrapper");
    const chk = wrap ? wrap.querySelector(".node-switch-mandatory") : null;
    if (chk) {
        chk.checked = !chk.checked;
        updateNodeCardState(chk);
    }
}

function toggleCanvasNodeParallel(btn) {
    const wrap = btn.closest(".workflow-node-wrapper");
    const chk = wrap ? wrap.querySelector(".node-switch-parallel") : null;
    if (chk) {
        chk.checked = !chk.checked;
        updateNodeCardState(chk);
    }
}

function removeCanvasNode(btn) {
    if (!confirm("Are you sure you want to remove this stage from the visual approval flow?")) return;
    const wrap = btn.closest(".workflow-node-wrapper");
    if (wrap) {
        wrap.style.transition = "opacity 0.3s, transform 0.3s";
        wrap.style.opacity = "0";
        wrap.style.transform = "scale(0.9)";
        setTimeout(() => {
            wrap.remove();
            reindexFlowchartSequence();
            markUnsavedVisualChanges();
        }, 300);
    }
}

// 6. EDIT MODAL FOR CANVAS NODES
function openCanvasNodeEdit(btn) {
    currentEditingNodeWrapper = btn.closest(".workflow-node-wrapper");
    if (!currentEditingNodeWrapper) return;

    document.getElementById("editNodeTitleInput").value = currentEditingNodeWrapper.dataset.name || "";
    document.getElementById("editNodeDescInput").value = currentEditingNodeWrapper.dataset.desc || "";
    document.getElementById("editNodeConditionSelect").value = currentEditingNodeWrapper.dataset.condition || "any_one";

    const roles = (currentEditingNodeWrapper.dataset.role || "").split(",").map(r => r.trim());
    document.querySelectorAll(".edit-role-chk").forEach(chk => {
        chk.checked = roles.includes(chk.value);
    });

    const actions = JSON.parse(currentEditingNodeWrapper.dataset.actions || '["approve","discuss","reject"]');
    document.querySelectorAll(".edit-action-chk").forEach(chk => {
        chk.checked = actions.includes(chk.value);
    });

    const modal = new bootstrap.Modal(document.getElementById("visualNodeEditModal"));
    modal.show();
}

function saveNodeEditChanges() {
    if (!currentEditingNodeWrapper) return;
    const title = document.getElementById("editNodeTitleInput").value.trim();
    const desc = document.getElementById("editNodeDescInput").value.trim();
    const condition = document.getElementById("editNodeConditionSelect").value;

    const checkedRoles = Array.from(document.querySelectorAll(".edit-role-chk:checked")).map(c => c.value);
    const checkedActions = Array.from(document.querySelectorAll(".edit-action-chk:checked")).map(c => c.value);

    currentEditingNodeWrapper.dataset.name = title;
    currentEditingNodeWrapper.dataset.desc = desc;
    currentEditingNodeWrapper.dataset.condition = condition;
    currentEditingNodeWrapper.dataset.role = checkedRoles.join(",") || "editor";
    currentEditingNodeWrapper.dataset.actions = JSON.stringify(checkedActions);

    const titleEl = currentEditingNodeWrapper.querySelector(".node-title");
    const descEl = currentEditingNodeWrapper.querySelector(".node-desc");
    const condBadge = currentEditingNodeWrapper.querySelector(".node-condition-badge");
    const rolesList = currentEditingNodeWrapper.querySelector(".node-roles-list");
    const actionsList = currentEditingNodeWrapper.querySelector(".node-actions-list");

    if (titleEl) titleEl.textContent = title;
    if (descEl) descEl.textContent = desc;
    if (condBadge) {
        condBadge.className = `badge ${condition === 'all_must' ? 'bg-danger' : 'bg-success'} small node-condition-badge`;
        condBadge.textContent = condition === 'all_must' ? 'All Must Approve' : 'Any One Approves';
    }
    if (rolesList) {
        rolesList.innerHTML = `<span class="small text-muted me-1">Required Approver(s):</span> ` + 
            checkedRoles.map(r => `<span class="badge bg-primary px-2 py-1">${r.toUpperCase()}</span>`).join(" ");
    }
    if (actionsList) {
        actionsList.innerHTML = checkedActions.map(act => `<span class="badge border bg-light text-dark">${act.charAt(0).toUpperCase() + act.slice(1)}</span>`).join(" ");
    }

    bootstrap.Modal.getInstance(document.getElementById("visualNodeEditModal")).hide();
    markUnsavedVisualChanges();
}

// 7. CUSTOM NODE MODAL
function openCustomNodeModal() {
    const m = new bootstrap.Modal(document.getElementById("customNodeModal"));
    m.show();
}

function insertCustomStageToCanvas() {
    const title = document.getElementById("customStageTitle").value.trim() || "Custom Review Stage";
    const role = document.getElementById("customStageRole").value;
    const desc = document.getElementById("customStageDesc").value.trim() || "Custom configured review stage";
    const isMandatory = document.getElementById("customStageMandatory").checked;
    const isParallel = document.getElementById("customStageParallel").checked;

    const newNodeHtml = createNodeWrapperHtml({
        id: null,
        role: role,
        name: title,
        desc: desc,
        actions: ['approve', 'discuss', 'reject', 'forward'],
        condition: isParallel ? 'all_must' : 'any_one',
        mandatory: isMandatory,
        active: true,
        parallel: isParallel
    });

    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = newNodeHtml.trim();
    const container = document.getElementById("flowNodesContainer");
    if (container) container.appendChild(tempDiv.firstChild);

    bootstrap.Modal.getInstance(document.getElementById("customNodeModal")).hide();
    markUnsavedVisualChanges();
    reindexFlowchartSequence();
}

// 8. LIVE FLOW SIMULATION
function runFlowSimulation() {
    const container = document.getElementById("flowNodesContainer");
    if (!container) return;
    const wrappers = Array.from(container.children);
    const listEl = document.getElementById("simulationStagesList");
    if (!listEl) return;

    listEl.innerHTML = "";
    wrappers.forEach((w, idx) => {
        const orderNum = idx + 1;
        const name = w.dataset.name;
        const role = w.dataset.role;
        const condition = w.dataset.condition;
        const isParallel = w.dataset.parallel === 'true';
        const isMandatory = w.dataset.mandatory === 'true';

        const stepDiv = document.createElement("div");
        stepDiv.className = "simulation-step mb-4 position-relative";
        stepDiv.style.opacity = "0";
        stepDiv.style.transition = "opacity 0.4s ease, transform 0.4s ease";
        stepDiv.style.transform = "translateX(-15px)";

        stepDiv.innerHTML = `
            <div class="position-absolute top-0 start-0 translate-middle rounded-circle ${isMandatory ? 'bg-primary' : 'bg-secondary'} text-white d-flex align-items-center justify-content-center shadow" style="width: 24px; height: 24px; margin-left: -14px;">
                <small class="fw-bold">${orderNum}</small>
            </div>
            <div class="card border-0 shadow-sm p-3 ${isParallel ? 'border-start border-4 border-info bg-info bg-opacity-10' : 'border-start border-4 border-primary'}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0 text-dark">Stage #${orderNum}: ${name}</h6>
                    <span class="badge bg-primary small">${role.toUpperCase()}</span>
                </div>
                <div class="small text-muted d-flex justify-content-between align-items-center">
                    <span>Rule: <strong>${condition === 'all_must' ? 'All Mapped Approvers Must Sign Off' : 'Any One Authorized Role Can Approve'}</strong></span>
                    <span class="badge ${isMandatory ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-dark'}">${isMandatory ? 'Mandatory' : 'Optional Advisory'}</span>
                </div>
                ${isParallel ? `<div class="mt-2 small text-info fw-semibold"><i class="bi bi-people-fill me-1"></i>Parallel execution: Notification sent simultaneously to all approvers in role.</div>` : ''}
            </div>
        `;

        listEl.appendChild(stepDiv);

        setTimeout(() => {
            stepDiv.style.opacity = "1";
            stepDiv.style.transform = "translateX(0)";
        }, (idx + 1) * 350);
    });

    const endStep = document.getElementById("simulationEndStep");
    if (endStep) {
        endStep.style.opacity = "0.3";
        setTimeout(() => {
            endStep.style.transition = "opacity 0.5s";
            endStep.style.opacity = "1";
        }, (wrappers.length + 1) * 350);
    }
}

// 9. PUBLISH & SYNC WORKFLOW BACKEND
function publishVisualWorkflow() {
    const container = document.getElementById("flowNodesContainer");
    if (!container) return;
    const wrappers = Array.from(container.children);
    
    if (wrappers.length === 0) {
        if (!confirm("Warning: You are about to publish an empty workflow with 0 approval stages. Activity submissions will auto-approve immediately. Continue?")) return;
    }

    const stagesPayload = wrappers.map((w, idx) => {
        return {
            id: w.dataset.stageId || null,
            stage_order: idx + 1,
            name: w.dataset.name,
            description: w.dataset.desc || '',
            roles: w.dataset.role.split(",").map(r => r.trim()),
            allowed_actions: JSON.parse(w.dataset.actions || '["approve","discuss","reject"]'),
            approval_condition: w.dataset.condition || 'any_one',
            is_mandatory: w.dataset.mandatory === 'true',
            is_active: w.dataset.active === 'true',
            allow_parallel: w.dataset.parallel === 'true'
        };
    });

    const btn = document.getElementById("publishWorkflowBtn");
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Syncing Pipeline...`;

    fetch("{{ route('superadmin.workflows.syncVisual') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({
            workflow_id: {{ $activeWorkflow->id ?? 'null' }},
            delete_missing: true,
            stages: stagesPayload
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            hasUnsavedVisualChanges = false;
            btn.classList.remove("pulse-warning", "btn-warning", "text-dark");
            btn.classList.add("btn-success");
            btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Workflow Published & Synced`;
            
            // Show toast or alert
            const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show shadow-sm mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            const canvasContainer = document.getElementById("visualFlowCanvas");
            if (canvasContainer) canvasContainer.insertAdjacentHTML("afterbegin", alertHtml);

            setTimeout(() => { window.location.reload(); }, 1200);
        } else {
            alert("Failed to publish workflow: " + (data.message || "Unknown error"));
            btn.innerHTML = originalText;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert("Error communicating with workflow server: " + err.message);
    });
}
</script>
@endpush

@endsection
