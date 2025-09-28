
@extends('partials.layouts.master3')

@section('title', 'Sistema de gestión nutricional Hospitalaria')
@section('sub-title', 'Inicio' )
@section('buttonTitle', 'Share')
@section('link', '#!')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-hover overflow-hidden">
                <div class="card-body hstack gap-2">
                    <div class="avatar avatar-item rounded-2">
                        <i class="ri-group-line"></i>
                    </div>
                    <div>
                        <span class="mb-2 fs-12 text-muted">Number of Students</span>
                        <h5 class="fw-medium mb-1">1,200</h5>
                    </div>
                </div>
                <div class="card-body bg-light py-2 bg-opacity-40 hstack justify-content-between gap-3">
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">Active Students:</h6>
                        <p class="fs-12 text-muted mb-0">1,000</p>
                    </div>
                    <div class="vr h-30px align-self-center bg-light"></div>
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">New Students:</h6>
                        <p class="fs-12 text-muted mb-0">200</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Courses Card -->
        <div class="col-lg-4">
            <div class="card card-hover overflow-hidden">
                <div class="card-body hstack gap-2">
                    <div class="avatar avatar-item rounded-2">
                        <i class="ri-book-line"></i>
                    </div>
                    <div>
                        <span class="mb-2 fs-12 text-muted">Total Courses</span>
                        <h5 class="fw-medium mb-1">30</h5>
                    </div>
                </div>
                <div class="card-body bg-light py-2 bg-opacity-40 hstack justify-content-between gap-3">
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">Active Courses:</h6>
                        <p class="fs-12 text-muted mb-0">25</p>
                    </div>
                    <div class="vr h-30px align-self-center bg-light"></div>
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">Archived:</h6>
                        <p class="fs-12 text-muted mb-0">5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructor Performance Card -->
        <div class="col-lg-4">
            <div class="card card-hover overflow-hidden">
                <div class="card-body hstack gap-2">
                    <div class="avatar avatar-item rounded-2">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <div>
                        <span class="mb-2 fs-12 text-muted">Instructor Performance</span>
                        <h5 class="fw-medium mb-1">John Doe - 4.8/5</h5>
                    </div>
                </div>
                <div class="card-body bg-light py-2 bg-opacity-40 hstack justify-content-between gap-3">
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">Completion Rate:</h6>
                        <p class="fs-12 text-muted mb-0">85%</p>
                    </div>
                    <div class="vr h-30px align-self-center bg-light"></div>
                    <div class="hstack gap-3">
                        <h6 class="mb-0 fw-semibold">New Reviews:</h6>
                        <p class="fs-12 text-muted mb-0">15</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h6 class="card-title mb-0">Calendario de menús</h6>

                </div>
                <div class="card-body">
                    @include('calendars.show')
                </div>
            </div>
            <div class="swiper leave-application-swiper">
                <div class="swiper-wrapper">
                    <!-- First leave application (already existing) -->
                    <div class="swiper-slide">
                        <div class="card mb-0 border border-light">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="hstack gap-2 overflow-hidden">
                                        <img class="avatar-lg avatar-item rounded-2 border-0" src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="student">
                                        <div class="overflow-hidden">
                                            <h6 class="hstack gap-2 mb-1 text-truncate">
                                                <a href="#!">James</a><span class="badge bg-danger-subtle text-danger">Emergency</span>
                                            </h6>
                                            <p class="text-truncate text-muted fs-13 mb-0">Physics Teacher</p>
                                        </div>
                                    </div>
                                    <div class="hstack gap-2">
                                        <a href="#!" class="btn icon-btn-sm btn-success"><i class="ri-check-double-line"></i></a>
                                        <a href="#!" class="btn icon-btn-sm btn-danger"><i class="ri-close-line"></i></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                    <p class="mb-0 text-muted fs-12">Leave : <span class="fw-semibold text-body">12 -13 May</span></p>
                                    <p class="mb-0 text-muted fs-12">Apply on : <span class="fw-semibold text-body">12 May</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second leave application -->
                    <div class="swiper-slide">
                        <div class="card mb-0 border border-light">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="hstack gap-2 overflow-hidden">
                                        <img class="avatar-lg avatar-item rounded-2 border-0" src="{{ asset('assets/images/avatar/avatar-2.jpg') }}" alt="student">
                                        <div class="overflow-hidden">
                                            <h6 class="hstack gap-2 mb-1 text-truncate">
                                                <a href="#!">Sophia</a><span class="badge bg-warning-subtle text-warning">Sick</span>
                                            </h6>
                                            <p class="text-truncate text-muted fs-13 mb-0">Mathematics Teacher</p>
                                        </div>
                                    </div>
                                    <div class="hstack gap-2">
                                        <a href="#!" class="btn icon-btn-sm btn-success"><i class="ri-check-double-line"></i></a>
                                        <a href="#!" class="btn icon-btn-sm btn-danger"><i class="ri-close-line"></i></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                    <p class="mb-0 text-muted fs-12">Leave : <span class="fw-semibold text-body">15 - 16 May</span></p>
                                    <p class="mb-0 text-muted fs-12">Apply on : <span class="fw-semibold text-body">14 May</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Third leave application -->
                    <div class="swiper-slide">
                        <div class="card mb-0 border border-light">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="hstack gap-2 overflow-hidden">
                                        <img class="avatar-lg avatar-item rounded-2 border-0" src="{{ asset('assets/images/avatar/avatar-3.jpg') }}" alt="student">
                                        <div class="overflow-hidden">
                                            <h6 class="hstack gap-2 mb-1 text-truncate">
                                                <a href="#!">Liam</a><span class="badge bg-info-subtle text-info">Personal</span>
                                            </h6>
                                            <p class="text-truncate text-muted fs-13 mb-0">Computer Science Teacher</p>
                                        </div>
                                    </div>
                                    <div class="hstack gap-2">
                                        <a href="#!" class="btn icon-btn-sm btn-success"><i class="ri-check-double-line"></i></a>
                                        <a href="#!" class="btn icon-btn-sm btn-danger"><i class="ri-close-line"></i></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                    <p class="mb-0 text-muted fs-12">Leave : <span class="fw-semibold text-body">18 - 20 May</span></p>
                                    <p class="mb-0 text-muted fs-12">Apply on : <span class="fw-semibold text-body">17 May</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fourth leave application -->
                    <div class="swiper-slide">
                        <div class="card mb-0 border border-light">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="hstack gap-2 overflow-hidden">
                                        <img class="avatar-lg avatar-item rounded-2 border-0" src="{{ asset('assets/images/avatar/avatar-4.jpg') }}" alt="student">
                                        <div class="overflow-hidden">
                                            <h6 class="hstack gap-2 mb-1 text-truncate">
                                                <a href="#!">Oliver</a><span class="badge bg-success-subtle text-success">Vacation</span>
                                            </h6>
                                            <p class="text-truncate text-muted fs-13 mb-0">History Teacher</p>
                                        </div>
                                    </div>
                                    <div class="hstack gap-2">
                                        <a href="#!" class="btn icon-btn-sm btn-success"><i class="ri-check-double-line"></i></a>
                                        <a href="#!" class="btn icon-btn-sm btn-danger"><i class="ri-close-line"></i></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                    <p class="mb-0 text-muted fs-12">Leave : <span class="fw-semibold text-body">25 - 27 May</span></p>
                                    <p class="mb-0 text-muted fs-12">Apply on : <span class="fw-semibold text-body">24 May</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fifth leave application -->
                    <div class="swiper-slide">
                        <div class="card mb-0 border border-light">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="hstack gap-2 overflow-hidden">
                                        <img class="avatar-lg avatar-item rounded-2 border-0" src="{{ asset('assets/images/avatar/avatar-5.jpg') }}" alt="student">
                                        <div class="overflow-hidden">
                                            <h6 class="hstack gap-2 mb-1 text-truncate">
                                                <a href="#!">Emma</a><span class="badge bg-primary-subtle text-primary">Training</span>
                                            </h6>
                                            <p class="text-truncate text-muted fs-13 mb-0">Chemistry Teacher</p>
                                        </div>
                                    </div>
                                    <div class="hstack gap-2">
                                        <a href="#!" class="btn icon-btn-sm btn-success"><i class="ri-check-double-line"></i></a>
                                        <a href="#!" class="btn icon-btn-sm btn-danger"><i class="ri-close-line"></i></a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                    <p class="mb-0 text-muted fs-12">Leave : <span class="fw-semibold text-body">1 - 3 June</span></p>
                                    <p class="mb-0 text-muted fs-12">Apply on : <span class="fw-semibold text-body">30 May</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Weekly Activity Overview</h5>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#!">Refresh</a></li>
                            <li><a class="dropdown-item" href="#!">View Full Report</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="orderAnalyticsDashboard" class="apexcharts-container apexcharts-white"></div>
                    <p class="text-muted fs-13 mt-3">Activity tracking for the entire week, with hours logged each day.</p>

                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Total Active Hours</span>
                                <h6 class="mt-1 mb-0">35 hrs</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Active Days</span>
                                <h6 class="mt-1 mb-0">5 Days</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Top Categories</h5>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#!">Add Category</a></li>
                            <li><a class="dropdown-item" href="#!">Edit Categories</a></li>
                            <li><a class="dropdown-item" href="#!">Generate Report</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0 online-course-scroll" data-simplebar>
                    <ul class="d-flex flex-column list-group">
                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-primary bg-primary-subtle">
                                    <i class="ri-dashboard-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">UI / UX Design</h6>
                                    <p class="fs-12 text-muted mb-0">10,000 + Courses</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$199.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-secondary bg-secondary-subtle">
                                    <i class="ri-brush-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">UI/UX Design & Development</h6>
                                    <p class="fs-12 text-muted mb-0">25,000+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$299.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-success bg-success-subtle">
                                    <i class="ri-code-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Full Stack Web Development</h6>
                                    <p class="fs-12 text-muted mb-0">18,500+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$249.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-danger bg-danger-subtle">
                                    <i class="ri-database-2-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Database Management & SQL</h6>
                                    <p class="fs-12 text-muted mb-0">22,000+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$199.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-warning bg-warning-subtle">
                                    <i class="ri-smartphone-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Mobile App Development</h6>
                                    <p class="fs-12 text-muted mb-0">15,000+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$249.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-primary bg-primary-subtle">
                                    <i class="ri-search-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Digital Marketing & SEO</h6>
                                    <p class="fs-12 text-muted mb-0">12,500+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$179.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-info bg-info-subtle">
                                    <i class="ri-camera-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Photography & Video Editing</h6>
                                    <p class="fs-12 text-muted mb-0">8,500+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$159.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-danger bg-danger-subtle">
                                    <i class="ri-robot-line fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Artificial Intelligence & Machine Learning</h6>
                                    <p class="fs-12 text-muted mb-0">20,000+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$299.99</span>
                            </div>
                        </li>

                        <li class="list-group-item list-group-item-action border-0">
                            <div class="hstack">
                                <span class="avatar avatar-item border-0 rounded-3 flex-shrink-0 text-primary bg-primary-subtle">
                                    <i class="bi bi-shield-lock fs-20 leading-none"></i>
                                </span>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fw-semibold mb-0">Cybersecurity & Ethical Hacking</h6>
                                    <p class="fs-12 text-muted mb-0">17,500+ Enrollments</p>
                                </div>
                                <span class="fs-14 fw-medium flex-shrink-0">$279.99</span>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Top Mentors</h5>
                    <a href="apps-course-teacher-list" class="btn btn-light btn-sm flex-shrink-0">View All</a>
                </div>
                <div class="card-body h-500px" data-simplebar>
                    <table class="table align-middle table-borderless table-centered table-nowrap mb-0">
                        <thead class="text-muted bg-light bg-opacity-40">
                            <tr>
                                <th scope="col">Mentor Name</th>
                                <th scope="col">Expertise</th>
                                <th scope="col">Course</th>
                                <th scope="col">Experience</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-2.jpg') }}" alt="Full Stack Development Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Caleb Riv</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Web Designer</td>
                                <td>110</td>
                                <td>12 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="Web Design Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Maria Stone</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Full Stack Developer</td>
                                <td>98</td>
                                <td>8 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-3.jpg') }}" alt="UI/UX Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Samuel Lee</p>
                                        </a>
                                    </div>
                                </td>
                                <td>UI/UX Designer</td>
                                <td>120</td>
                                <td>9 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-4.jpg') }}" alt="Data Science Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Nina Patel</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Data Scientist</td>
                                <td>75</td>
                                <td>10 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-5.jpg') }}" alt="Marketing Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">John Carter</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Digital Marketer</td>
                                <td>105</td>
                                <td>6 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-7.jpg') }}" alt="Photography Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Olivia Green</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Mobile App Developer</td>
                                <td>90</td>
                                <td>7 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-6.jpg') }}" alt="Mobile App Development Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Lucas Gray</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Photographer</td>
                                <td>60</td>
                                <td>15 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-8.jpg') }}" alt="AI & Machine Learning Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Ethan Black</p>
                                        </a>
                                    </div>
                                </td>
                                <td>AI Engineer</td>
                                <td>85</td>
                                <td>11 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-9.jpg') }}" alt="SQL & Database Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Sophia King</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Database Administrator</td>
                                <td>130</td>
                                <td>14 Years</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="Blockchain Development Course" class="avatar rounded-2">
                                        <a href="#!" class="text-body">
                                            <p class="mb-0 fw-medium text-truncate">Daniel Scott</p>
                                        </a>
                                    </div>
                                </td>
                                <td>Blockchain Developer</td>
                                <td>50</td>
                                <td>5 Years</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Top Courses</h5>
                    <div class="flex-shrink-0">
                        <a href="apps-course-overview" class="link-primary">View All <i class="ri-arrow-right-line"></i></a>
                    </div>
                </div>
                <div class="card-body h-500px" data-simplebar>
                    <ul class="vstack gap-4 list-unstyled mb-0">
                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-3.jpg') }}" alt="Health Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">UX Design</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Richardino Gueva</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate"> Mastering CSS Pseudo-classes: From Basics to Advanced Techniques.</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>2,189 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        (4.2)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-4.jpg') }}" alt="Web Development Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Web Development</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Jane Smith</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Responsive Web Design: Creating Seamless Experiences Across Devices</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>1,875 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (3.8)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-5.jpg') }}" alt="Photography Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Photography</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Emily Williams</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Mastering Light: Advanced Photography Techniques for Stunning Images</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>3,542 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        (4.5)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-6.jpg') }}" alt="Digital Marketing Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Digital Marketing</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Michael Jordan</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">SEO Strategies: Boosting Your Website's Traffic and Visibility</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>5,420 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (4.3)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-7.jpg') }}" alt="AI Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Artificial Intelligence</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Sarah Brown</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Introduction to AI: Understanding the Basics and Future Prospects</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>2,345 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (4.1)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-8.jpg') }}" alt="Blockchain Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Blockchain</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By David Harris</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Understanding Blockchain Technology: From Basics to Applications</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>4,123 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (4.4)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-9.jpg') }}" alt="Cybersecurity Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Cybersecurity</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Olivia Johnson</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Best Practices for Securing Your Online Presence and Protecting Data</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>6,321 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (4.6)
                                    </p>
                                </div>
                            </div>
                        </li>

                        <li class="hstack gap-3">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/small/img-10.jpg') }}" alt="Game Development Article" class="img-fluid rounded avatar-lg">
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <p class="mb-0 text-primary fw-semibold fs-12">Game Development</p>
                                    <span class="w-4px h-4px align-middle rounded-circle bg-secondary-subtle"></span>
                                    <p class="mb-0 text-muted fw-medium fs-12">By Lucas Morrow</p>
                                </div>
                                <a href="#!" class="d-block mb-1 fw-semibold text-body text-truncate">Building Interactive Worlds: A Beginner's Guide to Game Development</a>
                                <div class="d-flex flex-wrap align-items-center gap-2 fs-12">
                                    <p class="mb-0 text-muted fw-medium"><i class="ri-eye-line me-1"></i>1,800 Views</p>
                                    <p class="mb-0 text-muted fw-medium">
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-fill text-warning"></i>
                                        <i class="ri-star-half-fill text-warning"></i>
                                        <i class="ri-star-line text-muted"></i>
                                        (4.0)
                                    </p>
                                </div>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('js')

    <!-- Countup init -->
    <script type="module" src="{{ asset('assets/js/pages/countup.init.js') }}"></script>

    <!-- Swiper init -->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- ApexChat js -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Online Course Dashboard init -->
    <script src="{{ asset('assets/js/charts/apexcharts-config.init.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards/dashboard-online-course.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
