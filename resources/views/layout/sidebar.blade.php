<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="#" class="app-brand-link">
      <span class="app-brand-logo demo">
        
      </span>
      <span class="app-brand-text menu-text fw-bolder ms-2" style="font-size: x-large;">NSG MGMT</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>
  @if(Auth::user()->hasRole(['admin', 'manager', 'user']))
  <ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item @if(Request::url() == url('admin/dashboard')) active @endif">
      <a href="{{url('admin/dashboard')}}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Analytics">Dashboard</div>
      </a>
    </li>

    <!-- Components -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Navsons Group</span></li>
    <!-- Cards -->
    <li class="menu-item @if(Request::url() == url('admin/services/list') || Request::url() == url('admin/services/create')) active open @endif">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div data-i18n="Form Elements">Services</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item @if(Request::url() == url('admin/services/create')) active @endif">
          <a href="{{url('admin/services/create')}}" class="menu-link">
            <div data-i18n="Basic Inputs">Add New</div>
          </a>
        </li>
        <li class="menu-item @if(Request::url() == url('admin/services/list')) active @endif">
          <a href="{{url('admin/services/list')}}" class="menu-link">
            <div data-i18n="Basic Inputs">Wash Services</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item @if(Request::url() == url('admin/expenses/list') || Request::url() == url('admin/expenses/create') || Request::segment(2) == 'expenses') active @endif">
      <a href="{{url('admin/expenses/list')}}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-cut"></i>
        <div data-i18n="Basic">Expenses</div>
      </a>
    </li>
    <li class="menu-item @if(Request::url() == url('admin/groups') || Request::url() == url('admin/groups/create') || Request::segment(2) == 'groups') active @endif">
      <a href="#" class="menu-link">
        <i class="menu-icon tf-icons bx bx-menu"></i>
        <div data-i18n="Basic">Inventory</div>
      </a>
    </li>
    <li class="menu-item @if(Request::url() == url('admin/users/list/users') || Request::url() == url('admin/users/list/customers') || Request::url() == url('admin/users/create')) active open @endif">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-group"></i>
        <div data-i18n="Form Elements">Users</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item @if(Request::url() == url('admin/users/list/customers')) active @endif">
          <a href="{{ url('admin/users/list/customers') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Customers</div>
          </a>
        </li>
        <li class="menu-item @if(Request::url() == url('admin/users/list/users')) active @endif">
          <a href="{{ url('admin/users/list/users') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Users (Employees)</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item @if(Request::url() == url('admin/previous-payments') || Request::url() == url('admin/previous-payments/create') || Request::segment(2) == 'previous-payments') active @endif">
      <a href="#" class="menu-link">
        <i class="menu-icon tf-icons bx bx-money"></i>
        <div data-i18n="Basic">Payments</div>
      </a>
    </li>
    <!-- <li class="menu-item @if(Request::url() == url('admin/files') || Request::url() == url('admin/files/create') || Request::segment(2) == 'files') active @endif">
      <a href="{{url('admin/files')}}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file"></i>
        <div data-i18n="Basic">Files</div>
      </a>
    </li> -->

    <!-- Forms & Tables -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Access Control List (ACL)</span></li>
    <!-- Forms -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-lock"></i>
        <div data-i18n="Form Elements">ACL</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ url('admin/acl/roles') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Roles</div>
          </a>
        </li>
      </ul>
    </li>
  </ul>
  @endif
</aside>