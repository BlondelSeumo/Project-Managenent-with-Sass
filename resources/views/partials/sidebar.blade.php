<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ route('home') }}">
            <img src="{{asset(Storage::url('logo/logo-full.png'))}}" alt="{{ env('APP_NAME') }}" height="35">
        </a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="{{ route('home') }}"><img src="{{asset(Storage::url('logo/logo.png'))}}" alt="{{ env('APP_NAME') }}" height="25"></a>
    </div>
    <ul class="sidebar-menu">
        <li class="{{ (Request::route()->getName() == 'home' || Request::route()->getName() == NULL) ? ' active' : '' }}">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="dripicons-home"></i> <span> {{ __('Dashboard') }} </span>
            </a>
        </li>
        @if(isset($currantWorkspace) && $currantWorkspace)
            <li class="{{ (Request::route()->getName() == 'projects.index') ? ' active' : '' }}">
                <a class="nav-link" href="@auth('web'){{ route('projects.index',$currantWorkspace->slug) }}@elseauth{{ route('client.projects.index',$currantWorkspace->slug) }}@endauth">
                    <i class="dripicons-briefcase"></i>
                    <span> {{ __('Projects') }} </span>
                </a>
            </li>
            @auth('web')
                <li class="{{ (Request::route()->getName() == 'tasks.index') ? ' active' : '' }}">
                    <a class="nav-link" href="{{ route('tasks.index',$currantWorkspace->slug) }}">
                        <i class="dripicons-list"></i>
                        <span> {{ __('Tasks') }} </span>
                    </a>
                </li>
                <li class="nav-item {{ (Request::route()->getName() == 'timesheet.index') ? ' active' : '' }}">
                    <a class="nav-link" href="{{route('timesheet.index',$currantWorkspace->slug)}}">
                        <i class="dripicons-clock"></i>
                        <span> {{ __('Timesheet') }} </span>
                    </a>
                </li>
                @if($currantWorkspace->creater->id == Auth::user()->id)
                    <li class="{{ (Request::route()->getName() == 'invoices.index') ? ' active' : '' }}">
                        <a href="{{ route('invoices.index',$currantWorkspace->slug) }}">
                            <i class="dripicons-print"></i>
                            <span> {{ __('Invoices') }} </span>
                        </a>
                    </li>
                    <li class="{{ (Request::route()->getName() == 'clients.index') ? ' active' : '' }}">
                        <a href="{{ route('clients.index',$currantWorkspace->slug) }}">
                            <i class="dripicons-user"></i>
                            <span> {{ __('Clients') }} </span>
                        </a>
                    </li>
                @endif
                <li class="{{ (Request::route()->getName() == 'users.index') ? ' active' : '' }}">
                    <a href="{{ route('users.index',$currantWorkspace->slug) }}">
                        <i class="dripicons-network-3"></i>
                        <span> {{ __('Users') }} </span>
                    </a>
                </li>
                <li class="{{ (Request::route()->getName() == 'calender.index') ? ' active' : '' }}">
                    <a href="{{route('calender.index',$currantWorkspace->slug)}}">
                        <i class="dripicons-calendar"></i>
                        <span> {{ __('Calender') }} </span>
                    </a>
                </li>

                <li class="{{ (Request::route()->getName() == 'notes.index') ? ' active' : '' }}">
                    <a href="{{route('notes.index',$currantWorkspace->slug)}}">
                        <i class="dripicons-clipboard"></i>
                        <span> {{ __('Notes') }} </span>
                    </a>
                </li>
                @if(env('CHAT_MODULE') == 'yes')
                    <li class="{{ (Request::route()->getName() == 'chats.index') ? ' active' : '' }}">
                        <a href="{{ route('chats.index',$currantWorkspace->slug) }}">
                            <i class="dripicons-message"></i>
                            <span> {{ __('Chat') }} </span>
                        </a>
                    </li>
                @endif
            @elseauth
                <li class="nav-item {{ (Request::route()->getName() == 'client.timesheet.index') ? ' active' : '' }}">
                    <a class="nav-link" href="{{route('client.timesheet.index',$currantWorkspace->slug)}}">
                        <i class="dripicons-clock"></i>
                        <span> {{ __('Timesheet') }} </span>
                    </a>
                </li>
                <li class="{{ (Request::route()->getName() == 'client.invoices.index') ? ' active' : '' }}">
                    <a href="{{ route('client.invoices.index',$currantWorkspace->slug) }}">
                        <i class="dripicons-print"></i>
                        <span> {{ __('Invoices') }} </span>
                    </a>
                </li>
                <li class="{{ (Request::route()->getName() == 'client.calender.index') ? ' active' : '' }}">
                    <a href="{{route('client.calender.index',$currantWorkspace->slug)}}">
                        <i class="dripicons-calendar"></i>
                        <span> {{ __('Calender') }} </span>
                    </a>
                </li>
            @endauth
        @endif
        @if(Auth::user()->type == 'admin')
            <li class="{{ (Request::route()->getName() == 'users.index') ? ' active' : '' }}">
                <a href="{{ route('users.index','') }}">
                    <i class="dripicons-user-group"></i>
                    <span> {{ __('Users') }} </span>
                </a>
            </li>
        @endif
        @if(Auth::user()->type == 'admin' || ($currantWorkspace && $currantWorkspace->creater->id == Auth::user()->id))
            <li class="{{ (Request::route()->getName() == 'plans.index') ? ' active' : '' }}">
                <a href="{{ route('plans.index') }}">
                    <i class="dripicons-trophy"></i>
                    <span> {{ __('Plans') }} </span>
                </a>
            </li>
            <li class="{{ (Request::route()->getName() == 'order.index') ? ' active' : '' }}">
                <a href="{{ route('order.index') }}">
                    <i class="dripicons-card"></i>
                    <span> {{ __('Orders') }} </span>
                </a>
            </li>
        @endif

        @if(Auth::user()->type == 'admin')
            <li class="{{ (Request::route()->getName() == 'coupons.index') ? ' active' : '' }}">
                <a href="{{ route('coupons.index') }}">
                    <i class="dripicons-tag"></i>
                    <span> {{ __('Coupons') }} </span>
                </a>
            </li>
            <li class="{{ (Request::route()->getName() == 'coupon.index') ? ' active' : '' }}">
                <a href="{{ route('settings.index') }}">
                    <i class="dripicons-gear"></i>
                    <span> {{ __('Settings') }} </span>
                </a>
            </li>

        @endif

        @if(isset($currantWorkspace) && $currantWorkspace->creater->id == Auth::user()->id)
            <li class="{{ (Request::route()->getName() == 'workspace.settings') ? ' active' : '' }}">
                <a href="{{ route('workspace.settings',$currantWorkspace->slug) }}">
                    <i class="dripicons-gear"></i>
                    <span> {{ __('Workspace Settings') }} </span>
                </a>
            </li>
        @endif
    </ul>
</aside>
