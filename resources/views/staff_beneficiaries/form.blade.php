<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $beneficiary->full_name) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Puesto</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $beneficiary->job_title) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">
            <option value="1" @selected(old('status', $beneficiary->status ?? true))>Activo</option>
            <option value="0" @selected(!old('status', $beneficiary->status ?? true))>Inactivo</option>
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Hospital</label>
        <input type="text" class="form-control" value="Se asignará automáticamente" disabled>
    </div>
</div>
