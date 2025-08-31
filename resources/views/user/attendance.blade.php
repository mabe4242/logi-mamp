こちらは一般ユーザーです。

<form class="form" action="/logout" method="post">
    @csrf
    <button class="header-nav__button btn">ログアウト</button>
</form>

                <nav>
                    <ul class="header-nav">
                        @if (Auth::check())
                        <li class="header-nav__item">
                            <form class="form" action="/logout" method="post">
                                @csrf
                                <button class="header-nav__button btn">ログアウト</button>
                            </form>
                        </li>
                        @else 
                        <li class="header-nav__item">
                            <form class="form" action="/login">
                                <button class="header-nav__button btn">ログイン</button>
                            </form>
                        </li>
                        @endif
                        <li class="header-nav__item">
                            <a class="header-nav__link btn" href="">マイページ</a>
                        </li>
                    </ul>
                </nav>