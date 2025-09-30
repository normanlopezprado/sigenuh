@extends('partials.layouts.master2')
@section('title', 'New Beneficiary')
@section('content')
<div class="container-fluid max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header"><strong>New Beneficiary</strong></div>
        <div class="card-body">
            <form action="{{ route('staff-beneficiaries.store') }}" method="post">
                @csrf
                @include('staff_beneficiaries.form', ['beneficiary' => $beneficiary])
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-light">Cancel</a>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
