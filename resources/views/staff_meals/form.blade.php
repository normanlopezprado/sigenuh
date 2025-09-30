@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $beneficiary->full_name) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Job title</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $beneficiary->job_title) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="1" @selected(old('status', $beneficiary->status ?? true))>Active</option>
            <option value="0" @selected(!old('status', $beneficiary->status ?? true))>Inactive</option>
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Hospital</label>
        <input type="text" class="form-control" placeholder="(optional) set from user in controller" disabled>
        {{-- Si quieres un select real de hospitales, pásalo desde el controlador y pinta aquí --}}
        <input type="hidden" name="hospital_id" value="{{ old('hospital_id', $beneficiary->hospital_id) }}">
    </div>
</div>
