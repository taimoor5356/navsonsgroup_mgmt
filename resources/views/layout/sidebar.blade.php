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
        <li class="menu-item @if(Request::url() == url('admin/services/types')) active @endif">
          <a href="{{url('admin/services/types')}}" class="menu-link">
            <div data-i18n="Basic Inputs">Services Types</div>
          </a>
        </li>
      </ul>
    </li>
    @php
        $currentUrl = Request::url();
        $menuUrls = [
            url('admin/accounts/expenses/list'),
            url('admin/accounts/fines/list'),
            url('admin/accounts/payment-modes/list'),
            url('admin/accounts/create')
        ];

        $isActive = in_array($currentUrl, $menuUrls);
    @endphp

    <li class="menu-item {{ $isActive ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-money"></i>
        <div data-i18n="Form Elements">Accounts/Finances</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentUrl == url('admin/accounts/expenses/list') ? 'active' : '' }}">
          <a href="{{ url('admin/accounts/expenses/list') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Expenses</div>
          </a>
        </li>
        <li class="menu-item {{ $currentUrl == url('admin/accounts/fines/list') ? 'active' : '' }}">
          <a href="{{ url('admin/accounts/fines/list') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Fines</div>
          </a>
        </li>
        <li class="menu-item {{ $currentUrl == url('admin/accounts/payment-modes/list') ? 'active' : '' }}">
          <a href="{{ url('admin/accounts/payment-modes/list') }}" class="menu-link">
            <div data-i18n="Basic Inputs">Payment Types</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item @if(Request::url() == url('admin/inventory/list') || Request::url() == url('admin/inventory/create')) active open @endif">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-box"></i>
        <div data-i18n="Form Elements">Inventory</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item @if(Request::url() == url('admin/inventory/expenses')) active @endif">
          <a href="{{url('admin/inventory/expenses')}}" class="menu-link">
            <div data-i18n="Basic Inputs">Items</div>
          </a>
        </li>
        <li class="menu-item @if(Request::url() == url('admin/inventory/fines')) active @endif">
          <a href="{{url('admin/inventory/fines')}}" class="menu-link">
            <div data-i18n="Basic Inputs">Categories</div>
          </a>
        </li>
      </ul>
    </li>
    <!-- <li class="menu-item @if(Request::url() == url('admin/expenses/list') || Request::url() == url('admin/expenses/create') || Request::segment(2) == 'expenses') active @endif">
      <a href="{{url('admin/expenses/list')}}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-cut"></i>
        <div data-i18n="Basic">Expenses</div>
      </a>
    </li> -->
    <!-- <li class="menu-item @if(Request::url() == url('admin/groups') || Request::url() == url('admin/groups/create') || Request::segment(2) == 'groups') active @endif">
      <a href="#" class="menu-link">
        <i class="menu-icon tf-icons bx bx-menu"></i>
        <div data-i18n="Basic">Inventory</div>
      </a>
    </li> -->
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
    <!-- <li class="menu-item @if(Request::url() == url('admin/fines/list') || Request::url() == url('admin/fines/create') || Request::segment(2) == 'fines') active @endif">
      <a href="{{url('admin/fines/list')}}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-cut"></i>
        <div data-i18n="Basic">Fines</div>
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