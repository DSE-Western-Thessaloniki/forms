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
                        <a class="nav-link" href="{{ route('admin.form.index') }}">Φόρμες</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.school.index') }}">Σχολεία</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.user.index') }}">Χρήστες</a>
                    </li>
                @endauth
            </ul>
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest('web')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.login') }}">Σύνδεση</a>
                    </li>
                @endguest
                @auth('web')
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::guard('web')->user()->name }} <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('admin.user.change_password', Auth::user()) }}">
                                Αλλαγή κωδικού
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.logout') }}"
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
