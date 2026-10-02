@php
    $fieldName = fn (string $field) => $prefix === '' ? $field : $prefix.'['.$field.']';
    $fieldValue = fn (string $field, $default = '') => $values[$field] ?? $default;
@endphp

<div class="pairing-field-group">
    <div class="pairing-field-group-title">Informasi Umum</div>
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Project</label>
            <input type="text" name="{{ $fieldName('project') }}" class="form-control"
                list="pairing-project-options" value="{{ $fieldValue('project') }}" placeholder="Contoh: 560B" required>
        </div>
        <div class="col-md-9">
            <label class="form-label">Part Number Customer</label>
            <input type="text" name="{{ $fieldName('part_number_customer') }}" class="form-control"
                value="{{ $fieldValue('part_number_customer') }}" required>
        </div>
    </div>
</div>

<div class="pairing-field-group">
    <div class="pairing-field-group-title">Kanban Assembly</div>
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Part Number</label>
            <input type="text" name="{{ $fieldName('assembly_part') }}" class="form-control"
                value="{{ $fieldValue('assembly_part') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Back Number</label>
            <input type="text" name="{{ $fieldName('assembly_back_number') }}" class="form-control"
                value="{{ $fieldValue('assembly_back_number') }}" required>
        </div>
        <div class="col-md-1">
            <label class="form-label">Qty</label>
            <input type="number" name="{{ $fieldName('qty_assy') }}" class="form-control" min="1"
                value="{{ $fieldValue('qty_assy', 1) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nama Part Assembly</label>
            <input type="text" name="{{ $fieldName('part_name') }}" class="form-control"
                value="{{ $fieldValue('part_name') }}" required>
        </div>
    </div>
</div>

<div class="pairing-field-group mb-0">
    <div class="pairing-field-group-title">Kanban Painting</div>
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Part Number</label>
            <input type="text" name="{{ $fieldName('painting_part') }}" class="form-control"
                value="{{ $fieldValue('painting_part') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Back Number</label>
            <input type="text" name="{{ $fieldName('painting_back_number') }}" class="form-control"
                value="{{ $fieldValue('painting_back_number') }}" required>
        </div>
        <div class="col-md-1">
            <label class="form-label">Qty</label>
            <input type="number" name="{{ $fieldName('qty_painting') }}" class="form-control" min="1"
                value="{{ $fieldValue('qty_painting', 1) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Product / Nama Part Painting</label>
            <input type="text" name="{{ $fieldName('product') }}" class="form-control"
                value="{{ $fieldValue('product') }}" required>
        </div>
    </div>
</div>
