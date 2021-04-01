{{--@php--}}
{{--    $current_page = Route::currentRouteName();--}}
{{--@endphp--}}

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin.index')}}">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="{{ asset('backend/img/logo.png') }}" width="50" alt="{{ config('app.name') }}">
        </div>
        <div class="sidebar-brand-text mx-3">{{ config('app.name') }}</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <li class="nav-item ">
        <a href="{{route('admin.index')}}" class="nav-link">
            <i class=""></i>
            <span>Dashboard</span></a>
    </li>
    <hr class="sidebar-divider">

    <li class="nav-item ">
        <a href="#" class="nav-link">
            <i class=""></i>
            <span>Posts</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-item collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span></span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('posts')

                    <a class="collapse-item" href="{{route('admin.posts.index')}}">Posts</a>
                @endcan
                @can('post_comments')

                    <a class="collapse-item" href="{{route('admin.post_comments.index')}}">Comments</a>
                @endcan
                @can('post_categories')

                    <a class="collapse-item" href="{{route('admin.post_categories.index')}}">Category</a>
                @endcan

            </div>
        </div>
    </li>
    @can('pages')

        <hr class="sidebar-divider">
        <li class="nav-item ">
            <a href="{{route('admin.pages.index')}}" class="nav-link">
                <i class=""></i>
                <span>Page</span></a>
        </li>
    @endcan
    @can('contact_us')

        <hr class="sidebar-divider">

        <li class="nav-item ">
            <a href="{{route('admin.contact_us.index')}}" class="nav-link">
                <i class=""></i>
                <span>Contact_us</span></a>
        </li>
    @endcan
    @can('users')

        <hr class="sidebar-divider">

        <li class="nav-item ">
            <a href="{{route('admin.users.index')}}" class="nav-link">
                <i class=""></i>
                <span>users</span></a>
        </li>
    @endcan
    @can('roles')

        <hr class="sidebar-divider">

        <li class="nav-item ">
            <a href="{{route('admin.roles.index')}}" class="nav-link">
                <i class=""></i>
                <span>Roles</span></a>
        </li>
    @endcan
    @can('admins')

        <hr class="sidebar-divider">

        <li class="nav-item ">
            <a href="{{route('admin.admin.index')}}" class="nav-link">
                <i class=""></i>
                <span>Admins</span></a>
        </li>
@endcan

<!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

<!-- End of Sidebar -->
