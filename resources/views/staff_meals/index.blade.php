@extends('partials.layouts.master2')

@section('title', 'Staff Beneficiaries')
@section('pagetitle', 'Staff Beneficiaries')

@section('content')
<div class="container-fluid max-w-5xl mx-auto">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="d-flex" method="get">
            <input type="text" name="q" value="{{ $q }}" class="form-control me-2" placeholder="Search by name">
            <button class="btn btn-outline-secondary">Search</button>
        </form>
        <a href="{{ route('staff-beneficiaries.create') }}" class="btn btn-primary">
            <i class="ri-user-add-line"></i> New Beneficiary
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Job title</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $b)
                    <tr>
                        <td>{{ $b->full_name }}</td>
                        <td>{{ $b->job_title }}</td>
                        <td>
                            <span class="badge {{ $b->status ? 'bg-success':'bg-secondary' }}">
                                {{ $b->status ? 'Active':'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-light" href="{{ route('staff-beneficiaries.show', $b) }}">View</a>
                            <a class="btn btn-sm btn-warning" href="{{ route('staff-beneficiaries.edit', $b) }}">Edit</a>
                            <form action="{{ route('staff-beneficiaries.destroy', $b) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No records.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $items->links() }}</div>
    </div>
</div>
@endsection
