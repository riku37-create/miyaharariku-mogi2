<nav class="header-inner__nav">
    <ul class="nav-list">
        <li class="nav-item"><a class="nav-item__a" href="/attendance">勤怠</a></li>
        <li class="nav-item"><a class="nav-item__a" href="{{ route('staff.attendances.index') }}">勤怠一覧</a></li>
        <li class="nav-item"><a class="nav-item__a" href="{{ route('staff.correction_requests.index') }}">申請一覧</a></li>
        <li class="nav-item">
            @if (Auth::check())
            <form action="/logout" method="post">
                @csrf
                <button class="logout-btn">ログアウト</button>
            </form>
            @else
                <a class="nav-item__a" href="/login">ログイン</a>
            @endif
        </li>
    </ul>
</nav>