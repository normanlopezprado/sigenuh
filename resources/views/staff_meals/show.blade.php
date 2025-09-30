@extends('partials.layouts.master2')
@section('title', 'Beneficiary')
@section('content')
<div class="container-fluid max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header"><strong>Beneficiary</strong></div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Full name</dt>
                <dd class="col-sm-9">{{ $beneficiary->full_name }}</dd>

                <dt class="col-sm-3">Job title</dt>
                <dd class="col-sm-9">{{ $beneficiary->job_title }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $beneficiary->status ? 'Active' : 'Inactive' }}</dd>
            </dl>
            <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>
</div>
@endsection
