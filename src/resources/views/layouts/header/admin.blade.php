<nav class="header-inner__nav">
    <ul class="nav-list">
        <li class="nav-item"><a class="nav-item__a" href="{{ route('admin.attendances.index') }}">勤怠一覧</a></li>
        <li class="nav-item"><a class="nav-item__a" href="{{ route('admin.staff.index')}}">スタッフ一覧</a></li>
        <li class="nav-item"><a class="nav-item__a" href="{{ route('admin.correction_requests.index') }}">申請一覧</a></li>
        <li class="nav-item">
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button class="logout-btn">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>