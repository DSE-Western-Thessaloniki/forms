<nav class="navbar navbar-expand-md navbar-dark navbar-laravel bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/home') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                @auth('web')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.form.index') }}" test-data-id="nav-item-forms">
                            Φόρμες
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.school.index') }}" test-data-id="nav-item-schools">
                            Σχολεία
                        </a>
                    </li>
                    @if(Auth::user()->roles->where('name', 'Administrator')->count() || Auth::user()->roles->where('name', 'Author')->count())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.list.index') }}" test-data-id="nav-item-lists">
                            Λίστες
                        </a>
                    </li>
                    @endif
                    @if(Auth::user()->roles->where('name', 'Administrator')->count())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.user.index') }}" test-data-id="nav-item-users">
                                Χρήστες
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.options.index') }}" test-data-id="nav-item-users">
                                Επιλογές
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest('web')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.login') }}" test-data-id="nav-item-login">Σύνδεση</a>
                    </li>
                @endguest
                @auth('web')
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            test-data-id="navbar-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" v-pre>
                            {{ Auth::guard('web')->user()->name }} <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('admin.user.change_password', Auth::user()) }}"
                                test-data-id="navbar-item-change_password">
                                Αλλαγή κωδικού
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.logout') }}" test-data-id="navbar-item-logout"
                                onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                Αποσύνδεση
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
