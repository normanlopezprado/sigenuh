@extends('partials.layouts.master2')
@section('title', 'Edit Beneficiary')
@section('content')
<div class="container-fluid max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header"><strong>Edit Beneficiary</strong></div>
        <div class="card-body">
            <form action="{{ route('staff-beneficiaries.update', $beneficiary) }}" method="post">
                @csrf @method('PUT')
                @include('staff_beneficiaries.form', ['beneficiary' => $beneficiary])
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-light">Cancel</a>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
