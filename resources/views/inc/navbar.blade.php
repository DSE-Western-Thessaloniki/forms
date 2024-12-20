<nav class="navbar navbar-expand-md navbar-dark navbar-laravel bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
            </ul>
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @php
                    if (cas()->isAuthenticated()) {
                        $teacher_uid = cas()->getAttribute('employeenumber');
                        $login_category = cas()->getAttribute('businesscategory');
                        if ($login_category === 'ΕΚΠΑΙΔΕΥΤΙΚΟΣ' || $login_category === 'ΠΡΟΣΩΠΙΚΟ') {
                            // Εκπαιδευτικός
                            $school = null;
                            $teacher_name = cas()->getAttribute('cn');
                        } else {
                            // Σχολείο
                            $school = App\Models\School::where('username', cas()->getAttribute('uid'))
                                ->orWhere('email', cas()->getAttribute('mail'))
                                ->first();
                            $teacher_name = null;
                        }
                    }
                @endphp
                @if (cas()->isAuthenticated() && ($school || $teacher_name))
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ $school?->name }} {{ $teacher_name ?? '' }}
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                    document.getElementById('logout-form').submit();">
                                Αποσύνδεση
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}" test-data-id="nav-item-login">Σύνδεση</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
